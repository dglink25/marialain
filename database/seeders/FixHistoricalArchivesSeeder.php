<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\Grade;
use App\Models\Conduct;
use App\Models\Punishment;
use App\Models\Subject;
use App\Models\StudentPayment;

/**
 * FixHistoricalArchivesSeeder
 *
 * Problème résolu :
 *   Les élèves ont un seul academic_year_id (l'année courante après délibération).
 *   Résultat : ils sont invisibles dans les archives des années précédentes.
 *
 * Solution :
 *   Pour chaque année archivée (active=false), ce seeder :
 *   1. Trouve tous les élèves qui AVAIENT des notes/paiements cette année-là.
 *   2. Reconstitue leur classe d'origine via deliberation_students ou les grades.
 *   3. Crée un StudentAcademicRecord (snapshot) immuable par élève x année.
 *
 * Sécurité :
 *   - Utilise updateOrCreate → idempotent, relançable sans risque de doublons.
 *   - Ne touche pas aux données existantes (students, grades, payments...).
 */
class FixHistoricalArchivesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== FixHistoricalArchivesSeeder ===');
        $this->command->info('Reconstruction des snapshots historiques...');
        $this->command->newLine();

        // ── 1. Traiter toutes les années (actives ou non) ──────────────────
        $years = AcademicYear::orderBy('id')->get();

        foreach ($years as $year) {
            $this->command->info("→ Traitement année : {$year->name} (id={$year->id})");
            $this->traiterAnnee($year);
            $this->command->newLine();
        }

        $this->command->info('✅ Terminé. Snapshots créés/mis à jour.');
    }

    // ──────────────────────────────────────────────────────────────────────
    //  TRAITEMENT D'UNE ANNÉE
    // ──────────────────────────────────────────────────────────────────────

    private function traiterAnnee(AcademicYear $year): void
    {
        /*
         * Stratégie de détection des élèves de cette année :
         *
         * Source 1 (principale) : students.academic_year_id = year.id
         *   → Élèves dont c'est encore l'année courante (ou qui n'ont pas été délibérés)
         *
         * Source 2 (pour les élèves délibérés) : deliberation_students.old_academic_year_id = year.id
         *   → Élèves qui ont été promus / redoublants et dont l'année a changé
         *
         * Source 3 (fallback) : grades.academic_year_id = year.id → student_id distinct
         *   → Élèves qui ont des notes sur cette année mais dont les données ont bougé
         */

        $studentIds = collect();

        // Source 1
        $ids1 = Student::where('academic_year_id', $year->id)
            ->where('is_validated', true)
            ->pluck('id');
        $studentIds = $studentIds->merge($ids1);

        // Source 2 : deliberation_students
        if (DB::getSchemaBuilder()->hasTable('deliberation_students')) {
            $ids2 = DB::table('deliberation_students')
                ->where('old_academic_year_id', $year->id)
                ->pluck('student_id');
            $studentIds = $studentIds->merge($ids2);
        }

        // Source 3 : grades
        $ids3 = Grade::where('academic_year_id', $year->id)
            ->distinct()
            ->pluck('student_id');
        $studentIds = $studentIds->merge($ids3);

        $studentIds = $studentIds->unique()->values();

        $this->command->line("  Élèves détectés : {$studentIds->count()}");

        $created = 0;
        $updated = 0;

        foreach ($studentIds as $studentId) {
            $result = $this->creerSnapshot($studentId, $year);
            if ($result === 'created') $created++;
            if ($result === 'updated') $updated++;
        }

        $this->command->line("  ✓ Créés: {$created} | Mis à jour: {$updated}");
    }

    // ──────────────────────────────────────────────────────────────────────
    //  CRÉATION DU SNAPSHOT POUR UN ÉLÈVE + UNE ANNÉE
    // ──────────────────────────────────────────────────────────────────────

    private function creerSnapshot(int $studentId, AcademicYear $year): string
    {
        $student = Student::find($studentId);
        if (!$student) {
            return 'skip';
        }

        // ── Trouver la classe de l'élève POUR CETTE ANNÉE ─────────────────

        $classId  = null;
        $entityId = null;
        $statut   = 'pending';
        $nextClassId  = null;
        $nextYearId   = null;

        // Priorité 1 : deliberation_students (la plus fiable)
        if (DB::getSchemaBuilder()->hasTable('deliberation_students')) {
            $delib = DB::table('deliberation_students')
                ->where('student_id', $studentId)
                ->where('old_academic_year_id', $year->id)
                ->first();

            if ($delib) {
                $classId     = $delib->old_class_id;
                $statut      = $delib->status;          // passed | repeated
                $nextClassId = $delib->new_class_id;
                $nextYearId  = $delib->new_academic_year_id;
            }
        }

        // Priorité 2 : students.academic_year_id == year.id → classe courante
        if (!$classId && $student->academic_year_id == $year->id) {
            $classId = $student->class_id;
        }

        // Priorité 3 : inférer depuis les grades (classe la plus fréquente)
        if (!$classId) {
            $classId = Grade::where('student_id', $studentId)
                ->where('academic_year_id', $year->id)
                ->groupBy('class_id')
                ->orderByRaw('COUNT(*) DESC')
                ->value('class_id');
        }

        // Si toujours pas de classe, on skip (données insuffisantes)
        if (!$classId) {
            return 'skip';
        }

        // Récupérer entity_id depuis la classe
        $classe = Classe::find($classId);
        $entityId = $classe ? $classe->entity_id : ($student->entity_id ?? 1);

        // ── Calcul des moyennes par trimestre ─────────────────────────────

        $moyennes = $this->calculerMoyennesEleve($studentId, $classId, $year->id);

        // ── Montant payé cette année ──────────────────────────────────────

        $amountPaid = StudentPayment::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->sum('amount');

        // Si pas de paiement lié à cette année, tenter via student courant
        if ($amountPaid == 0 && $student->academic_year_id == $year->id) {
            $amountPaid = StudentPayment::where('student_id', $studentId)
                ->sum('amount');
        }

        // ── Données snapshot ──────────────────────────────────────────────

        $data = [
            'class_id'              => $classId,
            'entity_id'             => $entityId,
            'first_name'            => $student->first_name,
            'last_name'             => $student->last_name,
            'birth_date'            => $student->birth_date,
            'birth_place'           => $student->birth_place,
            'gender'                => $student->gender,
            'num_educ'              => $student->num_educ,
            'parent_full_name'      => $student->parent_full_name,
            'parent_email'          => $student->parent_email,
            'parent_phone'          => $student->parent_phone,
            'registration_type'     => $student->registration_type,
            'total_fees'            => $student->total_fees,
            'amount_paid'           => $amountPaid,
            'moy_trimestre_1'       => $moyennes[1] ?? null,
            'moy_trimestre_2'       => $moyennes[2] ?? null,
            'moy_trimestre_3'       => $moyennes[3] ?? null,
            'moy_annuelle'          => $moyennes['annuelle'] ?? null,
            'rang_annuel'           => null, // calculé après en batch
            'statut_deliberation'   => $statut,
            'next_class_id'         => $nextClassId,
            'next_academic_year_id' => $nextYearId,
            'is_validated'          => true,
            'archived_at'           => now(),
        ];

        $existing = StudentAcademicRecord::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->first();

        if ($existing) {
            $existing->update($data);
            $result = 'updated';
        } else {
            StudentAcademicRecord::create(array_merge($data, [
                'student_id'       => $studentId,
                'academic_year_id' => $year->id,
            ]));
            $result = 'created';
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────
    //  CALCUL DES MOYENNES D'UN ÉLÈVE (trimestre 1, 2, 3 + annuelle)
    // ──────────────────────────────────────────────────────────────────────

    private function calculerMoyennesEleve(int $studentId, int $classId, int $yearId): array
    {
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $yearId) {
            $q->where('class_id', $classId)
              ->where('academic_year_id', $yearId);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $yearId) {
            $q->where('class_id', $classId)
              ->where('academic_year_id', $yearId);
        }])->get();

        if ($subjects->isEmpty()) {
            return [1 => null, 2 => null, 3 => null, 'annuelle' => null];
        }

        $grades = Grade::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $yearId)
            ->get();

        $punishHours = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $yearId)
            ->sum('hours');

        $moyTrimestres = [];

        foreach ([1, 2, 3] as $t) {
            $conduct = Conduct::where('student_id', $studentId)
                ->where('trimestre', $t)
                ->where('academic_year_id', $yearId)
                ->first();

            $conduite = max(0, ($conduct ? $conduct->grade : 0) - ($punishHours / 2));

            $totalPoints = 0;
            $totalCoef   = 0;

            foreach ($subjects as $subject) {
                $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;

                $sg = $grades->where('subject_id', $subject->id)
                             ->where('trimestre', $t);

                $interros = $sg->where('type', 'interrogation')
                    ->pluck('value')
                    ->filter(fn($v) => $v !== null)
                    ->values()
                    ->toArray();

                $d1 = $sg->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                $d2 = $sg->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

                $moyInterro = !empty($interros)
                    ? array_sum($interros) / count($interros)
                    : null;

                $notes = array_filter([$moyInterro, $d1, $d2], fn($v) => $v !== null);

                if (!empty($notes)) {
                    $moy = array_sum($notes) / count($notes);
                    $totalPoints += $moy * $coef;
                    $totalCoef   += $coef;
                }
            }

            if ($conduite > 0) {
                $totalPoints += $conduite;
                $totalCoef   += 1;
            }

            $moyTrimestres[$t] = $totalCoef > 0
                ? round($totalPoints / $totalCoef, 2)
                : null;
        }

        $valides = array_filter($moyTrimestres, fn($v) => $v !== null);
        $moyTrimestres['annuelle'] = !empty($valides)
            ? round(array_sum($valides) / count($valides), 2)
            : null;

        return $moyTrimestres;
    }
}
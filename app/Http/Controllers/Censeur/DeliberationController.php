<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\Conduct;
use App\Models\Deliberation;
use App\Models\DeliberationStudent;
use App\Models\Grade;
use App\Models\Punishment;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\Subject;
use App\Services\AcademicRecordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DeliberationController extends Controller
{
    public function __construct(
        private readonly AcademicRecordService $recordService
    ) {}

    /* =====================================================================
     *  DONNÉES POUR LE MODAL DE DÉLIBÉRATION (JSON)
     * ===================================================================== */

    public function getModalData(int $classId): \Illuminate\Http\JsonResponse
    {
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $classe     = Classe::with('entity')->findOrFail($classId);

        // Matières de la classe
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)
              ->where('academic_year_id', $activeYear->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)
              ->where('academic_year_id', $activeYear->id);
        }])->get();

        // Élèves validés de la classe
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', true)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        // Calculer les moyennes annuelles
        $studentsData = [];
        foreach ($students as $student) {
            $moys = [];
            foreach ([1, 2, 3] as $t) {
                $moys[$t] = $this->recordService->calculerMoyenneTrimestre(
                    $student->id, $classId, $t, $activeYear, $subjects
                );
            }
            $valides = array_filter($moys, fn($v) => $v !== null);
            $moyAnn  = !empty($valides) ? round(array_sum($valides) / count($valides), 2) : null;

            $studentsData[] = [
                'id'           => $student->id,
                'full_name'    => $student->last_name . ' ' . $student->first_name,
                'num_educ'     => $student->num_educ,
                'moy_t1'       => $moys[1],
                'moy_t2'       => $moys[2],
                'moy_t3'       => $moys[3],
                'moy_annuelle' => $moyAnn,
                'admis'        => $moyAnn !== null && $moyAnn >= 10,
            ];
        }

        // Classes cibles disponibles (même entité, année active)
        $targetClasses = Classe::where('entity_id', $classe->entity_id)
            ->where('academic_year_id', $activeYear->id)
            ->where('id', '!=', $classId)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Années cibles (active + à venir)
        $targetYears = AcademicYear::orderByDesc('id')
            ->limit(3)
            ->get(['id', 'name']);

        // Délibération existante ?
        $existingDelib = Deliberation::where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->first();

        return response()->json([
            'classe'          => $classe,
            'students'        => $studentsData,
            'targetClasses'   => $targetClasses,
            'targetYears'     => $targetYears,
            'existingDelib'   => $existingDelib,
            'activeYear'      => $activeYear,
        ]);
    }

    /* =====================================================================
     *  VÉRIFIER SI UNE DÉLIBÉRATION EXISTE
     * ===================================================================== */

    public function checkExisting(int $classId): \Illuminate\Http\JsonResponse
    {
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        $existing = Deliberation::where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->with(['deliberationStudents.student', 'targetClass', 'targetAcademicYear'])
            ->first();

        return response()->json(['deliberation' => $existing]);
    }

    /* =====================================================================
     *  EFFECTUER LA DÉLIBÉRATION
     * ===================================================================== */

    public function deliberate(Request $request, int $classId): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'target_class_id'       => 'required|exists:classes,id',
            'target_academic_year_id' => 'required|exists:academic_years,id',
            'seuil_passage'         => 'required|numeric|min:0|max:20',
            'keep_timetable'        => 'boolean',
        ]);

        $activeYear   = AcademicYear::where('active', true)->firstOrFail();
        $sourceClass  = Classe::findOrFail($classId);
        $targetClass  = Classe::findOrFail($request->target_class_id);
        $targetYear   = AcademicYear::findOrFail($request->target_academic_year_id);
        $seuilPassage = (float) $request->seuil_passage;

        // Vérifier qu'il n'y a pas déjà une délibération active
        $existingDelib = Deliberation::where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->first();

        if ($existingDelib) {
            return back()->with('error', 'Une délibération existe déjà pour cette classe. Annulez-la d\'abord.');
        }

        // Matières de la classe source
        $subjects = $this->recordService->getSubjectsForClass($sourceClass, $activeYear);

        // Élèves validés
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', true)
            ->get();

        DB::beginTransaction();
        try {
            // ── ÉTAPE 1 : Calculer les moyennes annuelles ─────────────────
            $moyennesParEleve = [];
            foreach ($students as $student) {
                $moys = [];
                foreach ([1, 2, 3] as $t) {
                    $moys[$t] = $this->recordService->calculerMoyenneTrimestre(
                        $student->id, $classId, $t, $activeYear, $subjects
                    );
                }
                $valides = array_filter($moys, fn($v) => $v !== null);
                $moys['annuelle'] = !empty($valides)
                    ? round(array_sum($valides) / count($valides), 2)
                    : null;
                $moyennesParEleve[$student->id] = $moys;
            }

            // Calcul des rangs annuels
            $annuelles = array_filter(
                array_map(fn($d) => $d['annuelle'], $moyennesParEleve),
                fn($v) => $v !== null
            );
            arsort($annuelles);
            $rang = 1;
            $rangs = [];
            foreach ($annuelles as $sid => $moy) {
                $rangs[$sid] = $rang++;
            }

            // ── ÉTAPE 2 : CRÉER les snapshots AVANT tout déplacement ──────
            foreach ($students as $student) {
                $moyennes = $moyennesParEleve[$student->id];
                $statut   = ($moyennes['annuelle'] !== null && $moyennes['annuelle'] >= $seuilPassage)
                    ? 'passed'
                    : 'repeated';

                $nextClassId = $statut === 'passed' ? $targetClass->id : null;
                $nextYearId  = $statut === 'passed' ? $targetYear->id : null;

                $moyennes['rang'] = $rangs[$student->id] ?? null;

                StudentAcademicRecord::createOrUpdateSnapshot(
                    $student,
                    $activeYear,
                    $moyennes,
                    $statut,
                    $nextClassId,
                    $nextYearId
                );
            }

            // ── ÉTAPE 3 : Créer l'enregistrement de délibération ─────────
            $passedCount   = 0;
            $repeatedCount = 0;

            foreach ($students as $student) {
                $moyAnn = $moyennesParEleve[$student->id]['annuelle'];
                $admis  = $moyAnn !== null && $moyAnn >= $seuilPassage;
                if ($admis) $passedCount++;
                else $repeatedCount++;
            }

            $deliberation = Deliberation::create([
                'source_class_id'        => $classId,
                'source_academic_year_id'=> $activeYear->id,
                'target_class_id'        => $targetClass->id,
                'target_academic_year_id'=> $targetYear->id,
                'deliberated_by'         => auth()->id(),
                'keep_timetable'         => $request->boolean('keep_timetable'),
                'passed_count'           => $passedCount,
                'repeated_count'         => $repeatedCount,
                'deliberated_at'         => now(),
            ]);

            // ── ÉTAPE 4 : Enregistrer deliberation_students + déplacer ────
            foreach ($students as $student) {
                $moyAnn = $moyennesParEleve[$student->id]['annuelle'];
                $admis  = $moyAnn !== null && $moyAnn >= $seuilPassage;
                $statut = $admis ? 'passed' : 'repeated';

                DeliberationStudent::create([
                    'deliberation_id'        => $deliberation->id,
                    'student_id'             => $student->id,
                    'old_class_id'           => $classId,
                    'old_academic_year_id'   => $activeYear->id,
                    'old_registration_type'  => $student->registration_type,
                    'new_class_id'           => $admis ? $targetClass->id : $classId,
                    'new_academic_year_id'   => $admis ? $targetYear->id : $activeYear->id,
                    'new_registration_type'  => 're_registration',
                    'status'                 => $statut,
                    'annual_average'         => $moyAnn,
                ]);

                // Déplacer l'élève seulement s'il est admis
                if ($admis) {
                    $student->update([
                        'class_id'          => $targetClass->id,
                        'academic_year_id'  => $targetYear->id,
                        'registration_type' => 're_registration',
                    ]);
                }
                // Les redoublants restent dans la même classe mais gardent leur année
                // (ils seront ré-inscrits manuellement ou via une autre action)
            }

            DB::commit();

            return back()->with('success',
                "Délibération effectuée : {$passedCount} admis, {$repeatedCount} redoublants. " .
                "Les archives ont été créées automatiquement."
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la délibération : ' . $e->getMessage());
        }
    }


    public function cancel(int $deliberationId): \Illuminate\Http\RedirectResponse
    {
        $deliberation = Deliberation::with('deliberationStudents')->findOrFail($deliberationId);

        if ($deliberation->is_cancelled) {
            return back()->with('error', 'Cette délibération est déjà annulée.');
        }

        DB::beginTransaction();
        try {
            // Remettre les élèves dans leur état d'origine
            foreach ($deliberation->deliberationStudents as $ds) {
                $student = Student::find($ds->student_id);
                if (!$student) continue;

                // Rétablir uniquement les élèves qui avaient été déplacés (admis)
                if ($ds->status === 'passed') {
                    $student->update([
                        'class_id'         => $ds->old_class_id,
                        'academic_year_id' => $ds->old_academic_year_id,
                        'registration_type'=> $ds->old_registration_type,
                    ]);
                }

                // Remettre le snapshot en 'pending'
                StudentAcademicRecord::where('student_id', $ds->student_id)
                    ->where('academic_year_id', $ds->old_academic_year_id)
                    ->update([
                        'statut_deliberation'  => 'pending',
                        'next_class_id'        => null,
                        'next_academic_year_id'=> null,
                    ]);
            }

            // Marquer la délibération comme annulée
            $deliberation->update([
                'is_cancelled'  => true,
                'cancelled_at'  => now(),
                'cancelled_by'  => auth()->id(),
            ]);

            DB::commit();

            return back()->with('success', 'Délibération annulée. Les élèves ont été remis dans leur classe d\'origine.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }
}
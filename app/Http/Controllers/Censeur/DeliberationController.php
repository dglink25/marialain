<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Classe;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Deliberation;
use App\Models\DeliberationStudent;
use App\Models\Grade;
use App\Models\Conduct;
use App\Models\Punishment;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\ClassTeacherSubject;

class DeliberationController extends Controller
{
    private function hasAccess(): bool
    {
        return in_array(auth()->id(), [6, 7]);
    }

    // ─── Calcul de la moyenne annuelle d'un élève ────────────────────
    private function calculateAnnualAverage(int $studentId, int $classId, $activeYear, $subjects): ?float
    {
        $trimMoyennes = [];

        for ($t = 1; $t <= 3; $t++) {
            $grades = Grade::where('student_id', $studentId)
                ->where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $t)
                ->get();

            $conduct = Conduct::where('student_id', $studentId)
                ->where('trimestre', $t)
                ->where('academic_year_id', $activeYear->id)
                ->first();

            $punishments = Punishment::where('student_id', $studentId)
                ->where('academic_year_id', $activeYear->id)
                ->get();

            $conduiteSur20 = max(0, ($conduct ? $conduct->grade : 0) - ($punishments->sum('hours') / 2));

            $totalPoints = 0;
            $totalCoef   = 0;

            foreach ($subjects as $subject) {
                $coefRecord = $subject->classTeacherSubjects->first();
                $coef       = $coefRecord->coefficient ?? 1;
                $sg         = $grades->where('subject_id', $subject->id);

                $interroNotes = $sg->where('type', 'interrogation')
                    ->pluck('value')->filter(fn($v) => $v !== null)->values()->toArray();
                $devoir1 = $sg->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                $devoir2 = $sg->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

                $moyInterro = !empty($interroNotes) ? array_sum($interroNotes) / count($interroNotes) : null;

                $notes = array_filter([$moyInterro, $devoir1, $devoir2], fn($v) => $v !== null);
                if (!empty($notes)) {
                    $moy = array_sum($notes) / count($notes);
                    $totalPoints += $moy * $coef;
                    $totalCoef   += $coef;
                }
            }

            if ($conduiteSur20 > 0) {
                $totalPoints += $conduiteSur20;
                $totalCoef   += 1;
            }

            if ($totalCoef > 0) {
                $trimMoyennes[$t] = round($totalPoints / $totalCoef, 2);
            }
        }

        if (empty($trimMoyennes)) return null;

        return round(array_sum($trimMoyennes) / count($trimMoyennes), 2);
    }

    // ─── Données pour le modal ───────────────────────────────────────
    /**
     * Retourne :
     *  - inactive_years  : années académiques INACTIVES (destination des élèves)
     *  - target_classes  : classes de l'ANNÉE ACTIVE (classe dans laquelle les admis passent,
     *                      et dont l'emploi du temps sera copié)
     *  - existing_deliberation : délibération déjà effectuée pour cette classe/année active
     */
    public function getModalData(int $classId)
    {
        if (!$this->hasAccess()) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $activeYear = AcademicYear::where('active', true)->first();

        // ── Années INACTIVES : ce sont les années "futures" ou archivées
        //    vers lesquelles les élèves seront transférés.
        //    On exclut l'année active courante.
        $inactiveYears = AcademicYear::where('active', false)
            ->orderByDesc('id')
            ->get()
            ->map(fn($y) => ['id' => $y->id, 'name' => $y->name]);

        // ── Classes de l'ANNÉE ACTIVE (entity_id = 3 : secondaire)
        //    On exclut la classe source pour ne pas proposer de passer dans la même classe.
        $targetClasses = Classe::where('entity_id', 3)
            ->where('academic_year_id', $activeYear->id)   // ← UNIQUEMENT classes de l'année ACTIVE
            ->where('id', '!=', $classId)
            ->orderBy('name')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name]);

        // ── Délibération déjà existante pour cette classe/année active
        $existingDeliberation = null;
        if ($activeYear) {
            $existingDeliberation = Deliberation::where('source_class_id', $classId)
                ->where('source_academic_year_id', $activeYear->id)
                ->where('is_cancelled', false)
                ->first();
        }

        return response()->json([
            'inactive_years'        => $inactiveYears,
            'target_classes'        => $targetClasses,
            'existing_deliberation' => $existingDeliberation ? [
                'id'             => $existingDeliberation->id,
                'deliberated_at' => $existingDeliberation->deliberated_at?->format('d/m/Y H:i'),
                'passed_count'   => $existingDeliberation->passed_count,
                'repeated_count' => $existingDeliberation->repeated_count,
            ] : null,
        ]);
    }

    // ─── Délibérer ───────────────────────────────────────────────────
    /**
     * - target_academic_year_id : année INACTIVE vers laquelle les élèves sont transférés
     * - target_class_id         : classe de l'ANNÉE ACTIVE dans laquelle les admis passent
     *                             (son emploi du temps sera copié si keep_timetable = true)
     */
    public function deliberate(Request $request, int $classId)
    {
        if (!$this->hasAccess()) {
            return response()->json([
                'error' => 'Accès refusé. Seuls le Directeur Fondateur et la Secrétaire peuvent délibérer.'
            ], 403);
        }

        $request->validate([
            'target_academic_year_id' => 'required|exists:academic_years,id',
            'target_class_id'         => 'required|exists:classes,id',
            'keep_timetable'          => 'boolean',
        ]);

        $activeYear  = AcademicYear::where('active', true)->firstOrFail();
        $targetYear  = AcademicYear::findOrFail($request->target_academic_year_id);
        $sourceClass = Classe::findOrFail($classId);
        $targetClass = Classe::findOrFail($request->target_class_id);

        // L'année de destination DOIT être inactive
        if ($targetYear->active) {
            return response()->json([
                'error' => "L'année académique de destination doit être inactive (future ou archivée)."
            ], 422);
        }

        // La classe cible DOIT appartenir à l'année active
        if ((int) $targetClass->academic_year_id !== (int) $activeYear->id) {
            return response()->json([
                'error' => "La classe de destination doit appartenir à l'année académique active ({$activeYear->name})."
            ], 422);
        }

        // Pas de double délibération
        $existing = Deliberation::where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Une délibération a déjà été effectuée pour cette classe et cette année.'
            ], 422);
        }

        // Élèves validés de la classe source
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', 1)
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['error' => 'Aucun élève validé dans cette classe.'], 422);
        }

        // Matières pour le calcul des moyennes
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        }])->get();

        DB::beginTransaction();
        try {
            $deliberation = Deliberation::create([
                'source_class_id'         => $classId,
                'source_academic_year_id' => $activeYear->id,
                'target_class_id'         => $request->target_class_id,
                'target_academic_year_id' => $request->target_academic_year_id,
                'deliberated_by'          => auth()->id(),
                'keep_timetable'          => $request->boolean('keep_timetable'),
                'deliberated_at'          => now(),
                'is_cancelled'            => false,
            ]);

            $passedCount   = 0;
            $repeatedCount = 0;

            foreach ($students as $student) {
                $annualAverage = $this->calculateAnnualAverage(
                    $student->id,
                    $classId,
                    $activeYear,
                    $subjects
                );

                $isPassed = $annualAverage !== null && $annualAverage >= 10;

                $oldClassId = $student->class_id;
                $oldYearId  = $student->academic_year_id;
                $oldRegType = $student->registration_type;

                if ($isPassed) {
                    // Admis → nouvelle classe (cible), nouvelle année (inactive)
                    $newClassId = $request->target_class_id;
                    $newYearId  = $request->target_academic_year_id;
                    $newRegType = 're_registration';
                    $passedCount++;
                } else {
                    // Redoublant → même classe source, nouvelle année (inactive)
                    $newClassId = $classId;
                    $newYearId  = $request->target_academic_year_id;
                    $newRegType = 're_registration';
                    $repeatedCount++;
                }

                DeliberationStudent::create([
                    'deliberation_id'       => $deliberation->id,
                    'student_id'            => $student->id,
                    'old_class_id'          => $oldClassId,
                    'old_academic_year_id'  => $oldYearId,
                    'old_registration_type' => $oldRegType,
                    'new_class_id'          => $newClassId,
                    'new_academic_year_id'  => $newYearId,
                    'new_registration_type' => $newRegType,
                    'status'                => $isPassed ? 'passed' : 'repeated',
                    'annual_average'        => $annualAverage,
                ]);

                $student->update([
                    'class_id'          => $newClassId,
                    'academic_year_id'  => $newYearId,
                    'registration_type' => $newRegType,
                    'is_validated'      => false,
                    'amount_paid'       => 0,
                    'school_fees_paid'  => 0,
                    'total_fees'        => null,
                ]);
            }

            $deliberation->update([
                'passed_count'   => $passedCount,
                'repeated_count' => $repeatedCount,
            ]);

            // ── Copier l'emploi du temps depuis la classe CIBLE (année active)
            //    vers la même classe cible pour l'année INACTIVE choisie.
            if ($request->boolean('keep_timetable')) {
                $this->copyTimetableFromActiveTarget(
                    $request->target_class_id,         // classe cible (année active)
                    $activeYear->id,                    // année active (source des timetables)
                    $request->target_academic_year_id   // année inactive (destination des timetables)
                );
            }

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Délibération effectuée avec succès !',
                'passed_count'    => $passedCount,
                'repeated_count'  => $repeatedCount,
                'deliberation_id' => $deliberation->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur délibération : ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['error' => 'Erreur lors de la délibération : ' . $e->getMessage()], 500);
        }
    }

    // ─── Annuler une délibération ────────────────────────────────────
    public function cancel(Request $request, int $deliberationId)
    {
        if (!$this->hasAccess()) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $deliberation = Deliberation::with('deliberationStudents')->findOrFail($deliberationId);

        if ($deliberation->is_cancelled) {
            return response()->json(['error' => 'Cette délibération est déjà annulée.'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($deliberation->deliberationStudents as $ds) {
                $student = Student::find($ds->student_id);
                if ($student) {
                    $student->update([
                        'class_id'          => $ds->old_class_id,
                        'academic_year_id'  => $ds->old_academic_year_id,
                        'registration_type' => $ds->old_registration_type,
                        'is_validated'      => true,
                    ]);
                }
            }

            $deliberation->update([
                'is_cancelled' => true,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);

            // Supprimer l'emploi du temps copié sur la classe cible / année inactive
            if ($deliberation->keep_timetable) {
                Timetable::where('class_id', $deliberation->target_class_id)
                    ->where('academic_year_id', $deliberation->target_academic_year_id)
                    ->delete();

                ClassTeacherSubject::where('class_id', $deliberation->target_class_id)
                    ->where('academic_year_id', $deliberation->target_academic_year_id)
                    ->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Délibération annulée. Les élèves ont été remis dans leur état précédent.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur annulation délibération : ' . $e->getMessage());
            return response()->json(['error' => "Erreur lors de l'annulation : " . $e->getMessage()], 500);
        }
    }

    // ─── Copier l'emploi du temps depuis la classe cible (année active)
    //     vers la même classe pour l'année inactive ──────────────────
    /**
     * @param int $targetClassId   ID de la classe de destination (appartient à l'année active)
     * @param int $activeYearId    ID de l'année active (source des timetables à copier)
     * @param int $inactiveYearId  ID de l'année inactive (destination des timetables copiés)
     */
    private function copyTimetableFromActiveTarget(int $targetClassId, int $activeYearId, int $inactiveYearId): void
    {
        // ── 1. Copier les créneaux horaires ─────────────────────────
        $timetables = Timetable::where('class_id', $targetClassId)
            ->where('academic_year_id', $activeYearId)
            ->get();

        foreach ($timetables as $tt) {
            $exists = Timetable::where('class_id', $targetClassId)
                ->where('academic_year_id', $inactiveYearId)
                ->where('day', $tt->day)
                ->where('start_time', $tt->start_time)
                ->exists();

            if (!$exists) {
                Timetable::create([
                    'class_id'         => $targetClassId,
                    'teacher_id'       => $tt->teacher_id,
                    'subject_id'       => $tt->subject_id,
                    'day'              => $tt->day,
                    'start_time'       => $tt->start_time,
                    'end_time'         => $tt->end_time,
                    'academic_year_id' => $inactiveYearId,
                ]);
            }
        }

        // ── 2. Copier les relations enseignant-classe-matière ────────
        $cts = ClassTeacherSubject::where('class_id', $targetClassId)
            ->where('academic_year_id', $activeYearId)
            ->get();

        foreach ($cts as $item) {
            $exists = ClassTeacherSubject::where('class_id', $targetClassId)
                ->where('teacher_id', $item->teacher_id)
                ->where('subject_id', $item->subject_id)
                ->where('academic_year_id', $inactiveYearId)
                ->exists();

            if (!$exists) {
                ClassTeacherSubject::create([
                    'class_id'         => $targetClassId,
                    'teacher_id'       => $item->teacher_id,
                    'subject_id'       => $item->subject_id,
                    'academic_year_id' => $inactiveYearId,
                    'coefficient'      => $item->coefficient,
                    'amount_brut'      => $item->amount_brut,
                ]);
            }
        }
    }

    // ─── Vérifier si une délibération existe déjà ───────────────────
    public function checkExisting(int $classId)
    {
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return response()->json(['exists' => false]);
        }

        $deliberation = Deliberation::with(['targetClass', 'targetAcademicYear', 'deliberatedBy'])
            ->where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->first();

        if (!$deliberation) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => true,
            'deliberation' => [
                'id'             => $deliberation->id,
                'deliberated_at' => $deliberation->deliberated_at?->format('d/m/Y H:i'),
                'passed_count'   => $deliberation->passed_count,
                'repeated_count' => $deliberation->repeated_count,
                'target_class'   => $deliberation->targetClass->name ?? '?',
                'target_year'    => $deliberation->targetAcademicYear->name ?? '?',
                'deliberated_by' => $deliberation->deliberatedBy->name ?? '?',
            ],
        ]);
    }
}
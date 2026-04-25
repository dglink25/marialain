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

class DeliberationController extends Controller{
    private function hasAccess(): bool {
        $id = auth()->id();
        return in_array($id, [6, 7]);
    }

    private function calculateAnnualAverage(int $studentId, int $classId, $activeYear, $subjects): ?float {
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

    // ─── Données pour le modal : années inactives + classes cible ────
    public function getModalData(int $classId) {
        if (!$this->hasAccess()) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        $activeYear = AcademicYear::where('active', true)->first();

        // Années inactives (futurs, pour accueillir les élèves)
        $inactiveYears = AcademicYear::where('active', false)
            ->where('id', '!=', ($activeYear->id ?? 0))
            ->orderByDesc('id')
            ->get()
            ->map(fn($y) => ['id' => $y->id, 'name' => $y->name]);

        // Classes de l'entité 3 (secondaire) - exclure la classe source
        $targetClasses = Classe::where('entity_id', 3)
            ->where('id', '!=', $classId)
            ->orderBy('name')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'academic_year_id' => $c->academic_year_id]);

        // Vérifier si une délibération existe déjà pour cette classe/année
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
    public function deliberate(Request $request, int $classId)  {
        if (!$this->hasAccess()) {
            return response()->json(['error' => 'Accès refusé. Seuls le Directeur Fondateur et la Secrétaire peuvent délibérer.'], 403);
        }

        $request->validate([
            'target_academic_year_id' => 'required|exists:academic_years,id',
            'target_class_id'         => 'required|exists:classes,id',
            'keep_timetable'          => 'boolean',
        ]);

        $activeYear   = AcademicYear::where('active', true)->firstOrFail();
        $targetYear   = AcademicYear::findOrFail($request->target_academic_year_id);
        $sourceClass  = Classe::findOrFail($classId);
        $targetClass  = Classe::findOrFail($request->target_class_id);

        // Vérifier que l'année cible est bien inactive
        if ($targetYear->active) {
            return response()->json(['error' => "L'année académique cible doit être inactive."], 422);
        }

        // Vérifier qu'une délibération n'existe pas déjà
        $existing = Deliberation::where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Une délibération a déjà été effectuée pour cette classe et cette année.'], 422);
        }

        // Récupérer les élèves validés
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', 1)
            ->get();

        if ($students->isEmpty()) {
            return response()->json(['error' => 'Aucun élève validé dans cette classe.'], 422);
        }

        // Récupérer les matières pour calcul des moyennes
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        }])->get();

        DB::beginTransaction();
        try {
            // Créer l'enregistrement de délibération
            $deliberation = Deliberation::create([
                'source_class_id'        => $classId,
                'source_academic_year_id'=> $activeYear->id,
                'target_class_id'        => $request->target_class_id,
                'target_academic_year_id'=> $request->target_academic_year_id,
                'deliberated_by'         => auth()->id(),
                'keep_timetable'         => $request->boolean('keep_timetable'),
                'deliberated_at'         => now(),
                'is_cancelled'           => false,
            ]);

            $passedCount   = 0;
            $repeatedCount = 0;

            foreach ($students as $student) {
                // Calculer la moyenne annuelle
                $annualAverage = $this->calculateAnnualAverage(
                    $student->id,
                    $classId,
                    $activeYear,
                    $subjects
                );

                $isPassed = $annualAverage !== null && $annualAverage >= 10;

                // Snapshot avant modification
                $oldClassId      = $student->class_id;
                $oldYearId       = $student->academic_year_id;
                $oldRegType      = $student->registration_type;

                if ($isPassed) {
                    // Élève passe → nouvelle classe, nouvelle année, statut "new" (re_registration)
                    $newClassId  = $request->target_class_id;
                    $newYearId   = $request->target_academic_year_id;
                    $newRegType  = 're_registration';
                    $passedCount++;
                } else {
                    // Élève redouble → même classe, nouvelle année, statut "re_registration" (redoublant)
                    $newClassId  = $classId;
                    $newYearId   = $request->target_academic_year_id;
                    $newRegType  = 're_registration';
                    $repeatedCount++;
                }

                // Enregistrer le mouvement
                DeliberationStudent::create([
                    'deliberation_id'     => $deliberation->id,
                    'student_id'          => $student->id,
                    'old_class_id'        => $oldClassId,
                    'old_academic_year_id'=> $oldYearId,
                    'old_registration_type'=> $oldRegType,
                    'new_class_id'        => $newClassId,
                    'new_academic_year_id'=> $newYearId,
                    'new_registration_type'=> $newRegType,
                    'status'              => $isPassed ? 'passed' : 'repeated',
                    'annual_average'      => $annualAverage,
                ]);

                // Mettre à jour l'élève
                $student->update([
                    'class_id'            => $newClassId,
                    'academic_year_id'    => $newYearId,
                    'registration_type'   => $newRegType,
                    'is_validated'        => false, // À revalider pour la nouvelle année
                    'amount_paid'         => 0,
                    'school_fees_paid'    => 0,
                    'total_fees'          => null,
                ]);
            }

            // Mettre à jour les compteurs
            $deliberation->update([
                'passed_count'   => $passedCount,
                'repeated_count' => $repeatedCount,
            ]);

            // Copier l'emploi du temps si demandé
            if ($request->boolean('keep_timetable')) {
                $this->copyTimetable($classId, $request->target_class_id, $request->target_academic_year_id, $activeYear->id);
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => "Délibération effectuée avec succès !",
                'passed_count'   => $passedCount,
                'repeated_count' => $repeatedCount,
                'deliberation_id'=> $deliberation->id,
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
    public function cancel(Request $request, int $deliberationId) {
        if (!$this->hasAccess()) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $deliberation = Deliberation::with('deliberationStudents')->findOrFail($deliberationId);

        if ($deliberation->is_cancelled) {
            return response()->json(['error' => 'Cette délibération est déjà annulée.'], 422);
        }

        DB::beginTransaction();
        try {
            // Restaurer chaque élève à son état précédent
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

            // Marquer comme annulée
            $deliberation->update([
                'is_cancelled' => true,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);

            // Supprimer le timetable copié si applicable
            if ($deliberation->keep_timetable) {
                Timetable::where('class_id', $deliberation->target_class_id)
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
            return response()->json(['error' => 'Erreur lors de l\'annulation : ' . $e->getMessage()], 500);
        }
    }

    // ─── Copier l'emploi du temps ────────────────────────────────────
    private function copyTimetable(int $sourceClassId, int $targetClassId, int $targetYearId, int $sourceYearId): void {
        $timetables = Timetable::where('class_id', $sourceClassId)
            ->where('academic_year_id', $sourceYearId)
            ->get();

        foreach ($timetables as $tt) {
            // Vérifier s'il existe déjà
            $exists = Timetable::where('class_id', $targetClassId)
                ->where('academic_year_id', $targetYearId)
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
                    'academic_year_id' => $targetYearId,
                ]);
            }
        }

        // Copier aussi class_teacher_subject
        $cts = ClassTeacherSubject::where('class_id', $sourceClassId)
            ->where('academic_year_id', $sourceYearId)
            ->get();

        foreach ($cts as $item) {
            $exists = ClassTeacherSubject::where('class_id', $targetClassId)
                ->where('teacher_id', $item->teacher_id)
                ->where('subject_id', $item->subject_id)
                ->where('academic_year_id', $targetYearId)
                ->exists();

            if (!$exists) {
                ClassTeacherSubject::create([
                    'class_id'         => $targetClassId,
                    'teacher_id'       => $item->teacher_id,
                    'subject_id'       => $item->subject_id,
                    'academic_year_id' => $targetYearId,
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
            'exists'         => true,
            'deliberation'   => [
                'id'              => $deliberation->id,
                'deliberated_at'  => $deliberation->deliberated_at?->format('d/m/Y H:i'),
                'passed_count'    => $deliberation->passed_count,
                'repeated_count'  => $deliberation->repeated_count,
                'target_class'    => $deliberation->targetClass->name ?? '?',
                'target_year'     => $deliberation->targetAcademicYear->name ?? '?',
                'deliberated_by'  => $deliberation->deliberatedBy->name ?? '?',
            ],
        ]);
    }
}
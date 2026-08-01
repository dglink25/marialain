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


class DeliberationController extends Controller{
    public function __construct(
        private readonly AcademicRecordService $recordService
    ) {}

    public function getModalData(int $classId): \Illuminate\Http\JsonResponse  {
        $activeYear = AcademicYear::where('active', true)->first();
        
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

        
        // Années cibles : inactives avec nom > année active
        $activeStartYear = (int) explode('-', $activeYear->name)[0];

        $inactiveYears = AcademicYear::where('active', false)
            ->get(['id', 'name'])
            ->filter(function ($year) use ($activeStartYear) {
                $parts = explode('-', $year->name);
                return isset($parts[0]) && (int)$parts[0] > $activeStartYear;
            })
            ->values();

        // Classes cibles : celles de l'ANNÉE ACTIVE (même entité, sauf la classe courante)
        // C'est depuis ici que les élèves admis seront placés
        $targetClasses = Classe::where('entity_id', $classe->entity_id)
            ->where('academic_year_id', $activeYear->id)   // ← année ACTIVE ici, c'est voulu
            ->where('id', '!=', $classId)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Délibération existante ?
        $existingDelib = Deliberation::where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->first();

        return response()->json([
            'classe'                => $classe,
            'students'              => $studentsData,
            'inactive_years'        => $inactiveYears,      // ← clé attendue par le JS
            'target_classes'        => $targetClasses,       // ← clé attendue par le JS
            'existing_deliberation' => $existingDelib,       // ← clé attendue par le JS (ligne 683)
            'activeYear'            => $activeYear,
        ]);

    }

    public function checkExisting(int $classId): \Illuminate\Http\JsonResponse  {
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

    public function deliberate(Request $request, int $classId): \Illuminate\Http\JsonResponse
    {
        // Lire les données JSON ou form selon le Content-Type
        $payload = $request->isJson()
            ? $request->json()->all()
            : $request->all();

        \Illuminate\Support\Facades\Log::info('Deliberation payload', [
            'payload'      => $payload,
            'content_type' => $request->header('Content-Type'),
            'all'          => $request->all(),
        ]);

        $targetClassId  = $payload['target_class_id']          ?? $request->input('target_class_id');
        $targetYearId   = $payload['target_academic_year_id']  ?? $request->input('target_academic_year_id');
        $seuilPassage   = $payload['seuil_passage']            ?? $request->input('seuil_passage', 10);
        $keepTimetable  = $payload['keep_timetable']           ?? $request->input('keep_timetable', true);

        if (!$targetClassId || !$targetYearId) {
            return response()->json([
                'success' => false,
                'error'   => "Paramètres manquants. Reçu : target_class_id={$targetClassId}, target_academic_year_id={$targetYearId}",
                'payload' => $payload,
            ], 422);
        }

        $seuilPassage = (float) $seuilPassage;

        // Forcer un ROLLBACK SQL direct pour nettoyer toute transaction
        // PostgreSQL corrompue (erreur 25P02 sur AlwaysData/pooler)
        try {
            DB::statement('ROLLBACK');
        } catch (\Throwable $ignored) {}
        try {
            DB::purge('pgsql');
            DB::reconnect('pgsql');
        } catch (\Throwable $ignored) {}

        $activeYear   = AcademicYear::where('active', true)->firstOrFail();
        $sourceClass  = Classe::findOrFail($classId);
        $targetClass  = Classe::findOrFail($targetClassId);
        $targetYear   = AcademicYear::findOrFail($targetYearId);

        // Vérifier qu'il n'y a pas déjà une délibération active
        $existingDelib = Deliberation::where('source_class_id', $classId)
            ->where('source_academic_year_id', $activeYear->id)
            ->where('is_cancelled', false)
            ->first();

        if ($existingDelib) {
            return response()->json([
                'success' => false,
                'error'   => 'Une délibération existe déjà pour cette classe. Annulez-la d\'abord.',
            ], 422);
        }

        // Matières de la classe source
        $subjects = $this->recordService->getSubjectsForClass($sourceClass, $activeYear);

        // Élèves validés
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', true)
            ->get();

        // ── PRÉ-CALCUL des moyennes AVANT la transaction ─────────────────
        // Important : les SELECT doivent être hors transaction PostgreSQL
        // pour éviter l'erreur 25P02 si une requête précédente a échoué
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
        $rang = 1; $rangs = [];
        foreach ($annuelles as $sid => $moy) {
            $rangs[$sid] = $rang++;
        }

        DB::beginTransaction();
        try {
            // ── ÉTAPE 1 : Snapshots (données déjà calculées hors transaction) ──
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

            // ── ÉTAPE 3.5 : Créer/retrouver la classe source dans target_year pour les redoublants ──
            // Les redoublants restent dans la MÊME classe mais dans la NOUVELLE année
            $sourceClassInTargetYear = Classe::firstOrCreate(
                [
                    'name'             => $sourceClass->name,
                    'academic_year_id' => $targetYear->id,
                    'entity_id'        => $sourceClass->entity_id,
                ],
                [
                    'school_fees'         => $sourceClass->school_fees,
                    'registration_fee'    => $sourceClass->registration_fee,
                    're_registration_fee' => $sourceClass->re_registration_fee,
                    'description'         => $sourceClass->description,
                ]
            );

            // Créer aussi la classe cible dans target_year si elle n'existe pas encore
            $targetClassInTargetYear = Classe::firstOrCreate(
                [
                    'name'             => $targetClass->name,
                    'academic_year_id' => $targetYear->id,
                    'entity_id'        => $targetClass->entity_id,
                ],
                [
                    'school_fees'         => $targetClass->school_fees,
                    'registration_fee'    => $targetClass->registration_fee,
                    're_registration_fee' => $targetClass->re_registration_fee,
                    'description'         => $targetClass->description,
                ]
            );

            foreach ($students as $student) {
                $moyAnn = $moyennesParEleve[$student->id]['annuelle'];
                $admis  = $moyAnn !== null && $moyAnn >= $seuilPassage;
                if ($admis) $passedCount++;
                else $repeatedCount++;
            }

            $deliberation = Deliberation::create([
                'source_class_id'        => $classId,
                'source_academic_year_id'=> $activeYear->id,
                'target_class_id'        => $targetClassInTargetYear->id,
                'target_academic_year_id'=> $targetYear->id,
                'deliberated_by'         => auth()->id(),
                'keep_timetable'         => (bool) $keepTimetable,
                'passed_count'           => $passedCount,
                'repeated_count'         => $repeatedCount,
                'deliberated_at'         => now(),
            ]);

            // ── ÉTAPE 4 : Enregistrer deliberation_students + déplacer ────
            foreach ($students as $student) {
                $moyAnn = $moyennesParEleve[$student->id]['annuelle'];
                $admis  = $moyAnn !== null && $moyAnn >= $seuilPassage;
                $statut = $admis ? 'passed' : 'repeated';

                // Admis → classe cible dans nouvelle année
                // Redoublants → même classe source mais dans nouvelle année
                $newClassId = $admis ? $targetClassInTargetYear->id : $sourceClassInTargetYear->id;

                DeliberationStudent::create([
                    'deliberation_id'        => $deliberation->id,
                    'student_id'             => $student->id,
                    'old_class_id'           => $classId,
                    'old_academic_year_id'   => $activeYear->id,
                    'old_registration_type'  => $student->registration_type,
                    'new_class_id'           => $newClassId,
                    'new_academic_year_id'   => $targetYear->id,
                    'new_registration_type'  => 're_registration',
                    'status'                 => $statut,
                    'annual_average'         => $moyAnn,
                ]);

                // Déplacer TOUS les élèves (admis ET redoublants) vers la nouvelle année
                $student->update([
                    'class_id'          => $newClassId,
                    'academic_year_id'  => $targetYear->id,
                    'registration_type' => 're_registration',
                ]);
            }

            // ── ÉTAPE 5 : Copier class_teacher_subject vers les classes de la nouvelle année ─
            // 5a : Classe cible (pour les admis)
            $classesCibles = [$targetClassInTargetYear, $sourceClassInTargetYear];
            $classesSourceMap = [
                $targetClassInTargetYear->id => $targetClass->id, // admis : copier depuis classe cible active
                $sourceClassInTargetYear->id => $classId,          // redoublants : copier depuis classe source
            ];

            foreach ($classesCibles as $classCible) {
                $sourceClassId = $classesSourceMap[$classCible->id];
                $sourceCts = \App\Models\ClassTeacherSubject::where('class_id', $sourceClassId)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                foreach ($sourceCts as $cts) {
                    $exists = \App\Models\ClassTeacherSubject::where('class_id',         $classCible->id)
                        ->where('teacher_id',      $cts->teacher_id)
                        ->where('subject_id',       $cts->subject_id)
                        ->where('academic_year_id', $targetYear->id)
                        ->exists();

                    if (!$exists) {
                        \App\Models\ClassTeacherSubject::create([
                            'class_id'         => $classCible->id,
                            'academic_year_id' => $targetYear->id,
                            'teacher_id'       => $cts->teacher_id,
                            'subject_id'       => $cts->subject_id,
                            'coefficient'      => $cts->coefficient,
                            'amount_brut'      => $cts->amount_brut ?? '0.00',
                        ]);
                    }
                }
            }

            // ── ÉTAPE 6 : Copier les schedules (emploi du temps) ────────────
            // Copier uniquement si keep_timetable est activé ET pas de doublon
            if ((bool) $keepTimetable) {
                $sourceSchedules = \App\Models\Schedule::where('classe_id', $classId)->get();

                foreach ($sourceSchedules as $schedule) {
                    $exists = \App\Models\Schedule::where('classe_id', $targetClass->id)
                        ->where('day_of_week', $schedule->day_of_week)
                        ->where('start_time',  $schedule->start_time)
                        ->where('subject_id',  $schedule->subject_id)
                        ->exists();

                    if (!$exists) {
                        \App\Models\Schedule::create([
                            'classe_id'   => $targetClass->id,
                            'teacher_id'  => $schedule->teacher_id,
                            'subject_id'  => $schedule->subject_id,
                            'day_of_week' => $schedule->day_of_week,
                            'start_time'  => $schedule->start_time,
                            'end_time'    => $schedule->end_time,
                        ]);
                    }
                }
            }

            // ── ÉTAPE 7 : Copier les Timetables (emploi du temps secondaire) ─
            if ((bool) $keepTimetable) {
                $sourceTimetables = \App\Models\Timetable::where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                foreach ($sourceTimetables as $tt) {
                    $exists = \App\Models\Timetable::where('class_id', $targetClass->id)
                        ->where('academic_year_id', $targetYear->id)
                        ->where('subject_id', $tt->subject_id)
                        ->where('day',        $tt->day)
                        ->where('start_time', $tt->start_time)
                        ->exists();

                    if (!$exists) {
                        \App\Models\Timetable::create([
                            'class_id'         => $targetClass->id,
                            'academic_year_id' => $targetYear->id,
                            'teacher_id'       => $tt->teacher_id,
                            'subject_id'       => $tt->subject_id,
                            'day'              => $tt->day,
                            'start_time'       => $tt->start_time,
                            'end_time'         => $tt->end_time,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'passed_count'  => $passedCount,
                'repeated_count'=> $repeatedCount,
                'message'       => "Délibération effectuée : {$passedCount} admis, {$repeatedCount} redoublants.",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Délibération échouée', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')',
            ], 422);
        }
    }


    public function cancel(int $deliberationId): \Illuminate\Http\JsonResponse
    {
        $deliberation = Deliberation::with('deliberationStudents')->findOrFail($deliberationId);

        if ($deliberation->is_cancelled) {
            return response()->json(['success' => false, 'error' => 'Cette délibération est déjà annulée.'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($deliberation->deliberationStudents as $ds) {
                $student = Student::find($ds->student_id);
                if (!$student) continue;

                if ($ds->status === 'passed') {
                    $student->update([
                        'class_id'          => $ds->old_class_id,
                        'academic_year_id'  => $ds->old_academic_year_id,
                        'registration_type' => $ds->old_registration_type,
                    ]);
                }

                StudentAcademicRecord::where('student_id', $ds->student_id)
                    ->where('academic_year_id', $ds->old_academic_year_id)
                    ->update([
                        'statut_deliberation'   => 'pending',
                        'next_class_id'         => null,
                        'next_academic_year_id' => null,
                    ]);
            }

            $deliberation->update([
                'is_cancelled' => true,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Délibération annulée. Les élèves ont été remis dans leur classe d\'origine.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Annulation délibération échouée', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')',
            ], 422);
        }
    }
}
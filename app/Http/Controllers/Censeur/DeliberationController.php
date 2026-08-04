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

        $targetClassId  = $payload['target_class_id']          ?? $request->input('target_class_id');
        $targetYearId   = $payload['target_academic_year_id']  ?? $request->input('target_academic_year_id');
        $seuilPassage   = $payload['seuil_passage']            ?? $request->input('seuil_passage', 10);
        $keepTimetable  = $payload['keep_timetable']           ?? $request->input('keep_timetable', true);
        // Affectations individuelles : [{ student_id, target_class_id }]
        $studentAssignments = $payload['student_assignments']  ?? $request->input('student_assignments', []);

        if (!$targetYearId) {
            return response()->json([
                'success' => false,
                'error'   => "Paramètre manquant : target_academic_year_id requis.",
                'payload' => $payload,
            ], 422);
        }

        $seuilPassage = (float) $seuilPassage;

        // Construire un index rapide : student_id → target_class_id ('diplome', int, ou null)
        $assignmentMap = [];
        foreach ($studentAssignments as $a) {
            if (!empty($a['student_id'])) {
                $val = $a['target_class_id'] ?? null;
                // 'diplome' = diplômé/terminé, int = classe cible, null = redoublant
                $assignmentMap[(int)$a['student_id']] = ($val === 'diplome') ? 'diplome'
                    : ($val ? (int)$val : null);
            }
        }

        // ── Nettoyer l'état PostgreSQL avant tout ─────────────────────────
        try { DB::disconnect('pgsql'); } catch (\Throwable $ignored) {}

        $activeYear  = AcademicYear::where('active', true)->firstOrFail();
        $sourceClass = Classe::findOrFail($classId);
        $targetYear  = AcademicYear::findOrFail($targetYearId);

        // Charger toutes les classes cibles possibles (depuis les affectations, hors 'diplome')
        $targetClassIds   = array_unique(array_filter(array_values($assignmentMap), fn($v) => is_int($v)));
        $targetClassesMap = Classe::whereIn('id', $targetClassIds)->get()->keyBy('id');

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

        // ── Charger les élèves avec la relation 'classe' pré-chargée ─────
        // CRITIQUE : toutes les lectures SQL (moyennes + fees) doivent se faire
        // AVANT DB::beginTransaction() pour éviter l'erreur PostgreSQL 25P02
        // (transaction aborted) sur AlwaysData avec connexions persistantes.
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('is_validated', true)
            ->with('classe')   // ← eager-load pour éviter le lazy-load dans la transaction
            ->get();

        // ── PRÉ-CALCUL des moyennes AVANT la transaction ─────────────────
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

        // ── PRÉ-CHARGEMENT des fees de classe AVANT la transaction ────────
        $classeSnapshotData = [];
        foreach ($students as $student) {
            $classe = $student->classe; // déjà eager-loadé via with('classe')
            $classeSnapshotData[$student->id] = [
                'school_fees'         => $classe?->school_fees ?? null,
                'registration_fee'    => $classe?->registration_fee ?? null,
                're_registration_fee' => $classe?->re_registration_fee ?? null,
            ];
        }

        // ── ÉTAPE 1 : Snapshots AVANT la transaction ──────────────────────
        foreach ($students as $student) {
            $moyennes    = $moyennesParEleve[$student->id];
            $dest        = $assignmentMap[$student->id] ?? null;
            $admis       = $moyennes['annuelle'] !== null && $moyennes['annuelle'] >= $seuilPassage;

            if ($dest === 'diplome') {
                $statut = 'graduated';
            } elseif ($admis) {
                $statut = 'passed';
            } else {
                $statut = 'repeated';
            }

            $nextClassId = is_int($dest) ? $dest : null;
            $nextYearId  = ($statut !== 'repeated') ? $targetYear->id : null;
            $moyennes['rang'] = $rangs[$student->id] ?? null;

            StudentAcademicRecord::createOrUpdateSnapshot(
                $student, $activeYear, $moyennes, $statut,
                $nextClassId, $nextYearId,
                $classeSnapshotData[$student->id] ?? null
            );
        }

        DB::beginTransaction();
        try {
            $passedCount   = 0;
            $repeatedCount = 0;

            // ── ÉTAPE 2 : Créer la classe source dans la nouvelle année (redoublants) ─
            $sourceClassInTargetYear = Classe::firstOrCreate(
                ['name' => $sourceClass->name, 'academic_year_id' => $targetYear->id, 'entity_id' => $sourceClass->entity_id],
                ['school_fees' => $sourceClass->school_fees, 'registration_fee' => $sourceClass->registration_fee, 're_registration_fee' => $sourceClass->re_registration_fee, 'description' => $sourceClass->description]
            );

            // Créer les classes cibles distinctes dans la nouvelle année (admis)
            $targetClassesInTargetYear = []; // original_id => nouvelle Classe
            foreach ($targetClassesMap as $origId => $origClass) {
                $targetClassesInTargetYear[$origId] = Classe::firstOrCreate(
                    ['name' => $origClass->name, 'academic_year_id' => $targetYear->id, 'entity_id' => $origClass->entity_id],
                    ['school_fees' => $origClass->school_fees, 'registration_fee' => $origClass->registration_fee, 're_registration_fee' => $origClass->re_registration_fee, 'description' => $origClass->description]
                );
            }

            // Classe principale pour l'enregistrement Deliberation (première classe cible)
            $mainTargetClassInTargetYear = !empty($targetClassesInTargetYear)
                ? array_values($targetClassesInTargetYear)[0]
                : $sourceClassInTargetYear;

            // Compter admis/redoublants/diplômés
            $graduatedCount = 0;
            foreach ($students as $student) {
                $moyAnn = $moyennesParEleve[$student->id]['annuelle'];
                $admis  = $moyAnn !== null && $moyAnn >= $seuilPassage;
                $dest   = $assignmentMap[$student->id] ?? null;
                if ($dest === 'diplome') $graduatedCount++;
                elseif ($admis) $passedCount++;
                else $repeatedCount++;
            }

            $deliberation = Deliberation::create([
                'source_class_id'         => $classId,
                'source_academic_year_id' => $activeYear->id,
                'target_class_id'         => $mainTargetClassInTargetYear->id,
                'target_academic_year_id' => $targetYear->id,
                'deliberated_by'          => auth()->id(),
                'keep_timetable'          => (bool) $keepTimetable,
                'passed_count'            => $passedCount,
                'repeated_count'          => $repeatedCount,
                'deliberated_at'          => now(),
            ]);

            // ── ÉTAPE 4 : Enregistrer deliberation_students + déplacer ────
            foreach ($students as $student) {
                $moyAnn = $moyennesParEleve[$student->id]['annuelle'];
                $admis  = $moyAnn !== null && $moyAnn >= $seuilPassage;
                $dest   = $assignmentMap[$student->id] ?? null;

                if ($dest === 'diplome') {
                    $statut = 'graduated';
                } elseif ($admis) {
                    $statut = 'passed';
                } else {
                    $statut = 'repeated';
                }

                if ($statut === 'graduated') {
                    // Diplômé : pas de nouvelle classe, l'élève reste avec son ID
                    // mais on met academic_year_id à la nouvelle année pour traçabilité
                    $newClassId = $student->class_id; // garde la même classe
                } elseif ($statut === 'passed') {
                    $origTargetClassId    = is_int($dest) ? $dest : null;
                    $newClassInTargetYear = ($origTargetClassId && isset($targetClassesInTargetYear[$origTargetClassId]))
                        ? $targetClassesInTargetYear[$origTargetClassId]
                        : $mainTargetClassInTargetYear;
                    $newClassId = $newClassInTargetYear->id;
                } else {
                    $newClassId = $sourceClassInTargetYear->id;
                }

                DeliberationStudent::create([
                    'deliberation_id'        => $deliberation->id,
                    'student_id'             => $student->id,
                    'old_class_id'           => $classId,
                    'old_academic_year_id'   => $activeYear->id,
                    'old_registration_type'  => $student->registration_type,
                    'new_class_id'           => $newClassId,
                    'new_academic_year_id'   => $targetYear->id,
                    'new_registration_type'  => $statut === 'graduated' ? 'graduated' : 're_registration',
                    'status'                 => $statut,
                    'annual_average'         => $moyAnn,
                ]);

                $student->update([
                    'class_id'          => $newClassId,
                    'academic_year_id'  => $targetYear->id,
                    'registration_type' => $statut === 'graduated' ? 'graduated' : 're_registration',
                    'total_fees'        => 0,
                    'amount_paid'       => 0,
                ]);
            }

            // ── ÉTAPE 5 : Copier class_teacher_subject ─────────────────────
            // source → redoublants ; chaque cible → admis affectés
            $copieMap = [$sourceClassInTargetYear->id => $classId];
            foreach ($targetClassesInTargetYear as $origId => $newClass) {
                $copieMap[$newClass->id] = $origId;
            }
            foreach ($copieMap as $newClassId => $srcId) {
                $cts = \App\Models\ClassTeacherSubject::where('class_id', $srcId)
                    ->where('academic_year_id', $activeYear->id)->get();
                foreach ($cts as $ct) {
                    $exists = \App\Models\ClassTeacherSubject::where('class_id', $newClassId)
                        ->where('teacher_id', $ct->teacher_id)->where('subject_id', $ct->subject_id)
                        ->where('academic_year_id', $targetYear->id)->exists();
                    if (!$exists) {
                        \App\Models\ClassTeacherSubject::create([
                            'class_id' => $newClassId, 'academic_year_id' => $targetYear->id,
                            'teacher_id' => $ct->teacher_id, 'subject_id' => $ct->subject_id,
                            'coefficient' => $ct->coefficient, 'amount_brut' => $ct->amount_brut ?? '0.00',
                        ]);
                    }
                }
            }

            // ── ÉTAPE 6 & 7 : Copier emplois du temps ─────────────────────
            if ((bool) $keepTimetable) {
                $sourceSchedules  = \App\Models\Schedule::where('classe_id', $classId)->get();
                $sourceTimetables = \App\Models\Timetable::where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id)->get();

                foreach ($targetClassesInTargetYear as $newClass) {
                    foreach ($sourceSchedules as $s) {
                        $exists = \App\Models\Schedule::where('classe_id', $newClass->id)
                            ->where('day_of_week', $s->day_of_week)->where('start_time', $s->start_time)
                            ->where('subject_id', $s->subject_id)->exists();
                        if (!$exists) {
                            \App\Models\Schedule::create(['classe_id' => $newClass->id, 'teacher_id' => $s->teacher_id,
                                'subject_id' => $s->subject_id, 'day_of_week' => $s->day_of_week,
                                'start_time' => $s->start_time, 'end_time' => $s->end_time]);
                        }
                    }
                    foreach ($sourceTimetables as $tt) {
                        $exists = \App\Models\Timetable::where('class_id', $newClass->id)
                            ->where('academic_year_id', $targetYear->id)->where('subject_id', $tt->subject_id)
                            ->where('day', $tt->day)->where('start_time', $tt->start_time)->exists();
                        if (!$exists) {
                            \App\Models\Timetable::create(['class_id' => $newClass->id, 'academic_year_id' => $targetYear->id,
                                'teacher_id' => $tt->teacher_id, 'subject_id' => $tt->subject_id,
                                'day' => $tt->day, 'start_time' => $tt->start_time, 'end_time' => $tt->end_time]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success'         => true,
                'passed_count'    => $passedCount,
                'repeated_count'  => $repeatedCount,
                'graduated_count' => $graduatedCount,
                'message'         => "Délibération effectuée : {$passedCount} admis, {$repeatedCount} redoublants, {$graduatedCount} diplômés.",
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
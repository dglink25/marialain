<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classe;
use App\Models\ClassTeacherSubject;
use App\Models\Conduct;
use App\Models\Grade;
use App\Models\Punishment;
use App\Models\Student;
use App\Models\StudentAcademicRecord;
use App\Models\Subject;
use App\Models\Timetable;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    /* =====================================================================
     *  INDEX – liste des années archivées
     * ===================================================================== */

    public function index()
    {
        $archives = AcademicYear::archives()->orderByDesc('id')->get();
        return view('archives.index', compact('archives'));
    }

    /* =====================================================================
     *  SHOW – classes d'une année archivée (selon le rôle)
     * ===================================================================== */

    public function show($id)
    {
        $user = auth()->user();
        $year = AcademicYear::findOrFail($id);

        if ($year->active) {
            return redirect()->route('home')
                ->with('error', 'Cette année est encore active, pas une archive.');
        }

        // ── Récupérer les classes qui ont des records pour cette année ──────
        $classIdsQuery = StudentAcademicRecord::where('academic_year_id', $year->id)
            ->distinct()
            ->pluck('class_id');

        // Fallback : classes directement liées à l'année
        $classIdsFromYear = Classe::where('academic_year_id', $year->id)->pluck('id');
        $allClassIds = $classIdsQuery->merge($classIdsFromYear)->unique();

        $classesQuery = Classe::with(['entity'])
            ->whereIn('id', $allClassIds);

        // ── Filtrage selon le rôle ──────────────────────────────────────────
        if ($this->isAdminOrSecretary($user)) {
            $classes = $classesQuery->get();
        } elseif ($user->id == 5) {
            $classes = $classesQuery->whereIn('entity_id', [1, 2])->get();
        } elseif ($this->isCenseur($user)) {
            $classes = $classesQuery->where('entity_id', 3)->get();
        } else {
            // Enseignant → ses classes uniquement
            $allowedClassIds = ClassTeacherSubject::where('teacher_id', $user->id)
                ->where('academic_year_id', $year->id)
                ->pluck('class_id');
            $classes = $classesQuery->whereIn('id', $allowedClassIds)->get();
        }

        // Comptage des élèves via les records
        foreach ($classes as $class) {
            $class->studentsCount = StudentAcademicRecord::where('academic_year_id', $year->id)
                ->where('class_id', $class->id)
                ->count();
        }

        $canViewPayments  = $this->isAdminOrSecretary($user);
        $canViewTimetable = $this->isAdminOrSecretary($user) || $this->isCenseur($user);
        $canViewNotes     = $this->isAdminOrSecretary($user) || $this->isCenseur($user);

        $paymentStats = null;
        if ($canViewPayments) {
            $paymentStats = $this->getYearPaymentStats($year);
        }

        return view('archives.show', compact(
            'year', 'classes',
            'canViewPayments', 'canViewTimetable', 'canViewNotes',
            'paymentStats'
        ));
    }

    public function classStudents($yearId, $classId)
    {
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::with('entity')->findOrFail($classId);
        $user  = auth()->user();

        $this->authorizeAccess($user, $class, $year);

        // Récupérer les records archivés pour cette classe + année
        $records = StudentAcademicRecord::where('academic_year_id', $year->id)
            ->where('class_id', $class->id)
            ->with(['student', 'entity', 'classe'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(30);

        $canViewPayments = $this->isAdminOrSecretary($user);

        $classPaymentStats = null;
        if ($canViewPayments) {
            $classPaymentStats = $this->getClassPaymentStatsFromRecords($classId, $year);
        }

        return view('archives.class_students', compact(
            'year', 'class', 'records',
            'canViewPayments', 'classPaymentStats'
        ));
    }

    public function classTimetables($yearId, $classId)
    {
        $user  = auth()->user();
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::findOrFail($classId);

        if (!$this->isAdminOrSecretary($user) && !$this->isCenseur($user)) {
            abort(403, 'Accès refusé.');
        }

        $timetables = Timetable::with(['teacher', 'subject'])
            ->where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->get();

        $hours = [];
        for ($h = 7; $h < 19; $h++) {
            $hours[] = sprintf('%02dh-%02dh', $h, $h + 1);
        }
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        return view('archives.class_timetables', compact('year', 'class', 'timetables', 'hours', 'days'));
    }

    public function classNotes($yearId, $classId)
    {
        $user  = auth()->user();
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::with('entity')->findOrFail($classId);

        if (!$this->isAdminOrSecretary($user) && !$this->isCenseur($user)) {
            abort(403, 'Accès refusé.');
        }

        // Matières via class_teacher_subject
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        }])->orderBy('name')->get();

        // Élèves via les records archivés (source fiable)
        $records = StudentAcademicRecord::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->with('student')
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        // Récupérer les vrais IDs student pour accéder aux grades
        $studentIds = $records->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)
            ->orderBy('last_name')->orderBy('first_name')
            ->get()
            ->keyBy('id');

        // Toutes les notes en une requête
        $allGrades = Grade::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy(['student_id', 'trimestre', 'subject_id']);

        // Conduites et punitions
        $conducts = Conduct::where('academic_year_id', $year->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy(['student_id', 'trimestre']);

        $punishments = Punishment::where('academic_year_id', $year->id)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('student_id, SUM(hours) as total_hours')
            ->groupBy('student_id')
            ->pluck('total_hours', 'student_id');

        // Calcul des moyennes par trimestre pour chaque élève
        $tableauNotes = [];
        $trimestres   = [1, 2, 3];

        foreach ($records as $record) {
            $studentId = $record->student_id;
            $student   = $students[$studentId] ?? null;
            if (!$student) continue;

            $row = ['student' => $student, 'record' => $record, 'trimestres' => []];

            foreach ($trimestres as $t) {
                $punishH    = $punishments[$studentId] ?? 0;
                $conductGrd = $conducts[$studentId][$t][0]->grade ?? 0;
                $conduite   = max(0, $conductGrd - ($punishH / 2));

                $totalPts  = 0;
                $totalCoef = 0;

                foreach ($subjects as $subject) {
                    $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                    $sg   = $allGrades[$studentId][$t][$subject->id] ?? collect();

                    $interros = $sg->where('type', 'interrogation')
                        ->pluck('value')->filter()->values()->toArray();
                    $d1 = $sg->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                    $d2 = $sg->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

                    $moyInterro = !empty($interros) ? array_sum($interros) / count($interros) : null;
                    $notes4Moy  = array_filter([$moyInterro, $d1, $d2], fn($v) => $v !== null);
                    $moyMatiere = !empty($notes4Moy) ? array_sum($notes4Moy) / count($notes4Moy) : null;

                    if ($moyMatiere !== null) {
                        $totalPts  += $moyMatiere * $coef;
                        $totalCoef += $coef;
                    }
                }

                if ($conduite > 0) {
                    $totalPts  += $conduite;
                    $totalCoef += 1;
                }

                $moyGen = $totalCoef > 0 ? round($totalPts / $totalCoef, 2) : null;
                $row['trimestres'][$t] = [
                    'moyenne'  => $moyGen,
                    'conduite' => round($conduite, 2),
                ];
            }

            // Priorité : prendre la moy_annuelle du snapshot si disponible
            if ($record->moy_annuelle !== null) {
                $row['moy_annuelle'] = (float) $record->moy_annuelle;
            } else {
                $moys = array_filter(array_column($row['trimestres'], 'moyenne'), fn($v) => $v !== null);
                $row['moy_annuelle'] = !empty($moys) ? round(array_sum($moys) / count($moys), 2) : null;
            }

            $tableauNotes[] = $row;
        }

        // Rangs par trimestre
        foreach ($trimestres as $t) {
            $moyennes = array_filter(
                array_map(fn($r) => ['id' => $r['student']->id, 'moy' => $r['trimestres'][$t]['moyenne']], $tableauNotes),
                fn($r) => $r['moy'] !== null
            );
            usort($moyennes, fn($a, $b) => $b['moy'] <=> $a['moy']);
            $rangMap = [];
            $rang = 1;
            foreach ($moyennes as $idx => $item) {
                if ($idx > 0 && $item['moy'] == $moyennes[$idx - 1]['moy']) {
                    $rangMap[$item['id']] = $rangMap[$moyennes[$idx - 1]['id']];
                } else {
                    $rangMap[$item['id']] = $rang;
                }
                $rang++;
            }
            foreach ($tableauNotes as &$row) {
                $row['trimestres'][$t]['rang'] = $rangMap[$row['student']->id] ?? '-';
            }
        }

        // Rang annuel (depuis snapshot si dispo, sinon calculé)
        $moyAnn = array_filter(
            array_map(fn($r) => ['id' => $r['student']->id, 'moy' => $r['moy_annuelle']], $tableauNotes),
            fn($r) => $r['moy'] !== null
        );
        usort($moyAnn, fn($a, $b) => $b['moy'] <=> $a['moy']);
        $rangAnnMap = [];
        $rang = 1;
        foreach ($moyAnn as $idx => $item) {
            if ($idx > 0 && $item['moy'] == $moyAnn[$idx - 1]['moy']) {
                $rangAnnMap[$item['id']] = $rangAnnMap[$moyAnn[$idx - 1]['id']];
            } else {
                $rangAnnMap[$item['id']] = $rang;
            }
            $rang++;
        }
        foreach ($tableauNotes as &$row) {
            $row['rang_annuel'] = $rangAnnMap[$row['student']->id] ?? '-';
        }

        return view('archives.class_notes', compact(
            'year', 'class', 'subjects', 'students',
            'tableauNotes', 'trimestres'
        ));
    }

    public function classPaymentStats($yearId, $classId)
    {
        $user  = auth()->user();
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::findOrFail($classId);

        if (!$this->isAdminOrSecretary($user)) abort(403);

        // Utiliser les records archivés
        $records = StudentAcademicRecord::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->with('student')
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        $stats = $this->getClassPaymentStatsFromRecords($classId, $year);

        return view('archives.class_payment_stats', compact('year', 'class', 'records', 'stats'));
    }


    public function parentIndex()
    {
        $parent   = auth('parent')->user();
        $archives = AcademicYear::archives()->orderByDesc('id')->get();

        // Garder uniquement les années où le parent a des enfants (via records)
        $archives = $archives->filter(function ($year) use ($parent) {
            return StudentAcademicRecord::where('academic_year_id', $year->id)
                ->where('parent_phone', $parent->phone)
                ->exists();
        });

        return view('archives.parent.index', compact('archives'));
    }

    public function parentShow($yearId)
    {
        $parent = auth('parent')->user();
        $year   = AcademicYear::findOrFail($yearId);

        if ($year->active) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Cette année est encore active.');
        }

        // Chercher les records de l'enfant pour cette année
        $records = StudentAcademicRecord::where('academic_year_id', $year->id)
            ->where('parent_phone', $parent->phone)
            ->with(['student', 'classe'])
            ->get();

        if ($records->isEmpty()) {
            return redirect()->route('archives.parent.index')
                ->with('error', 'Aucun enfant trouvé pour cette année.');
        }

        return view('archives.parent.show', compact('year', 'records'));
    }

    public function parentChildDetails($yearId, $studentId)
    {
        $parent  = auth('parent')->user();
        $year    = AcademicYear::findOrFail($yearId);
        $student = Student::findOrFail($studentId);

        // Vérification accès via le record archivé
        $record = StudentAcademicRecord::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->where('parent_phone', $parent->phone)
            ->firstOr(function () {
                abort(403, 'Accès refusé.');
            });

        $classId = $record->class_id;
        $class   = Classe::findOrFail($classId);

        // Matières
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        }])->orderBy('name')->get();

        // Notes
        $grades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->get()
            ->groupBy(['trimestre', 'subject_id']);

        // Conduite & punitions
        $conducts = Conduct::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->get()->keyBy('trimestre');

        $punishHours = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->sum('hours');

        // Paiements
        $payments   = Student::find($studentId)->payments()
            ->where('academic_year_id', $year->id)->get();
        $totalPaid  = $record->amount_paid ?? $payments->sum('amount');
        $totalFees  = $record->total_fees ?? 0;

        // Emploi du temps
        $timetables = Timetable::with(['teacher', 'subject'])
            ->where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->get();

        $hours = [];
        for ($h = 7; $h < 19; $h++) {
            $hours[] = sprintf('%02dh-%02dh', $h, $h + 1);
        }
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        // Calcul des moyennes par trimestre
        $trimestresData = [];
        foreach ([1, 2, 3] as $t) {
            $cGrade   = $conducts[$t]->grade ?? 0;
            $conduite = max(0, $cGrade - ($punishHours / 2));

            $totalPts = 0; $totalCoef = 0;
            $bulletinRows = [];

            foreach ($subjects as $subject) {
                $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                $sg   = $grades[$t][$subject->id] ?? collect();

                $interros  = $sg->where('type', 'interrogation')->pluck('value')->filter()->values()->toArray();
                $d1        = $sg->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                $d2        = $sg->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;
                $moyInterr = !empty($interros) ? round(array_sum($interros) / count($interros), 2) : null;
                $notes4Moy = array_filter([$moyInterr, $d1, $d2], fn($v) => $v !== null);
                $moyMat    = !empty($notes4Moy) ? round(array_sum($notes4Moy) / count($notes4Moy), 2) : null;

                if ($moyMat !== null) {
                    $totalPts  += $moyMat * $coef;
                    $totalCoef += $coef;
                }

                $bulletinRows[] = [
                    'subject'      => $subject->name,
                    'coef'         => $coef,
                    'interros'     => $interros,
                    'devoir1'      => $d1,
                    'devoir2'      => $d2,
                    'moyInterro'   => $moyInterr,
                    'moyenne'      => $moyMat,
                    'appreciation' => $this->getAppreciation($moyMat),
                ];
            }

            if ($conduite > 0) {
                $totalPts  += $conduite;
                $totalCoef += 1;
            }

            $moyGen = $totalCoef > 0 ? round($totalPts / $totalCoef, 2) : null;

            $trimestresData[$t] = [
                'bulletin'        => $bulletinRows,
                'conduite'        => round($conduite, 2),
                'moyenneGenerale' => $moyGen,
                'appreciation'    => $this->getAppreciation($moyGen),
            ];
        }

        // Moyenne annuelle (snapshot si dispo)
        $moyAnnuelle = $record->moy_annuelle;
        if ($moyAnnuelle === null) {
            $moys = array_filter(array_column($trimestresData, 'moyenneGenerale'), fn($v) => $v !== null);
            $moyAnnuelle = !empty($moys) ? round(array_sum($moys) / count($moys), 2) : null;
        }

        return view('archives.parent.child_details', compact(
            'year', 'student', 'record', 'class', 'subjects',
            'trimestresData', 'moyAnnuelle',
            'payments', 'totalPaid', 'totalFees',
            'timetables', 'hours', 'days'
        ));
    }

    public function studentNotesJson($yearId, $classId, $studentId)
    {
        $year    = AcademicYear::findOrFail($yearId);
        $class   = Classe::with('entity')->findOrFail($classId);
        $user    = auth()->user();

        // Vérification accès
        $this->authorizeAccess($user, $class, $year);

        // Vérifier que l'élève appartient bien à cette classe/année via le record
        $record = StudentAcademicRecord::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->where('class_id', $classId)
            ->firstOrFail();

        $student = Student::findOrFail($studentId);

        // Matières
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        }])->orderBy('name')->get();

        // Toutes les notes de l'élève
        $allGrades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->whereIn('class_id', [$classId]) // sécurité
            ->get()
            ->groupBy(['trimestre', 'subject_id']);

        // Conduites et punitions
        $conducts = Conduct::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->get()->keyBy('trimestre');

        $punishHours = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->sum('hours');

        // Calculer les rangs par trimestre pour cet élève (on a besoin de tous les élèves)
        $allStudentIds = StudentAcademicRecord::where('academic_year_id', $year->id)
            ->where('class_id', $classId)
            ->pluck('student_id');

        $allGradesClass = Grade::where('academic_year_id', $year->id)
            ->whereIn('student_id', $allStudentIds)
            ->get()
            ->groupBy(['student_id', 'trimestre', 'subject_id']);

        $allConduitsClass = Conduct::where('academic_year_id', $year->id)
            ->whereIn('student_id', $allStudentIds)
            ->get()->groupBy(['student_id', 'trimestre']);

        $allPunitionsClass = Punishment::where('academic_year_id', $year->id)
            ->whereIn('student_id', $allStudentIds)
            ->selectRaw('student_id, SUM(hours) as total_hours')
            ->groupBy('student_id')
            ->pluck('total_hours', 'student_id');

        // Calcul des moyennes générales par trimestre pour tous les élèves (pour le rang)
        $moyParTrimestre = [];
        foreach ([1,2,3] as $t) {
            foreach ($allStudentIds as $sid) {
                $cGrade   = $allConduitsClass[$sid][$t][0]->grade ?? 0;
                $punH     = $allPunitionsClass[$sid] ?? 0;
                $conduite = max(0, $cGrade - ($punH / 2));
                $tp = 0; $tc = 0;
                foreach ($subjects as $subject) {
                    $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                    $sg   = $allGradesClass[$sid][$t][$subject->id] ?? collect();
                    $interros = $sg->where('type','interrogation')->pluck('value')->filter()->values()->toArray();
                    $d1 = $sg->where('type','devoir')->where('sequence',1)->first()->value ?? null;
                    $d2 = $sg->where('type','devoir')->where('sequence',2)->first()->value ?? null;
                    $mi = !empty($interros) ? array_sum($interros)/count($interros) : null;
                    $notes = array_filter([$mi,$d1,$d2], fn($v) => $v!==null);
                    if (!empty($notes)) {
                        $moy = array_sum($notes)/count($notes);
                        $tp += $moy * $coef; $tc += $coef;
                    }
                }
                if ($conduite > 0) { $tp += $conduite; $tc += 1; }
                if ($tc > 0) $moyParTrimestre[$t][$sid] = round($tp/$tc, 2);
            }
        }

        // Rangs
        $rangs = [];
        foreach ([1,2,3] as $t) {
            $sorted = $moyParTrimestre[$t] ?? [];
            arsort($sorted);
            $rang = 1;
            foreach ($sorted as $sid => $m) {
                $rangs[$t][$sid] = $rang++;
            }
        }

        // Construire les données de bulletin par trimestre
        $trimestresData = [];
        foreach ([1,2,3] as $t) {
            $cGrade   = $conducts[$t]->grade ?? 0;
            $conduite = max(0, $cGrade - ($punishHours / 2));

            $rows = [];
            $tp = 0; $tc = 0;

            foreach ($subjects as $subject) {
                $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                $sg   = $allGrades[$t][$subject->id] ?? collect();

                $interros = $sg->where('type','interrogation')->sortBy('sequence')
                    ->pluck('value')->filter()->values()->toArray();
                $d1 = $sg->where('type','devoir')->where('sequence',1)->first()->value ?? null;
                $d2 = $sg->where('type','devoir')->where('sequence',2)->first()->value ?? null;

                $mi = !empty($interros) ? round(array_sum($interros)/count($interros),2) : null;
                $notes = array_filter([$mi,$d1,$d2], fn($v) => $v!==null);
                $moy = !empty($notes) ? round(array_sum($notes)/count($notes),2) : null;

                if ($moy !== null) {
                    $tp += $moy * $coef; $tc += $coef;
                }

                $rows[] = [
                    'subject'      => $subject->name,
                    'coef'         => $coef,
                    'interros'     => $interros,
                    'devoir1'      => $d1,
                    'devoir2'      => $d2,
                    'moyInterro'   => $mi,
                    'moyenne'      => $moy,
                    'appreciation' => $this->getAppreciation($moy),
                ];
            }

            if ($conduite > 0) { $tp += $conduite; $tc += 1; }
            $moyGen = $tc > 0 ? round($tp/$tc, 2) : null;

            $trimestresData[$t] = [
                'bulletin'        => $rows,
                'conduite'        => round($conduite, 2),
                'moyenneGenerale' => $moyGen,
                'rang'            => $rangs[$t][$studentId] ?? '-',
                'totalEleves'     => count($allStudentIds),
                'appreciation'    => $this->getAppreciation($moyGen),
            ];
        }

        // Moyenne annuelle
        $moyAnn = $record->moy_annuelle;
        if ($moyAnn === null) {
            $moys = array_filter(array_column($trimestresData, 'moyenneGenerale'), fn($v) => $v !== null);
            $moyAnn = !empty($moys) ? round(array_sum($moys)/count($moys), 2) : null;
        }

        // Rang annuel
        $allMoyAnn = [];
        foreach ($allStudentIds as $sid) {
            $ms = [];
            foreach ([1,2,3] as $t) {
                if (isset($moyParTrimestre[$t][$sid])) $ms[] = $moyParTrimestre[$t][$sid];
            }
            if (!empty($ms)) $allMoyAnn[$sid] = round(array_sum($ms)/count($ms),2);
        }
        arsort($allMoyAnn);
        $rangAnn = 1;
        $rangAnnMap = [];
        foreach ($allMoyAnn as $sid => $m) { $rangAnnMap[$sid] = $rangAnn++; }

        return response()->json([
            'student'      => [
                'id'         => $student->id,
                'nom'        => $student->last_name,
                'prenom'     => $student->first_name,
                'num_educ'   => $student->num_educ,
                'gender'     => $student->gender,
            ],
            'record'       => [
                'statut'     => $record->statut_deliberation,
                'moy_annuelle'=> $moyAnn,
                'rang_annuel' => $rangAnnMap[$studentId] ?? '-',
                'total_eleves'=> count($allStudentIds),
            ],
            'year'         => ['id' => $year->id, 'name' => $year->name],
            'class'        => ['id' => $class->id, 'name' => $class->name],
            'trimestres'   => $trimestresData,
            'moy_annuelle' => $moyAnn,
        ]);
    }


    public function studentBulletinPdf($yearId, $classId, $studentId, $trimestre) {
        $year    = AcademicYear::findOrFail($yearId);
        $class   = Classe::with('entity')->findOrFail($classId);
        $user    = auth()->user();

        $this->authorizeAccess($user, $class, $year);

        $record  = StudentAcademicRecord::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->where('class_id', $classId)
            ->firstOrFail();

        $student = Student::findOrFail($studentId);

        // Matières
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        }])->orderBy('name')->get();

        // Tous les élèves de la classe pour les rangs
        $allStudentIds = StudentAcademicRecord::where('academic_year_id', $year->id)
            ->where('class_id', $classId)->pluck('student_id');

        $allGradesClass = Grade::where('academic_year_id', $year->id)
            ->whereIn('student_id', $allStudentIds)
            ->where('trimestre', $trimestre)
            ->get()->groupBy(['student_id', 'subject_id']);

        $allConduitsClass = Conduct::where('academic_year_id', $year->id)
            ->where('trimestre', $trimestre)
            ->whereIn('student_id', $allStudentIds)
            ->get()->keyBy('student_id');

        $allPunitionsClass = Punishment::where('academic_year_id', $year->id)
            ->whereIn('student_id', $allStudentIds)
            ->selectRaw('student_id, SUM(hours) as total_hours')
            ->groupBy('student_id')
            ->pluck('total_hours', 'student_id');

        // Calcul des moyennes générales de la classe pour ce trimestre
        $moyennesClasse = [];
        $moyennesParMatiere = []; // pour les rangs par matière

        foreach ($allStudentIds as $sid) {
            $cGrade   = $allConduitsClass[$sid]->grade ?? 0;
            $punH     = $allPunitionsClass[$sid] ?? 0;
            $conduite = max(0, $cGrade - ($punH / 2));

            $tp = 0; $tc = 0;

            foreach ($subjects as $subject) {
                $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                $sg   = $allGradesClass[$sid][$subject->id] ?? collect();

                $interros = $sg->where('type','interrogation')->pluck('value')->filter()->values()->toArray();
                $d1 = $sg->where('type','devoir')->where('sequence',1)->first()->value ?? null;
                $d2 = $sg->where('type','devoir')->where('sequence',2)->first()->value ?? null;
                $mi = !empty($interros) ? array_sum($interros)/count($interros) : null;
                $notes = array_filter([$mi,$d1,$d2], fn($v) => $v!==null);

                if (!empty($notes)) {
                    $moy = array_sum($notes)/count($notes);
                    $moyennesParMatiere[$subject->id][$sid] = $moy;
                    $tp += $moy * $coef; $tc += $coef;
                }
            }

            $moyennesParMatiere['CONDUITE'][$sid] = $conduite;
            if ($conduite > 0) { $tp += $conduite; $tc += 1; }
            if ($tc > 0) $moyennesClasse[$sid] = round($tp/$tc, 2);
        }

        // Rangs par matière
        $rangsParMatiere = [];
        foreach ($moyennesParMatiere as $subId => $moys) {
            arsort($moys);
            $rang = 1;
            foreach ($moys as $sid => $m) {
                $rangsParMatiere[$subId][$sid] = $rang . 'e';
                $rang++;
            }
        }

        // Rang général
        arsort($moyennesClasse);
        $rangGen = 1;
        $rangsGen = [];
        foreach ($moyennesClasse as $sid => $m) { $rangsGen[$sid] = $rangGen++; }

        $plusForte  = !empty($moyennesClasse) ? max($moyennesClasse) : 0;
        $plusFaible = !empty($moyennesClasse) ? min($moyennesClasse) : 0;
        $moyClasse  = !empty($moyennesClasse)
            ? round(array_sum($moyennesClasse)/count($moyennesClasse), 2) : 0;

        // Notes de l'élève pour ce trimestre
        $grades = Grade::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)
            ->where('trimestre', $trimestre)
            ->get();

        $conduct = Conduct::where('student_id', $studentId)
            ->where('trimestre', $trimestre)
            ->where('academic_year_id', $year->id)->first();

        $punishHours = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $year->id)->sum('hours');

        $conduiteSur20 = round(max(0, ($conduct?->grade ?? 0) - ($punishHours / 2)), 2);

        // Construction du bulletin
        $bulletin = [];
        $totalMoyCoeff = 0; $totalCoeff = 0;
        $moyLitt = []; $moySci = []; $moyAutres = [];

        $matieresLitt = ['COMMUNICATION ECRITE','LECTURE','ANGLAIS','HISTOIRE-GEOGRAPHIE','FRANÇAIS','PHILOSOPHIE','ESPAGNOL','HGGSP'];
        $matieresSci  = ['MATHEMATIQUES','PHYSIQUE CHIMIE ET TECHNOLOGIE (PCT)','SCIENCE DE LA VIE ET DE LA TERRE (SVT)','ENSEIGNEMENTS SCIENTIFIQUES'];
        $matieresAut  = ['EDUCATION PHYSIQUE ET SPORTIVE (EPS)','CONDUITE'];

        foreach ($subjects as $subject) {
            $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
            $sg   = $grades->where('subject_id', $subject->id);

            $interros = $sg->where('type','interrogation')->sortBy('sequence')
                ->pluck('value')->filter()->values()->toArray();
            $d1 = $sg->where('type','devoir')->where('sequence',1)->first()->value ?? null;
            $d2 = $sg->where('type','devoir')->where('sequence',2)->first()->value ?? null;
            $mi = !empty($interros) ? round(array_sum($interros)/count($interros),2) : null;
            $notes = array_filter([$mi,$d1,$d2], fn($v) => $v!==null);
            $moy   = !empty($notes) ? round(array_sum($notes)/count($notes),2) : null;
            $moyCoeff = $moy !== null ? round($moy * $coef, 2) : null;

            if ($moy !== null) {
                $totalMoyCoeff += $moyCoeff; $totalCoeff += $coef;
                $nom = strtoupper($subject->name);
                if (in_array($nom, $matieresLitt)) $moyLitt[] = $moy;
                elseif (in_array($nom, $matieresSci)) $moySci[] = $moy;
                elseif (in_array($nom, $matieresAut)) $moyAutres[] = $moy;
            }

            $interrosF = [];
            for ($i = 1; $i <= 5; $i++) {
                $interrosF[$i] = isset($interros[$i-1]) ? number_format($interros[$i-1],2,',','') : '-';
            }

            $bulletin[] = [
                'subject'        => strtoupper($subject->name),
                'coef'           => $coef,
                'interros'       => $interrosF,
                'devoirs'        => [
                    1 => $d1 !== null ? number_format($d1,2,',','') : '-',
                    2 => $d2 !== null ? number_format($d2,2,',','') : '-',
                ],
                'moyenneInterro' => $mi !== null ? number_format($mi,2,',','') : '-',
                'moyenne'        => $moy !== null ? number_format($moy,2,',','') : '-',
                'moyCoeff'       => $moyCoeff !== null ? number_format($moyCoeff,2,',','') : '-',
                'rang'           => $rangsParMatiere[$subject->id][$studentId] ?? '-',
                'appreciation'   => $this->getAppreciation($moy),
            ];
        }

        // Conduite
        $conduiteApp = $this->getAppreciation($conduiteSur20);
        if ($conduiteSur20 > 0) {
            $totalMoyCoeff += $conduiteSur20; $totalCoeff += 1;
            $moyAutres[] = $conduiteSur20;
        }
        $bulletin[] = [
            'subject'        => 'CONDUITE',
            'coef'           => 1,
            'interros'       => [1=>'-',2=>'-',3=>'-',4=>'-',5=>'-'],
            'devoirs'        => [1=>'-', 2=>number_format($conduiteSur20,2,',','')],
            'moyenneInterro' => '-',
            'moyenne'        => $conduiteSur20 > 0 ? number_format($conduiteSur20,2,',','') : '-',
            'moyCoeff'       => $conduiteSur20 > 0 ? number_format($conduiteSur20,2,',','') : '-',
            'rang'           => $rangsParMatiere['CONDUITE'][$studentId] ?? '-',
            'appreciation'   => $conduiteApp,
        ];

        $moyGen = $totalCoeff > 0 ? round($totalMoyCoeff/$totalCoeff, 2) : null;
        $fmt    = fn($v) => ($v === null || $v === 0) ? '0,00' : number_format($v,2,',','');

        // Décision conseil
        $felicitation = $encouragement = $tableauHonneur = $avertissement = false;
        if ($moyGen !== null && $conduiteSur20 > 0) {
            if ($moyGen >= 16 && $conduiteSur20 >= 14)       $felicitation = true;
            elseif ($moyGen >= 14 && $conduiteSur20 >= 12)   $encouragement = true;
            elseif ($moyGen >= 12 && $conduiteSur20 >= 10)   $tableauHonneur = true;
            elseif ($conduiteSur20 < 10 || $moyGen < 10)     $avertissement = true;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('archives.bulletin_pdf', [
            'student'              => $student,
            'classe'               => $class,
            'activeYear'           => $year,
            'bulletin'             => $bulletin,
            'trimestre'            => $trimestre,
            'moyenneGenerale'      => $fmt($moyGen),
            'moyenneLitteraire'    => $fmt(!empty($moyLitt) ? round(array_sum($moyLitt)/count($moyLitt),2) : 0),
            'moyenneScientifique'  => $fmt(!empty($moySci)  ? round(array_sum($moySci)/count($moySci),2)   : 0),
            'moyenneAutres'        => $fmt(!empty($moyAutres)? round(array_sum($moyAutres)/count($moyAutres),2):0),
            'appreciationGenerale' => $this->getAppreciation($moyGen),
            'conduite'             => $fmt($conduiteSur20),
            'appreciationConduite' => $conduiteApp,
            'rang'                 => ($rangsGen[$studentId] ?? '-') . 'e',
            'plusForte'            => $fmt($plusForte),
            'plusFaible'           => $fmt($plusFaible),
            'moyClasse'            => $fmt($moyClasse),
            'felicitation'         => $felicitation,
            'encouragement'        => $encouragement,
            'tableauHonneur'       => $tableauHonneur,
            'avertissement'        => $avertissement,
            'totalMoyCoeff'        => $fmt($totalMoyCoeff),
            'totalCoeff'           => $totalCoeff,
            'totalEleves'          => count($allStudentIds),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Bulletin_Archive_{$student->last_name}_{$student->first_name}_T{$trimestre}_{$year->name}.pdf");
    }


    public function classBulletinsPdf($yearId, $classId, $trimestre) {
        $year   = AcademicYear::findOrFail($yearId);
        $class  = Classe::with('entity')->findOrFail($classId);
        $user   = auth()->user();

        if (!$this->isAdminOrSecretary($user) && !$this->isCenseur($user)) {
            abort(403, 'Accès refusé.');
        }

        $records = StudentAcademicRecord::where('academic_year_id', $year->id)
            ->where('class_id', $classId)
            ->with('student')
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        }])->orderBy('name')->get();

        $allStudentIds = $records->pluck('student_id');

        $allGradesClass = Grade::where('academic_year_id', $year->id)
            ->whereIn('student_id', $allStudentIds)
            ->where('trimestre', $trimestre)
            ->get()->groupBy(['student_id', 'subject_id']);

        $allConduitsClass = Conduct::where('academic_year_id', $year->id)
            ->where('trimestre', $trimestre)
            ->whereIn('student_id', $allStudentIds)
            ->get()->keyBy('student_id');

        $allPunitionsClass = Punishment::where('academic_year_id', $year->id)
            ->whereIn('student_id', $allStudentIds)
            ->selectRaw('student_id, SUM(hours) as total_hours')
            ->groupBy('student_id')
            ->pluck('total_hours', 'student_id');

        // Moyennes générales pour stats et rangs
        $moyennesClasse = [];
        $moyennesParMatiere = [];

        foreach ($allStudentIds as $sid) {
            $cGrade   = $allConduitsClass[$sid]->grade ?? 0;
            $punH     = $allPunitionsClass[$sid] ?? 0;
            $conduite = max(0, $cGrade - ($punH / 2));
            $tp = 0; $tc = 0;

            foreach ($subjects as $subject) {
                $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                $sg   = $allGradesClass[$sid][$subject->id] ?? collect();
                $interros = $sg->where('type','interrogation')->pluck('value')->filter()->values()->toArray();
                $d1 = $sg->where('type','devoir')->where('sequence',1)->first()->value ?? null;
                $d2 = $sg->where('type','devoir')->where('sequence',2)->first()->value ?? null;
                $mi = !empty($interros) ? array_sum($interros)/count($interros) : null;
                $notes = array_filter([$mi,$d1,$d2], fn($v) => $v!==null);
                if (!empty($notes)) {
                    $moy = array_sum($notes)/count($notes);
                    $moyennesParMatiere[$subject->id][$sid] = $moy;
                    $tp += $moy * $coef; $tc += $coef;
                }
            }
            $moyennesParMatiere['CONDUITE'][$sid] = $conduite;
            if ($conduite > 0) { $tp += $conduite; $tc += 1; }
            if ($tc > 0) $moyennesClasse[$sid] = round($tp/$tc, 2);
        }

        $rangsParMatiere = [];
        foreach ($moyennesParMatiere as $subId => $moys) {
            arsort($moys);
            $rang = 1;
            foreach ($moys as $sid => $m) {
                $rangsParMatiere[$subId][$sid] = $rang . 'e';
                $rang++;
            }
        }

        arsort($moyennesClasse);
        $rang = 1; $rangsGen = [];
        foreach ($moyennesClasse as $sid => $m) { $rangsGen[$sid] = $rang++; }

        $plusForte  = !empty($moyennesClasse) ? max($moyennesClasse) : 0;
        $plusFaible = !empty($moyennesClasse) ? min($moyennesClasse) : 0;
        $moyClasse  = !empty($moyennesClasse)
            ? round(array_sum($moyennesClasse)/count($moyennesClasse), 2) : 0;

        $fmt = fn($v) => ($v === null || $v === 0) ? '0,00' : number_format($v,2,',','');

        $matieresLitt = ['COMMUNICATION ECRITE','LECTURE','ANGLAIS','HISTOIRE-GEOGRAPHIE','FRANÇAIS','PHILOSOPHIE','ESPAGNOL','HGGSP'];
        $matieresSci  = ['MATHEMATIQUES','PHYSIQUE CHIMIE ET TECHNOLOGIE (PCT)','SCIENCE DE LA VIE ET DE LA TERRE (SVT)','ENSEIGNEMENTS SCIENTIFIQUES'];
        $matieresAut  = ['EDUCATION PHYSIQUE ET SPORTIVE (EPS)','CONDUITE'];

        $allBulletins = [];

        foreach ($records as $record) {
            $student = $record->student;
            if (!$student) continue;
            $sid = $student->id;

            $grades = Grade::where('student_id', $sid)
                ->where('academic_year_id', $year->id)
                ->where('trimestre', $trimestre)
                ->get();

            $conduct = $allConduitsClass[$sid] ?? null;
            $punH    = $allPunitionsClass[$sid] ?? 0;
            $conduiteSur20 = round(max(0, ($conduct?->grade ?? 0) - ($punH / 2)), 2);

            $bulletin = [];
            $totalMoyCoeff = 0; $totalCoeff = 0;
            $moyLitt = []; $moySci = []; $moyAutres = [];

            foreach ($subjects as $subject) {
                $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                $sg   = $grades->where('subject_id', $subject->id);

                $interros = $sg->where('type','interrogation')->sortBy('sequence')
                    ->pluck('value')->filter()->values()->toArray();
                $d1 = $sg->where('type','devoir')->where('sequence',1)->first()->value ?? null;
                $d2 = $sg->where('type','devoir')->where('sequence',2)->first()->value ?? null;
                $mi = !empty($interros) ? round(array_sum($interros)/count($interros),2) : null;
                $notes = array_filter([$mi,$d1,$d2], fn($v) => $v!==null);
                $moy   = !empty($notes) ? round(array_sum($notes)/count($notes),2) : null;
                $moyCoeff = $moy !== null ? round($moy * $coef, 2) : null;

                if ($moy !== null) {
                    $totalMoyCoeff += $moyCoeff; $totalCoeff += $coef;
                    $nom = strtoupper($subject->name);
                    if (in_array($nom, $matieresLitt)) $moyLitt[] = $moy;
                    elseif (in_array($nom, $matieresSci)) $moySci[] = $moy;
                    elseif (in_array($nom, $matieresAut)) $moyAutres[] = $moy;
                }

                $interrosF = [];
                for ($i = 1; $i <= 5; $i++) {
                    $interrosF[$i] = isset($interros[$i-1]) ? number_format($interros[$i-1],2,',','') : '-';
                }

                $bulletin[] = [
                    'subject'        => strtoupper($subject->name),
                    'coef'           => $coef,
                    'interros'       => $interrosF,
                    'devoirs'        => [
                        1 => $d1 !== null ? number_format($d1,2,',','') : '-',
                        2 => $d2 !== null ? number_format($d2,2,',','') : '-',
                    ],
                    'moyenneInterro' => $mi !== null ? number_format($mi,2,',','') : '-',
                    'moyenne'        => $moy !== null ? number_format($moy,2,',','') : '-',
                    'moyCoeff'       => $moyCoeff !== null ? number_format($moyCoeff,2,',','') : '-',
                    'rang'           => $rangsParMatiere[$subject->id][$sid] ?? '-',
                    'appreciation'   => $this->getAppreciation($moy),
                ];
            }

            $conduiteApp = $this->getAppreciation($conduiteSur20);
            if ($conduiteSur20 > 0) {
                $totalMoyCoeff += $conduiteSur20; $totalCoeff += 1;
                $moyAutres[] = $conduiteSur20;
            }
            $bulletin[] = [
                'subject'        => 'CONDUITE',
                'coef'           => 1,
                'interros'       => [1=>'-',2=>'-',3=>'-',4=>'-',5=>'-'],
                'devoirs'        => [1=>'-', 2=>number_format($conduiteSur20,2,',','')],
                'moyenneInterro' => '-',
                'moyenne'        => $conduiteSur20 > 0 ? number_format($conduiteSur20,2,',','') : '-',
                'moyCoeff'       => $conduiteSur20 > 0 ? number_format($conduiteSur20,2,',','') : '-',
                'rang'           => $rangsParMatiere['CONDUITE'][$sid] ?? '-',
                'appreciation'   => $conduiteApp,
            ];

            $moyGen = $totalCoeff > 0 ? round($totalMoyCoeff/$totalCoeff, 2) : null;

            $felicitation = $encouragement = $tableauHonneur = $avertissement = false;
            if ($moyGen !== null && $conduiteSur20 > 0) {
                if ($moyGen >= 16 && $conduiteSur20 >= 14)       $felicitation = true;
                elseif ($moyGen >= 14 && $conduiteSur20 >= 12)   $encouragement = true;
                elseif ($moyGen >= 12 && $conduiteSur20 >= 10)   $tableauHonneur = true;
                elseif ($conduiteSur20 < 10 || $moyGen < 10)     $avertissement = true;
            }

            $allBulletins[] = [
                'student'              => $student,
                'classe'               => $class,
                'activeYear'           => $year,
                'bulletin'             => $bulletin,
                'trimestre'            => $trimestre,
                'moyenneGenerale'      => $fmt($moyGen),
                'moyenneLitteraire'    => $fmt(!empty($moyLitt) ? round(array_sum($moyLitt)/count($moyLitt),2) : 0),
                'moyenneScientifique'  => $fmt(!empty($moySci)  ? round(array_sum($moySci)/count($moySci),2)   : 0),
                'moyenneAutres'        => $fmt(!empty($moyAutres)? round(array_sum($moyAutres)/count($moyAutres),2):0),
                'appreciationGenerale' => $this->getAppreciation($moyGen),
                'conduite'             => $fmt($conduiteSur20),
                'appreciationConduite' => $conduiteApp,
                'rang'                 => ($rangsGen[$sid] ?? '-') . 'e',
                'plusForte'            => $fmt($plusForte),
                'plusFaible'           => $fmt($plusFaible),
                'moyClasse'            => $fmt($moyClasse),
                'felicitation'         => $felicitation,
                'encouragement'        => $encouragement,
                'tableauHonneur'       => $tableauHonneur,
                'avertissement'        => $avertissement,
                'totalMoyCoeff'        => $fmt($totalMoyCoeff),
                'totalCoeff'           => $totalCoeff,
                'totalEleves'          => count($allStudentIds),
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('archives.all_bulletins_pdf', [
            'allBulletins' => $allBulletins,
        ])->setPaper('a4', 'portrait');

        $nomClasse = str_replace([' ', '/'], '_', $class->name);
        return $pdf->download("Bulletins_Archive_{$nomClasse}_T{$trimestre}_{$year->name}.pdf");
    }


    public function classNotesParMatiereJson($yearId, $classId)
    {
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::with('entity')->findOrFail($classId);
        $user  = auth()->user();
        $this->authorizeAccess($user, $class, $year);

        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        }])->orderBy('name')->get();

        $records = StudentAcademicRecord::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        $studentIds = $records->pluck('student_id');

        $students = Student::whereIn('id', $studentIds)
            ->orderBy('last_name')->orderBy('first_name')
            ->get()->keyBy('id');

        $trimestres = [1, 2, 3];
        $result = [];

        foreach ($subjects as $subject) {
            $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
            $subjectData = [
                'id'   => $subject->id,
                'name' => $subject->name,
                'coef' => $coef,
                'eleves' => [],
            ];

            $allGrades = Grade::where('class_id', $classId)
                ->where('academic_year_id', $year->id)
                ->where('subject_id', $subject->id)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->groupBy(['student_id', 'trimestre']);

            $moyennesParTrimestre = []; // pour rangs

            foreach ($records as $record) {
                $sid = $record->student_id;
                $student = $students[$sid] ?? null;
                if (!$student) continue;

                $eleveData = [
                    'id'      => $sid,
                    'nom'     => $student->last_name,
                    'prenom'  => $student->first_name,
                    'trimestres' => [],
                ];

                foreach ($trimestres as $t) {
                    $sg = $allGrades[$sid][$t] ?? collect();
                    $interros = $sg->where('type', 'interrogation')->pluck('value')->filter()->values()->toArray();
                    $d1 = $sg->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                    $d2 = $sg->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;
                    $mi = !empty($interros) ? round(array_sum($interros) / count($interros), 2) : null;
                    $notes = array_filter([$mi, $d1, $d2], fn($v) => $v !== null);
                    $moy = !empty($notes) ? round(array_sum($notes) / count($notes), 2) : null;

                    if ($moy !== null) {
                        $moyennesParTrimestre[$t][$sid] = $moy;
                    }

                    $eleveData['trimestres'][$t] = [
                        'interros' => $interros,
                        'devoir1'  => $d1,
                        'devoir2'  => $d2,
                        'moyInterro' => $mi,
                        'moyenne'  => $moy,
                    ];
                }

                $subjectData['eleves'][] = $eleveData;
            }

            // Rangs par trimestre
            foreach ($trimestres as $t) {
                $sorted = $moyennesParTrimestre[$t] ?? [];
                arsort($sorted);
                $rang = 1;
                $rangs = [];
                foreach ($sorted as $sid => $m) { $rangs[$sid] = $rang++; }

                foreach ($subjectData['eleves'] as &$el) {
                    $el['trimestres'][$t]['rang'] = $rangs[$el['id']] ?? null;
                }
                unset($el);
            }

            $result[] = $subjectData;
        }

        return response()->json([
            'year'     => ['id' => $year->id, 'name' => $year->name],
            'class'    => ['id' => $class->id, 'name' => $class->name],
            'total'    => $records->count(),
            'subjects' => $result,
        ]);
    }
    
    public function classNotesParMatierePdf($yearId, $classId, $subjectId, $trimestre)
    {
        $year    = AcademicYear::findOrFail($yearId);
        $class   = Classe::with('entity')->findOrFail($classId);
        $user    = auth()->user();
        $this->authorizeAccess($user, $class, $year);

        $subject = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)->where('academic_year_id', $year->id);
        }])->findOrFail($subjectId);

        $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;

        $records = StudentAcademicRecord::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        $studentIds = $records->pluck('student_id');
        $students   = Student::whereIn('id', $studentIds)
            ->orderBy('last_name')->orderBy('first_name')
            ->get()->keyBy('id');

        $allGrades = Grade::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->where('subject_id', $subjectId)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->groupBy('student_id');

        $rows = [];
        $moyennes = [];

        foreach ($records as $record) {
            $sid     = $record->student_id;
            $student = $students[$sid] ?? null;
            if (!$student) continue;

            $sg = $allGrades[$sid] ?? collect();

            if ($trimestre == 0) {
                // Toutes les trimestres
                $moyTri = [];
                foreach ([1,2,3] as $t) {
                    $sgT = $sg->where('trimestre', $t);
                    $interros = $sgT->where('type', 'interrogation')->pluck('value')->filter()->values()->toArray();
                    $d1 = $sgT->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                    $d2 = $sgT->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;
                    $mi = !empty($interros) ? round(array_sum($interros) / count($interros), 2) : null;
                    $notes = array_filter([$mi, $d1, $d2], fn($v) => $v !== null);
                    $moyTri[$t] = !empty($notes) ? round(array_sum($notes) / count($notes), 2) : null;
                }
                $moysValid = array_filter($moyTri, fn($v) => $v !== null);
                $moyAnn = !empty($moysValid) ? round(array_sum($moysValid) / count($moysValid), 2) : null;

                $rows[] = [
                    'student' => $student,
                    'moy_t1'  => $moyTri[1],
                    'moy_t2'  => $moyTri[2],
                    'moy_t3'  => $moyTri[3],
                    'moy_ann' => $moyAnn,
                ];
                if ($moyAnn !== null) $moyennes[$sid] = $moyAnn;
            } else {
                $sgT = $sg->where('trimestre', $trimestre);
                $interros = $sgT->where('type', 'interrogation')->sortBy('sequence')->pluck('value')->filter()->values()->toArray();
                $d1 = $sgT->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                $d2 = $sgT->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;
                $mi = !empty($interros) ? round(array_sum($interros) / count($interros), 2) : null;
                $notes = array_filter([$mi, $d1, $d2], fn($v) => $v !== null);
                $moy   = !empty($notes) ? round(array_sum($notes) / count($notes), 2) : null;

                $rows[] = [
                    'student'    => $student,
                    'interros'   => $interros,
                    'devoir1'    => $d1,
                    'devoir2'    => $d2,
                    'moyInterro' => $mi,
                    'moyenne'    => $moy,
                ];
                if ($moy !== null) $moyennes[$sid] = $moy;
            }
        }

        // Rangs
        arsort($moyennes);
        $rang = 1; $rangs = [];
        foreach ($moyennes as $sid => $m) { $rangs[$sid] = $rang++; }

        foreach ($rows as &$row) {
            $sid = $row['student']->id;
            $row['rang'] = $rangs[$sid] ?? '-';
        }
        unset($row);

        // Stats
        $allMoys  = array_values($moyennes);
        $maxMoy   = !empty($allMoys) ? max($allMoys) : 0;
        $minMoy   = !empty($allMoys) ? min($allMoys) : 0;
        $moyClass = !empty($allMoys) ? round(array_sum($allMoys) / count($allMoys), 2) : 0;
        $admis    = count(array_filter($allMoys, fn($m) => $m >= 10));

        $fmt = fn($v) => $v !== null ? number_format($v, 2, ',', '') : '-';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('archives.class_notes_matiere_pdf', [
            'year'      => $year,
            'class'     => $class,
            'subject'   => $subject,
            'coef'      => $coef,
            'trimestre' => $trimestre,
            'rows'      => $rows,
            'maxMoy'    => $fmt($maxMoy),
            'minMoy'    => $fmt($minMoy),
            'moyClass'  => $fmt($moyClass),
            'admis'     => $admis,
            'total'     => count($rows),
            'fmt'       => $fmt,
        ])->setPaper('a4', 'landscape');

        $nomMat = str_replace([' ', '/'], '_', $subject->name);
        $tri    = $trimestre == 0 ? 'Annuel' : "T{$trimestre}";
        return $pdf->download("Notes_{$nomMat}_{$class->name}_{$tri}_{$year->name}.pdf");
    }


    private function isAdminOrSecretary($user): bool {
        return in_array($user->id, [1, 8])
            || in_array(optional($user->role)->name, ['super_admin', 'secretaire']);
    }



    private function isCenseur($user): bool
    {
        return $user->id == 6
            || optional($user->role)->name === 'censeur';
    }

    private function authorizeAccess($user, $class, $year): void
    {
        if ($this->isAdminOrSecretary($user)) return;
        if ($user->id == 5 && in_array($class->entity_id, [1, 2])) return;
        if ($this->isCenseur($user) && $class->entity_id == 3) return;

        $isTeacherOfClass = ClassTeacherSubject::where('teacher_id', $user->id)
            ->where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->exists();

        if (!$isTeacherOfClass) abort(403);
    }

    private function getYearPaymentStats(AcademicYear $year): array
    {
        $records  = StudentAcademicRecord::where('academic_year_id', $year->id)->get();
        $total    = $records->sum('total_fees');
        $paid     = $records->sum('amount_paid');
        $students = $records->count();

        return [
            'total_students' => $students,
            'total_fees'     => $total,
            'total_paid'     => $paid,
            'total_remaining'=> max(0, $total - $paid),
            'rate'           => $total > 0 ? round(($paid / $total) * 100) : 0,
        ];
    }

    private function getClassPaymentStatsFromRecords(int $classId, AcademicYear $year): array
    {
        $records = StudentAcademicRecord::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->get();

        $total = $records->sum('total_fees');
        $paid  = $records->sum('amount_paid');

        return [
            'total_students'  => $records->count(),
            'fully_paid'      => $records->filter(fn($r) => $r->amount_paid >= $r->total_fees && $r->total_fees > 0)->count(),
            'total_fees'      => $total,
            'total_paid'      => $paid,
            'total_remaining' => max(0, $total - $paid),
            'rate'            => $total > 0 ? round(($paid / $total) * 100) : 0,
        ];
    }

    private function getAppreciation(?float $moy): string
    {
        if ($moy === null) return '-';
        return match (true) {
            $moy > 16  => 'Très Bien',
            $moy >= 14 => 'Bien',
            $moy >= 12 => 'Assez Bien',
            $moy >= 10 => 'Passable',
            $moy >= 8  => 'Insuffisant',
            $moy >= 6  => 'Faible',
            $moy >= 4  => 'Médiocre',
            default    => 'Très Faible',
        };
    }
}
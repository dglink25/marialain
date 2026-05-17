<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\Timetable;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\ClassTeacherSubject;
use App\Models\Conduct;
use App\Models\Punishment;


class ArchiveController extends Controller
{
    // ─── Rôles autorisés ────────────────────────────────────────────────────────
    // super_admin / secretaire  → id 1, 8   (accès total)
    // censeur                   → id 6       (classes, élèves, EDT, notes)
    // directeur_primaire        → id 5       (entities 1 & 2)
    // teacher                   → rôle       (ses propres classes)
    // parent                    → guard      (ses enfants uniquement)

    /* =====================================================================
     *  INDEX – liste des années archivées
     * ===================================================================== */
    public function index()
    {
        $archives = AcademicYear::archives()->get();
        return view('archives.index', compact('archives'));
    }

    /* =====================================================================
     *  SHOW – classes d'une année archivée (selon le rôle)
     * ===================================================================== */
    public function show($id)
    {
        $user  = auth()->user();
        $year  = AcademicYear::findOrFail($id);

        if ($year->active) {
            return redirect()->route('home')
                ->with('error', 'Cette année est encore active, pas une archive.');
        }

        $classesQuery = $year->classes()->with(['students', 'entity']);

        // ── Filtrage selon le rôle ─────────────────────────────────────────
        if ($this->isAdminOrSecretary($user)) {
            $classes = $classesQuery->get();

        } elseif ($user->id == 5) {
            // directeur primaire → entities 1 & 2
            $classes = $classesQuery->whereIn('entity_id', [1, 2])->get();

        } elseif ($this->isCenseur($user)) {
            // censeur → entity 3
            $classes = $classesQuery->where('entity_id', 3)->get();

        } else {
            // Enseignant → uniquement ses classes
            $classes = $classesQuery->whereHas('classTeacherSubjects', function ($q) use ($user, $year) {
                $q->where('teacher_id', $user->id)
                  ->where('academic_year_id', $year->id);
            })->get();
        }

        // Comptage des élèves pour chaque classe
        foreach ($classes as $class) {
            $class->studentsCount = $class->students()
                ->where('academic_year_id', $year->id)
                ->count();
        }

        $canViewPayments  = $this->isAdminOrSecretary($user);
        $canViewTimetable = $this->isAdminOrSecretary($user) || $this->isCenseur($user);
        $canViewNotes     = $this->isAdminOrSecretary($user) || $this->isCenseur($user);

        // Stats globales de paiement pour admin/secretaire
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

    /* =====================================================================
     *  ÉLÈVES D'UNE CLASSE ARCHIVÉE
     * ===================================================================== */
    public function classStudents($yearId, $classId)
    {
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::with('entity')->findOrFail($classId);
        $user  = auth()->user();

        if ($class->academic_year_id !== $year->id) abort(404);

        $this->authorizeAccess($user, $class, $year);

        $students = $class->students()
            ->where('academic_year_id', $year->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(30);

        // Stats paiement par élève (admin/sec uniquement)
        $canViewPayments = $this->isAdminOrSecretary($user);

        // Stats de paiement par classe
        $classPaymentStats = null;
        if ($canViewPayments) {
            $classPaymentStats = $this->getClassPaymentStats($class, $year);
        }

        return view('archives.class_students', compact(
            'year', 'class', 'students',
            'canViewPayments', 'classPaymentStats'
        ));
    }

    /* =====================================================================
     *  EMPLOI DU TEMPS ARCHIVÉ
     * ===================================================================== */
    public function classTimetables($yearId, $classId)
    {
        $user  = auth()->user();
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::findOrFail($classId);

        if ($class->academic_year_id !== $year->id) {
            abort(404, 'Cette classe n\'appartient pas à cette année.');
        }

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

    /* =====================================================================
     *  NOTES ARCHIVÉES D'UNE CLASSE (tous les trimestres)
     * ===================================================================== */
    public function classNotes($yearId, $classId)
    {
        $user  = auth()->user();
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::with('entity')->findOrFail($classId);

        if ($class->academic_year_id !== $year->id) abort(404);

        if (!$this->isAdminOrSecretary($user) && !$this->isCenseur($user)) {
            abort(403, 'Accès refusé.');
        }

        // Matières de la classe
        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)
              ->where('academic_year_id', $year->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $year) {
            $q->where('class_id', $classId)
              ->where('academic_year_id', $year->id);
        }])->orderBy('name')->get();

        // Élèves validés
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->where('is_validated', 1)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();

        // Récupérer toutes les notes en une seule requête
        $allGrades = Grade::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy(['student_id', 'trimestre', 'subject_id']);

        // Conduites et punitions
        $conducts = Conduct::where('academic_year_id', $year->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy(['student_id', 'trimestre']);

        $punishments = Punishment::where('academic_year_id', $year->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->selectRaw('student_id, SUM(hours) as total_hours')
            ->groupBy('student_id')
            ->pluck('total_hours', 'student_id');

        // Calculer les moyennes par trimestre pour chaque élève
        $tableauNotes = [];
        $trimestres = [1, 2, 3];

        foreach ($students as $student) {
            $row = ['student' => $student, 'trimestres' => []];

            foreach ($trimestres as $t) {
                $punishH    = $punishments[$student->id] ?? 0;
                $conductGrd = $conducts[$student->id][$t][0]->grade ?? 0;
                $conduite   = max(0, $conductGrd - ($punishH / 2));

                $totalPts  = 0;
                $totalCoef = 0;

                foreach ($subjects as $subject) {
                    $coef = $subject->classTeacherSubjects->first()->coefficient ?? 1;
                    $studentSubjectGrades = $allGrades[$student->id][$t][$subject->id] ?? collect();

                    $interros = $studentSubjectGrades->where('type', 'interrogation')
                        ->pluck('value')->filter()->values()->toArray();
                    $d1 = $studentSubjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                    $d2 = $studentSubjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

                    $moyInterro   = !empty($interros) ? array_sum($interros) / count($interros) : null;
                    $notes4Moy    = array_filter([$moyInterro, $d1, $d2], fn($v) => $v !== null);
                    $moyMatiere   = !empty($notes4Moy) ? array_sum($notes4Moy) / count($notes4Moy) : null;

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

            // Moyenne annuelle
            $moys = array_filter(array_column($row['trimestres'], 'moyenne'), fn($v) => $v !== null);
            $row['moy_annuelle'] = !empty($moys) ? round(array_sum($moys) / count($moys), 2) : null;

            $tableauNotes[] = $row;
        }

        // Rangs par trimestre + annuel
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

        // Rang annuel
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

    /* =====================================================================
     *  STATS DE PAIEMENT D'UNE CLASSE (admin/sec uniquement)
     * ===================================================================== */
    public function classPaymentStats($yearId, $classId)
    {
        $user  = auth()->user();
        $year  = AcademicYear::findOrFail($yearId);
        $class = Classe::findOrFail($classId);

        if (!$this->isAdminOrSecretary($user)) abort(403);
        if ($class->academic_year_id !== $year->id) abort(404);

        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $year->id)
            ->orderBy('last_name')->orderBy('first_name')
            ->with('payments')
            ->get();

        $stats = $this->getClassPaymentStats($class, $year);

        return view('archives.class_payment_stats', compact('year', 'class', 'students', 'stats'));
    }

    /* =====================================================================
     *  ARCHIVES POUR LES PARENTS (enfants uniquement)
     * ===================================================================== */
    public function parentIndex()
    {
        $parent   = auth('parent')->user();
        $archives = AcademicYear::archives()->get();

        // Garder uniquement les années où le parent a des enfants
        $archives = $archives->filter(function ($year) use ($parent) {
            return Student::where('parent_phone', $parent->phone)
                ->where('academic_year_id', $year->id)
                ->exists();
        });

        return view('archives.parent.index', compact('archives'));
    }

    public function parentShow($yearId)
    {
        $parent   = auth('parent')->user();
        $year     = AcademicYear::findOrFail($yearId);

        if ($year->active) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Cette année est encore active.');
        }

        $students = Student::where('parent_phone', $parent->phone)
            ->where('academic_year_id', $year->id)
            ->with(['classe', 'payments'])
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('archives.parent.index')
                ->with('error', 'Aucun enfant trouvé pour cette année.');
        }

        return view('archives.parent.show', compact('year', 'students'));
    }

    public function parentChildDetails($yearId, $studentId)
    {
        $parent  = auth('parent')->user();
        $year    = AcademicYear::findOrFail($yearId);
        $student = Student::findOrFail($studentId);

        // Vérification d'accès
        if ($student->parent_phone !== $parent->phone) abort(403);
        if ($student->academic_year_id !== $year->id) abort(404);

        $classId = $student->class_id;
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
        $payments   = $student->payments()->where('academic_year_id', $year->id)->get();
        $totalPaid  = $payments->sum('amount');
        $totalFees  = $student->total_fees ?? 0;

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

        // Calculer les moyennes par trimestre
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
                    'subject'    => $subject->name,
                    'coef'       => $coef,
                    'interros'   => $interros,
                    'devoir1'    => $d1,
                    'devoir2'    => $d2,
                    'moyInterro' => $moyInterr,
                    'moyenne'    => $moyMat,
                    'appreciation' => $this->getAppreciation($moyMat),
                ];
            }

            if ($conduite > 0) { $totalPts += $conduite; $totalCoef += 1; }

            $moyGen = $totalCoef > 0 ? round($totalPts / $totalCoef, 2) : null;

            $trimestresData[$t] = [
                'bulletin'        => $bulletinRows,
                'conduite'        => round($conduite, 2),
                'moyenneGenerale' => $moyGen,
                'appreciation'    => $this->getAppreciation($moyGen),
            ];
        }

        // Moyenne annuelle
        $moys = array_filter(array_column($trimestresData, 'moyenneGenerale'), fn($v) => $v !== null);
        $moyAnnuelle = !empty($moys) ? round(array_sum($moys) / count($moys), 2) : null;

        return view('archives.parent.child_details', compact(
            'year', 'student', 'class', 'subjects',
            'trimestresData', 'moyAnnuelle',
            'payments', 'totalPaid', 'totalFees',
            'timetables', 'hours', 'days'
        ));
    }

    /* =====================================================================
     *  MÉTHODES PRIVÉES
     * ===================================================================== */

    private function isAdminOrSecretary($user): bool
    {
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

        // Enseignant → vérifier qu'il est dans cette classe
        $isTeacherOfClass = ClassTeacherSubject::where('teacher_id', $user->id)
            ->where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->exists();

        if (!$isTeacherOfClass) abort(403);
    }

    private function getYearPaymentStats($year): array
    {
        $classes  = $year->classes()->with('students.payments')->get();
        $total    = 0; $paid = 0; $students = 0;

        foreach ($classes as $class) {
            foreach ($class->students as $student) {
                $students++;
                $total += $student->total_fees ?? 0;
                $paid  += $student->payments->where('academic_year_id', $year->id)->sum('amount');
            }
        }

        return [
            'total_students' => $students,
            'total_fees'     => $total,
            'total_paid'     => $paid,
            'total_remaining'=> max(0, $total - $paid),
            'rate'           => $total > 0 ? round(($paid / $total) * 100) : 0,
        ];
    }

    private function getClassPaymentStats($class, $year): array
    {
        $students = $class->students()->where('academic_year_id', $year->id)->with('payments')->get();
        $total = 0; $paid = 0;

        foreach ($students as $s) {
            $total += $s->total_fees ?? 0;
            $paid  += $s->payments->where('academic_year_id', $year->id)->sum('amount');
        }

        return [
            'total_students'  => $students->count(),
            'fully_paid'      => $students->filter(fn($s) => ($s->payments->sum('amount') >= ($s->total_fees ?? 0)) && ($s->total_fees ?? 0) > 0)->count(),
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
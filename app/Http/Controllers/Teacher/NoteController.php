<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NoteController extends Controller{
    public function index($classId){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $classe = Classe::with('students')->findOrFail($classId);

        return view('teacher.notes.index', compact('classe', 'activeYear'));
    }

    public function create($classId, $type, $num){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Pas d\'année académique active.');
        }

        $classe = Classe::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classId);

        // Validation : ne pas ajouter Interro2 si Interro1 vide
        if ($type === 'interrogation' && $num > 1) {
            $previous = Grade::where('class_id', $classId)
                ->where('type', 'interrogation')
                ->where('sequence', $num - 1)
                ->where('academic_year_id', $activeYear->id)
                ->exists();

            if (!$previous) {
                return back()->with('error', "Impossible d'ajouter Interrogation $num tant que Interrogation " . ($num - 1) . " n'est pas saisie.");
            }
        }

        // Nouvelle vérification : notes déjà saisies pour cette interrogation/devoir
        $existing = Grade::where('class_id', $classId)
            ->where('type', $type)
            ->where('sequence', $num)
            ->where('academic_year_id', $activeYear->id)
            ->exists();

        if ($existing) {
            return back()->with('error', "Les notes pour $type $num ont déjà été saisies pour cette classe. Vous ne pouvez pas les ajouter à nouveau.");
        }

        return view('teacher.notes.create', compact('classe', 'type', 'num'));
    }


    public function store(Request $request, $classId, $type, $num){
        $request->validate([
            'notes.*' => 'nullable|numeric|min:0|max:20',
        ]);

        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with('students')->findOrFail($classId);

        foreach ($classe->students as $student) {
            if (isset($request->notes[$student->id])) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $request->subject_id, // ⚠️ à récupérer dynamiquement selon la matière
                        'type' => $type,
                        'sequence' => $num,
                        'class_id' => $classId,
                        'trimestre' => $request->trimestre,
                        'academic_year_id' => $activeYear->id,
                    ],
                    [
                        'value' => $request->notes[$student->id],
                    ]
                );
            }
        }

        return redirect()->route('teacher.classes.notes', $classId)->with('success', 'Notes enregistrées.');
    }

    public function list($classId){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $classe = Classe::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classId);

        return view('teacher.notes.list', compact('classe', 'activeYear'));
    }

    public function showClassNotesAll($classId){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $classe = Classe::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
        }, 'subjects'])->findOrFail($classId);

        // Préparer les notes de chaque élève par matière et type
        $gradesData = [];
        foreach ($classe->students as $student) {
            $studentGrades = [];
            foreach ($classe->subjects as $subject) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                $interros = $grades->where('type', 'interrogation')->sortBy('sequence')->pluck('value')->toArray();
                $devoirs  = $grades->where('type', 'devoir')->sortBy('sequence')->pluck('value')->toArray();

                $moyenneInterro = count($interros) > 0 ? round(array_sum($interros)/count($interros), 2) : null;
                $coef = $subject->coefficient ?? 1;
                $moyenneMat = $moyenneInterro && count($devoirs) > 0
                    ? round((($moyenneInterro + array_sum($devoirs)/count($devoirs)) / 2) * $coef, 2)
                    : null;

                $studentGrades[$subject->id] = [
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenneInterro' => $moyenneInterro,
                    'coef' => $coef,
                    'moyenneMat' => $moyenneMat,
                ];

            }   
            
            $gradesData[$student->id] = $studentGrades;
            dd($gradesData);
        }

        return view('teacher.notes.class_notes', compact('classe', 'gradesData', 'activeYear'));
    }

    public function showClassNotes($classId){
        // 1) année active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // 2) récupérer la classe + élèves
        $classe = Classe::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classId);

        // 3) id enseignant connecté
        $teacherId = Auth::id();

        // 4) détecter la table pivot et les colonnes disponibles
        $possiblePivotTables = [
            'class_teacher_subjects',
            'class_teacher_subject',
            'classe_subject_teacher',
            'classe_subjects',
            'class_subject_teacher',
            'class_subjects',
        ];

        $pivotTable = null;
        foreach ($possiblePivotTables as $t) {
            if (Schema::hasTable($t)) { $pivotTable = $t; break; }
        }

        if (! $pivotTable) {
            return back()->with('error', "Table pivot introuvable (class <-> subject <-> teacher). Créez `class_teacher_subjects` ou adaptez la configuration.");
        }

        // identifier colonnes possibles dans la pivot
        $classCols = ['classe_id','class_id','classe','class'];
        $subjectCols = ['subject_id','matiere_id','subject','matiere'];
        $teacherCols = ['teacher_id','user_id','teacher','user'];

        $classCol = null; $subjectCol = null; $teacherCol = null;

        foreach ($classCols as $c) { if (Schema::hasColumn($pivotTable, $c)) { $classCol = $c; break; } }
        foreach ($subjectCols as $c) { if (Schema::hasColumn($pivotTable, $c)) { $subjectCol = $c; break; } }
        foreach ($teacherCols as $c) { if (Schema::hasColumn($pivotTable, $c)) { $teacherCol = $c; break; } }

        if (! $classCol || ! $subjectCol || ! $teacherCol) {
            return back()->with('error', "La table pivot `$pivotTable` existe mais les colonnes attendues sont manquantes (attendues: ".implode(',', $classCols)." | ".implode(',', $subjectCols)." | ".implode(',', $teacherCols).").");
        }

        // 5) récupérer les subject_ids liés à cette classe pour l'enseignant
        $subjectIds = DB::table($pivotTable)
            ->where($classCol, $classId)
            ->where($teacherCol, $teacherId)
            ->pluck($subjectCol)
            ->unique()
            ->toArray();

        if (empty($subjectIds)) {
            return back()->with('error', "Aucune matière assignée à cet enseignant pour cette classe (vérifiez la table pivot `$pivotTable`).");
        }

        // 6) charger les matières sélectionnées
        $subjects = Subject::whereIn('id', $subjectIds)->get();
        if ($subjects->isEmpty()) {
            return back()->with('error', "Les matières liées via la pivot existent mais n'ont pas été trouvées dans la table `subjects`.");
        }

        // 7) préparer les notes par élève × matière
        $gradesData = [];
        foreach ($classe->students as $student) {
            $studentGrades = [];
            foreach ($subjects as $subject) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                $interros = $grades->where('type', 'interrogation')->sortBy('sequence')->pluck('value')->toArray();
                $devoirs  = $grades->where('type', 'devoir')->sortBy('sequence')->pluck('value')->toArray();

                $moyenneInterro = count($interros) > 0 ? round(array_sum($interros) / count($interros), 2) : null;
                $coef = $subject->coefficient ?? ($subject->coef ?? 1);
                $moyenneMat = null;
                // calcul simple : moyenneInterro + moyenne devoirs / 2 (si données présentes)
                if ($moyenneInterro !== null) {
                    $moyDevoirs = count($devoirs) > 0 ? (array_sum($devoirs) / count($devoirs)) : null;
                    if ($moyDevoirs !== null) {
                        $avg = ($moyenneInterro + $moyDevoirs) / 2;
                        $moyenneMat = round($avg * $coef, 2);
                    }
                }

                $studentGrades[$subject->id] = [
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenneInterro' => $moyenneInterro,
                    'coef' => $coef,
                    'moyenneMat' => $moyenneMat,
                    'subject' => $subject,
                ];
            }
            $gradesData[$student->id] = $studentGrades;
        }

        // 8) envoyer à la vue
        return view('teacher.notes.class_notes', [
            'classe' => $classe,
            'subjects' => $subjects,        // matières de l'enseignant (pratique en blade)
            'gradesData' => $gradesData,
            'activeYear' => $activeYear,
        ]);
    }

    public function calcInterro($classId){
        // ⚡ calculer moyenne interro par élève (minimum 2 notes)
    }

    public function calcTrimestre($classId) {
        // ⚡ calculer moyenne trimestrielle et sauvegarder
    }
}


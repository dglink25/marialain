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

    public function index($classId, $trimestre){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // Vérifier si le censeur a autorisé la saisie pour cette classe et trimestre
        $permission = \App\Models\NotePermission::where('class_id', $classId)
            
            ->where('trimestre', $trimestre)
            ->first();

        if (!$permission || $permission->is_open != 1) {
            return back()->with('error', "La saisie des notes n'est pas encore autorisée pour ce trimestre.");
        }

        $classe = Classe::with(['students.grades' => function ($q) use ($activeYear, $trimestre) {
            $q->where('academic_year_id', $activeYear->id)
            ->where('trimestre', $trimestre);
        }])->findOrFail($classId);

        // Vérifier si des notes existent
        $hasNotes = $classe->students->flatMap->grades->isNotEmpty();

        return view('teacher.notes.index', compact('classe', 'activeYear', 'trimestre', 'hasNotes'));
    }

    public function read($classId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Pas d\'année académique active.');
        }

        $classe = Classe::with(['students' => function($q) use ($activeYear, $type, $num, $trimestre) {
            $q->where('academic_year_id', $activeYear->id)
                ->orderBy('last_name')
                ->where('is_validated', 1)
                ->with(['grades' => function($q2) use ($activeYear, $type, $num, $trimestre) {
                    $q2->where('type', $type)
                        ->where('sequence', $num)
                        ->where('trimestre', $trimestre)
                        ->where('academic_year_id', $activeYear->id);
                }]);
        }])->findOrFail($classId);

        return view('teacher.notes.read', compact('classe', 'type', 'num', 'trimestre'));
    }

    public function chooseTrimestre($classId){
        $classe = Classe::findOrFail($classId);
        return view('teacher.notes.trimestres', compact('classe'));
    }

    public function create($classId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Pas d\'année académique active.');
        }

        $classe = Classe::with(['students' => function ($query) use ($activeYear) {
            $query->where('is_validated', 1) 
                ->where('academic_year_id', $activeYear->id)
                ->orderBy('last_name')
                ->orderBy('first_name');
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

        return view('teacher.notes.create', compact('classe', 'type', 'num', 'trimestre'));
    }


    public function store(Request $request, $classId, $type, $num, $trimestre){
        $request->validate([
            'notes.*' => 'nullable|numeric|min:0|max:20',
        ]);

        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with('students')->findOrFail($classId);

        // ⚡ récupérer le subject_id assigné à l'enseignant pour cette classe
        $subjectPivot = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('teacher_id', Auth::id())
            ->first();

        if (!$subjectPivot) {
            return back()->with('error', 'Aucune matière assignée à cet enseignant pour cette classe.');
        }

        $subjectId = $subjectPivot->subject_id;

        foreach ($classe->students as $student) {
            if (isset($request->notes[$student->id])) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subjectId,  
                        'type' => $type,
                        'sequence' => $num,
                        'class_id' => $classId,
                        'trimestre' => $trimestre,
                        'academic_year_id' => $activeYear->id,
                    ],
                    [
                        'value' => $request->notes[$student->id],
                    ]
                );
            }
        }

        return redirect()->route('teacher.classes.notes', [$classe->id, $trimestre])
            ->with('success', 'Notes enregistrées.');
    }

    public function edit($classId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function($q) use ($type, $num, $activeYear, $trimestre) {
            $q->where('academic_year_id', $activeYear->id)
                ->where('is_validated', 1)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->with(['grades' => function($q2) use ($type, $num, $activeYear, $trimestre) {
                    $q2->where('type', $type)
                        ->where('sequence', $num)
                        ->where('trimestre', $trimestre)
                        ->where('academic_year_id', $activeYear->id);
                }]);
        }])->findOrFail($classId);

        return view('teacher.notes.edit', compact('classe','type','num','trimestre'));
    }

    // Mettre à jour les notes
    public function update(Request $request, $classId, $type, $num, $trimestre){
        $request->validate([
            'notes.*' => 'nullable|min:0|max:20'
        ]);
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // Récupérer subject_id de l'enseignant pour cette classe
        $subject = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('teacher_id', Auth::id())
            ->first();

        if (!$subject) {
            return back()->with('error','Aucune matière assignée à cet enseignant.');
        }

        foreach ($request->notes as $studentId => $value) {
            Grade::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $subject->subject_id,
                    'type' => $type,
                    'sequence' => $num,
                    'trimestre' => $trimestre,
                    'class_id' => $classId,
                    'academic_year_id' => $activeYear->id
                ],
                ['value' => $value]
            );
        }

        return redirect()->route('teacher.classes.notes.read', [$classId, $type, $num, $trimestre])
                         ->with('success','Notes mises à jour avec succès.');
    }

    // Supprimer toutes les notes pour ce type/séquence
    public function destroy($classId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $subject = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('teacher_id', Auth::id())
            ->first();

        if (!$subject) {
            return back()->with('error','Aucune matière assignée à cet enseignant.');
        }

        Grade::where('class_id', $classId)
            ->where('subject_id', $subject->subject_id)
            ->where('type', $type)
            ->where('sequence', $num)
            ->where('academic_year_id', $activeYear->id)
            ->delete();

        return redirect()->route('teacher.classes.notes.read', [$classId, $type, $num, $trimestre])
                         ->with('success','Toutes les notes ont été supprimées.');
    }

    public function showClassNotesAll($classId, $trimestre){
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
                    ->where('trimestre', $trimestre)
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

        return view('teacher.notes.class_notes', compact('classe', 'gradesData', 'activeYear', 'trimestre'));
    }

    public function showClassNotes($classId, $trimestre){
        // 1) année académique active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // 2) récupérer la classe avec les élèves
        $classe = Classe::with(['students' => function ($q) use ($activeYear) {
            $q->where('is_validated', 1)
              ->where('academic_year_id', $activeYear->id)
              ->orderBy('last_name')->orderBy('first_name');
            }])->findOrFail($classId);

        // 3) récupérer les matières liées à l’enseignant connecté
        $teacherId = Auth::id();

        // Vérifier table pivot existante
        $pivotTable = 'class_teacher_subject'; // adapter si nécessaire

        $subjectIds = DB::table($pivotTable)
            ->where('class_id', $classId)
            ->where('teacher_id', $teacherId)
            ->pluck('subject_id')
            ->unique()
            ->toArray();

        if (empty($subjectIds)) {
            return back()->with('error', "Aucune matière assignée à cet enseignant pour cette classe.");
        }

        $subjects = Subject::whereIn('id', $subjectIds)->get();

        // 4) préparation des notes par élève × matière
        $gradesData = [];

        foreach ($classe->students as $student) {
            $studentGrades = [];

            foreach ($subjects as $subject) {

                // récupérer toutes les notes de l’élève pour cette matière et cette année
                $grades = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();

                // préparation des tableaux
                $interros = [];
                $devoirs = [];

                foreach ($grades as $grade) {
                    if ($grade->type === 'interrogation') {
                        $interros[$grade->sequence] = $grade->value; // clé = numéro de l’interro
                    }
                    if ($grade->type === 'devoir') {
                        $devoirs[$grade->sequence] = $grade->value; // clé = numéro du devoir
                    }
                }

                // ordonner par séquence
                ksort($interros);
                ksort($devoirs);

                // calcul des moyennes
                $moyenneInterro = count($interros) > 0 ? round(array_sum($interros) / count($interros), 2) : null;
                $moyenneDevoir = count($devoirs) > 0 ? round(array_sum($devoirs) / count($devoirs), 2) : null;

                $coef = $subject->coefficient ?? 1;

                $moyenneMat = null;
                if ($moyenneInterro !== null && $moyenneDevoir !== null) {
                    $moyenneMat = round((($moyenneInterro + $moyenneDevoir) / 2) * $coef, 2);
                }

                $moyenne = null;
                if ($moyenneInterro !== null && $moyenneDevoir !== null) {
                    $moyenne = round((($moyenneInterro + $moyenneDevoir) / 2), 2);
                }

                $studentGrades[$subject->id] = [
                    'interros' => $interros,       // [1 => note1, 2 => note2, ...]
                    'devoirs' => $devoirs,         // [1 => note1, 2 => note2]
                    'moyenneInterro' => $moyenneInterro,
                    'coef' => $coef,
                    'moyenne' => $moyenne,
                    'moyenneMat' => $moyenneMat,
                    'subject' => $subject,
                    'rang' => null,
                ];

                if ($moyenne !== null) {
                    $classeMoyennes[$student->id] = $moyenne;
                }
            }

            $gradesData[$student->id] = $studentGrades;
        }

        if (!empty($classeMoyennes)) {
            // Trier du plus grand au plus petit
            arsort($classeMoyennes);

            $rank = 1;
            $previousMoyenne = null;
            $sameRankCount = 0;

            foreach ($classeMoyennes as $studentId => $moyenne) {
                if ($moyenne === $previousMoyenne) {
                    // même moyenne → même rang
                    $sameRankCount++;
                } else {
                    // nouvelle moyenne → rang suivant
                    $rank += $sameRankCount;
                    $sameRankCount = 1;
                }

                $gradesData[$studentId][$subject->id]['rang'] = $rank;
                $previousMoyenne = $moyenne;
            }
        }

        // 5) envoyer à la vue
        return view('teacher.notes.class_notes', [
            'classe' => $classe,
            'subjects' => $subjects,
            'gradesData' => $gradesData,
            'activeYear' => $activeYear,
            'trimestre'  => $trimestre,
        ]);
    }


    public function calcInterro($classId){
        // ⚡ calculer moyenne interro par élève (minimum 2 notes)
    }

    public function calcTrimestre($classId) {
        // ⚡ calculer moyenne trimestrielle et sauvegarder
    }
}


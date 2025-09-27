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
                        'trimestre' => $request->trimestre,
                        'academic_year_id' => $activeYear->id,
                    ],
                    [
                        'value' => $request->notes[$student->id],
                    ]
                );
            }
        }

        return redirect()->route('teacher.classes.notes', $classId)
            ->with('success', 'Notes enregistrées.');
    }


    public function edit($classId, $type, $num){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students.grades' => function($q) use ($type, $num, $activeYear){
            $q->where('type', $type)
              ->where('sequence', $num)
              ->where('academic_year_id', $activeYear->id);
        }])->findOrFail($classId);

        return view('teacher.notes.edit', compact('classe','type','num'));
    }

    // Mettre à jour les notes
    public function update(Request $request, $classId, $type, $num){
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
                    'class_id' => $classId,
                    'academic_year_id' => $activeYear->id
                ],
                ['value' => $value]
            );
        }

        return redirect()->route('teacher.classes.notes.read', [$classId, $type, $num])
                         ->with('success','Notes mises à jour avec succès.');
    }

    // Supprimer toutes les notes pour ce type/séquence
    public function destroy($classId, $type, $num){
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

        return redirect()->route('teacher.classes.notes.read', [$classId, $type, $num])
                         ->with('success','Toutes les notes ont été supprimées.');
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
        // 1) année académique active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // 2) récupérer la classe avec les élèves
        $classe = Classe::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
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
                ];
            }
        

            $gradesData[$student->id] = $studentGrades;
        }

        // 5) envoyer à la vue
        return view('teacher.notes.class_notes', [
            'classe' => $classe,
            'subjects' => $subjects,
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


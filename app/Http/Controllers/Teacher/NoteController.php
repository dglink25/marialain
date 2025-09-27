<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Grade;
use App\Models\AcademicYear;
use App\Models\Student;
 use Illuminate\Support\Facades\Auth;

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

        }

        return view('teacher.notes.class_notes', compact('classe', 'gradesData', 'activeYear'));
    }

   

public function showClassNotes($classId)
{
    $activeYear = AcademicYear::where('active', true)->first();
    if (!$activeYear) {
        return back()->with('error', 'Aucune année académique active trouvée.');
    }

    $teacherId = Auth::id();
    
    $classe = Classe::with(['students' => function ($q) {
        $q->orderBy('last_name')->orderBy('first_name');
    }, 'subject' => function ($q) use ($teacherId) {
        $q->where('teacher_id', $teacherId);
    }])->findOrFail($classId);

    if ($classe->subjects->isEmpty()) {
        return back()->with('error', "Aucune matière assignée à cet enseignant dans cette classe.");
    }

    // Préparer les notes de chaque élève pour SES matières uniquement
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
    }

    return view('teacher.notes.class_notes', compact('classe', 'gradesData', 'activeYear'));
}


    public function calcInterro($classId){
        // ⚡ calculer moyenne interro par élève (minimum 2 notes)
    }

    public function calcTrimestre($classId) {
        // ⚡ calculer moyenne trimestrielle et sauvegarder
    }
}


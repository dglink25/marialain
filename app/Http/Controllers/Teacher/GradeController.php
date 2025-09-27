<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Grade;
use App\Models\AcademicYear;

class GradeController extends Controller
{
    public function index($classId)
    {
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $classe = Classe::with('students')->findOrFail($classId);

        return view('teacher.notes.index', compact('classe', 'activeYear'));
    }

    public function create($classId, $type, $num)
    {
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Pas d\'année académique active.');
        }

        $classe = Classe::with('students')->findOrFail($classId);

        // Validation : ne pas ajouter Interro2 si Interro1 vide
        if ($type === 'interrogation' && $num > 1) {
            $previous = Grade::where('classe_id', $classId)
                ->where('type', 'interrogation')
                ->where('sequence', $num - 1)
                ->where('academic_year_id', $activeYear->id)
                ->exists();
            if (!$previous) {
                return back()->with('error', "Impossible d'ajouter Interrogation $num tant que Interrogation " . ($num - 1) . " n'est pas saisie.");
            }
        }

        return view('teacher.notes.create', compact('classe', 'type', 'num'));
    }

    public function store(Request $request, $classId, $type, $num)
    {
        $request->validate([
            'notes.*' => 'nullable|numeric|min:0|max:20',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with('students')->findOrFail($classId);

        foreach ($classe->students as $student) {
            if (isset($request->notes[$student->id])) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $request->subject_id,
                        'type' => $type,
                        'sequence' => $num,
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

    // ✅ Nouvelle méthode pour lire les notes (lecture seule)
    public function read($classId, $type, $num)
    {
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Pas d\'année académique active.');
        }

        $classe = Classe::with(['students' => function($q) use ($activeYear, $type, $num){
            $q->orderBy('last_name')
              ->with(['grades' => function($q2) use ($activeYear, $type, $num){
                  $q2->where('type', $type)
                     ->where('sequence', $num)
                     ->where('academic_year_id', $activeYear->id);
              }]);
        }])->findOrFail($classId);

        return view('teacher.notes.read', compact('classe', 'type', 'num'));
    }
}

<?php 

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\User;    

class SubjectController extends Controller{
    public function index(){
        // Récupère l'année scolaire active
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            // Si aucune année active, renvoyer une collection vide + message
            return view('censeur.subjects.index', [
                'subjects' => collect(),
                'activeYear' => null,
                'error' => "Aucune année scolaire active n’a été trouvée."
            ]);
        }

        // Récupère uniquement les matières de l'année active
        $subjects = Subject::where('academic_year_id', $activeYear->id)->get();

        return view('censeur.subjects.index', compact('subjects', 'activeYear'));
    }


    public function store(Request $request) {
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }

        $request->validate(['name'=>'required']);
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        Subject::create([
            'name'             => $request->name,
            'coefficient'      => 1,
            'academic_year_id' => $activeYear->id,
        ]);
        
        return back()->with('success','Matière ajoutée.');
    }
    public function teachers($subjectId){
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return redirect()->route('censeur.subjects.index')
                ->with('error', "Aucune année scolaire active n'a été trouvée.");
        }

        $subject = Subject::where('academic_year_id', $activeYear->id)
            ->findOrFail($subjectId);

        // Récupérer toutes les assignations de cette matière dans les classes de l'année active
        $assignments = \App\Models\ClassTeacherSubject::with(['teacher', 'classe'])
            ->where('subject_id', $subjectId)
            ->whereHas('classe', function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id); // ✅ pas d'ambiguïté ici
            })
            ->get();

        // Enseignants uniques
        $teachers = $assignments->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();

        // Pour chaque enseignant, ne garder que les classes concernées (année active + matière)
        $teachers->each(function ($teacher) use ($assignments) {
            $classes = $assignments
                ->where('teacher_id', $teacher->id)
                ->pluck('classe')
                ->filter()
                ->unique('id')
                ->values();
            $teacher->setRelation('classes', $classes);
        });

        $subject->setRelation('teachers', $teachers);

        return view('censeur.subjects.teachers', compact('subject', 'activeYear'));
    }

}

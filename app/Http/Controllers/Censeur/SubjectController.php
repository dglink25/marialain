<?php 

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\AcademicYear;

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
            'academic_year_id' => $activeYear->id,
        ]);
        
        return back()->with('success','Matière ajoutée.');
    }
    public function teachers($subjectId){
        $subject = Subject::with('teachers')->findOrFail($subjectId);

        return view('censeur.subjects.teachers', compact('subject'));
    }
}

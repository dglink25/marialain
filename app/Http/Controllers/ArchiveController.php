<?php


namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\Timetable;



class ArchiveController extends Controller{
    public function index(){
        $archives = AcademicYear::archives()->get();
        return view('archives.index', compact('archives'));
    }

    public function show($id){
        $user = auth()->user(); 
        $year = AcademicYear::findOrFail($id);

        if ($year->active) {
            return redirect()->route('home')
                ->with('error', 'Cette année est encore active, pas une archive.');
        }

        $classesQuery = $year->classes()->with('students'); 

        if ($user->id == 1 || $user->id == 8) {
          
            $classes = $classesQuery->get();
        } 
        elseif ($user->id == 5) {
           
            $classes = $classesQuery->whereIn('entity_id', [1, 2])->get();
        } 
        elseif ($user->id == 6) {

            $classes = $classesQuery->where('entity_id', 3)->get(); 
        } 
        
        else {

            $classes = $classesQuery->whereHas('classTeacherSubjects', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })->get();
        }

        foreach ($classes as $class) {
            $class->studentsCount = $class->students()
                ->where('academic_year_id', $year->id)
                ->count();
        }

        return view('archives.show', compact('year', 'classes'));
    }


    public function classStudents($yearId, $classId){
        $year = AcademicYear::findOrFail($yearId);
        $class = Classe::findOrFail($classId);

        if ($class->academic_year_id !== $year->id) {
            abort(404);
        }

        $students = $class->students()
            ->where('academic_year_id', $year->id)
            ->paginate(15); 

        return view('archives.class_students', compact('year', 'class', 'students'));
    }

    public function classTimetables($yearId, $classId){
        $year = AcademicYear::findOrFail($yearId);
        $class = Classe::findOrFail($classId);

        // Vérifier que la classe appartient à l'année
        if ($class->academic_year_id !== $year->id) {
            abort(404, 'Cette classe n’appartient pas à cette année.');
        }

        // Vérifier que seul le censeur (id = 6) a accès
        if (auth()->id() != 6) {
            abort(403, "Accès refusé. Seul le censeur peut voir les anciens emplois du temps.");
        }

        // Charger les emplois du temps archivés
        $timetables = Timetable::with(['teacher', 'subject'])
            ->where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->get();

        // Génération des tranches horaires (07h-08h ... 18h-19h)
        $hours = [];
        $start = 7; // 7h du matin
        $end = 19;  // 19h
        for ($h = $start; $h < $end; $h++) {
            $hours[] = sprintf('%02dh-%02dh', $h, $h + 1);
        }

        // Jours de la semaine
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        return view('archives.class_timetables', compact('year', 'class', 'timetables', 'hours', 'days'));
    }



}

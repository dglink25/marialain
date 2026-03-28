<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\User;
use App\Models\Timetable;
use Barryvdh\DomPDF\Facade\Pdf; // Import du PDF
use App\Models\AcademicYear;


class ClasseController extends Controller{
    
    public function index(){
        // Récupère l'année scolaire active
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            // Si aucune année active, renvoie une vue vide avec un message
            return view('censeur.classes.index', [
                'classes' => collect(), // collection vide
                'activeYear' => null,
                'error' => "Aucune année scolaire active n’a été trouvée."
            ]);
        }

        // Récupère les classes de l'entité 3 pour l'année active
        $classes = Classe::with(['entity', 'academicYear'])
            ->where('entity_id', 3)
            ->where('academic_year_id', $activeYear->id)
            ->get();

        return view('censeur.classes.index', compact('classes', 'activeYear'));
    }

    public function students($classId){

        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }
        
        $class = Classe::with(['students' => function($query) use ($activeYear) {
        $query->where('is_validated', 1) 
              ->where('academic_year_id', $activeYear->id)
              ->orderBy('last_name')
              ->orderBy('first_name');
        }])->findOrFail($classId);


        return view('censeur.classes.students', compact('class'));
    }

    public function timetable($classId){
        $class = Classe::findOrFail($classId);
        return redirect()->route('censeur.timetables.index', $classId);
    }

    public function teachers($id){
        $class = Classe::with(['timetables.teacher', 'timetables.subject'])->findOrFail($id);

        // Regrouper par enseignant et collecter ses matières
        $teachers = $class->timetables
            ->groupBy('teacher_id')
            ->map(function ($items) {
                $teacher = $items->first()->teacher;
                $subjects = $items->pluck('subject.name')->unique()->values();
                return [
                    'teacher' => $teacher,
                    'subjects' => $subjects,
                ];
            });

        return view('censeur.classes.teachers', compact('class', 'teachers'));
    }

    public function downloadStudentsPdf($classId){
        $class = Classe::with(['students' => function($query) {
            $query->where('is_validated', 1);
        }])->findOrFail($classId);

        // Trier les élèves par nom et prénom
        $students = $class->students->sortBy([
            ['last_name', 'asc'],
            ['first_name', 'asc']
        ]);

        $pdf = Pdf::loadView('censeur.classes.students_pdf', compact('class', 'students'));

        return $pdf->download('eleves_'.$class->name.'.pdf');
    }

    public function export($id){
        $class = Classe::with(['timetables.teacher', 'timetables.subject'])->findOrFail($id);

        // Regrouper les enseignants + matières comme dans ta fonction teachers()
        $teachers = $class->timetables
            ->groupBy('teacher_id')
            ->map(function ($items) {
                $teacher = $items->first()->teacher;
                $subjects = $items->pluck('subject.name')->unique()->values();
                return [
                    'teacher' => $teacher,
                    'subjects' => $subjects,
                ];
            });

        // Génération du PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('censeur.teachers.export', [
            'class' => $class,
            'teachers' => $teachers,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("enseignants_{$class->name}.pdf");
    }


    

}

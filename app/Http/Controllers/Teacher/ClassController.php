<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use Carbon\Carbon;
use App\Models\Timetable;

class ClassController extends Controller{
    // Liste des classes de l’enseignant connecté
    

    public function index(){
        $teacherId = auth()->id();
        $classes = auth()->user()->classes; // ou selon ta relation

        $today = Carbon::now()->format('l');
        $now = Carbon::now()->format('H:i:s');
        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        // Pour chaque classe, récupérer le cours en cours (null si aucun)
        foreach ($classes as $class) {
            $class->currentLesson = Timetable::with('subject')
                ->where('class_id', $class->id)
                ->where('teacher_id', $teacherId)
                ->where('day', $today)
                ->where('academic_year_id', $academicYear->id)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->first();
        }

        return view('teacher.classes.index', compact('classes'));
    }


    // Liste des élèves d’une classe
    public function students($classId){
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        $class = Classe::with(['students' => function ($query) use ($activeYear) {
            $query->where('is_validated', 1) 
                ->where('academic_year_id', $activeYear->id)
                ->orderBy('last_name')
                ->orderBy('first_name');
        }])->findOrFail($classId);

        $students = $class->students;

        return view('teacher.classes.students', compact('class', 'students', 'activeYear'));
    }



    public function timetable($classId){

        $class = Classe::with(['timetables.teacher', 'timetables.subject'])
                    ->findOrFail($classId);

        $timetables = $class->timetables;

        $hours = [
            '07h-08h',
            '08h-09h',
            '09h-10h',
            '10h-11h',
            '11h-12h',
            '12h-13h',
            '13h-14h',
            '14h-15h',
            '15h-16h',
            '16h-17h',
            '17h-18h',
            '18h-19h',
        ];

        return view('teacher.classes.timetable', compact('class', 'timetables', 'hours'));
    }


    
}

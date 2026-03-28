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
        $teacher = auth()->user();
        $teacherId = $teacher->id;

        $today = Carbon::now()->format('l');
        $now = Carbon::now()->format('H:i:s');
        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        // Récupérer les classes de l'enseignant (sans doublons)
        $classes = $teacher->classes()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->with(['timetables.subject'])
            ->get()
            ->unique('id');

        // Ajouter les matières enseignées par l'enseignant dans chaque classe
        foreach ($classes as $class) {
            $classTeacherSubjects = \App\Models\ClassTeacherSubject::with('subject')
                ->where('class_id', $class->id)
                ->where('teacher_id', $teacherId)
                ->where('academic_year_id', $academicYear->id)
                ->get();

            // Extraire uniquement les matières
            $class->subjects = $classTeacherSubjects->map(function($cts) {
                return $cts->subject;
            });

            // Cours en cours
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

    public function timetable($classId)
    {
        $class = Classe::with(['timetables.teacher', 'timetables.subject'])
                    ->findOrFail($classId);

        $timetables = $class->timetables;
        
        // Organiser les cours par jour pour faciliter l'affichage
        $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $sortedTimetables = [];
        
        foreach ($joursSemaine as $jour) {
            $sortedTimetables[$jour] = $timetables->where('day', $jour)->sortBy('start_time')->values();
        }

        // Définir les créneaux horaires avec start/end pour faciliter les comparaisons
        $hours = [
            ['slot' => '07h-08h', 'start' => '07:00', 'end' => '08:00'],
            ['slot' => '08h-09h', 'start' => '08:00', 'end' => '09:00'],
            ['slot' => '09h-10h', 'start' => '09:00', 'end' => '10:00'],
            ['slot' => '10h-11h', 'start' => '10:00', 'end' => '11:00'],
            ['slot' => '11h-12h', 'start' => '11:00', 'end' => '12:00'],
            ['slot' => '12h-13h', 'start' => '12:00', 'end' => '13:00'],
            ['slot' => '13h-14h', 'start' => '13:00', 'end' => '14:00'],
            ['slot' => '14h-15h', 'start' => '14:00', 'end' => '15:00'],
            ['slot' => '15h-16h', 'start' => '15:00', 'end' => '16:00'],
            ['slot' => '16h-17h', 'start' => '16:00', 'end' => '17:00'],
            ['slot' => '17h-18h', 'start' => '17:00', 'end' => '18:00'],
        ];

        return view('teacher.classes.timetable', compact('class', 'timetables', 'hours', 'joursSemaine', 'sortedTimetables'));
    }


    
}

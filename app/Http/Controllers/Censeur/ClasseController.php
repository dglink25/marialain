<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\User;
use App\Models\Timetable;
use Barryvdh\DomPDF\Facade\Pdf; // Import du PDF


class ClasseController extends Controller
{
    public function index()
    {
        $classes = Classe::where('entity_id', 3)->get();
        return view('censeur.classes.index', compact('classes'));
    }

    public function students($classId)
    {
        $class = Classe::with(['students' => function($query) {
            $query->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classId);

        return view('censeur.classes.students', compact('class'));
    }


    public function timetable($classId)
    {
        $class = Classe::findOrFail($classId);
        return redirect()->route('censeur.timetables.index', $classId);
    }

    public function teachers($id)
    {
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

    public function downloadStudentsPdf($classId)
    {
        $class = Classe::with('students')->findOrFail($classId);

        // Trier les élèves par nom et prénom
        $students = $class->students->sortBy([
            ['last_name', 'asc'],
            ['first_name', 'asc']
        ]);

        $pdf = Pdf::loadView('censeur.classes.students_pdf', compact('class', 'students'));

        return $pdf->download('eleves_'.$class->name.'.pdf');
    }


}

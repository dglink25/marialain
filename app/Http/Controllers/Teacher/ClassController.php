<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Timetable;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    // Liste des classes assignées à l’enseignant connecté
    public function index()
    {
        $classes = Classe::whereHas('teachers', function ($q) {
            $q->where('user_id', Auth::id());
        })->get();

        return view('teacher.classes.index', compact('classes'));
    }

    // Liste des élèves d’une classe
    public function students($classId)
    {
        $class = Classe::with('students')->findOrFail($classId);

        return view('teacher.classes.students', compact('class'));
    }

    // Emploi du temps d’une classe
    public function timetable($classId)
    {
        $class = Classe::findOrFail($classId);

        $timetables = Timetable::where('class_id', $classId)
            ->with('subject', 'teacher')
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        return view('teacher.timetable', compact('class', 'timetables'));
    }
}

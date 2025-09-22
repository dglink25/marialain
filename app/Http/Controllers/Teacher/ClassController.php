<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    // Liste des classes de l’enseignant connecté
    public function index()
    {
        // Récupérer l'enseignant connecté
        $teacher = Auth::user();

        // Récupérer les classes via la relation belongsToMany
        $classes = $teacher->classes()->with('teachers')->get();

        return view('teacher.classes.index', compact('classes'));
    }

    // Liste des élèves d’une classe
    public function students($classId)
    {
        $class = Classe::with('students')->findOrFail($classId);

        return view('teacher.classes.students', compact('class'));
    }

    // Emploi du temps d’une classe
    // Emploi du temps d’une classe
    public function timetable($classId){
        // Charger la classe avec ses emplois du temps + relations
        $class = Classe::with(['timetables.teacher', 'timetables.subject'])
                    ->findOrFail($classId);

        // Récupérer tous les créneaux de cette classe
        $timetables = $class->timetables;

        // Définir les plages horaires fixes (par ex. 8h → 18h)
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

<?php

// app/Http/Controllers/ArchiveController.php
namespace App\Http\Controllers;

use App\Models\AcademicYear;

class ArchiveController extends Controller
{
    public function index()
    {
        // Récupère toutes les années non actives
        $archives = AcademicYear::archives()->get();
        return view('archives.index', compact('archives'));
    }

    public function show($id)
    {
        $year = AcademicYear::findOrFail($id);

        if ($year->active) {
            return redirect()->route('home')
                ->with('error', 'Cette année est encore active, pas une archive.');
        }

        // Ici tu peux charger les données liées (élèves, classes, notes...)
        $students = $year->students ?? [];
        $classes = $year->classes ?? [];

        return view('archives.show', compact('year', 'students', 'classes'));
    }
}

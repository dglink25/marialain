<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classe;
use Barryvdh\DomPDF\Facade\Pdf; // Import du PDF
use App\Models\AcademicYear;

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $annee_academique = AcademicYear::where('active', 1)-> first();
        $students = Student::whereHas('classe', function ($query) {
            $query->whereHas('entity', function ($q) {
                $q->where('name', 'primaire');
            });
        })->with('classe')->orderBy('last_name')->orderBy('first_name')->get();
        if ($request->has('sort')) {
            $students = $students->sortBy($request->sort);
        }

        return view('primaire.ecoliers.liste', compact('students', 'annee_academique'));
    }
    public function downloadPrimaireStudents()
{
    $students = Student::whereHas('classe.entity', function ($query) {
        $query->where('name', 'primaire');
    })->with('classe')->orderBy('last_name')->orderBy('first_name')->get();

    // Si tu veux afficher juste "Primaire"
    $class = (object) ['name' => 'Primaire'];

    $pdf = Pdf::loadView('primaire.ecoliers.pdf', compact('students', 'class'));
    return $pdf->download('liste_des_eleves.pdf');
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

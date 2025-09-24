<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use App\Models\TeacherInvitation;
use App\Models\User;
use App\Models\Classe;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf ;
use App\Models\AcademicYear;

class primaryteacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    // Récupère tous les enseignants du primaire
    $annee_academique = AcademicYear::where('active', 1)-> first();
    $teachers = User::whereHas('role', function ($query) {
        $query->where('name', 'teacher');
    })->with('classePrimaire') // eager load de la classe du primaire
      ->get();

    return view('primaire.enseignants.enseignants', compact('teachers', 'annee_academique'));
}
public function downloadTeachersList(){
   // Récupère tous les enseignants du primaire
    $teachers = User::whereHas('role', function ($query) {
        $query->where('name', 'teacher');
    })->with('classePrimaire') // eager load de la classe du primaire
      ->get();    $pdf = Pdf::loadView('primaire.enseignants.pdf', compact ('teachers'));
    return $pdf->download('liste_des_enseignants.pdf');
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

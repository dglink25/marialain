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
     $annee_academique = AcademicYear::where('active', 1)-> first();
     if(!$annee_academique){
        echo ('Erreur ! Aucune année académique en cours');
     }
    // Récupère tous les enseignants du primaire de l'année acadmique en cours
   
    $teachers = User::whereHas('role', function ($q) {
        $q->where('name', 'teacher');
    })
    ->whereHas('classe', function ($q2) use ($annee_academique) {
        $q2->whereHas('entity', function ($q3) {
            $q3->where('name', 'primaire');
        })
        ->where('academic_year_id', $annee_academique->id);
    })-> with('classePrimaire')
    ->get();

return view('primaire.enseignants.enseignants', compact('teachers', 'annee_academique'));}

public function downloadTeachersList(){
   // Récupère tous les enseignants du primaire
    $teachers = User::whereHas('role', function ($query) {
        $query->where('name', 'teacher');
    })->with('classePrimaire') // eager load de la classe du primaire
      ->get();  
        $pdf = Pdf::loadView('primaire.enseignants.pdf', compact ('teachers'));
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
        $teacher = User::FindorFail($id);
        return view('primaire.enseignants.show', compact('teacher'));
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

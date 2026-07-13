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
    public function index(){
        $annee_academique = AcademicYear::where('active', 1)->first();
        if (!$annee_academique) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // Enseignants du primaire et maternelle = ceux dont teacher_id
        // correspond à une classe avec entity_id IN (1=maternelle, 2=primaire)
        $teachers = User::whereHas('role', function ($q) {
                $q->where('name', 'enseignant');
            })
            ->whereHas('classe', function ($q) use ($annee_academique) {
                $q->whereIn('entity_id', [1, 2])
                  ->where('academic_year_id', $annee_academique->id);
            })
            ->with(['classe' => function ($q) use ($annee_academique) {
                $q->whereIn('entity_id', [1, 2])
                  ->where('academic_year_id', $annee_academique->id);
            }])
            ->orderBy('name')
            ->get();

        return view('primaire.enseignants.enseignants', compact('teachers', 'annee_academique'));
    }

    public function downloadTeachersList(){
        $annee_academique = AcademicYear::where('active', 1)->first();

        $teachers = User::whereHas('role', function ($q) {
                $q->where('name', 'enseignant');
            })
            ->whereHas('classe', function ($q) use ($annee_academique) {
                $q->whereIn('entity_id', [1, 2])
                  ->where('academic_year_id', $annee_academique->id);
            })
            ->with(['classe' => function ($q) use ($annee_academique) {
                $q->whereIn('entity_id', [1, 2])
                  ->where('academic_year_id', $annee_academique->id);
            }])
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('primaire.enseignants.pdf', compact('teachers'));
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

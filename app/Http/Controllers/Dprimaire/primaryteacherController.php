<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use App\Models\TeacherInvitation;
use App\Models\User;
use App\Models\Classe;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf ;
use App\Models\AcademicYear;

class primaryteacherController extends Controller{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        $annee_academique = AcademicYear::where('active', 1)->first();
        if (!$annee_academique) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // On part des classes primaire/maternelle (entity_id 1 ou 2)
        // et on récupère leur enseignant via la relation teacher (teacher_id)
        $classes = Classe::where('academic_year_id', $annee_academique->id)
            ->whereIn('entity_id', [1, 2])
            ->whereNotNull('teacher_id')
            ->with('teacher')
            ->get();

        // On déduplique au cas où un enseignant aurait plusieurs classes
        $teachers = $classes->map(fn($c) => $c->teacher)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        // On attache la classe à chaque enseignant pour l'affichage
        foreach ($teachers as $teacher) {
            $teacher->setRelation('classe', $classes->firstWhere('teacher_id', $teacher->id));
        }

        return view('primaire.enseignants.enseignants', compact('teachers', 'annee_academique'));
    }

    public function downloadTeachersList(){
        $annee_academique = AcademicYear::where('active', 1)->first();

        $classes = Classe::where('academic_year_id', $annee_academique->id)
            ->whereIn('entity_id', [1, 2])
            ->whereNotNull('teacher_id')
            ->with('teacher')
            ->get();

        $teachers = $classes->map(fn($c) => $c->teacher)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        foreach ($teachers as $teacher) {
            $teacher->setRelation('classe', $classes->firstWhere('teacher_id', $teacher->id));
        }

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

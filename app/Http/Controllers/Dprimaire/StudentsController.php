<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classe;
use Barryvdh\DomPDF\Facade\Pdf; // Import du PDF
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $annee_academique = AcademicYear::where('active', 1)->first();
    if(!$annee_academique){
        return back()->with('error', 'Aucune année académique active trouvée.');
    }

    // On démarre la requête
    $query = Student::whereHas('classe', function ($q) {
        $q->whereHas('entity', function ($q2) {
            $q2->whereIn('name', ['primaire','maternelle']); // ne garde que les élèves du primaire
        });
    });

    // Filtres dynamiques
    if ($request->filled('classe')) {
        $query->whereHas('classe', function ($q) use ($request) {
            $q->where('name', $request->classe);
        });
    }
    if ($request->filled('gender')) {
        $query->where('gender', $request->gender);
    }
    if ($request->filled('search')) {
    $search = $request->search;
    $query->where(function ($q) use ($search) {
        $q->where('last_name', 'like', "%$search%")
          ->orWhere('first_name', 'like', "%$search%")
          ->orWhere(DB::raw("CONCAT(last_name, ' ', first_name)"), 'like', "%$search%");
    });
}


    // Tri
    if ($request->filled('sort')) {
        $query->orderBy($request->sort);
    } else {
        $query->orderBy('last_name')->orderBy('first_name');
    }

    // Récupération finale
    $students = $query->with('classe')->get();

    // Pour le menu déroulant des classes
            $classes = Classe::where('academic_year_id', $annee_academique->id)
                ->whereHas('entity', function ($query) {
                    $query->whereIn('name', ['primaire', 'maternelle']);
                })
                ->get();

    return view('primaire.ecoliers.liste', compact('students', 'annee_academique', 'classes'));
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
        $student = Student:: FindorFail($id);
        return view('primaire.ecoliers.show', compact('student'));
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

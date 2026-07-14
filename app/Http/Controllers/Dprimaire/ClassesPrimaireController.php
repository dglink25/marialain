<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\AcademicYear;
use Barryvdh\DomPDF\Facade\Pdf; // Import du PDF

use App\Models\Student;
class ClassesPrimaireController extends Controller{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){
        try {
            // Vérifier l'année académique active
            $annee_academique = AcademicYear::where('active', 1)->first();

            if (!$annee_academique) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }

            // Récupérer les classes primaire + maternelle avec leurs enseignants
            $classes = Classe::where('academic_year_id', $annee_academique->id)
                ->whereHas('entity', function ($query) {
                    $query->whereIn('slug', ['primaire', 'maternelle']);
                })
                ->with(['academicYear', 'teacher'])
                ->get();

            return view('primaire.classe.classes', compact('classes', 'annee_academique'));
        } catch (\Exception $e) {
            // Gestion des exceptions générales
            return back()->with('error', 'Erreur lors du chargement des classes : ' . $e->getMessage());
        }
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
    public function store(Request $request){
        //
        $request-> validate([
            'name'=> 'required|max:255',

        ]);
        $annee = AcademicYear:: where('active', '1')->value('id');
        Classe::create([
            'name' => $request-> name,
            'entity_id' => 2,
            'academic_year_id' => $annee
        ]);
        return redirect()-> route('primaire.classe.classes')-> with('success', 'Classe ajoutée avec succes');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id){
        //
         $annee_academique = AcademicYear::where('active', 1)-> first();
        $class = Classe::with(['students' => function($query) {
            $query->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($id);
        return view('primaire.classe.showclass', compact('class', 'annee_academique'));
    }

    public function downloadClassStudents($id){
        $class = Classe::findOrFail($id);
        $annee_academique = AcademicYear::where('active', 1)->first();
        $students = Student::where('class_id', $class->id)
            ->where('academic_year_id', $annee_academique->id)
            ->where('is_validated', 1)
            ->orderBy('last_name')->orderBy('first_name')
            ->get();
        $pdf = Pdf::loadView('primaire.classe.pdf', compact('students', 'class', 'annee_academique'));
        return $pdf->download('liste_' . $class->name . '.pdf');
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

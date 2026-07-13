<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classe;
use Barryvdh\DomPDF\Facade\Pdf; // Import du PDF
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){
        $annee_academique = AcademicYear::where('active', 1)->first();
        if(!$annee_academique){
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // On démarre la requête
        $query = Student::whereHas('classe', function ($q) {
            $q->whereHas('entity', function ($q2) {
                $q2->whereIn('slug', ['primaire','maternelle']); // ne garde que les élèves du primaire
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
                        $query->whereIn('slug', ['primaire', 'maternelle']);
                    })
                    ->get();

        return view('primaire.ecoliers.liste', compact('students', 'annee_academique', 'classes'));
    }

    public function downloadPrimaireStudents(Request $request) {
        $annee_academique = AcademicYear::where('active', 1)->first();

        $query = Student::whereHas('classe', function ($q) {
            $q->whereHas('entity', function ($q2) {
                $q2->whereIn('slug', ['primaire', 'maternelle']);
            });
        });

        // Appliquer les mêmes filtres que la liste
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
        if ($request->filled('sort')) {
            $query->orderBy($request->sort);
        } else {
            $query->orderBy('last_name')->orderBy('first_name');
        }

        $students = $query->with('classe')->get();

        // Construire un libellé descriptif pour le titre du PDF
        $parts = [];
        if ($request->filled('classe'))  $parts[] = $request->classe;
        if ($request->filled('gender'))  $parts[] = ($request->gender === 'M' ? 'Garçons' : 'Filles');
        if ($request->filled('search'))  $parts[] = 'Recherche : "' . $request->search . '"';
        $label = $parts ? implode(' — ', $parts) : 'Primaire & Maternelle';

        $class = (object) ['name' => $label];

        $pdf = Pdf::loadView('primaire.ecoliers.pdf', compact('students', 'class'));
        $filename = 'liste_eleves_' . str_replace([' ', '—', '"', ':'], ['_', '-', '', ''], $label) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
        $student = Student:: FindorFail($id);
        return view('primaire.ecoliers.show', compact('student'));
    }


}

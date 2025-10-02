<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Classe;
use App\Models\AcademicYear;


use Illuminate\Http\Request;

class dashboardPrimaireController extends Controller
{
    //
    public function index()
    {
        try {
            $user = Auth::user();
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
                ->with('students', 'teacher') // Charger les enseignants associés
                ->get();
            //nombre d'elèves au primaire
            $primaryClassCount = Classe::where('academic_year_id', $annee_academique->id)
                ->whereHas('entity', function ($query) {
                    $query->whereIn('slug', ['primaire', 'maternelle']);
                })
                ->count();
            //nombre d'elèves au primaire
            $primaryStudentsCount = Student::where('academic_year_id', $annee_academique->id)
                ->whereHas('entity', function ($q) {
                    $q->whereIn('slug', ['primaire', 'maternelle']);
                })->count();
                $students = Student::where('academic_year_id', $annee_academique->id)
                ->whereHas('entity', function ($q) {
                    $q->whereIn('slug', ['primaire', 'maternelle']);
                })->get();
            //récupérer les enseignants du primaire
            $primaryTeacherCount = User::whereHas('role', function ($q) {
                $q->where('name', 'teacher');
            })
                ->whereHas('classe', function ($q2) use ($annee_academique) {
                    $q2->whereHas('entity', function ($q3) {
                        $q3->where('name', 'primaire');
                    })
                        ->where('academic_year_id', $annee_academique->id);
                })->with('classePrimaire')->count();
           return view('dashboards.directeur', compact('user', 'students', 'primaryClassCount', 'primaryStudentsCount', 'primaryTeacherCount', 'annee_academique', 'classes'));   } catch (\Exception $e) {
            // Gestion des exceptions générales
            return back()->with('error', 'Erreur lors du chargement des classes : ' . $e->getMessage());
        }
    }
public function show(string $id){
        //
         $annee_academique = AcademicYear::where('active', 1)-> first();
         if(!$annee_academique){
            return back()-> with('error', 'Aucune année académique active trouvée.');
         }
        $class = Classe::with(['students' => function($query) {
            $query->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($id);
        return view('primaire.classe.showclass', compact('class', 'annee_academique'));
    }
}

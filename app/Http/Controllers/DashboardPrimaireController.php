<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Classe;
use App\Models\AcademicYear;


use Illuminate\Http\Request;

class DashboardPrimaireController extends Controller{
    //
    public function index()  {
        try {
            $user = Auth::user();
            // Vérifier l'année académique active
            $annee_academique = AcademicYear::where('active', 1)->first();

            if (!$annee_academique) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }

            // Récupérer les classes primaire + maternelle avec leurs enseignants
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
            //récupérer le nombre d'enseignants du primaire via les classes (entity_id 1 ou 2)
            $primaryTeacherCount = Classe::where('academic_year_id', $annee_academique->id)
                ->whereIn('entity_id', [1, 2])
                ->whereNotNull('teacher_id')
                ->distinct('teacher_id')
                ->count('teacher_id');

            // Répartition des classes par nom (pour l'affichage du dashboard)
            $classesRepartition = Classe::where('academic_year_id', $annee_academique->id)
                ->whereIn('entity_id', [1, 2])
                ->withCount('students')
                ->orderBy('name')
                ->get();

           return view('dashboards.directeur', compact(
               'user',
               'annee_academique',
               'primaryClassCount',
               'primaryStudentsCount',
               'primaryTeacherCount',
               'classesRepartition'
           ));   } catch (\Exception $e) {
            // Gestion des exceptions générales
            return back()->with('error', 'Erreur lors du chargement des classes : ' . $e->getMessage());
        }
    }

}

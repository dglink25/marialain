<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classe;
use App\Models\User;
use App\Models\AcademicYear;

class welcomeController extends Controller
{
    //
    public function index()
    {
        try {
            // Vérifier l'année académique active
            $annee_academique = AcademicYear::where('active', 1)->first();

            if (!$annee_academique) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }

            // Récupérer les classes primaire + maternelle avec leurs enseignants
            $primaryClassCount = Classe::where('academic_year_id', $annee_academique->id)
                ->whereHas('entity', function ($query) {
                    $query->whereIn('name', ['primaire', 'maternelle']);
                })
                ->count();
            //nombre d'elèves au primaire
            $primaryStudentsCount = Student::where('academic_year_id', $annee_academique->id)
                ->whereHas('entity', function ($q) {
                    $q->whereIn('name', ['primaire', 'maternelle']);
                })->count();
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
            return view('welcome', compact('primaryStudentsCount', 'primaryClassCount', 'primaryTeacherCount'));
        } catch (\Exception $e) {
            // Gestion des exceptions générales
            return back()->with('error', 'Erreur lors du chargement des classes : ' . $e->getMessage());
        }

        /*$primaryTeacherCount = User:: whereHas('role', function($q){
                $q ->whereHas('name', 'teacher');
            }) -> whereHas('classes.entity', function($q2){ $q2 -> where('name', 'primaire');
            }) -> count();*/
    }
}

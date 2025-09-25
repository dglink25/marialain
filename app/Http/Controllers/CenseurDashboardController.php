<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\TeacherInvitation;
use App\Models\Classe;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;

class CenseurDashboardController extends Controller
{
    public function index(){
        $user = Auth::user();
        // Année scolaire active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return view('dashboards.censeur', compact('user'));
        }

        // Statistiques
        $secondaryEntityId = 3; 
        $studentsCount = Student::where('entity_id', $secondaryEntityId)
                                ->whereHas('classe', function($q) use ($activeYear) {
                                    $q->where('academic_year_id', $activeYear->id);
                                })
                                ->count();

        $teachersCount = TeacherInvitation::where('censeur_id', 6) // ID du censeur
                                  ->count();

        $classesCount = Classe::where('entity_id', $secondaryEntityId)
                              ->where('academic_year_id', $activeYear->id)
                              ->count();

        return view('dashboards.censeur', compact(
            'user',
            'studentsCount',
            'teachersCount',
            'classesCount',
            'activeYear'
        ));
    }
}

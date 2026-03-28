<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Classe;



class AdminAuthController extends Controller{
    public function index(){
        try {
            // 🔹 Récupération des données principales
            $studentsCount = Student::count();
            //dd(Student::count());

            $teachersCount = User::count();
            $classesCount  = Classe::count();
            $academicYearsCount = AcademicYear::count();
            

            // 🔹 Année académique active
            $activeYear = AcademicYear::where('active', true)->first();
            
            // 🔹 Si aucune année active trouvée, on le gère
            if (!$activeYear) {
                $activeYear = AcademicYear::latest('id')->first();
            }

            // 🔹 Nombre d'élèves dans l'année active
            $studentsInActiveYear = 0;
            if ($activeYear) {
                $studentsInActiveYear = Student::where('academic_year_id', $activeYear->id)->count();
            }

            // 🔹 Retour à la vue
            //return view('admin.dashboard', compact('academicYearsCount','classesCount','invitationsCount'));
            return view('admin.dashboard', [
                'studentsCount' => $studentsCount,
                'teachersCount' => $teachersCount,
                'classesCount' => $classesCount,
                'academicYearsCount' => $academicYearsCount,
                'activeYear' => $activeYear,
                'studentsInActiveYear' => $studentsInActiveYear,
            ]);

        } 
        catch (\Throwable $e) {
            // 🔥 Journaliser l'erreur
            Log::error('Erreur Dashboard Fondateur : ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // 🔹 Retour avec message d’erreur
            return back()->with('error', "Une erreur est survenue lors du chargement du tableau de bord.");
        }

    }

    public function accueil(){
        $academicYearsCount = \App\Models\AcademicYear::count();
        $classesCount = \App\Models\Classe::count();
        $invitationsCount = \App\Models\Invitation::count();

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
    
        return view('welcome', compact('primaryStudentsCount', 'primaryClassCount', 'primaryTeacherCount', 'academicYearsCount','classesCount','invitationsCount'));
    }


    public function createAdmin(Request $request){
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.dashboard')->with('success', 'Administrateur créé.');
    }
}

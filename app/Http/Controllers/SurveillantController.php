<?php

namespace App\Http\Controllers;

use App\Models\{Student, Classe, Punishment, Conduct, AcademicYear, Entity};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PunishmentNotification;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Support\Facades\Hash;


use App\Models\Teacher;
use App\Models\SchoolClass;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class SurveillantController extends Controller{
    // Liste des classes du secondaire pour l'année active
    public function classesList() {
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) return back()->withErrors("Aucune année académique active trouvée.");

        $secondary = Entity::where('name', 'Secondaire')->first();
        if (!$secondary) return back()->withErrors("L'entité 'secondaire' est introuvable.");

        $classes = Classe::where('academic_year_id', $activeYear->id)
            ->where('entity_id', $secondary->id)
            ->withCount(['students' => function($q) use ($activeYear, $secondary) {
                $q->where('academic_year_id', $activeYear->id)
                ->where('entity_id', $secondary->id);
            }])
            ->orderBy('name')
            ->get();

        return view('surveillant.classes.index', compact('classes'));
    }

    // Attribuer conduite à tous les élèves d'une classe
    public function assignConducts(Request $request, $classId) {
        $request->validate([
            'grade' => 'required|string|max:2',
            'comment' => 'nullable|string|max:255',
        ]);

        $activeYear = AcademicYear::where('active', true)->first();
        $secondary = Entity::where('name', 'secondaire')->first();

        if (!$activeYear || !$secondary) {
            return back()->withErrors("Impossible d'attribuer la conduite.");
        }

        $students = Student::where('academic_year_id', $activeYear->id)
            ->where('entity_id', $secondary->id)
            ->where('class_id', $classId)
            ->get();

        foreach ($students as $student) {
            Conduct::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $activeYear->id,
                    'entity_id' => $secondary->id,
                ],
                [
                    'grade' => $request->grade,
                    'comment' => $request->comment,
                ]
            );
        }

        return back()->with('success', "Conduite attribuée à tous les élèves de la classe.");
    }

    // Liste des élèves d’une classe
    public function classStudents($classId) {
        $activeYear = AcademicYear::where('active', true)->first();
        $secondary = Entity::where('name', 'secondaire')->first();

        if (!$activeYear || !$secondary) {
            return back()->withErrors("Impossible de charger les élèves.");
        }

        $students = Student::where('academic_year_id', $activeYear->id)
            ->where('entity_id', $secondary->id)
            ->where('class_id', $classId)
            ->orderBy('last_name')
            ->get();

        return view('surveillant.students.index', compact('students'));
    }

    // Punir un élève
    public function punish(Request $request, $studentId) {
        $request->validate([
            'reason' => 'required|string|max:255',
            'hours' => 'required|integer|min:1',
        ]);

        $activeYear = AcademicYear::where('active', true)->first();
        $secondary = Entity::where('name', 'secondaire')->first();

        if (!$activeYear || !$secondary) {
            return back()->withErrors("Impossible de punir l’élève.");
        }

        $punishment = Punishment::create([
            'student_id' => $studentId,
            'academic_year_id' => $activeYear->id,
            'entity_id' => $secondary->id,
            'reason' => $request->reason,
            'hours' => $request->hours,
            'date_punishment' => now(),
        ]);

        $student = $punishment->student;
        if ($student && $student->parent_email) {
            try {
                if ($student->parent_email) {
                    Mail::to($student->parent_email)
                        ->send(new PunishmentNotification($student, $request->reason, $request->hours));
                }
            } catch (\Exception $e) {
                return back()->withErrors("Punition enregistrée mais échec d’envoi du mail.");
            }
        }

        return back()->with('success', 'Élève puni et mail envoyé au parent.');
    }

    // Historique des punitions d’un élève
    public function punishmentsHistory($studentId) {
        $student = Student::with('punishments')->findOrFail($studentId);
        return view('surveillant.students.history', compact('student'));
    }
    public function surveillant(){
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
            return view('dashboards.surveillant', [
                'studentsCount' => $studentsCount,
                'teachersCount' => $teachersCount,
                'classesCount' => $classesCount,
                'academicYearsCount' => $academicYearsCount,
                'activeYear' => $activeYear,
                'studentsInActiveYear' => $studentsInActiveYear,
            ]);

        } 
        catch (\Throwable $e) {
            Log::error('Erreur Dashboard Fondateur : ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // 🔹 Retour avec message d’erreur
            return back()->with('error', "Une erreur est survenue lors du chargement du tableau de bord.");
        }
    }
}

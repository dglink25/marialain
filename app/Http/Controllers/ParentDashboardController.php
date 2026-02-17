<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use App\Models\Timetable;
use Illuminate\Support\Facades\Log;
use App\Models\Conduct;
use App\Models\Punishment;
use App\Models\ClassTeacherSubject;


class ParentDashboardController extends Controller{
   
    public function index(){
        // Récupérer l'année académique active
        $activeAcademicYear = AcademicYear::where('active', true)->first();
        
        // Récupérer les élèves du parent connecté pour l'année active
        $parent = auth('parent')->user();
        $students = Student::where('parent_phone', $parent->phone)
            ->when($activeAcademicYear, function ($query) use ($activeAcademicYear) {
                return $query->where('academic_year_id', $activeAcademicYear->id);
            })
            ->with(['classe', 'payments' => function ($query) use ($activeAcademicYear) {
                if ($activeAcademicYear) {
                    $query->where('academic_year_id', $activeAcademicYear->id);
                }
            }])
            ->get();

        // Calculer les statistiques globales
        $totalStudents = $students->count();
        
        // Calculer le pourcentage global de scolarité payée
        $totalSchoolFees = 0;
        $totalPaid = 0;
        
        foreach ($students as $student) {
            if ($student->classe && $student->classe->school_fees) {
                $totalSchoolFees += $student->classe->school_fees;
                $totalPaid += $student->payments->sum('amount');
            }
        }
        
        $paymentPercentage = $totalSchoolFees > 0 
            ? round(($totalPaid / $totalSchoolFees) * 100) 
            : 0;

        // Récupérer les dernières notes pour chaque élève
        $latestGrades = collect();
        foreach ($students as $student) {
            $grades = Grade::where('student_id', $student->id)
                ->with('subject')
                ->latest()
                ->take(5)
                ->get();
            $latestGrades = $latestGrades->concat($grades);
        }

        // Récupérer les actualités/événements (à adapter selon votre modèle)
        $news = collect([
            (object)[
                'day' => now()->day,
                'month' => now()->format('M'),
                'title' => 'Compositions du 2ème trimestre',
                'description' => 'Les compositions débutent le ' . now()->addDays(5)->format('d F Y'),
                'badge' => 'Nouveau',
                'badge_color' => 'success'
            ],
            (object)[
                'day' => now()->addDays(7)->day,
                'month' => now()->addDays(7)->format('M'),
                'title' => 'Réunion parents-professeurs',
                'description' => 'Salle polyvalente à 16h30',
                'badge' => 'À venir',
                'badge_color' => 'warning'
            ],
        ]);

        return view('parent.dashboard', compact(
            'students',
            'totalStudents',
            'paymentPercentage',
            'latestGrades',
            'news',
            'activeAcademicYear'
        ));
    }

    public static function getStudentStats($student, $academicYearId = null) {
    
        // Nombre d'absences (à adapter selon votre structure)
        $absences = 0; // À implémenter selon votre modèle d'absence
        
        // Récupérer le rang si la classe existe
        $rank = null;
        $totalStudents = 0;
        
        if ($student->classe) {
            $totalStudents = $student->classe->students()
                ->when($academicYearId, function ($query) use ($academicYearId) {
                    return $query->where('academic_year_id', $academicYearId);
                })
                ->count();
                
            // Calcul du rang (simplifié - à adapter selon votre logique)
            $rank = rand(1, max($totalStudents, 1)); // Exemple temporaire
        }
        $average = 1;
        

        return [
            'average' => $average ? round($average, 1) : null,
            'absences' => $absences,
            'rank' => $rank,
            'total_students' => $totalStudents
        ];
    }

    public function grades(Student $student){
        try {
            // Vérifier que l'étudiant appartient au parent connecté
            $parent = auth('parent')->user();
            
            if ($student->parent_phone !== $parent->phone) {
                return redirect()->route('parent.dashboard')
                    ->with('error', 'Vous n\'avez pas accès aux notes de cet élève.');
            }

            // Récupérer l'année académique active
            $activeYear = AcademicYear::where('active', true)->first();
            
            if (!$activeYear) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }

            // Charger les relations nécessaires
            $student->load(['classe']);

            // Récupérer toutes les matières de la classe avec leurs coefficients
            $subjects = ClassTeacherSubject::where('class_id', $student->class_id)
                ->with(['subject', 'teacher'])
                ->get();

            // Récupérer toutes les notes pour les 3 trimestres
            $grades = Grade::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->with('subject')
                ->get()
                ->groupBy(['trimestre', 'subject_id', 'type']);

            // Récupérer les conduites pour chaque trimestre
            $conducts = Conduct::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->get()
                ->keyBy('trimestre');

            // Récupérer les punitions pour calculer la conduite
            $punishments = Punishment::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->get();

            $totalPunishmentHours = $punishments->sum('hours');

            // Calculer les statistiques par trimestre
            $trimestres = [1, 2, 3];
            $stats = [];
            $allMoyennesCoeff = []; // Pour calculer le rang

            foreach ($trimestres as $trimestre) {
                $trimestreGrades = $grades[$trimestre] ?? collect();
                
                // Calculer la moyenne générale du trimestre
                $totalWeighted = 0;
                $totalCoeff = 0;
                $subjectStats = [];
                $matieresAvecNotes = 0;

                foreach ($subjects as $classSubject) {
                    $subjectGrades = $trimestreGrades[$classSubject->subject_id] ?? collect();
                    
                    // Séparer interrogations et devoirs
                    $interrogations = collect();
                    $devoirs = collect();
                    
                    if ($subjectGrades->isNotEmpty()) {
                        if (isset($subjectGrades['interrogation'])) {
                            $interrogations = collect($subjectGrades['interrogation'])->pluck('value');
                        }
                        if (isset($subjectGrades['devoir'])) {
                            $devoirs = collect($subjectGrades['devoir'])->pluck('value');
                        }
                    }
                    
                    // Calculer la moyenne des interrogations
                    $moyenneInterro = $interrogations->isNotEmpty() ? round($interrogations->avg(), 2) : 0;
                    
                    // Calculer la moyenne des devoirs
                    $moyenneDevoir = $devoirs->isNotEmpty() ? round($devoirs->avg(), 2) : 0;

                    // Calcul personnalisé de la moyenne sur 20

                    $nbDevoirs = $devoirs->count();

                    if ($moyenneInterro > 0 && $nbDevoirs > 0) {
                        
                        if ($nbDevoirs >= 2) {
                            // moyenneInterro + 2 devoirs / 3
                            $moyenneSur20 = round(
                                ($moyenneInterro + $devoirs->take(2)->sum()) / 3,
                                2
                            );
                        } else {
                            // moyenneInterro + 1 devoir / 2
                            $moyenneSur20 = round(
                                ($moyenneInterro + $devoirs->first()) / 2,
                                2
                            );
                        }

                    } elseif ($moyenneInterro > 0 && $nbDevoirs == 0) {
                        
                        // Pas de devoir
                        $moyenneSur20 = $moyenneInterro;

                    } elseif ($moyenneInterro == 0 && $nbDevoirs > 0) {
                        
                        // Pas d'interrogation
                        $moyenneSur20 = round($devoirs->avg(), 2);

                    } else {
                        
                        $moyenneSur20 = 0;
                    }

                    
                    // Moyenne coefficientée
                    $moyenneCoeff = round($moyenneSur20 * $classSubject->coefficient, 2);
                    
                    $subjectStats[$classSubject->subject_id] = [
                        'name' => $classSubject->subject->name,
                        'coefficient' => $classSubject->coefficient,
                        'teacher' => $classSubject->teacher->name,
                        'interrogations' => $interrogations->values(),
                        'devoirs' => $devoirs->values(),
                        'moyenne_interro' => $moyenneInterro,
                        'moyenne_devoir' => $moyenneDevoir,
                        'moyenne_sur_20' => $moyenneSur20,
                        'moyenne_coeff' => $moyenneCoeff
                    ];

                    if ($moyenneSur20 > 0) {
                        $totalWeighted += $moyenneSur20 * $classSubject->coefficient;
                        $totalCoeff += $classSubject->coefficient;
                        $matieresAvecNotes++;
                    }
                }

                // Récupérer la note de conduite de base
                $conduiteBase = $conducts[$trimestre]->grade ?? 10; // Note par défaut si non définie
                
                // Calculer la conduite finale (note de base - moitié des heures de punition)
                $conduiteFinale = max(0, $conduiteBase - ($totalPunishmentHours / 2));
                $conduiteFinale = round($conduiteFinale, 2);

                // Moyenne générale du trimestre (incluant la conduite)
                $moyenneGenerale = 0;
                if ($totalCoeff > 0 || $matieresAvecNotes > 0) {
                    // Somme des moyennes coeff + conduite divisé par (total coeff + 1)
                    $moyenneGenerale = round(($totalWeighted + $conduiteFinale) / ($totalCoeff + 1), 2);
                }

                $stats[$trimestre] = [
                    'subjects' => $subjectStats,
                    'moyenne_generale' => $moyenneGenerale,
                    'conduite_base' => round($conduiteBase, 2),
                    'conduite_finale' => $conduiteFinale,
                    'total_coeff' => $totalCoeff,
                    'total_weighted' => round($totalWeighted, 2),
                    'total_punishment_hours' => $totalPunishmentHours,
                    'matieres_avec_notes' => $matieresAvecNotes
                ];

                // Stocker pour le calcul du rang
                if ($moyenneGenerale > 0) {
                    $allMoyennesCoeff[$trimestre] = $moyenneGenerale;
                }
            }

            // Calculer les statistiques générales
            $effectif = $student->classe->students()->where('academic_year_id', $activeYear->id)->count();
            
            // Calculer les moyennes de toute la classe pour comparaison
            $classAverages = $this->calculateClassAverages($student->class_id, $activeYear->id);

            // Calculer les rangs par trimestre
            $rangs = $this->calculateRanks($student->class_id, $activeYear->id, $stats);

            return view('parent.grades.index', compact(
                'student',
                'subjects',
                'stats',
                'punishments',
                'activeYear',
                'effectif',
                'classAverages',
                'rangs'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur chargement notes parent: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des notes.');
        }
    }

    /**
     * Calculer les moyennes de la classe
     */
    private function calculateClassAverages($classId, $academicYearId){
        // Récupérer tous les élèves de la classe
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        $allAverages = [];

        foreach ($students as $student) {
            $grades = Grade::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->get()
                ->groupBy('subject_id');

            $totalWeighted = 0;
            $totalCoeff = 0;

            foreach ($grades as $subjectId => $subjectGrades) {
                $classSubject = ClassTeacherSubject::where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->first();

                if ($classSubject) {
                    $toutesNotes = $subjectGrades->pluck('value');
                    $moyenneSur20 = $toutesNotes->isNotEmpty() ? $toutesNotes->avg() : 0;
                    
                    $totalWeighted += $moyenneSur20 * $classSubject->coefficient;
                    $totalCoeff += $classSubject->coefficient;
                }
            }

            if ($totalCoeff > 0) {
                $allAverages[] = $totalWeighted / $totalCoeff;
            }
        }

        return [
            'plus_forte' => !empty($allAverages) ? round(max($allAverages), 2) : 0,
            'plus_faible' => !empty($allAverages) ? round(min($allAverages), 2) : 0,
            'moyenne_classe' => !empty($allAverages) ? round(array_sum($allAverages) / count($allAverages), 2) : 0
        ];
    }

    /**
     * Calculer les rangs par trimestre
     */
    private function calculateRanks($classId, $academicYearId, $studentStats){
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        $rangs = [1 => null, 2 => null, 3 => null];

        foreach ([1, 2, 3] as $trimestre) {
            $moyennes = [];
            
            foreach ($students as $student) {
                // Récupérer la moyenne de cet élève pour ce trimestre
                // Cette logique est simplifiée - à adapter selon votre structure
                $grades = Grade::where('student_id', $student->id)
                    ->where('academic_year_id', $academicYearId)
                    ->where('trimestre', $trimestre)
                    ->get()
                    ->groupBy('subject_id');

                $totalWeighted = 0;
                $totalCoeff = 0;

                foreach ($grades as $subjectId => $subjectGrades) {
                    $classSubject = ClassTeacherSubject::where('class_id', $classId)
                        ->where('subject_id', $subjectId)
                        ->first();

                    if ($classSubject) {
                        $toutesNotes = $subjectGrades->pluck('value');
                        $moyenneSur20 = $toutesNotes->isNotEmpty() ? $toutesNotes->avg() : 0;
                        
                        $totalWeighted += $moyenneSur20 * $classSubject->coefficient;
                        $totalCoeff += $classSubject->coefficient;
                    }
                }

                if ($totalCoeff > 0) {
                    $moyennes[] = $totalWeighted / $totalCoeff;
                }
            }

            // Trier par ordre décroissant
            rsort($moyennes);
            
            // Trouver le rang de notre élève
            $notreMoyenne = $studentStats[$trimestre]['moyenne_generale'] ?? 0;
            
            foreach ($moyennes as $index => $moyenne) {
                if (abs($moyenne - $notreMoyenne) < 0.01) {
                    $rangs[$trimestre] = $index + 1;
                    break;
                }
            }
        }

        return $rangs;
    }

    
    public function attendance(Student $student) {
      
        return view('parent.child.attendance', compact('student'));
    }

    public function payments(Student $student)  {
     
        return view('parent.child.payments', compact('student'));
    }

    public function timetable($studentId) {
        try {
            // Vérifie s'il existe une année active
            $activeYear = AcademicYear::where('active', true)->first();

            if (!$activeYear) {
                return redirect()->route('parent.dashboard')
                    ->with('error', 'Aucune année scolaire active trouvée.');
            }

            // Vérifie que l'étudiant appartient au parent connecté
            $parent = auth('parent')->user();
            
            $student = Student::where('id', $studentId)
                ->where('parent_phone', $parent->phone)
                ->where('academic_year_id', $activeYear->id)
                ->with('classe')
                ->firstOrFail();

            // Vérifie si la classe existe
            if (!$student->classe) {
                return redirect()->route('parent.dashboard')
                    ->with('error', 'Cet élève n\'a pas de classe assignée.');
            }

            // Récupération des emplois du temps pour cette classe et année
            $timetables = Timetable::where('class_id', $student->class_id)
                ->where('academic_year_id', $activeYear->id)
                ->with(['teacher', 'subject'])
                ->get();

            // Jours de la semaine dans l'ordre
            $joursSemaine = [
                'Lundi' => 1, 
                'Mardi' => 2, 
                'Mercredi' => 3, 
                'Jeudi' => 4, 
                'Vendredi' => 5, 
                'Samedi' => 6
            ];
            
            // Grouper par jour
            $timetablesByDay = $timetables->groupBy('day');
            
            // Trier les emplois du temps par jour et heure
            $sortedTimetables = collect();
            foreach ($joursSemaine as $jour => $order) {
                if (isset($timetablesByDay[$jour])) {
                    $sortedTimetables[$jour] = $timetablesByDay[$jour]->sortBy('start_time');
                } else {
                    $sortedTimetables[$jour] = collect();
                }
            }

            // Génération des heures (7h à 18h)
            $hours = [];
            for ($h = 7; $h < 18; $h++) {
                $hours[] = [
                    'slot' => sprintf('%02dh-%02dh', $h, $h + 1),
                    'start' => sprintf('%02d:00', $h),
                    'end' => sprintf('%02d:00', $h + 1)
                ];
            }

            // Debug: Vérifier les données
            Log::info('Student classe ID: ' . $student->class_id);
            Log::info('Timetables found: ' . $timetables->count());
            Log::info('Timetables data: ' . $timetables->toJson());

            return view('parent.timetable', compact(
                'student', 
                'hours', 
                'sortedTimetables',
                'timetables', // Important: on passe aussi $timetables pour la vérification dans la vue
                'activeYear',
                'joursSemaine'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Élève non trouvé: ' . $e->getMessage());
            return redirect()->route('parent.dashboard')
                ->with('error', 'Élève introuvable ou non associé à votre compte.');
        } catch (\Exception $e) {
            Log::error('Erreur chargement emploi du temps: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('parent.dashboard')
                ->with('error', 'Erreur lors du chargement de l\'emploi du temps: ' . $e->getMessage());
        }
    }

}

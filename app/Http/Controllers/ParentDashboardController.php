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

        // Calculer les statistiques globales avec le nouveau système total_fees
        $totalStudents = $students->count();
        
        // Calculer le pourcentage global de scolarité payée (utilisant total_fees)
        $totalFeesToPay = 0;
        $totalPaid = 0;
        
        foreach ($students as $student) {
            // Utiliser total_fees s'il existe, sinon calculer à partir de la classe
            if ($student->total_fees) {
                $totalFeesToPay += $student->total_fees;
            } elseif ($student->classe) {
                $totalFeesToPay += $student->classe->school_fees ?? 0;
                // Ajouter les frais d'inscription si le type est défini
                if ($student->registration_type === 'new') {
                    $totalFeesToPay += $student->classe->registration_fee ?? 0;
                } elseif ($student->registration_type === 're_registration') {
                    $totalFeesToPay += $student->classe->re_registration_fee ?? 0;
                }
            }
            $totalPaid += $student->payments->sum('amount');
        }
        
        $paymentPercentage = $totalFeesToPay > 0 
            ? round(($totalPaid / $totalFeesToPay) * 100) 
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
            'activeAcademicYear',
            'totalFeesToPay',
            'totalPaid'
        ));
    }

    public static function getStudentStats($student, $academicYearId = null)  {
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

        // Calculer la moyenne générale (simplifié)
        $average = 1;

        // Récupérer les informations de frais
        $totalFees = $student->total_fees;
        $totalPaid = $student->payments->sum('amount');
        $remainingFees = $totalFees - $totalPaid;
        $paymentStatus = $totalFees > 0 ? round(($totalPaid / $totalFees) * 100) : 0;

        return [
            'average' => $average ? round($average, 1) : null,
            'absences' => $absences,
            'rank' => $rank,
            'total_students' => $totalStudents,
            'total_fees' => $totalFees,
            'total_paid' => $totalPaid,
            'remaining_fees' => $remainingFees,
            'payment_status' => $paymentStatus,
            'registration_type' => $student->registration_type,
            'registration_type_label' => $student->registration_type === 'new' ? 'Nouvelle inscription' : 
                                        ($student->registration_type === 're_registration' ? 'Réinscription' : 'Non défini')
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

            // Récupérer les informations de frais
            $totalFees = $student->total_fees;
            $totalPaid = $student->payments()->where('academic_year_id', $activeYear->id)->sum('amount');
            $remainingFees = $totalFees - $totalPaid;
            $paymentStatus = $totalFees > 0 ? round(($totalPaid / $totalFees) * 100) : 0;

            // Déplacer ces variables en dehors de la boucle foreach des trimestres
            $allClassStudents = Student::where('class_id', $student->class_id)
                ->where('academic_year_id', $activeYear->id)
                ->get();

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

            // Récupérer toutes les notes pour tous les élèves de la classe
            $allGrades = Grade::whereIn('student_id', $allClassStudents->pluck('id'))
                ->where('academic_year_id', $activeYear->id)
                ->get()
                ->groupBy(['student_id', 'trimestre', 'subject_id', 'type']);

            foreach ($trimestres as $trimestre) {
                $trimestreGrades = $grades[$trimestre] ?? collect();
                
                // Calculer d'abord les moyennes pour tous les élèves de la classe
                $classAveragesForRank = [];
                $subjectScoresForRank = [];

                foreach ($allClassStudents as $classStudent) {
                    $studentTrimestreGrades = $allGrades[$classStudent->id][$trimestre] ?? collect();
                    
                    $totalWeighted = 0;
                    $totalCoeff = 0;
                    $studentSubjectAverages = [];

                    foreach ($subjects as $classSubject) {
                        $subjectGrades = $studentTrimestreGrades[$classSubject->subject_id] ?? collect();
                        
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
                        
                        // Calcul personnalisé de la moyenne sur 20
                        $nbDevoirs = $devoirs->count();

                        if ($interrogations->isNotEmpty() && $nbDevoirs > 0) {
                            if ($nbDevoirs >= 2) {
                                $moyenneSur20 = round(
                                    ($interrogations->avg() + $devoirs->take(2)->sum()) / 3,
                                    2
                                );
                            } else {
                                $moyenneSur20 = round(
                                    ($interrogations->avg() + $devoirs->first()) / 2,
                                    2
                                );
                            }
                        } elseif ($interrogations->isNotEmpty() && $nbDevoirs == 0) {
                            $moyenneSur20 = round($interrogations->avg(), 2);
                        } elseif ($interrogations->isEmpty() && $nbDevoirs > 0) {
                            $moyenneSur20 = round($devoirs->avg(), 2);
                        } else {
                            $moyenneSur20 = 0;
                        }

                        $studentSubjectAverages[$classSubject->subject_id] = $moyenneSur20;

                        if ($moyenneSur20 > 0) {
                            $totalWeighted += $moyenneSur20 * $classSubject->coefficient;
                            $totalCoeff += $classSubject->coefficient;
                        }
                    }

                    // Récupérer la conduite pour cet élève
                    $studentConduct = Conduct::where('student_id', $classStudent->id)
                        ->where('academic_year_id', $activeYear->id)
                        ->where('trimestre', $trimestre)
                        ->first();
                    
                    $conduiteBase = $studentConduct->grade ?? 10;
                    
                    $studentPunishments = Punishment::where('student_id', $classStudent->id)
                        ->where('academic_year_id', $activeYear->id)
                        ->get();
                    
                    $studentPunishmentHours = $studentPunishments->sum('hours');
                    $conduiteFinale = max(0, $conduiteBase - ($studentPunishmentHours / 2));
                    
                    if ($totalCoeff > 0) {
                        $moyenneGenerale = round(($totalWeighted + $conduiteFinale) / ($totalCoeff + 1), 2);
                        $classAveragesForRank[$classStudent->id] = [
                            'moyenne' => $moyenneGenerale,
                            'subject_averages' => $studentSubjectAverages
                        ];
                    }
                }

                // Trier les élèves par moyenne générale pour les rangs généraux
                uasort($classAveragesForRank, function($a, $b) {
                    return $b['moyenne'] <=> $a['moyenne'];
                });

                // Calculer les rangs par matière
                $subjectRanks = [];
                foreach ($subjects as $classSubject) {
                    $subjectId = $classSubject->subject_id;
                    $subjectScores = [];
                    
                    foreach ($classAveragesForRank as $studentId => $data) {
                        if (isset($data['subject_averages'][$subjectId]) && $data['subject_averages'][$subjectId] > 0) {
                            $subjectScores[$studentId] = $data['subject_averages'][$subjectId];
                        }
                    }
                    
                    // Trier par note décroissante
                    arsort($subjectScores);
                    
                    // Trouver le rang de notre élève
                    $rank = 1;
                    foreach ($subjectScores as $studentId => $score) {
                        if ($studentId == $student->id) {
                            $subjectRanks[$subjectId] = $rank;
                            break;
                        }
                        $rank++;
                    }
                    
                    // Si l'élève n'a pas de note dans cette matière
                    if (!isset($subjectRanks[$subjectId])) {
                        $subjectRanks[$subjectId] = null;
                    }
                }

                // Calculer le rang général
                $generalRank = null;
                $rank = 0;
                foreach ($classAveragesForRank as $studentId => $data) {
                    $rank++;
                    if ($studentId == $student->id) {
                        $generalRank = $rank;
                        break;
                    }
                }

                // Ensuite, calculer les stats pour l'élève spécifique
                $totalWeighted = 0;
                $totalCoeff = 0;
                $subjectStats = [];
                $matieresAvecNotes = 0;

                foreach ($subjects as $classSubject) {
                    $subjectGrades = $trimestreGrades[$classSubject->subject_id] ?? collect();
                    
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
                    
                    $moyenneInterro = $interrogations->isNotEmpty() ? round($interrogations->avg(), 2) : 0;
                    $moyenneDevoir = $devoirs->isNotEmpty() ? round($devoirs->avg(), 2) : 0;

                    // Calcul personnalisé de la moyenne sur 20
                    $nbDevoirs = $devoirs->count();

                    if ($interrogations->isNotEmpty() && $nbDevoirs > 0) {
                        if ($nbDevoirs >= 2) {
                            $moyenneSur20 = round(
                                ($moyenneInterro + $devoirs->take(2)->sum()) / 3,
                                2
                            );
                        } else {
                            $moyenneSur20 = round(
                                ($moyenneInterro + $devoirs->first()) / 2,
                                2
                            );
                        }
                    } elseif ($interrogations->isNotEmpty() && $nbDevoirs == 0) {
                        $moyenneSur20 = $moyenneInterro;
                    } elseif ($interrogations->isEmpty() && $nbDevoirs > 0) {
                        $moyenneSur20 = round($devoirs->avg(), 2);
                    } else {
                        $moyenneSur20 = 0;
                    }
                    
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
                        'moyenne_coeff' => $moyenneCoeff,
                        'rang' => $subjectRanks[$classSubject->subject_id] ?? null
                    ];

                    if ($moyenneSur20 > 0) {
                        $totalWeighted += $moyenneSur20 * $classSubject->coefficient;
                        $totalCoeff += $classSubject->coefficient;
                        $matieresAvecNotes++;
                    }
                }

                // Récupérer la note de conduite de base
                $conduiteBase = $conducts[$trimestre]->grade ?? 10;
                $conduiteFinale = max(0, $conduiteBase - ($totalPunishmentHours / 2));
                $conduiteFinale = round($conduiteFinale, 2);

                // Moyenne générale du trimestre
                $moyenneGenerale = 0;
                if ($totalCoeff > 0 || $matieresAvecNotes > 0) {
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
                    'matieres_avec_notes' => $matieresAvecNotes,
                    'rang_general' => $generalRank,
                    'effectif_classe' => count($classAveragesForRank)
                ];
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
                'rangs',
                'totalFees',
                'totalPaid',
                'remainingFees',
                'paymentStatus'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur chargement notes parent: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des notes.');
        }
    }

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

    private function calculateClassAverages($classId, $academicYearId){
        // Récupérer tous les élèves de la classe
        $students = Student::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->get();

        // Récupérer toutes les matières de la classe
        $subjects = ClassTeacherSubject::where('class_id', $classId)
            ->with(['subject', 'teacher'])
            ->get();

        // Récupérer toutes les notes pour tous les élèves
        $allGrades = Grade::whereIn('student_id', $students->pluck('id'))
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->groupBy(['student_id', 'trimestre', 'subject_id', 'type']);

        $trimestreAverages = [1 => [], 2 => [], 3 => []];

        foreach ($students as $student) {
            for ($trimestre = 1; $trimestre <= 3; $trimestre++) {
                $studentTrimestreGrades = $allGrades[$student->id][$trimestre] ?? collect();
                
                $totalWeighted = 0;
                $totalCoeff = 0;

                foreach ($subjects as $classSubject) {
                    $subjectGrades = $studentTrimestreGrades[$classSubject->subject_id] ?? collect();
                    
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
                    
                    // Calcul personnalisé de la moyenne sur 20 (MÊME LOGIQUE QUE POUR L'ÉLÈVE)
                    $nbDevoirs = $devoirs->count();

                    if ($interrogations->isNotEmpty() && $nbDevoirs > 0) {
                        if ($nbDevoirs >= 2) {
                            $moyenneSur20 = ($interrogations->avg() + $devoirs->take(2)->sum()) / 3;
                        } else {
                            $moyenneSur20 = ($interrogations->avg() + $devoirs->first()) / 2;
                        }
                    } elseif ($interrogations->isNotEmpty() && $nbDevoirs == 0) {
                        $moyenneSur20 = $interrogations->avg();
                    } elseif ($interrogations->isEmpty() && $nbDevoirs > 0) {
                        $moyenneSur20 = $devoirs->avg();
                    } else {
                        $moyenneSur20 = 0;
                    }
                    
                    if ($moyenneSur20 > 0) {
                        $totalWeighted += $moyenneSur20 * $classSubject->coefficient;
                        $totalCoeff += $classSubject->coefficient;
                    }
                }

                // Récupérer les punitions pour la conduite
                $punishments = Punishment::where('student_id', $student->id)
                    ->where('academic_year_id', $academicYearId)
                    ->get();
                
                $totalPunishmentHours = $punishments->sum('hours');
                
                // Récupérer la conduite
                $conduct = Conduct::where('student_id', $student->id)
                    ->where('academic_year_id', $academicYearId)
                    ->where('trimestre', $trimestre)
                    ->first();
                
                $conduiteBase = $conduct->grade ?? 10;
                $conduiteFinale = max(0, $conduiteBase - ($totalPunishmentHours / 2));

                if ($totalCoeff > 0) {
                    $moyenneAvecConduite = ($totalWeighted + $conduiteFinale) / ($totalCoeff + 1);
                    $trimestreAverages[$trimestre][] = $moyenneAvecConduite;
                }
            }
        }

        // Calculer les stats pour chaque trimestre
        $result = [];
        for ($trimestre = 1; $trimestre <= 3; $trimestre++) {
            $averages = $trimestreAverages[$trimestre];
            $result[$trimestre] = [
                'plus_forte' => !empty($averages) ? round(max($averages), 2) : 0,
                'plus_faible' => !empty($averages) ? round(min($averages), 2) : 0,
                'moyenne_classe' => !empty($averages) ? round(array_sum($averages) / count($averages), 2) : 0
            ];
        }

        return $result;
    }

}
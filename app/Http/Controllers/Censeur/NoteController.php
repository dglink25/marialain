<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\NotePermission;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\ClassTeacherSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Grade;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Conduct;
use App\Models\Punishment;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotesTrimestreExport;
use Carbon\Carbon;
use App\Models\NoteEditPermission;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;    
use App\Exports\NotesSubjectExport;


    class NoteController extends Controller{

        public function index(){
            $activeYear = AcademicYear::where('active', true)->firstOrFail();

            $classes = Classe::where('entity_id', 3)->where('academic_year_id', $activeYear->id)->get();

            return view('censeur.classes.notes.index', compact('classes'));
        }

        public function downloadPdf($classId, $studentId, $trimestre) {
            try {
                // 🔹 Année académique active
                $activeYear = AcademicYear::where('active', true)->firstOrFail();

                // 🔹 Élève et classe
                $student = Student::findOrFail($studentId);
                $classe = Classe::with(['students' => function ($query) use ($activeYear) {
                    $query->where('is_validated', 1)
                        ->where('academic_year_id', $activeYear->id)
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])->findOrFail($classId);

                // 🔹 Récupérer les matières
                $subjects = Subject::whereHas('classTeacherSubjects', function($query) use ($classId, $activeYear) {
                    $query->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id);
                })->with(['classTeacherSubjects' => function($query) use ($classId, $activeYear) {
                    $query->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id);
                }])->orderBy('name')->get();

                // 🔹 PRÉ-CALCUL : Stocker les moyennes par matière pour tous les élèves pour calculer les rangs
                $allStudentsMoyennesParMatiere = [];
                $allStudentsData = [];
                
                // Préparer toutes les données de base pour chaque élève
                foreach ($classe->students as $st) {
                    $stId = $st->id;
                    
                    // Récupération des notes pour cet élève
                    $stGrades = Grade::where('student_id', $stId)
                        ->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id)
                        ->where('trimestre', $trimestre)
                        ->get();
                    
                    // Conduite et punitions
                    $stConduct = Conduct::where('student_id', $stId)
                        ->where('trimestre', $trimestre)
                        ->where('academic_year_id', $activeYear->id)
                        ->first();
                    
                    $stPunishments = Punishment::where('student_id', $stId)
                        ->where('academic_year_id', $activeYear->id)
                        ->get();
                    
                    $stPunishHours = $stPunishments->sum('hours');
                    
                    // Calcul de la conduite
                    $stConductGrade = $stConduct ? $stConduct->grade : 0;
                    $stConductFinal = max(0, $stConductGrade - ($stPunishHours / 2));
                    $stConduiteSur20 = round($stConductFinal, 2);
                    
                    // Stocker les données de base pour cet élève
                    $allStudentsData[$stId] = [
                        'student' => $st,
                        'grades' => $stGrades,
                        'conduiteSur20' => $stConduiteSur20,
                        'moyennesParMatiere' => []
                    ];
                    
                    // Calculer les moyennes par matière pour cet élève
                    foreach ($subjects as $subject) {
                        // Récupérer le coefficient
                        $coefRecord = $subject->classTeacherSubjects->first();
                        $coef = $coefRecord->coefficient ?? 1;
                        
                        // Récupération des notes
                        $subjectGrades = $stGrades->where('subject_id', $subject->id);
                        
                        // Notes d'interrogations
                        $interroNotes = $subjectGrades->where('type', 'interrogation')
                            ->sortBy('sequence')
                            ->pluck('value')
                            ->filter(fn($v) => $v !== null) 
                            ->values()
                            ->toArray();
                        
                        // Notes de devoir
                        $devoir1 = $subjectGrades->where('type', 'devoir')
                            ->where('sequence', 1)
                            ->first()->value ?? null;
                        $devoir2 = $subjectGrades->where('type', 'devoir')
                            ->where('sequence', 2)
                            ->first()->value ?? null;
                        
                        // Calcul de la moyenne des interrogations
                        $moyenneInterro = !empty($interroNotes) ? 
                            round(array_sum($interroNotes) / count($interroNotes), 2) : null;
                        
                        // Calcul de la moyenne matière
                        $moyenneMatiere = null;
                        $notesPourMoyenne = [];
                        
                        if ($moyenneInterro !== null) {
                            $notesPourMoyenne[] = $moyenneInterro;
                        }
                        
                        if ($devoir1 !== null) {
                            $notesPourMoyenne[] = $devoir1;
                        }
                        if ($devoir2 !== null) {
                            $notesPourMoyenne[] = $devoir2;
                        }
                        
                        if (!empty($notesPourMoyenne)) {
                            $moyenneMatiere = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                        }
                        
                        // Stocker la moyenne par matière pour le calcul du rang
                        if ($moyenneMatiere !== null) {
                            $allStudentsData[$stId]['moyennesParMatiere'][$subject->id] = $moyenneMatiere;
                            $allStudentsMoyennesParMatiere[$subject->id][$stId] = $moyenneMatiere;
                        }
                    }
                    
                    // Ajouter la conduite comme une "matière" spéciale
                    $allStudentsMoyennesParMatiere['CONDUITE'][$stId] = $stConduiteSur20;
                    $allStudentsData[$stId]['moyennesParMatiere']['CONDUITE'] = $stConduiteSur20;
                }
                
                // 🔹 Calculer les rangs par matière pour tous les élèves
                $rangsParMatiere = [];
                foreach ($allStudentsMoyennesParMatiere as $subjectId => $moyennes) {
                    if (!empty($moyennes)) {
                        // Trier par ordre décroissant (meilleure note en premier)
                        arsort($moyennes);
                        
                        // Assigner les rangs
                        $rang = 1;
                        $previousValue = null;
                        $sameRankCount = 0;
                        
                        foreach ($moyennes as $stId => $value) {
                            if ($previousValue !== null && $value == $previousValue) {
                                $sameRankCount++;
                            } else {
                                $rang += $sameRankCount;
                                $sameRankCount = 1;
                            }
                            
                            $rangsParMatiere[$subjectId][$stId] = $rang . 'e';
                            $previousValue = $value;
                        }
                    }
                }

                // 🔹 Récupération des notes pour l'élève courant
                $grades = Grade::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();

                // 🔹 Conduite et punitions pour l'élève courant
                $conduct = Conduct::where('student_id', $studentId)
                    ->where('trimestre', $trimestre)
                    ->where('academic_year_id', $activeYear->id)
                    ->first();
                
                $punishments = Punishment::where('student_id', $studentId)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                $punishHours = $punishments->sum('hours');

                // Calcul de la conduite
                $conductGrade = $conduct ? $conduct->grade : 0;
                $conductFinal = max(0, $conductGrade - ($punishHours / 2));
                $conduiteSur20 = round($conductFinal, 2);

                // 🔹 Calcul des moyennes par matière
                $bulletin = [];
                $totalMoyCoeff = 0;
                $totalCoeff = 0;
                $moyennesLitteraires = [];
                $moyennesScientifiques = [];
                $moyennesAutres = [];

                // Liste des matières par catégorie
                $matieresLitteraires = ['COMMUNICATION ECRITE', 'LECTURE', 'ANGLAIS', 'HISTOIRE-GEOGRAPHIE', 'FRANÇAIS', 'PHILOSOPHIE', 'ESPAGNOL', 'HGGSP'];
                $Autrematiere = ['EDUCATION PHYSIQUE ET SPORTIVE (EPS)', 'CONDUITE'];
                $matieresScientifiques = ['MATHEMATIQUES', 'PHYSIQUE CHIMIE ET TECHNOLOGIE (PCT)', 'SCIENCE DE LA VIE ET DE LA TERRE (SVT)', 'ENSEIGNEMENTS SCIENTIFIQUES'];

                foreach ($subjects as $subject) {
                    // Récupérer le coefficient
                    $coefRecord = $subject->classTeacherSubjects->first();
                    $coef = $coefRecord->coefficient ?? 1;

                    // Récupération des notes
                    $subjectGrades = $grades->where('subject_id', $subject->id);

                    // Notes d'interrogations
                    $interroNotes = $subjectGrades->where('type', 'interrogation')
                        ->sortBy('sequence')
                        ->pluck('value')
                        ->filter(fn($v) => $v !== null)
                        ->values()
                        ->toArray();

                    // Notes de devoir
                    $devoir1 = $subjectGrades->where('type', 'devoir')
                        ->where('sequence', 1)
                        ->first()->value ?? null;
                    $devoir2 = $subjectGrades->where('type', 'devoir')
                        ->where('sequence', 2)
                        ->first()->value ?? null;

                    // Calcul de la moyenne des interrogations
                    $moyenneInterro = !empty($interroNotes) ? 
                        round(array_sum($interroNotes) / count($interroNotes), 2) : null;

                    // Calcul de la moyenne matière
                    $moyenneMatiere = null;
                    $notesPourMoyenne = [];
                    
                    if ($moyenneInterro !== null) {
                        $notesPourMoyenne[] = $moyenneInterro;
                    }
                    
                    if ($devoir1 !== null) {
                        $notesPourMoyenne[] = $devoir1;
                    }
                    if ($devoir2 !== null) {
                        $notesPourMoyenne[] = $devoir2;
                    }
                    
                    if (!empty($notesPourMoyenne)) {
                        $moyenneMatiere = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                    }

                    // Calculer moyenne coefficientée
                    $moyCoeff = null;
                    $appreciation = '-';
                    
                    if ($moyenneMatiere !== null) {
                        $moyCoeff = round($moyenneMatiere * $coef, 2);
                        
                        // Appréciation par matière
                        if ($moyenneMatiere > 16) $appreciation = 'Très Bien';
                        elseif ($moyenneMatiere >= 14) $appreciation = 'Bien';
                        elseif ($moyenneMatiere >= 12) $appreciation = 'Assez Bien';
                        elseif ($moyenneMatiere >= 10) $appreciation = 'Passable';
                        elseif ($moyenneMatiere >= 8) $appreciation = 'Insuffisant';
                        elseif ($moyenneMatiere >= 6) $appreciation = 'Faible';
                        elseif ($moyenneMatiere >= 4) $appreciation = 'Médiocre';
                        else $appreciation = 'Très Faible';

                        // Classer par catégorie pour les moyennes par domaine
                        $nomMatiere = strtoupper($subject->name);
                        if (in_array($nomMatiere, $matieresLitteraires)) {
                            $moyennesLitteraires[] = $moyenneMatiere;
                        } elseif (in_array($nomMatiere, $matieresScientifiques)) {
                            $moyennesScientifiques[] = $moyenneMatiere;
                        } elseif (in_array($nomMatiere, $Autrematiere)) {
                            $moyennesAutres[] = $moyenneMatiere;
                        }
                    }

                    // Formatage des notes pour l'affichage
                    $interrosFormatted = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $interrosFormatted[$i] = isset($interroNotes[$i-1]) ? number_format($interroNotes[$i-1], 2, ',', '') : '-';
                    }

                    $devoirsFormatted = [];
                    $devoirsFormatted[1] = $devoir1 !== null ? number_format($devoir1, 2, ',', '') : '-';
                    $devoirsFormatted[2] = $devoir2 !== null ? number_format($devoir2, 2, ',', '') : '-';

                    // Récupérer le rang pour cette matière
                    $rangMatiere = isset($rangsParMatiere[$subject->id][$studentId]) ? 
                        $rangsParMatiere[$subject->id][$studentId] : '-';

                    $bulletin[] = [
                        'subject' => strtoupper($subject->name),
                        'coef' => $coef,
                        'interros' => $interrosFormatted,
                        'devoirs' => $devoirsFormatted,
                        'moyenneInterro' => $moyenneInterro !== null ? number_format($moyenneInterro, 2, ',', '') : '-',
                        'moyenne' => $moyenneMatiere !== null ? number_format($moyenneMatiere, 2, ',', '') : '-',
                        'moyCoeff' => $moyCoeff !== null ? number_format($moyCoeff, 2, ',', '') : '-',
                        'rang' => $rangMatiere,
                        'appreciation' => $appreciation,
                    ];

                    // Pour le calcul de la moyenne générale
                    if ($moyenneMatiere !== null && $moyCoeff !== null) {
                        $totalCoeff += $coef;
                        $totalMoyCoeff += $moyCoeff;
                    }
                }

                // 🔹 Ajouter la CONDUITE comme une matière
                $conduiteAppreciation = '-';
                if ($conduiteSur20 > 0) {
                    if ($conduiteSur20 >= 14) $conduiteAppreciation = 'Très Bien';
                    elseif ($conduiteSur20 >= 12) $conduiteAppreciation = 'Bien';
                    elseif ($conduiteSur20 >= 10) $conduiteAppreciation = 'Passable';
                    elseif ($conduiteSur20 >= 8) $conduiteAppreciation = 'Insuffisante';
                    elseif ($conduiteSur20 >= 6) $conduiteAppreciation = 'Faible';
                    elseif ($conduiteSur20 >= 4) $conduiteAppreciation = 'Médiocre';
                    else $conduiteAppreciation = 'Très Faible';

                    $totalCoeff += 1;
                    $totalMoyCoeff += $conduiteSur20;
                    
                    // Ajouter la conduite aux autres matières
                    $moyennesAutres[] = $conduiteSur20;
                    
                    // Récupérer le rang pour la conduite
                    $rangConduite = isset($rangsParMatiere['CONDUITE'][$studentId]) ? 
                        $rangsParMatiere['CONDUITE'][$studentId] : '-';
                } else {
                    $rangConduite = '-';
                }

                // Ajouter la conduite au bulletin
                $bulletin[] = [
                    'subject' => 'CONDUITE',
                    'coef' => 1,
                    'interros' => [1 => '-', 2 => '-', 3 => '-', 4 => '-', 5 => '-'],
                    'devoirs' => [1 => '-', 2 => number_format($conduiteSur20, 2, ',', '')],
                    'moyenneInterro' => '-',
                    'moyenne' => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
                    'moyCoeff' => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
                    'rang' => $rangConduite,
                    'appreciation' => $conduiteAppreciation,
                ];

                // 🔹 Calcul de la moyenne générale
                $moyenneGenerale = null;
                if ($totalCoeff > 0) {
                    $moyenneGenerale = round($totalMoyCoeff / $totalCoeff, 2);
                }

                // 🔹 Calcul des moyennes par domaine
                $moyenneLitteraire = !empty($moyennesLitteraires) ? 
                    round(array_sum($moyennesLitteraires) / count($moyennesLitteraires), 2) : 0;
                $moyenneScientifique = !empty($moyennesScientifiques) ? 
                    round(array_sum($moyennesScientifiques) / count($moyennesScientifiques), 2) : 0;
                $moyenneAutresMatières = !empty($moyennesAutres) ? 
                    round(array_sum($moyennesAutres) / count($moyennesAutres), 2) : 0;

                // 🔹 Appréciation générale
                $appreciationGenerale = '-';
                if ($moyenneGenerale !== null) {
                    if ($moyenneGenerale > 16) $appreciationGenerale = 'Très Bien';
                    elseif ($moyenneGenerale >= 14) $appreciationGenerale = 'Bien';
                    elseif ($moyenneGenerale >= 12) $appreciationGenerale = 'Assez Bien';
                    elseif ($moyenneGenerale >= 10) $appreciationGenerale = 'Passable';
                    elseif ($moyenneGenerale >= 8) $appreciationGenerale = 'Insuffisant';
                    elseif ($moyenneGenerale >= 6) $appreciationGenerale = 'Faible';
                    elseif ($moyenneGenerale >= 4) $appreciationGenerale = 'Médiocre';
                    else $appreciationGenerale = 'Très Faible';
                }

                // 🔹 Calcul des moyennes générales de la classe pour le rang général
                $moyennesGeneralesClasse = [];
                
                foreach ($allStudentsData as $stId => $stData) {
                    $stGrades = $stData['grades'];
                    $stConduiteSur20 = $stData['conduiteSur20'];
                    
                    $stTotalPoints = 0;
                    $stTotalCoef = 0;
                    
                    // Calcul pour chaque matière
                    foreach ($subjects as $subject) {
                        $coefRecord = $subject->classTeacherSubjects->first();
                        $coef = $coefRecord->coefficient ?? 1;
                        
                        $subjectGrades = $stGrades->where('subject_id', $subject->id);
                        
                        // Calcul des notes
                        $interroNotes = $subjectGrades->where('type', 'interrogation')
                            ->pluck('value')
                            ->filter(fn($v) => $v !== null)
                            ->values()
                            ->toArray();
                        
                        $devoir1 = $subjectGrades->where('type', 'devoir')
                            ->where('sequence', 1)
                            ->first()->value ?? null;
                        $devoir2 = $subjectGrades->where('type', 'devoir')
                            ->where('sequence', 2)
                            ->first()->value ?? null;
                        
                        // Calcul de la moyenne matière
                        $moyenneInterro = !empty($interroNotes) ? 
                            array_sum($interroNotes) / count($interroNotes) : null;
                        
                        $notesPourMoyenne = [];
                        if ($moyenneInterro !== null) $notesPourMoyenne[] = $moyenneInterro;
                        if ($devoir1 !== null) $notesPourMoyenne[] = $devoir1;
                        if ($devoir2 !== null) $notesPourMoyenne[] = $devoir2;
                        
                        if (!empty($notesPourMoyenne)) {
                            $moyenneMatiere = array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                            $stTotalPoints += $moyenneMatiere * $coef;
                            $stTotalCoef += $coef;
                        }
                    }
                    
                    // Ajouter la conduite
                    if ($stConduiteSur20 > 0) {
                        $stTotalPoints += $stConduiteSur20;
                        $stTotalCoef += 1;
                    }
                    
                    // Calcul moyenne générale élève
                    if ($stTotalCoef > 0) {
                        $moyennesGeneralesClasse[$stId] = round($stTotalPoints / $stTotalCoef, 2);
                    }
                }

                // Calcul des statistiques de la classe
                $plusForte = !empty($moyennesGeneralesClasse) ? max($moyennesGeneralesClasse) : 0;
                $plusFaible = !empty($moyennesGeneralesClasse) ? min($moyennesGeneralesClasse) : 0;
                $moyClasse = !empty($moyennesGeneralesClasse) ? 
                    round(array_sum($moyennesGeneralesClasse) / count($moyennesGeneralesClasse), 2) : 0;

                // Calcul du rang général pour cet élève
                $rang = '-';
                if (isset($moyennesGeneralesClasse[$studentId])) {
                    arsort($moyennesGeneralesClasse);
                    $positions = array_keys($moyennesGeneralesClasse);
                    $position = array_search($studentId, $positions);
                    $rang = $position !== false ? ($position + 1) . 'e' : '-';
                }

                // 🔹 Décision du Conseil des Enseignants
                $felicitation = false;
                $encouragement = false;
                $tableauHonneur = false;
                $avertissement = false;
                
                if ($moyenneGenerale !== null && $conduiteSur20 > 0) {
                    if ($moyenneGenerale >= 16 && $conduiteSur20 >= 14) {
                        $felicitation = true;
                    } elseif ($moyenneGenerale >= 14 && $moyenneGenerale < 16 && $conduiteSur20 >= 12) {
                        $encouragement = true;
                    } elseif ($moyenneGenerale >= 12 && $moyenneGenerale < 14 && $conduiteSur20 >= 10) {
                        $tableauHonneur = true;
                    } elseif (($conduiteSur20 < 10 || $moyenneGenerale < 10) && ($moyenneGenerale < 8 || $conduiteSur20 < 8)) {
                        $avertissement = true;
                    }
                }

                // Formatage des résultats pour l'affichage
                $formatNumber = function($value) {
                    if ($value === null || $value === 0) {
                        return '0,00';
                    }
                    return number_format($value, 2, ',', '');
                };

                // Générer le QR code en base64
                $qrCode = "";
                
                // 🔹 Rendu PDF
                $pdf = Pdf::loadView('censeur.classes.notes.bulletin_pdf', [
                    'qrCode'   => $qrCode,
                    'student' => $student,
                    'classe' => $classe,
                    'bulletin' => $bulletin,
                    'trimestre' => $trimestre,
                    'moyenneGenerale' => $formatNumber($moyenneGenerale),
                    'moyenneLitteraire' => $formatNumber($moyenneLitteraire),
                    'moyenneScientifique' => $formatNumber($moyenneScientifique),
                    'moyenneAutres' => $formatNumber($moyenneAutresMatières),
                    'appreciationGenerale' => $appreciationGenerale,
                    'conduite' => $formatNumber($conduiteSur20),
                    'appreciationConduite' => $conduiteAppreciation,
                    'rang' => $rang,
                    'plusForte' => $formatNumber($plusForte),
                    'plusFaible' => $formatNumber($plusFaible),
                    'moyClasse' => $formatNumber($moyClasse),
                    'felicitation' => $felicitation,
                    'encouragement' => $encouragement,
                    'tableauHonneur' => $tableauHonneur,
                    'avertissement' => $avertissement,
                    'totalMoyCoeff' => $formatNumber($totalMoyCoeff),
                    'totalCoeff' => $totalCoeff,
                    'activeYear' => $activeYear,
                ])->setPaper('a4', 'portrait');
                    
                return $pdf->download("Bulletin_{$student->last_name}_{$student->first_name}_T{$trimestre}.pdf");

            } catch (\Exception $e) {
                // Log::error('Erreur PDF Bulletin: ' . $e->getMessage());
                // Log::error($e->getTraceAsString());
                return back()->with('error', 'Impossible de générer le PDF du bulletin: ' . $e->getMessage());
            }
        }

        public function listeEleves($classId, $trimestre) {
            try {
                // 1) Année académique active
                $activeYear = AcademicYear::where('active', true)->firstOrFail();

                // 2) Classe et étudiants validés (triés par ordre alphabétique)
                $classe = Classe::with(['students' => function ($query) use ($activeYear) {
                    $query->where('is_validated', 1)
                        ->where('academic_year_id', $activeYear->id)
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])->findOrFail($classId);

                // 3) Matières de la classe - CORRECTION: Récupérer directement les objets Subject
                $subjects = Subject::whereHas('classTeacherSubjects', function($query) use ($classId, $activeYear) {
                    $query->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id);
                })->with(['classTeacherSubjects' => function($query) use ($classId, $activeYear) {
                    $query->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id);
                }])->get();

                if ($subjects->isEmpty()) {
                    return back()->with('error', 'Aucune matière trouvée pour cette classe.');
                }

                // 4) Notes (grades)
                $grades = Grade::whereIn('student_id', $classe->students->pluck('id'))
                    ->whereIn('subject_id', $subjects->pluck('id'))
                    ->where('class_id', $classId)
                    ->where('trimestre', $trimestre)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                // 5) Conduites et punitions
                $conducts = Conduct::where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->whereIn('student_id', $classe->students->pluck('id'))
                    ->get()
                    ->keyBy('student_id');

                $punishments = Punishment::where('academic_year_id', $activeYear->id)
                    ->whereIn('student_id', $classe->students->pluck('id'))
                    ->selectRaw('student_id, SUM(hours) as total_hours')
                    ->groupBy('student_id')
                    ->get()
                    ->keyBy('student_id');

                // 6) Calcul de la conduite ajustée
                $conductData = [];
                foreach ($classe->students as $student) {
                    $studentId = $student->id;
                    $conduct = $conducts[$studentId]->grade ?? 0;
                    $punishHours = $punishments[$studentId]->total_hours ?? 0;
                    $conductFinal = max(0, $conduct - ($punishHours / 2));
                    $conductData[$studentId] = round($conductFinal, 2);
                }

                // 7) Préparer les notes avec la logique EXACTE (VERSION 2)
                $gradesData = [];
                foreach ($classe->students as $student) {
                    $studentId = $student->id;
                    
                    foreach ($subjects as $subject) {
                        $subjectId = $subject->id;
                        
                        $studentGrades = $grades->where('student_id', $studentId)
                            ->where('subject_id', $subjectId);

                        // Récupérer toutes les notes d'interrogations
                        $interros = $studentGrades->where('type', 'interrogation')
                            ->sortBy('sequence')
                            ->pluck('value')
                            ->filter(fn($v) => $v !== null) 
                            ->values()
                            ->toArray();

                        // Récupérer les 2 notes de devoir
                        $devoir1 = $studentGrades->where('type', 'devoir')
                            ->where('sequence', 1)
                            ->first()->value ?? null;
                        $devoir2 = $studentGrades->where('type', 'devoir')
                            ->where('sequence', 2)
                            ->first()->value ?? null;

                        // 1. Calcul de la moyenne des interrogations
                        $moyenneInterro = !empty($interros) ? 
                            round(array_sum($interros) / count($interros), 2) : null;

                        // 2. Calcul de la moyenne matière EXACTE (VERSION 2)
                        $moyenneMatiere = null;
                        $notesPourMoyenne = [];
                        
                        // Ajouter la moyenne d'interro si elle existe
                        if ($moyenneInterro !== null) {
                            $notesPourMoyenne[] = $moyenneInterro;
                        }
                        
                        // Ajouter les notes de devoir si elles existent
                        if ($devoir1 !== null) {
                            $notesPourMoyenne[] = $devoir1;
                        }
                        if ($devoir2 !== null) {
                            $notesPourMoyenne[] = $devoir2;
                        }
                        
                        // Calculer la moyenne si on a au moins une note
                        if (!empty($notesPourMoyenne)) {
                            $moyenneMatiere = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                        }

                        // 3. Moyenne coefficientée
                        // Récupérer le coefficient depuis ClassTeacherSubject
                        $coefRecord = $subject->classTeacherSubjects->first();
                        $coef = $coefRecord->coefficient ?? 1;
                        
                        $moyenneCoef = $moyenneMatiere !== null ? round($moyenneMatiere * $coef, 2) : null;
                        
                        $gradesData[$studentId][$subjectId] = [
                            'interros' => $interros,
                            'devoir1' => $devoir1,
                            'devoir2' => $devoir2,
                            'moyenneInterro' => $moyenneInterro,
                            'moyenneMatiere' => $moyenneMatiere,
                            'coef' => $coef,
                            'moyenneCoef' => $moyenneCoef,
                            'subject_name' => $subject->name,
                        ];
                    }
                }

                // 8) Calcul des moyennes générales EXACTE
                $moyennesGenerales = [];
                
                foreach ($classe->students as $student) {
                    $studentId = $student->id;
                    $totalPoints = 0;
                    $totalCoef = 0;

                    // Ajouter les points des matières
                    foreach ($gradesData[$studentId] ?? [] as $subjectId => $matiere) {
                        if (isset($matiere['moyenneCoef']) && $matiere['moyenneCoef'] !== null) {
                            $totalPoints += $matiere['moyenneCoef'];
                            $totalCoef += $matiere['coef'];
                        }
                    }

                    // Ajouter la conduite (coefficient 1)
                    $conduite = $conductData[$studentId] ?? 0;
                    if ($conduite > 0) {
                        $totalPoints += $conduite * 1;
                        $totalCoef += 1;
                    }

                    // Calcul de la moyenne générale
                    $moyenneGenerale = ($totalCoef > 0) ? 
                        round($totalPoints / $totalCoef, 2) : 0;

                    $gradesData[$studentId]['moyenne_generale'] = $moyenneGenerale;
                    $gradesData[$studentId]['conduite_finale'] = $conduite;
                    
                    // Garder pour le calcul du rang
                    if ($totalCoef > 0) {
                        $moyennesGenerales[$studentId] = $moyenneGenerale;
                    } else {
                        $moyennesGenerales[$studentId] = 0;
                    }
                }

                // 9) Calcul des rangs (tri décroissant des moyennes)
                arsort($moyennesGenerales);
                $rang = 1;
                foreach ($moyennesGenerales as $studentId => $moyenne) {
                    $gradesData[$studentId]['rang_general'] = $rang++;
                }

                // 10) Ajouter les coefficients aux objets Subject pour la vue
                foreach ($subjects as $subject) {
                    $coefRecord = $subject->classTeacherSubjects->first();
                    $subject->coefficient = $coefRecord->coefficient ?? 1;
                }

                // 11) Retour à la vue
                return view('censeur.classes.notes.liste_eleves', compact(
                    'classe', 
                    'subjects', 
                    'gradesData', 
                    'conductData', 
                    'trimestre', 
                    'activeYear'
                ));
                
            } catch (\Exception $e) {
                return back()->with('error', 'Erreur : ' . $e->getMessage());
            }
        }
        

        public function trimestres($id)  {
            $activeYear = AcademicYear::where('active', true)->first();

            $classe = Classe::findOrFail($id);

            $trimestres = [1, 2, 3];

            // Récupération des matières via ClassTeacherSubject (avec le nom et le coeff)
            // Utiliser with('subject') pour avoir le nom de la matière
            $matieresPivot = ClassTeacherSubject::with('subject')
                ->where('class_id', $id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->get();

            // On mappe pour exposer les champs utiles dans la vue
            $matieres = $matieresPivot->map(function ($item) {
                $subject = $item->subject;
                if (!$subject) return null;

                // On enrichit l'objet subject avec les infos du pivot
                $subject->coefficient  = $item->coefficient ?? 1;
                // subject_id : l'ID réel de la matière (utile dans le select de la vue)
                $subject->subject_id   = $subject->id;

                return $subject;
            })->filter()->values(); // supprime les null et réindexe

            $coef = $matieresPivot->first();

            return view('censeur.classes.notes.trimestres', compact(
                'classe',
                'trimestres',
                'matieres',
                'coef',
                'activeYear'
            ));
        }

        // Gérer les permissions de saisie des notes pour une classe
        public function permissions($classId){
            $classe = Classe::findOrFail($classId);

            // On garantit l’existence des permissions pour les 3 trimestres
            for ($i = 1; $i <= 3; $i++) {

                $permission = NotePermission::where('class_id', $classId)
                    ->where('trimestre', $i)
                    ->first();

                if (!$permission) {
                    $permission = new NotePermission();
                    $permission->class_id = $classId;
                    $permission->trimestre = $i;
                    $permission->is_open = false;
                    $permission->open_at = null;
                    $permission->close_at = null;
                    $permission->save();
                }

                // 🚦 Vérifier auto-fermeture
                if ($permission->closes_at && now()->greaterThan($permission->closes_at)) {
                    if ($permission->is_open) {
                        $permission->is_open = false;
                        $permission->save();
                    }
                }
            }

            $permissions = NotePermission::where('class_id', $classId)->get();

            return view('censeur.permissions.index', compact('classe', 'permissions'));
        }

        public function setDates(Request $request, $classId, $trimestre){
            $request->validate([
                'open_at' => 'required|date',
                'close_at' => 'required|date|after:open_at',
            ]);

            $permission = NotePermission::where('class_id', $classId)
                ->where('trimestre', $trimestre)
                ->firstOrFail();

            $permission->update([
                'open_at' => $request->open_at,
                'close_at' => $request->close_at,
                'is_open' => true,
            ]);

            return back()->with('success', 'Période définie avec succès.');
        }
        // Toggle autorisation/revocation
        public function toggle(Request $request, $classId, $trimestre){
            $permission = NotePermission::where('class_id', $classId)
                ->where('trimestre', $trimestre)
                ->firstOrFail();

            // Si l’utilisateur met des dates → on les prend
            if ($request->filled('opens_at') && $request->filled('closes_at')) {

                $request->validate([
                    'opens_at' => 'required|date',
                    'closes_at' => 'required|date|after:opens_at',
                ]);

                $permission->opens_at = $request->opens_at;
                $permission->closes_at = $request->closes_at;

                // Activer automatiquement si on est dans la période
                $now = now();
                $permission->is_open = $now->between($permission->opens_at, $permission->closes_at);

            } 
            else {

                // Mode manuel ON/OFF
                if($permission->is_open == true){
                    $permission->open_at = null;
                    $permission->close_at = null;
                }

                else {
                    $permission->open_at = now();
                    $permission->close_at = now()->addDay(7);;
                }
                $permission->is_open = !$permission->is_open;
            }

            $permission->save();

            return back()->with('success', 'Permission mise à jour avec succès.');
        }

        public function bulletin($classId, $studentId, $trimestre){
            try {
                $activeYear = AcademicYear::where('active', true)->firstOrFail();

                // Récupération de l'élève
                $student = Student::findOrFail($studentId);

                // Récupération des matières enseignées dans cette classe via la table pivot
                $subjects = Subject::whereHas('classTeacherSubjects', function($query) use ($classId, $activeYear) {
                    $query->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id);
                })
                ->with(['grades' => function($q) use ($studentId, $activeYear, $trimestre) {
                    $q->where('student_id', $studentId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre);
                }])
                ->get();

                // Récupération de la classe
                $classe = Classe::with(['students' => function ($query) use ($activeYear) {
                    $query->where('is_validated', 1)
                        ->where('academic_year_id', $activeYear->id)
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])->findOrFail($classId);

                // Conduite et punitions
                $conduct = Conduct::where('student_id', $student->id)
                    ->where('trimestre', $trimestre)
                    ->where('academic_year_id', $activeYear->id)
                    ->value('grade') ?? 0;

                $punishHours = Punishment::where('student_id', $student->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->sum('hours');

                $conduiteFinale = max(0, $conduct - ($punishHours / 2));

                $bulletin = [];
                $totalMoyCoeff = 0;
                $totalCoeff = 0;
                $matieresAvecNotes = 0;

                foreach ($subjects as $subject) {
                    // Récupérer le coefficient depuis la table pivot
                    $pivot = $subject->classTeacherSubjects
                        ->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id)
                        ->first();
                    
                    $coef = $pivot->coefficient ?? $subject->coefficient ?? 1;

                    // Récupérer les notes depuis la relation grades chargée avec with
                    $subjectGrades = $subject->grades;

                    // Récupérer toutes les notes d'interrogations (1 à 5)
                    $notesInterro = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $note = $subjectGrades->first(function($n) use ($i) {
                            return $n->type == 'interrogation' && $n->sequence == $i;
                        });
                        $notesInterro[$i] = $note->value ?? null;
                    }

                    // Récupérer les devoirs (1 et 2)
                    $notesDevoir = [];
                    for ($i = 1; $i <= 2; $i++) {
                        $note = $subjectGrades->first(function($n) use ($i) {
                            return $n->type == 'devoir' && $n->sequence == $i;
                        });
                        $notesDevoir[$i] = $note->value ?? null;
                    }

                    // CORRECTION: Calcul de la moyenne (moyenne interros + notes de devoir) / (1 + nombre de devoirs)
                    $interroValues = collect($notesInterro)->filter();
                    $devoirValues = collect($notesDevoir)->filter();
                    
                    $moyenne = null;
                    $moyenneInterro = null;

                    if ($interroValues->isNotEmpty() || $devoirValues->isNotEmpty()) {
                        // Moyenne des interrogations
                        if ($interroValues->isNotEmpty()) {
                            $moyenneInterro = round($interroValues->avg(), 2);
                        }
                        
                        // Calcul final: (moyenne interro + notes de devoir) / (1 + nombre de devoirs)
                        $notesPourMoyenne = [];
                        
                        if ($moyenneInterro !== null) {
                            $notesPourMoyenne[] = $moyenneInterro;
                        }
                        
                        foreach ($devoirValues as $devoir) {
                            if ($devoir !== null) {
                                $notesPourMoyenne[] = $devoir;
                            }
                        }
                        
                        if (!empty($notesPourMoyenne)) {
                            $moyenne = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                        }
                    }

                    // Si moyenne est null (pas de notes), on ne prend pas en compte
                    if ($moyenne === null) {
                        $moyCoeff = 0;
                        $appreciation = '-';
                    } else {
                        $moyCoeff = round($moyenne * $coef, 2);
                        $matieresAvecNotes++;
                        
                        // Appréciation par matière
                        if ($moyenne > 16) $appreciation = 'Très Bien';
                        elseif ($moyenne >= 14) $appreciation = 'Bien';
                        elseif ($moyenne >= 12) $appreciation = 'Assez Bien';
                        elseif ($moyenne >= 10) $appreciation = 'Passable';
                        elseif ($moyenne >= 8) $appreciation = 'Insuffisant';
                        elseif ($moyenne >= 6) $appreciation = 'Faible';
                        elseif ($moyenne >= 4) $appreciation = 'Médiocre';
                        else $appreciation = 'Très Faible';
                    }

                    $bulletin[] = [
                        'subject' => $subject->name,
                        'coef' => $coef,
                        'interros' => $notesInterro,
                        'devoirs' => $notesDevoir,
                        'moyenneInterro' => $moyenneInterro,
                        'moyenne' => $moyenne,
                        'moyCoeff' => $moyCoeff,
                        'appreciation' => $appreciation,
                    ];

                    // Ne compter que les matières qui ont une moyenne (note non null)
                    if ($moyenne !== null) {
                        $totalCoeff += $coef;
                        $totalMoyCoeff += $moyCoeff;
                    }
                }
                
                // CORRECTION: Calcul de la moyenne générale avec conduite comme matière avec coefficient 1
                $moyenneMatieres = null;
                $moyenneGenerale = null;
                
                if ($totalCoeff > 0) {
                    // Moyenne pondérée des matières (sans conduite)
                    $moyenneMatieres = round($totalMoyCoeff / $totalCoeff, 2);
                    
                    // Ajouter la conduite comme matière avec coefficient 1
                    $totalCoeffAvecConduite = $totalCoeff + 1;
                    $totalPointsAvecConduite = $totalMoyCoeff + ($conduiteFinale * 1);
                    
                    // Moyenne générale avec conduite incluse
                    $moyenneGenerale = round($totalPointsAvecConduite / $totalCoeffAvecConduite, 2);
                }

                // Appréciation générale basée sur la moyenne générale
                $appreciationGenerale = '-';
                if ($moyenneGenerale !== null) {
                    if ($moyenneGenerale > 16) $appreciationGenerale = 'Très Bien';
                    elseif ($moyenneGenerale >= 14) $appreciationGenerale = 'Bien';
                    elseif ($moyenneGenerale >= 12) $appreciationGenerale = 'Assez Bien';
                    elseif ($moyenneGenerale >= 10) $appreciationGenerale = 'Passable';
                    elseif ($moyenneGenerale >= 8) $appreciationGenerale = 'Insuffisant';
                    elseif ($moyenneGenerale >= 6) $appreciationGenerale = 'Faible';
                    elseif ($moyenneGenerale >= 4) $appreciationGenerale = 'Médiocre';
                    else $appreciationGenerale = 'Très Faible';
                }

                // Appréciation de la conduite
                $appreciationConduite = '-';
                if ($conduiteFinale >= 14) $appreciationConduite = 'Excellente';
                elseif ($conduiteFinale >= 12) $appreciationConduite = 'Très Bien';
                elseif ($conduiteFinale >= 10) $appreciationConduite = 'Bien';
                elseif ($conduiteFinale >= 8) $appreciationConduite = 'Passable';
                elseif ($conduiteFinale >= 6) $appreciationConduite = 'Insuffisante';
                elseif ($conduiteFinale >= 4) $appreciationConduite = 'Médiocre';
                else $appreciationConduite = 'Très Faible';

                // Calcul du rang général avec la même logique
                $classStudents = Student::where('class_id', $classId)
                                        ->where('is_validated', 1)
                                        ->where('academic_year_id', $activeYear->id)
                                        ->get();
                $classMoyennes = [];
                
                // Fonction pour calculer la moyenne d'un élève (avec la même logique)
                $calculateStudentAverage = function($studentId) use ($subjects, $classId, $activeYear, $trimestre) {
                    $totalMoyCoeff = 0;
                    $totalCoeff = 0;
                    
                    foreach ($subjects as $subject) {
                        // Récupérer le coefficient
                        $pivot = $subject->classTeacherSubjects
                            ->where('class_id', $classId)
                            ->where('academic_year_id', $activeYear->id)
                            ->first();
                        
                        $coef = $pivot->coefficient ?? $subject->coefficient ?? 1;
                        
                        // Récupérer les notes de cet élève pour cette matière
                        $subjectGrades = Grade::where('student_id', $studentId)
                            ->where('subject_id', $subject->id)
                            ->where('academic_year_id', $activeYear->id)
                            ->where('trimestre', $trimestre)
                            ->get();
                        
                        if ($subjectGrades->isNotEmpty()) {
                            // Calculer la moyenne avec la même logique
                            $notesInterro = [];
                            $notesDevoir = [];
                            
                            foreach ($subjectGrades as $grade) {
                                if ($grade->type == 'interrogation' && $grade->sequence <= 5) {
                                    $notesInterro[$grade->sequence] = $grade->value;
                                } elseif ($grade->type == 'devoir' && $grade->sequence <= 2) {
                                    $notesDevoir[$grade->sequence] = $grade->value;
                                }
                            }
                            
                            $interroValues = collect($notesInterro)->filter();
                            $devoirValues = collect($notesDevoir)->filter();
                            
                            if ($interroValues->isNotEmpty() || $devoirValues->isNotEmpty()) {
                                $moyenneInterro = $interroValues->isNotEmpty() ? $interroValues->avg() : null;
                                
                                $notesPourMoyenne = [];
                                if ($moyenneInterro !== null) {
                                    $notesPourMoyenne[] = $moyenneInterro;
                                }
                                
                                foreach ($devoirValues as $devoir) {
                                    if ($devoir !== null) {
                                        $notesPourMoyenne[] = $devoir;
                                    }
                                }
                                
                                if (!empty($notesPourMoyenne)) {
                                    $moyenne = array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                                    $totalMoyCoeff += $moyenne * $coef;
                                    $totalCoeff += $coef;
                                }
                            }
                        }
                    }
                    
                    // Conduite pour cet élève
                    $conduct = Conduct::where('student_id', $studentId)
                        ->where('trimestre', $trimestre)
                        ->where('academic_year_id', $activeYear->id)
                        ->value('grade') ?? 0;
                    
                    $punishHours = Punishment::where('student_id', $studentId)
                        ->where('academic_year_id', $activeYear->id)
                        ->sum('hours');
                    
                    $conductFinal = max(0, $conduct - ($punishHours / 2));
                    
                    // Ajouter la conduite
                    if ($totalCoeff > 0 || $conductFinal > 0) {
                        $totalCoeff += 1;
                        $totalMoyCoeff += ($conductFinal * 1);
                        return $totalMoyCoeff / $totalCoeff;
                    }
                    
                    return null;
                };
                
                // Calculer les moyennes de tous les élèves
                foreach ($classStudents as $st) {
                    $moy = $calculateStudentAverage($st->id);
                    if ($moy !== null) {
                        $classMoyennes[$st->id] = $moy;
                    }
                }
                
                // Rang de l'élève
                $rang = null;
                if (!empty($classMoyennes) && isset($classMoyennes[$student->id])) {
                    arsort($classMoyennes);
                    $positions = array_keys($classMoyennes);
                    $rang = array_search($student->id, $positions) + 1;
                }

                // Statistiques de classe
                if (!empty($classMoyennes)) {
                    $plusForte = round(max($classMoyennes), 2);
                    $plusFaible = round(min($classMoyennes), 2);
                    $moyClasse = round(array_sum($classMoyennes) / count($classMoyennes), 2);
                } else {
                    $plusForte = $plusFaible = $moyClasse = '-';
                }

                return view('censeur.classes.notes.bulletin', compact(
                    'student', 'classe', 'bulletin', 'moyenneGenerale', 'moyenneMatieres',
                    'appreciationGenerale', 'conduiteFinale', 'appreciationConduite', 'rang', 'trimestre', 'activeYear',
                    'matieresAvecNotes', 'totalCoeff', 'totalMoyCoeff', 'plusForte', 'plusFaible', 'moyClasse'
                ));

            } catch (\Exception $e) {
                return back()->with('error', 'Erreur lors du chargement du bulletin : ' . $e->getMessage());
            }
        }

        // Petite fonction utilitaire
        private function calculMoyenne($studentId, $classId, $trimestre, $yearId){
            $student = Student::with(['grades' => fn($q) => $q
                ->where('academic_year_id', $yearId)
                ->where('trimestre', $trimestre)
            ])->find($studentId);

            if (!$student) return null;

            $subjects = Subject::whereHas('classes', fn($q) => $q->where('classes.id', $classId))->get();

            $totalMoyCoeff = 0;
            $totalCoeff = 0;

            foreach ($subjects as $subject) {
                $coef = $subject->coefficient ?? 1;
                $notes = $student->grades->where('subject_id', $subject->id)->pluck('value');
                if ($notes->isNotEmpty()) {
                    $moy = $notes->avg();
                    $totalMoyCoeff += $moy * $coef;
                    $totalCoeff += $coef;
                }
            }

            return $totalCoeff ? round($totalMoyCoeff / $totalCoeff, 2) : null;
        }

        public function matiere($classe, $t){
            // Récupère l'année scolaire active
            $activeYear = AcademicYear::where('active', true)->first();

            if (!$activeYear) {
                return view('censeur.classes.index', [
                    'classes' => collect(),
                    'activeYear' => null,
                    'error' => "Aucune année scolaire active n’a été trouvée."
                ]);
            }

            // Vérifie si la classe existe
            $classe = Classe::findOrFail($classe);

            // Récupère les relations "matière - enseignant" via class_teacher_subject
            // On filtre par l'année académique active et la classe
            $classSubjects = ClassTeacherSubject::with(['subject', 'teacher'])
                ->where('class_id', $classe->id)
                ->where('academic_year_id', $activeYear->id)
                ->get();

            $subjects = $classSubjects->map(function ($item) {
                $subject = $item->subject;
                $subject->teacher_name = $item->teacher->name ?? 'Non assigné';
                $subject->coefficient = $item->coefficient; // ✅ On ajoute le coefficient du pivot
                return $subject;
            });


            // 5️⃣ On récupère le trimestre
            $trimestre = $t;

            // 6️⃣ On renvoie la même structure de variables que ta vue attend
            return view('censeur.classes.subject', compact('subjects', 'activeYear', 'classe', 'trimestre'));
        }

        public function notes_trimestre($classId, $trimestre, $subjectId){
            $activeYear = AcademicYear::where('active', true)->first();
            if (!$activeYear) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }

            // Vérifier que la matière existe bien dans cette classe et cette année
            $subject = ClassTeacherSubject::where('subject_id', $subjectId)
                ->where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->first();

            if (!$subject) {
                return back()->with('error', 'La matière sélectionnée n’existe pas pour cette classe ou cette année académique.');
            }

            // Charger la classe et uniquement les notes de cette matière + trimestre
            $classe = Classe::with(['students.grades' => function ($q) use ($activeYear, $trimestre, $subjectId) {
                $q->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->where('subject_id', $subjectId);
            }])->findOrFail($classId);

            // Vérifier si des notes existent
            $hasNotes = $classe->students->flatMap->grades->isNotEmpty();

            return view('censeur.notes.notes_trimestre', compact('classe', 'subject', 'activeYear', 'trimestre', 'hasNotes'));
        }

        public function viewEvaluationNotes($classId, $subjectId, $type, $sequence, $trimestre) {
            try {

                // 1. Récupérer l'année académique active
                $activeYear = AcademicYear::where('active', true)->firstOrFail();

                // 2. Récupérer la classe avec les étudiants validés
                $classe = Classe::with(['students' => function ($query) use ($activeYear) {
                    $query->where('is_validated', 1)
                        ->where('academic_year_id', $activeYear->id)
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])->findOrFail($classId);

                // 3. Récupérer la matière (ClassTeacherSubject) - avec vérification flexible
                $subjectPivot = ClassTeacherSubject::where('subject_id', $subjectId)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->first();
                
                // Si pas trouvé, chercher sans la condition d'année académique
                if (!$subjectPivot) {
                    $subjectPivot = ClassTeacherSubject::where('subject_id', $subjectId)
                        ->where('class_id', $classId)
                        ->first();
                }
                
                // Si toujours pas trouvé, retourner une erreur
                if (!$subjectPivot) {
                    throw new \Exception("La matière n'est pas associée à cette classe.");
                }

                // 4. Récupérer le nom de la matière
                $subject = Subject::findOrFail($subjectId);
                
                // 5. Récupérer l'enseignant si disponible
                $teacher = null;
                if ($subjectPivot->teacher_id) {
                    $teacher = User::find($subjectPivot->teacher_id);
                }
                
                // 6. Récupérer toutes les notes pour cette évaluation spécifique
                $gradesData = [];
                
                
                foreach ($classe->students as $student) {
                    $grade = Grade::where('student_id', $student->id)
                        ->where('subject_id', $subjectId)
                        ->where('academic_year_id', $activeYear->id)
                        ->where('trimestre', $trimestre)
                        ->where('type', $type)
                        ->where('sequence', $sequence)
                        ->first();

                    $gradesData[$student->id] = [
                        'note' => $grade ? $grade->value : null,
                        'created_at' => $grade ? $grade->created_at : null,
                        'updated_at' => $grade ? $grade->updated_at : null,
                        'has_note' => $grade !== null,
                    ];
                }

                // 7. Calculer les statistiques
                $notes = collect($gradesData)->pluck('note')->filter();
                $stats = [
                    'total_etudiants' => $classe->students->count(),
                    'etudiants_notes' => $notes->count(),
                    'etudiants_sans_notes' => $classe->students->count() - $notes->count(),
                    'moyenne_generale' => $notes->isNotEmpty() ? round($notes->avg(), 2) : null,
                    'note_max' => $notes->isNotEmpty() ? $notes->max() : null,
                    'note_min' => $notes->isNotEmpty() ? $notes->min() : null,
                ];

                // 8. Déterminer le type d'évaluation en français
                $type_fr = $type == 'interrogation' ? 'Interrogation' : 'Devoir';
                $titre_page = "{$type_fr} {$sequence} - {$subject->name}";

                return view('censeur.notes.evaluation_notes', compact(
                    'classe',
                    'subject',
                    'subjectPivot',
                    'teacher',
                    'gradesData',
                    'activeYear',
                    'trimestre',
                    'type',
                    'sequence',
                    'type_fr',
                    'titre_page',
                    'stats'
                ));

            } catch (\Exception $e) {
                //\Log::error('Erreur consultation notes évaluation: ' . $e->getMessage());
                return back()->with('error', 'Impossible de charger les notes de l\'évaluation : ' . $e->getMessage());
            }
        }

        public function showClassNote($classId, $trimestre, $subjectId){
            // 1 Année académique active
            $activeYear = AcademicYear::where('active', true)->first();
            if (!$activeYear) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }
            
            // 2 Récupérer la classe et ses étudiants valides pour l'année active
            $classe = Classe::with(['students' => function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id)
                ->where('is_validated', 1)
                ->orderBy('last_name')
                ->orderBy('first_name');
            }])->find($classId);

            if (!$classe) {
                return back()->with('error', "Classe introuvable.");
            }
            
            // 3 Récupérer la matière concernée (ClassTeacherSubject)
            $subjectPivot = ClassTeacherSubject::where('subject_id', $subjectId)
                ->where('class_id', $classId)
                ->first();

            if (!$subjectPivot) {
                return back()->with('error', "Matière introuvable.");
            }

            // Récupérer le nom de la matière depuis le modèle Subject
            $subject = Subject::find($subjectId);
            if (!$subject) {
                return back()->with('error', "Matière introuvable.");
            }

            // 4 Préparation des données de notes avec nouvelle logique
            $gradesData = [];
            $classeMoyennes = []; // pour calcul du rang

            foreach ($classe->students as $student) {
                // Récupérer les notes de cet élève pour cette matière
                $grades = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();

                $interros = [];
                $devoirs = [];

                foreach ($grades as $grade) {
                    if ($grade->type === 'interrogation') {
                        $interros[$grade->sequence] = $grade->value;
                    } elseif ($grade->type === 'devoir') {
                        $devoirs[$grade->sequence] = $grade->value;
                    }
                }

                ksort($interros);
                ksort($devoirs);

                // CORRECTION: Calcul avec nouvelle logique
                $moyenneInterro = count($interros) > 0 
                    ? round(array_sum($interros) / count($interros), 2) 
                    : null;
                
                $coef = $subjectPivot->coefficient ?? $subject->coefficient ?? 1;

                $moyenne = null;
                $moyenneMat = null;

                if ($moyenneInterro !== null || count($devoirs) > 0) {
                    $notesPourMoyenne = [];
                    
                    // Moyenne des interrogations
                    if ($moyenneInterro !== null) {
                        $notesPourMoyenne[] = $moyenneInterro;
                    }
                    
                    // Ajouter chaque devoir individuellement
                    foreach ($devoirs as $note) {
                        if ($note !== null) {
                            $notesPourMoyenne[] = $note;
                        }
                    }
                    
                    if (!empty($notesPourMoyenne)) {
                        $moyenne = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                        $moyenneMat = round($moyenne * $coef, 2);
                    }
                }

                $gradesData[$student->id][$subjectId] = [
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenneInterro' => $moyenneInterro,
                    'moyenne' => $moyenne,
                    'coef' => $coef,
                    'moyenneMat' => $moyenneMat,
                    'rang' => null,
                ];

                if ($moyenne !== null) {
                    $classeMoyennes[$student->id] = $moyenne;
                }
            }

            // 5 Calcul des rangs
            if (!empty($classeMoyennes)) {
                // Trier du plus grand au plus petit
                arsort($classeMoyennes);

                $rank = 1;
                $previousMoyenne = null;
                $sameRankCount = 0;

                foreach ($classeMoyennes as $studentId => $moyenne) {
                    if ($moyenne === $previousMoyenne) {
                        // même moyenne → même rang
                        $sameRankCount++;
                    } else {
                        // nouvelle moyenne → rang suivant
                        $rank += $sameRankCount;
                        $sameRankCount = 1;
                    }

                    $gradesData[$studentId][$subjectId]['rang'] = $rank;
                    $previousMoyenne = $moyenne;
                }
            }

            // 6 Envoi à la vue
            return view('censeur.notes.class_notes', [
                'classe' => $classe,
                'subjectPivot' => $subjectPivot, // Pour le coefficient
                'subject' => $subject, // Pour le nom et autres infos
                'gradesData' => $gradesData,
                'activeYear' => $activeYear,
                'trimestre' => $trimestre,
            ]);
        }

        public function setCoefficient(Request $request, $classeId, $subjectId){
            $request->validate([
                'coefficient' => 'required|integer|min:1|max:10',
            ]);

            // Récupérer l'année académique active
            $academicYear = AcademicYear::where('active', 1)->firstOrFail();

            // Trouver la ligne correspondante dans class_subject_teacher
            $record = DB::table('class_teacher_subject')
                ->where('class_id', $classeId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYear->id)
                ->first();

            if (!$record) {
                return back()->with('error', 'Association classe matière non trouvée pour cette année académique.');
            }

            // Mettre à jour le coefficient
            DB::table('class_teacher_subject')
                ->where('class_id', $classeId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYear->id)
                ->update(['coefficient' => $request->coefficient]);

            return back()->with('success', 'Coefficient mis à jour avec succès.');
        }


        public function exportNotesPDF($classId, $trimestre, $subjectId){

            // 1 Récupération de l'année académique active
            $activeYear = AcademicYear::where('active', true)->first();
            if (!$activeYear) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }

            // 2 Récupération de la classe et des élèves
            $classe = Classe::with(['students' => function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id)
                ->where('is_validated', 1)
                ->orderBy('last_name')
                ->orderBy('first_name');
            }])->find($classId);

            if (!$classe) {
                return back()->with('error', "Classe introuvable.");
            }

            // 3 Matière concernée (ClassTeacherSubject pour le coefficient)
            $subjectPivot = ClassTeacherSubject::where('subject_id', $subjectId)
                ->where('class_id', $classId)
                ->first();

            if (!$subjectPivot) {
                return back()->with('error', "Matière introuvable.");
            }

            // Récupérer le modèle Subject pour le nom
            $subject = Subject::find($subjectId);
            if (!$subject) {
                return back()->with('error', "Matière introuvable.");
            }

            // 4 Récupération et calcul des notes avec la nouvelle logique
            $gradesData = [];
            $classeMoyennes = [];

            foreach ($classe->students as $student) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();

                $interros = [];
                $devoirs = [];

                foreach ($grades as $grade) {
                    if ($grade->type === 'interrogation') {
                        $interros[$grade->sequence] = $grade->value;
                    } elseif ($grade->type === 'devoir') {
                        $devoirs[$grade->sequence] = $grade->value;
                    }
                }

                ksort($interros);
                ksort($devoirs);

                // Calcul avec nouvelle logique
                $moyenneInterro = count($interros) > 0
                    ? round(array_sum($interros) / count($interros), 2)
                    : null;
                
                $coef = $subjectPivot->coefficient ?? $subject->coefficient ?? 1;

                $moyenne = null;
                $moyenneMat = null;

                $notesPourMoyenne = [];

                // Moyenne des interrogations
                if ($moyenneInterro !== null) {
                    $notesPourMoyenne[] = $moyenneInterro;
                }

                // Ajouter chaque devoir individuellement
                foreach ($devoirs as $note) {
                    if ($note !== null) {
                        $notesPourMoyenne[] = $note;
                    }
                }

                if (count($notesPourMoyenne) > 0) {
                    $moyenne = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                    $moyenneMat = round($moyenne * $coef, 2);
                }

                $gradesData[$student->id][$subjectId] = [
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenneInterro' => $moyenneInterro,
                    'moyenne' => $moyenne,
                    'coef' => $coef,
                    'moyenneMat' => $moyenneMat,
                    'rang' => null,
                ];

                if ($moyenne !== null) {
                    $classeMoyennes[$student->id] = $moyenne;
                }
            }

            // 5 Calcul du rang
            if (!empty($classeMoyennes)) {
                arsort($classeMoyennes);
                $rank = 1;
                $previousMoyenne = null;
                $sameRankCount = 0;

                foreach ($classeMoyennes as $studentId => $moyenne) {
                    if ($moyenne === $previousMoyenne) {
                        $sameRankCount++;
                    } else {
                        $rank += $sameRankCount;
                        $sameRankCount = 1;
                    }

                    $gradesData[$studentId][$subjectId]['rang'] = $rank;
                    $previousMoyenne = $moyenne;
                }
            }

            // 6 Génération du PDF
            $pdf = Pdf::loadView('censeur.notes.pdf.class_notes_pdf', [
                'classe' => $classe,
                'subject' => $subject, // Modèle Subject pour le nom
                'subjectPivot' => $subjectPivot, // ClassTeacherSubject pour le coefficient
                'gradesData' => $gradesData,
                'activeYear' => $activeYear,
                'trimestre' => $trimestre,
            ])->setPaper('a4', 'landscape');

            $filename = 'Notes_' . $classe->name . '_' . $subject->name . '_T' . $trimestre . '.pdf';

            return $pdf->download($filename);
        }



        public function exportSubjectExcel(int $classId, int $trimestre, int $subjectId) {
            try {
                // ── Données de base ────────────────────────────────────────────────────
                $activeYear = AcademicYear::where('active', true)->firstOrFail();
                $classe     = Classe::findOrFail($classId);
                $subject    = Subject::findOrFail($subjectId);

                // ── Nom du fichier ─────────────────────────────────────────────────────
                $nomClasse   = str_replace([' ', '/'], '_', $classe->name);
                $nomMatiere  = str_replace([' ', '/'], '_', $subject->name);
                $fileName    = "Notes_{$nomMatiere}_{$nomClasse}_T{$trimestre}.xlsx";

                // ── Génération et téléchargement ───────────────────────────────────────
                return Excel::download(
                    new NotesSubjectExport($classe, $subject, $trimestre, $activeYear),
                    $fileName
                );

            } catch (\Exception $e) {
                return back()->with('error', 'Impossible de générer le fichier Excel : ' . $e->getMessage());
            }
        }

        public function telechargerPDF($classId, $trimestre){
            try {
                // Année académique active
                $activeYear = AcademicYear::where('active', true)->firstOrFail();

                // Classe + élèves validés
                $classe = Classe::with(['students' => function ($query) use ($activeYear) {
                    $query->where('is_validated', 1)
                        ->where('academic_year_id', $activeYear->id)
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])->findOrFail($classId);

                // CORRECTION: Récupérer les objets Subject directement
                $subjects = Subject::whereHas('classTeacherSubjects', function($query) use ($classId, $activeYear) {
                    $query->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id);
                })->with(['classTeacherSubjects' => function($query) use ($classId, $activeYear) {
                    $query->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id);
                }])->get();

                // Vérifier si des matières existent
                if ($subjects->isEmpty()) {
                    return back()->with('error', 'Aucune matière trouvée pour cette classe.');
                }

                // Notes
                $grades = Grade::whereIn('student_id', $classe->students->pluck('id'))
                    ->whereIn('subject_id', $subjects->pluck('id'))
                    ->where('class_id', $classId) // Ajouté pour cohérence
                    ->where('trimestre', $trimestre)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                // Conduites & punitions
                $conducts = Conduct::where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->whereIn('student_id', $classe->students->pluck('id'))
                    ->get()
                    ->keyBy('student_id');

                $punishments = Punishment::where('academic_year_id', $activeYear->id)
                    ->whereIn('student_id', $classe->students->pluck('id'))
                    ->selectRaw('student_id, SUM(hours) as total_hours')
                    ->groupBy('student_id')
                    ->get()
                    ->keyBy('student_id');

                // Calculs avec la MÊME logique exacte que listeEleves
                $conductData = [];
                $gradesData = [];
                $moyennesGenerales = [];

                foreach ($classe->students as $student) {
                    $studentId = $student->id;
                    
                    // Conduite ajustée
                    $conduct = $conducts[$studentId]->grade ?? 0;
                    $punishHours = $punishments[$studentId]->total_hours ?? 0;
                    $conductFinal = max(0, $conduct - ($punishHours / 2));
                    $conductData[$studentId] = round($conductFinal, 2);

                    foreach ($subjects as $subject) {
                        $studentGrades = $grades->where('student_id', $studentId)
                                                ->where('subject_id', $subject->id);

                        // Récupérer toutes les notes d'interrogations
                        $interros = $studentGrades->where('type', 'interrogation')
                                                ->sortBy('sequence')
                                                ->pluck('value')
                                                ->filter(fn($v) => $v !== null) 
                                                ->values()
                                                ->toArray();

                        // Récupérer les 2 notes de devoir
                        $devoir1 = $studentGrades->where('type', 'devoir')
                                                ->where('sequence', 1)
                                                ->first()->value ?? null;
                        $devoir2 = $studentGrades->where('type', 'devoir')
                                                ->where('sequence', 2)
                                                ->first()->value ?? null;

                        // 1. Calcul de la moyenne des interrogations
                        $moyenneInterro = !empty($interros) ? 
                            round(array_sum($interros) / count($interros), 2) : null;

                        // 2. Calcul de la moyenne matière EXACTE (même logique que listeEleves)
                        $moyenneMatiere = null;
                        $notesPourMoyenne = [];
                        
                        // Ajouter la moyenne d'interro si elle existe
                        if ($moyenneInterro !== null) {
                            $notesPourMoyenne[] = $moyenneInterro;
                        }
                        
                        // Ajouter les notes de devoir si elles existent
                        if ($devoir1 !== null) {
                            $notesPourMoyenne[] = $devoir1;
                        }
                        if ($devoir2 !== null) {
                            $notesPourMoyenne[] = $devoir2;
                        }
                        
                        // Calculer la moyenne si on a au moins une note
                        if (!empty($notesPourMoyenne)) {
                            $moyenneMatiere = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                        }

                        // 3. Moyenne coefficientée
                        // Récupérer le coefficient depuis ClassTeacherSubject
                        $coefRecord = $subject->classTeacherSubjects->first();
                        $coef = $coefRecord->coefficient ?? 1;
                        
                        $moyenneCoef = $moyenneMatiere !== null ? round($moyenneMatiere * $coef, 2) : null;
                        
                        $gradesData[$studentId][$subject->id] = [
                            'moyenneMatiere' => $moyenneMatiere,
                            'coef' => $coef,
                            'moyenneCoef' => $moyenneCoef,
                            'subject_name' => $subject->name, // Ajouté pour le PDF
                        ];
                    }
                }

                // Calcul des moyennes générales EXACTE (même logique que listeEleves)
                foreach ($classe->students as $student) {
                    $studentId = $student->id;
                    $totalPoints = 0;
                    $totalCoef = 0;

                    // Ajouter les points des matières
                    foreach ($gradesData[$studentId] ?? [] as $subjectId => $matiere) {
                        if (isset($matiere['moyenneCoef']) && $matiere['moyenneCoef'] !== null) {
                            $totalPoints += $matiere['moyenneCoef'];
                            $totalCoef += $matiere['coef'];
                        }
                    }

                    // Ajouter la conduite (coefficient 1)
                    $conduite = $conductData[$studentId] ?? 0;
                    if ($conduite > 0) {
                        $totalPoints += $conduite * 1; // coefficient 1
                        $totalCoef += 1; // coefficient de la conduite
                    }

                    // Calcul de la moyenne générale
                    $moyenneGenerale = ($totalCoef > 0) ? 
                        round($totalPoints / $totalCoef, 2) : 0;

                    $gradesData[$studentId]['moyenne_generale'] = $moyenneGenerale;
                    $gradesData[$studentId]['conduite_finale'] = $conduite;
                    
                    // Garder pour le calcul du rang
                    if ($totalCoef > 0) {
                        $moyennesGenerales[$studentId] = $moyenneGenerale;
                    } else {
                        $moyennesGenerales[$studentId] = 0;
                    }
                }

                // Calcul des rangs (tri décroissant des moyennes)
                arsort($moyennesGenerales);
                $rang = 1;
                foreach ($moyennesGenerales as $studentId => $moyenne) {
                    $gradesData[$studentId]['rang_general'] = $rang++;
                }

                // Calcul des statistiques pour le PDF
                $stats = [
                    'moyenneClasse' => 0,
                    'nombreAdmis' => 0,
                    'tauxReussite' => 0,
                    'meilleureMoyenne' => 0,
                ];

                $moyennes = array_filter(array_column($gradesData, 'moyenne_generale'));
                if (count($moyennes) > 0) {
                    $stats['moyenneClasse'] = round(array_sum($moyennes) / count($moyennes), 2);
                    $stats['nombreAdmis'] = count(array_filter($moyennes, fn($m) => $m >= 10));
                    $stats['tauxReussite'] = round(($stats['nombreAdmis'] / count($moyennes)) * 100, 1);
                    $stats['meilleureMoyenne'] = max($moyennes);
                }

                // Ajouter les coefficients aux objets Subject pour la vue PDF
                foreach ($subjects as $subject) {
                    $coefRecord = $subject->classTeacherSubjects->first();
                    $subject->coefficient = $coefRecord->coefficient ?? 1;
                }

                // Date de téléchargement
                $dateDownload = now()->format('d/m/Y à H:i');

                // Génération du PDF
                $pdf = Pdf::loadView('censeur.notes.pdf.notes_trimestre', [
                    'classe' => $classe,
                    'subjects' => $subjects,
                    'gradesData' => $gradesData,
                    'conductData' => $conductData,
                    'trimestre' => $trimestre,
                    'activeYear' => $activeYear,
                    'stats' => $stats,
                    'dateDownload' => $dateDownload,
                ])->setPaper('a4', 'landscape');

                return $pdf->download("Fiche_notes_{$classe->name}_T{$trimestre}.pdf");
            } catch (\Exception $e) {
                return back()->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
            }
        }

        public function telechargerExcel($classId, $trimestre){
            try {
                $activeYear = AcademicYear::where('active', true)->firstOrFail();
                $classe = Classe::findOrFail($classId);

                $fileName = "Fiche_notes_{$classe->name}_T{$trimestre}.xlsx";

                return Excel::download(new NotesTrimestreExport($classId, $trimestre, $activeYear->id), $fileName);
            } catch (\Exception $e) {
                return back()->with('error', 'Erreur lors de la génération du fichier Excel : ' . $e->getMessage());
            }
        } 

    private function getAppreciation($moy){
        if (is_null($moy)) return '-';
        return match (true) {
            $moy >= 16 => 'Très Bien',
            $moy >= 14 => 'Bien',
            $moy >= 12 => 'Assez Bien',
            $moy >= 10 => 'Passable',
            $moy >= 8  => 'Insuffisant',
            $moy >= 6  => 'Faible',
            $moy >= 4  => 'Médiocre',
            default => 'Nul',
        };
    }

    public function pointsDisponibles($classId, $trimestre){
        $activeYear = \App\Models\AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $classe = \App\Models\Classe::findOrFail($classId);

        // Récupération des matières et des coefficients pour cette classe
        $matieres = \App\Models\ClassTeacherSubject::with('subject')
            ->where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->get();

        // Types d’évaluations - Correction ici
        $interrogations = [1, 2, 3, 4, 5];  // Numéros de séquence
        $devoirs = [1, 2];  // Numéros de séquence

        $notesDisponibles = [];

        foreach ($matieres as $m) {
            $totalNotes = 0;
            $subjectName = $m->subject->name;

            // Interrogations - CORRECTION
            foreach ($interrogations as $seq) {
                $exists = \App\Models\Grade::where([
                    ['class_id', '=', $classId],
                    ['subject_id', '=', $m->subject_id],
                    ['academic_year_id', '=', $activeYear->id],
                    ['trimestre', '=', $trimestre],
                    ['type', '=', 'interrogation'],
                    ['sequence', '=', $seq],
                ])->exists();

                $notesDisponibles[$subjectName]["I$seq"] = $exists;
                if ($exists) $totalNotes++;
            }

            // Devoirs - CORRECTION
            foreach ($devoirs as $seq) {
                $exists = \App\Models\Grade::where([
                    ['class_id', '=', $classId],
                    ['subject_id', '=', $m->subject_id],
                    ['academic_year_id', '=', $activeYear->id],
                    ['trimestre', '=', $trimestre],
                    ['type', '=', 'devoir'],
                    ['sequence', '=', $seq],
                ])->exists();

                $notesDisponibles[$subjectName]["D$seq"] = $exists;
                if ($exists) $totalNotes++;
            }

            // Total
            $notesDisponibles[$subjectName]['total'] = $totalNotes;
        }

        return view('censeur.classes.notes.points', compact(
            'classe',
            'activeYear',
            'trimestre',
            'matieres',
            'notesDisponibles',
            'interrogations',
            'devoirs'
        ));
    }

    public function autoriserModification(Request $request){
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'trimestre' => 'required|string',
            'type' => 'required|string', // ex: I1, I2, D1, D2...
        ]);

        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // Crée une autorisation temporaire (2h)
        NoteEditPermission::create([
            'teacher_id' => $request->teacher_id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $activeYear->id,
            'trimestre' => $request->trimestre,
            'type' => $request->type,
            'expires_at' => now()->addHours(2),
        ]);

        return back()->with('success', "L'autorisation pour modifier les notes de {$request->type} a été accordée pour 2 heures.");
    }

    public function downloadAllBulletinsPdf($classId, $trimestre) {
        try {
            // Année académique active
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
            
            // Classe avec tous les élèves validés
            $classe = Classe::with(['students' => function ($query) use ($activeYear) {
                $query->where('is_validated', 1)
                    ->where('academic_year_id', $activeYear->id)
                    ->orderBy('last_name')
                    ->orderBy('first_name');
            }])->findOrFail($classId);
            
            // 🔹 Récupérer les matières une seule fois pour toutes les pages
            $subjects = Subject::whereHas('classTeacherSubjects', function($query) use ($classId, $activeYear) {
                $query->where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id);
            })->with(['classTeacherSubjects' => function($query) use ($classId, $activeYear) {
                $query->where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id);
            }])->orderBy('name')->get();
            
            // Liste des matières par catégorie
            $matieresLitteraires = ['COMMUNICATION ECRITE', 'LECTURE', 'ANGLAIS', 'HISTOIRE-GEOGRAPHIE', 'FRANÇAIS'];
            $Autrematiere = ['EDUCATION PHYSIQUE ET SPORTIVE (EPS)', 'CONDUITE'];
            $matieresScientifiques = ['MATHEMATIQUES', 'PHYSIQUE CHIMIE ET TECHNOLOGIE (PCT)', 'SCIENCE DE LA VIE ET DE LA TERRE (SVT)'];
            
            // Formatage des nombres
            $formatNumber = function($value) {
                if ($value === null || $value === 0) {
                    return '0,00';
                }
                return number_format($value, 2, ',', '');
            };
            
            // 🔹 PRÉ-CALCUL : Stocker les moyennes par matière pour tous les élèves pour calculer les rangs
            $allStudentsMoyennesParMatiere = [];
            $allStudentsData = [];
            
            // Préparer toutes les données de base pour chaque élève
            foreach ($classe->students as $student) {
                $studentId = $student->id;
                
                // Récupération des notes pour cet élève
                $grades = Grade::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();
                
                // Conduite et punitions
                $conduct = Conduct::where('student_id', $studentId)
                    ->where('trimestre', $trimestre)
                    ->where('academic_year_id', $activeYear->id)
                    ->first();
                
                $punishments = Punishment::where('student_id', $studentId)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();
                
                $punishHours = $punishments->sum('hours');
                
                // Calcul de la conduite
                $conductGrade = $conduct ? $conduct->grade : 0;
                $conductFinal = max(0, $conductGrade - ($punishHours / 2));
                $conduiteSur20 = round($conductFinal, 2);
                
                // Stocker les données de base pour cet élève
                $allStudentsData[$studentId] = [
                    'student' => $student,
                    'grades' => $grades,
                    'conduiteSur20' => $conduiteSur20,
                    'moyennesParMatiere' => []
                ];
                
                // Calculer les moyennes par matière pour cet élève
                foreach ($subjects as $subject) {
                    // Récupérer le coefficient
                    $coefRecord = $subject->classTeacherSubjects->first();
                    $coef = $coefRecord->coefficient ?? 1;
                    
                    // Récupération des notes
                    $subjectGrades = $grades->where('subject_id', $subject->id);
                    
                    // Notes d'interrogations
                    $interroNotes = $subjectGrades->where('type', 'interrogation')
                        ->sortBy('sequence')
                        ->pluck('value')
                        ->filter(fn($v) => $v !== null) 
                        ->values()
                        ->toArray();
                    
                    // Notes de devoir
                    $devoir1 = $subjectGrades->where('type', 'devoir')
                        ->where('sequence', 1)
                        ->first()->value ?? null;
                    $devoir2 = $subjectGrades->where('type', 'devoir')
                        ->where('sequence', 2)
                        ->first()->value ?? null;
                    
                    // Calcul de la moyenne des interrogations
                    $moyenneInterro = !empty($interroNotes) ? 
                        round(array_sum($interroNotes) / count($interroNotes), 2) : null;
                    
                    // Calcul de la moyenne matière
                    $moyenneMatiere = null;
                    $notesPourMoyenne = [];
                    
                    if ($moyenneInterro !== null) {
                        $notesPourMoyenne[] = $moyenneInterro;
                    }
                    
                    if ($devoir1 !== null) {
                        $notesPourMoyenne[] = $devoir1;
                    }
                    if ($devoir2 !== null) {
                        $notesPourMoyenne[] = $devoir2;
                    }
                    
                    if (!empty($notesPourMoyenne)) {
                        $moyenneMatiere = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                    }
                    
                    // Stocker la moyenne par matière pour le calcul du rang
                    if ($moyenneMatiere !== null) {
                        $allStudentsData[$studentId]['moyennesParMatiere'][$subject->id] = $moyenneMatiere;
                        $allStudentsMoyennesParMatiere[$subject->id][$studentId] = $moyenneMatiere;
                    }
                }
                
                // Ajouter la conduite comme une "matière" spéciale
                $allStudentsMoyennesParMatiere['CONDUITE'][$studentId] = $conduiteSur20;
                $allStudentsData[$studentId]['moyennesParMatiere']['CONDUITE'] = $conduiteSur20;
            }
            
            // 🔹 Calculer les rangs par matière pour tous les élèves
            $rangsParMatiere = [];
            foreach ($allStudentsMoyennesParMatiere as $subjectId => $moyennes) {
                if (!empty($moyennes)) {
                    // Trier par ordre décroissant (meilleure note en premier)
                    arsort($moyennes);
                    
                    // Assigner les rangs
                    $rang = 1;
                    $previousValue = null;
                    $sameRankCount = 0;
                    
                    foreach ($moyennes as $studentId => $value) {
                        if ($previousValue !== null && $value == $previousValue) {
                            $sameRankCount++;
                        } else {
                            $rang += $sameRankCount;
                            $sameRankCount = 1;
                        }
                        
                        $rangsParMatiere[$subjectId][$studentId] = $rang . 'e';
                        $previousValue = $value;
                    }
                }
            }
            
            // 🔹 Préparer les données pour tous les élèves
            $allBulletinsData = [];
            
            foreach ($classe->students as $student) {
                $studentId = $student->id;
                $studentData = $allStudentsData[$studentId];
                
                // 🔹 Récupération des notes pour cet élève
                $grades = $studentData['grades'];
                $conduiteSur20 = $studentData['conduiteSur20'];
                
                // 🔹 Calcul des moyennes par matière pour cet élève
                $bulletin = [];
                $totalMoyCoeff = 0;
                $totalCoeff = 0;
                $moyennesLitteraires = [];
                $moyennesScientifiques = [];
                $moyennesAutres = [];
                
                foreach ($subjects as $subject) {
                    // Récupérer le coefficient
                    $coefRecord = $subject->classTeacherSubjects->first();
                    $coef = $coefRecord->coefficient ?? 1;
                    
                    // Récupération des notes
                    $subjectGrades = $grades->where('subject_id', $subject->id);
                    
                    // Notes d'interrogations
                    $interroNotes = $subjectGrades->where('type', 'interrogation')
                        ->sortBy('sequence')
                        ->pluck('value')
                        ->filter(fn($v) => $v !== null)
                        ->values()
                        ->toArray();
                    
                    // Notes de devoir
                    $devoir1 = $subjectGrades->where('type', 'devoir')
                        ->where('sequence', 1)
                        ->first()->value ?? null;
                    $devoir2 = $subjectGrades->where('type', 'devoir')
                        ->where('sequence', 2)
                        ->first()->value ?? null;
                    
                    // Calcul de la moyenne des interrogations
                    $moyenneInterro = !empty($interroNotes) ? 
                        round(array_sum($interroNotes) / count($interroNotes), 2) : null;
                    
                    // Calcul de la moyenne matière
                    $moyenneMatiere = null;
                    $notesPourMoyenne = [];
                    
                    if ($moyenneInterro !== null) {
                        $notesPourMoyenne[] = $moyenneInterro;
                    }
                    
                    if ($devoir1 !== null) {
                        $notesPourMoyenne[] = $devoir1;
                    }
                    if ($devoir2 !== null) {
                        $notesPourMoyenne[] = $devoir2;
                    }
                    
                    if (!empty($notesPourMoyenne)) {
                        $moyenneMatiere = round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2);
                    }
                    
                    // Calculer moyenne coefficientée
                    $moyCoeff = null;
                    $appreciation = '-';
                    
                    if ($moyenneMatiere !== null) {
                        $moyCoeff = round($moyenneMatiere * $coef, 2);
                        
                        // Appréciation par matière
                        if ($moyenneMatiere > 16) $appreciation = 'Très Bien';
                        elseif ($moyenneMatiere >= 14) $appreciation = 'Bien';
                        elseif ($moyenneMatiere >= 12) $appreciation = 'Assez Bien';
                        elseif ($moyenneMatiere >= 10) $appreciation = 'Passable';
                        elseif ($moyenneMatiere >= 8) $appreciation = 'Insuffisant';
                        elseif ($moyenneMatiere >= 6) $appreciation = 'Faible';
                        elseif ($moyenneMatiere >= 4) $appreciation = 'Médiocre';
                        else $appreciation = 'Très Faible';
                        
                        // Classer par catégorie pour les moyennes par domaine
                        $nomMatiere = strtoupper($subject->name);
                        if (in_array($nomMatiere, $matieresLitteraires)) {
                            $moyennesLitteraires[] = $moyenneMatiere;
                        } elseif (in_array($nomMatiere, $matieresScientifiques)) {
                            $moyennesScientifiques[] = $moyenneMatiere;
                        } elseif (in_array($nomMatiere, $Autrematiere)) {
                            $moyennesAutres[] = $moyenneMatiere;
                        }
                    }
                    
                    // Formatage des notes pour l'affichage
                    $interrosFormatted = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $interrosFormatted[$i] = isset($interroNotes[$i-1]) ? number_format($interroNotes[$i-1], 2, ',', '') : '-';
                    }
                    
                    $devoirsFormatted = [];
                    $devoirsFormatted[1] = $devoir1 !== null ? number_format($devoir1, 2, ',', '') : '-';
                    $devoirsFormatted[2] = $devoir2 !== null ? number_format($devoir2, 2, ',', '') : '-';
                    
                    // Récupérer le rang pour cette matière
                    $rangMatiere = isset($rangsParMatiere[$subject->id][$studentId]) ? 
                        $rangsParMatiere[$subject->id][$studentId] : '-';
                    
                    $bulletin[] = [
                        'subject' => strtoupper($subject->name),
                        'coef' => $coef,
                        'interros' => $interrosFormatted,
                        'devoirs' => $devoirsFormatted,
                        'moyenneInterro' => $moyenneInterro !== null ? number_format($moyenneInterro, 2, ',', '') : '-',
                        'moyenne' => $moyenneMatiere !== null ? number_format($moyenneMatiere, 2, ',', '') : '-',
                        'moyCoeff' => $moyCoeff !== null ? number_format($moyCoeff, 2, ',', '') : '-',
                        'rang' => $rangMatiere,
                        'appreciation' => $appreciation,
                    ];
                    
                    // Pour le calcul de la moyenne générale
                    if ($moyenneMatiere !== null && $moyCoeff !== null) {
                        $totalCoeff += $coef;
                        $totalMoyCoeff += $moyCoeff;
                    }
                }
                
                // 🔹 Ajouter la CONDUITE comme une matière
                $conduiteAppreciation = '-';
                if ($conduiteSur20 > 0) {
                    if ($conduiteSur20 >= 14) $conduiteAppreciation = 'Très Bien';
                    elseif ($conduiteSur20 >= 12) $conduiteAppreciation = 'Bien';
                    elseif ($conduiteSur20 >= 10) $conduiteAppreciation = 'Passable';
                    elseif ($conduiteSur20 >= 8) $conduiteAppreciation = 'Insuffisante';
                    elseif ($conduiteSur20 >= 6) $conduiteAppreciation = 'Faible';
                    elseif ($conduiteSur20 >= 4) $conduiteAppreciation = 'Médiocre';
                    else $conduiteAppreciation = 'Très Faible';
                    
                    $totalCoeff += 1;
                    $totalMoyCoeff += $conduiteSur20;
                    
                    // Ajouter la conduite aux autres matières
                    $moyennesAutres[] = $conduiteSur20;
                    
                    // Récupérer le rang pour la conduite
                    $rangConduite = isset($rangsParMatiere['CONDUITE'][$studentId]) ? 
                        $rangsParMatiere['CONDUITE'][$studentId] : '-';
                } else {
                    $rangConduite = '-';
                }
                
                // Ajouter la conduite au bulletin
                $bulletin[] = [
                    'subject' => 'CONDUITE',
                    'coef' => 1,
                    'interros' => [1 => '-', 2 => '-', 3 => '-', 4 => '-', 5 => '-'],
                    'devoirs' => [1 => '-', 2 => number_format($conduiteSur20, 2, ',', '')],
                    'moyenneInterro' => '-',
                    'moyenne' => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
                    'moyCoeff' => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
                    'rang' => $rangConduite,
                    'appreciation' => $conduiteAppreciation,
                ];
                
                // 🔹 Calcul de la moyenne générale
                $moyenneGenerale = null;
                if ($totalCoeff > 0) {
                    $moyenneGenerale = round($totalMoyCoeff / $totalCoeff, 2);
                }
                
                // 🔹 Calcul des moyennes par domaine
                $moyenneLitteraire = !empty($moyennesLitteraires) ? 
                    round(array_sum($moyennesLitteraires) / count($moyennesLitteraires), 2) : 0;
                $moyenneScientifique = !empty($moyennesScientifiques) ? 
                    round(array_sum($moyennesScientifiques) / count($moyennesScientifiques), 2) : 0;
                $moyenneAutresMatières = !empty($moyennesAutres) ? 
                    round(array_sum($moyennesAutres) / count($moyennesAutres), 2) : 0;
                
                // 🔹 Appréciation générale
                $appreciationGenerale = '-';
                if ($moyenneGenerale !== null) {
                    if ($moyenneGenerale > 16) $appreciationGenerale = 'Très Bien';
                    elseif ($moyenneGenerale >= 14) $appreciationGenerale = 'Bien';
                    elseif ($moyenneGenerale >= 12) $appreciationGenerale = 'Assez Bien';
                    elseif ($moyenneGenerale >= 10) $appreciationGenerale = 'Passable';
                    elseif ($moyenneGenerale >= 8) $appreciationGenerale = 'Insuffisant';
                    elseif ($moyenneGenerale >= 6) $appreciationGenerale = 'Faible';
                    elseif ($moyenneGenerale >= 4) $appreciationGenerale = 'Médiocre';
                    else $appreciationGenerale = 'Très Faible';
                }
                
                // 🔹 Calcul des moyennes générales de la classe pour le rang général
                $moyennesGeneralesClasse = [];
                
                foreach ($allStudentsData as $stId => $stData) {
                    $stGrades = $stData['grades'];
                    $stConduiteSur20 = $stData['conduiteSur20'];
                    
                    $stTotalPoints = 0;
                    $stTotalCoef = 0;
                    
                    // Calcul pour chaque matière
                    foreach ($subjects as $subject) {
                        $coefRecord = $subject->classTeacherSubjects->first();
                        $coef = $coefRecord->coefficient ?? 1;
                        
                        $subjectGrades = $stGrades->where('subject_id', $subject->id);
                        
                        // Calcul des notes
                        $interroNotes = $subjectGrades->where('type', 'interrogation')
                            ->pluck('value')
                            ->filter(fn($v) => $v !== null)
                            ->values()
                            ->toArray();
                        
                        $devoir1 = $subjectGrades->where('type', 'devoir')
                            ->where('sequence', 1)
                            ->first()->value ?? null;
                        $devoir2 = $subjectGrades->where('type', 'devoir')
                            ->where('sequence', 2)
                            ->first()->value ?? null;
                        
                        // Calcul de la moyenne matière
                        $moyenneInterro = !empty($interroNotes) ? 
                            array_sum($interroNotes) / count($interroNotes) : null;
                        
                        $notesPourMoyenne = [];
                        if ($moyenneInterro !== null) $notesPourMoyenne[] = $moyenneInterro;
                        if ($devoir1 !== null) $notesPourMoyenne[] = $devoir1;
                        if ($devoir2 !== null) $notesPourMoyenne[] = $devoir2;
                        
                        if (!empty($notesPourMoyenne)) {
                            $moyenneMatiere = array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                            $stTotalPoints += $moyenneMatiere * $coef;
                            $stTotalCoef += $coef;
                        }
                    }
                    
                    // Ajouter la conduite
                    if ($stConduiteSur20 > 0) {
                        $stTotalPoints += $stConduiteSur20;
                        $stTotalCoef += 1;
                    }
                    
                    // Calcul moyenne générale élève
                    if ($stTotalCoef > 0) {
                        $moyennesGeneralesClasse[$stId] = round($stTotalPoints / $stTotalCoef, 2);
                    }
                }
                
                // Calcul des statistiques de la classe
                $plusForte = !empty($moyennesGeneralesClasse) ? max($moyennesGeneralesClasse) : 0;
                $plusFaible = !empty($moyennesGeneralesClasse) ? min($moyennesGeneralesClasse) : 0;
                $moyClasse = !empty($moyennesGeneralesClasse) ? 
                    round(array_sum($moyennesGeneralesClasse) / count($moyennesGeneralesClasse), 2) : 0;
                
                // Calcul du rang général pour cet élève
                $rang = '-';
                if (isset($moyennesGeneralesClasse[$studentId])) {
                    arsort($moyennesGeneralesClasse);
                    $positions = array_keys($moyennesGeneralesClasse);
                    $position = array_search($studentId, $positions);
                    $rang = $position !== false ? ($position + 1) . 'e' : '-';
                }
                
                // 🔹 Décision du Conseil des Enseignants
                $felicitation = false;
                $encouragement = false;
                $tableauHonneur = false;
                $avertissement = false;
                
                if ($moyenneGenerale !== null && $conduiteSur20 > 0) {
                    if ($moyenneGenerale >= 16 && $conduiteSur20 >= 14) {
                        $felicitation = true;
                    } elseif ($moyenneGenerale >= 14 && $moyenneGenerale < 16 && $conduiteSur20 >= 12) {
                        $encouragement = true;
                    } elseif ($moyenneGenerale >= 12 && $moyenneGenerale < 14 && $conduiteSur20 >= 10) {
                        $tableauHonneur = true;
                    } elseif (($conduiteSur20 < 10 || $moyenneGenerale < 10) && ($moyenneGenerale < 8 || $conduiteSur20 < 8)) {
                        $avertissement = true;
                    }
                }
                
                // 🔹 Générer le QR code (vous pouvez utiliser une bibliothèque comme simplesoftwareio/simple-qrcode)
                $qrCode = ""; // Générez votre QR code ici si nécessaire
                
                // Ajouter les données de cet élève
                $allBulletinsData[] = [
                    'qrCode' => $qrCode,
                    'student' => $student,
                    'classe' => $classe,
                    'bulletin' => $bulletin,
                    'trimestre' => $trimestre,
                    'moyenneGenerale' => $formatNumber($moyenneGenerale),
                    'moyenneLitteraire' => $formatNumber($moyenneLitteraire),
                    'moyenneScientifique' => $formatNumber($moyenneScientifique),
                    'moyenneAutres' => $formatNumber($moyenneAutresMatières),
                    'appreciationGenerale' => $appreciationGenerale,
                    'conduite' => $formatNumber($conduiteSur20),
                    'appreciationConduite' => $conduiteAppreciation,
                    'rang' => $rang,
                    'plusForte' => $formatNumber($plusForte),
                    'plusFaible' => $formatNumber($plusFaible),
                    'moyClasse' => $formatNumber($moyClasse),
                    'felicitation' => $felicitation,
                    'encouragement' => $encouragement,
                    'tableauHonneur' => $tableauHonneur,
                    'avertissement' => $avertissement,
                    'totalMoyCoeff' => $formatNumber($totalMoyCoeff),
                    'totalCoeff' => $totalCoeff,
                    'activeYear' => $activeYear,
                ];
            }
            
            // 🔹 Générer le PDF avec toutes les pages
            $pdf = Pdf::loadView('censeur.classes.notes.all_bulletins_pdf', [
                'allBulletins' => $allBulletinsData,
            ])->setPaper('a4', 'portrait');
            
            $nomClasse = str_replace([' ', '/'], '_', $classe->name);
            return $pdf->download("Tous_Bulletins_{$nomClasse}_T{$trimestre}.pdf");
            
        } 
        catch (\Exception $e) {
            
            return back()->with('error', 'Impossible de générer le PDF de tous les bulletins: ' . $e->getMessage());
        }
    }

    private function calculerMoyenneEleve(int $studentId, int $classId, int $trimestre, $activeYear, $subjects): ?float{
        $grades = Grade::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('trimestre', $trimestre)
            ->get();

        $conduct = Conduct::where('student_id', $studentId)
            ->where('trimestre', $trimestre)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        $punishments = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $activeYear->id)
            ->get();

        $punishHours = $punishments->sum('hours');
        $conductGrade = $conduct ? $conduct->grade : 0;
        $conduiteSur20 = max(0, $conductGrade - ($punishHours / 2));

        $totalPoints = 0;
        $totalCoef = 0;

        foreach ($subjects as $subject) {
            $coefRecord = $subject->classTeacherSubjects->first();
            $coef = $coefRecord->coefficient ?? 1;

            $subjectGrades = $grades->where('subject_id', $subject->id);

            $interroNotes = $subjectGrades->where('type', 'interrogation')
                ->pluck('value')
                ->filter(fn($v) => $v !== null)
                ->values()
                ->toArray();

            $devoir1 = $subjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
            $devoir2 = $subjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

            $moyenneInterro = !empty($interroNotes) ? array_sum($interroNotes) / count($interroNotes) : null;

            $notesPourMoyenne = [];
            if ($moyenneInterro !== null) $notesPourMoyenne[] = $moyenneInterro;
            if ($devoir1 !== null) $notesPourMoyenne[] = $devoir1;
            if ($devoir2 !== null) $notesPourMoyenne[] = $devoir2;

            if (!empty($notesPourMoyenne)) {
                $moy = array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                $totalPoints += $moy * $coef;
                $totalCoef += $coef;
            }
        }

        // Conduite
        if ($conduiteSur20 > 0) {
            $totalPoints += $conduiteSur20;
            $totalCoef += 1;
        }

        return $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : null;
    }


    private function getBulletinData(int $studentId, int $classId, int $trimestre, $activeYear): array {
        $student = Student::findOrFail($studentId);
        $classe = Classe::with(['students' => function ($q) use ($activeYear) {
            $q->where('is_validated', 1)
            ->where('academic_year_id', $activeYear->id)
            ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classId);

        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        }])->orderBy('name')->get();

        // Pré-calcul de toutes les moyennes par matière pour les rangs
        $allStudentsMoyennesParMatiere = [];
        $allStudentsData = [];

        foreach ($classe->students as $st) {
            $stId = $st->id;
            $stGrades = Grade::where('student_id', $stId)
                ->where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $trimestre)
                ->get();

            $stConduct = Conduct::where('student_id', $stId)
                ->where('trimestre', $trimestre)
                ->where('academic_year_id', $activeYear->id)
                ->first();

            $stPunishments = Punishment::where('student_id', $stId)
                ->where('academic_year_id', $activeYear->id)
                ->get();

            $stConduiteSur20 = max(0, ($stConduct ? $stConduct->grade : 0) - ($stPunishments->sum('hours') / 2));

            $allStudentsData[$stId] = [
                'student' => $st,
                'grades' => $stGrades,
                'conduiteSur20' => $stConduiteSur20,
            ];

            foreach ($subjects as $subject) {
                $subjectGrades = $stGrades->where('subject_id', $subject->id);
                $interroNotes = $subjectGrades->where('type', 'interrogation')
                    ->pluck('value')->filter(fn($v) => $v !== null)->values()->toArray();
                $devoir1 = $subjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                $devoir2 = $subjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;
                $moyenneInterro = !empty($interroNotes) ? array_sum($interroNotes) / count($interroNotes) : null;
                $notesPourMoyenne = array_filter([$moyenneInterro, $devoir1, $devoir2], fn($v) => $v !== null);
                if (!empty($notesPourMoyenne)) {
                    $moy = array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                    $allStudentsMoyennesParMatiere[$subject->id][$stId] = $moy;
                }
            }
            $allStudentsMoyennesParMatiere['CONDUITE'][$stId] = $stConduiteSur20;
        }

        // Rangs par matière
        $rangsParMatiere = [];
        foreach ($allStudentsMoyennesParMatiere as $subjectId => $moyennes) {
            arsort($moyennes);
            $rang = 1; $prev = null; $sameCount = 1;
            foreach ($moyennes as $stId => $value) {
                if ($prev !== null && $value == $prev) { $sameCount++; }
                else { $rang += ($sameCount - 1); $sameCount = 1; }
                $rangsParMatiere[$subjectId][$stId] = $rang . 'e';
                $prev = $value;
                $rang++;
            }
        }

        // Données pour l'élève courant
        $grades = Grade::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('trimestre', $trimestre)
            ->get();

        $conduct = Conduct::where('student_id', $studentId)
            ->where('trimestre', $trimestre)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        $punishments = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $activeYear->id)
            ->get();

        $conduiteSur20 = round(max(0, ($conduct ? $conduct->grade : 0) - ($punishments->sum('hours') / 2)), 2);

        $conduiteAppreciation = '-';
        if ($conduiteSur20 > 16) $conduiteAppreciation = 'Très Bien';
        elseif ($conduiteSur20 >= 14) $conduiteAppreciation = 'Bien';
        elseif ($conduiteSur20 >= 12) $conduiteAppreciation = 'Assez Bien';
        elseif ($conduiteSur20 >= 10) $conduiteAppreciation = 'Passable';
        elseif ($conduiteSur20 >= 8)  $conduiteAppreciation = 'Insuffisant';
        elseif ($conduiteSur20 >= 6)  $conduiteAppreciation = 'Faible';
        elseif ($conduiteSur20 > 0)   $conduiteAppreciation = 'Médiocre';

        $matieresLitteraires  = ['COMMUNICATION ECRITE','LECTURE','ANGLAIS','HISTOIRE-GEOGRAPHIE','FRANÇAIS','PHILOSOPHIE','ESPAGNOL','HGGSP'];
        $matieresScientifiques = ['MATHEMATIQUES','PHYSIQUE CHIMIE ET TECHNOLOGIE (PCT)','SCIENCE DE LA VIE ET DE LA TERRE (SVT)','ENSEIGNEMENTS SCIENTIFIQUES'];
        $Autrematiere = ['EDUCATION PHYSIQUE ET SPORTIVE (EPS)','CONDUITE'];

        $bulletin = [];
        $totalMoyCoeff = 0;
        $totalCoeff = 0;
        $moyennesLitteraires = []; $moyennesScientifiques = []; $moyennesAutres = [];

        foreach ($subjects as $subject) {
            $coefRecord = $subject->classTeacherSubjects->first();
            $coef = $coefRecord->coefficient ?? 1;
            $subjectGrades = $grades->where('subject_id', $subject->id);

            $interroNotes = $subjectGrades->where('type', 'interrogation')
                ->sortBy('sequence')->pluck('value')
                ->filter(fn($v) => $v !== null)->values()->toArray();

            $devoir1 = $subjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
            $devoir2 = $subjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

            $moyenneInterro = !empty($interroNotes) ? round(array_sum($interroNotes) / count($interroNotes), 2) : null;

            $notesPourMoyenne = array_filter([$moyenneInterro, $devoir1, $devoir2], fn($v) => $v !== null);
            $moyenneMatiere = !empty($notesPourMoyenne) ? round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2) : null;

            $moyCoeff = null;
            $appreciation = '-';

            if ($moyenneMatiere !== null) {
                $moyCoeff = round($moyenneMatiere * $coef, 2);
                $totalMoyCoeff += $moyCoeff;
                $totalCoeff += $coef;

                if ($moyenneMatiere > 16)       $appreciation = 'Très Bien';
                elseif ($moyenneMatiere >= 14)  $appreciation = 'Bien';
                elseif ($moyenneMatiere >= 12)  $appreciation = 'Assez Bien';
                elseif ($moyenneMatiere >= 10)  $appreciation = 'Passable';
                elseif ($moyenneMatiere >= 8)   $appreciation = 'Insuffisant';
                elseif ($moyenneMatiere >= 6)   $appreciation = 'Faible';
                elseif ($moyenneMatiere >= 4)   $appreciation = 'Médiocre';
                else $appreciation = 'Très Faible';

                $nomMatiere = strtoupper($subject->name);
                if (in_array($nomMatiere, $matieresLitteraires)) $moyennesLitteraires[] = $moyenneMatiere;
                elseif (in_array($nomMatiere, $matieresScientifiques)) $moyennesScientifiques[] = $moyenneMatiere;
                elseif (in_array($nomMatiere, $Autrematiere)) $moyennesAutres[] = $moyenneMatiere;
            }

            $interrosFormatted = [];
            for ($i = 1; $i <= 5; $i++) {
                $interrosFormatted[$i] = isset($interroNotes[$i-1]) ? number_format($interroNotes[$i-1], 2, ',', '') : '-';
            }
            $devoirsFormatted = [
                1 => $devoir1 !== null ? number_format($devoir1, 2, ',', '') : '-',
                2 => $devoir2 !== null ? number_format($devoir2, 2, ',', '') : '-',
            ];

            $bulletin[] = [
                'subject'        => strtoupper($subject->name),
                'coef'           => $coef,
                'interros'       => $interrosFormatted,
                'devoirs'        => $devoirsFormatted,
                'moyenneInterro' => $moyenneInterro !== null ? number_format($moyenneInterro, 2, ',', '') : '-',
                'moyenne'        => $moyenneMatiere !== null ? number_format($moyenneMatiere, 2, ',', '') : '-',
                'moyCoeff'       => $moyCoeff !== null ? number_format($moyCoeff, 2, ',', '') : '-',
                'rang'           => $rangsParMatiere[$subject->id][$studentId] ?? '-',
                'appreciation'   => $appreciation,
            ];
        }

        // Conduite
        if ($conduiteSur20 > 0) {
            $totalCoeff += 1;
            $totalMoyCoeff += $conduiteSur20;
            $moyennesAutres[] = $conduiteSur20;
        }

        $bulletin[] = [
            'subject'        => 'CONDUITE',
            'coef'           => 1,
            'interros'       => [1=>'-',2=>'-',3=>'-',4=>'-',5=>'-'],
            'devoirs'        => [1=>'-', 2=>number_format($conduiteSur20, 2, ',', '')],
            'moyenneInterro' => '-',
            'moyenne'        => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
            'moyCoeff'       => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
            'rang'           => $rangsParMatiere['CONDUITE'][$studentId] ?? '-',
            'appreciation'   => $conduiteAppreciation,
        ];

        $moyenneGenerale = $totalCoeff > 0 ? round($totalMoyCoeff / $totalCoeff, 2) : null;

        $moyenneLitteraire   = !empty($moyennesLitteraires)   ? round(array_sum($moyennesLitteraires) / count($moyennesLitteraires), 2) : 0;
        $moyenneScientifique = !empty($moyennesScientifiques) ? round(array_sum($moyennesScientifiques) / count($moyennesScientifiques), 2) : 0;
        $moyenneAutresVal    = !empty($moyennesAutres)        ? round(array_sum($moyennesAutres) / count($moyennesAutres), 2) : 0;

        $appreciationGenerale = '-';
        if ($moyenneGenerale !== null) {
            if ($moyenneGenerale > 16)       $appreciationGenerale = 'Très Bien';
            elseif ($moyenneGenerale >= 14)  $appreciationGenerale = 'Bien';
            elseif ($moyenneGenerale >= 12)  $appreciationGenerale = 'Assez Bien';
            elseif ($moyenneGenerale >= 10)  $appreciationGenerale = 'Passable';
            elseif ($moyenneGenerale >= 8)   $appreciationGenerale = 'Insuffisant';
            elseif ($moyenneGenerale >= 6)   $appreciationGenerale = 'Faible';
            elseif ($moyenneGenerale >= 4)   $appreciationGenerale = 'Médiocre';
            else $appreciationGenerale = 'Très Faible';
        }

        // Moyennes générales de la classe pour stats et rang
        $moyennesGeneralesClasse = [];
        foreach ($allStudentsData as $stId => $stData) {
            $stGrades = $stData['grades'];
            $stConduiteSur20 = $stData['conduiteSur20'];
            $stTotalPoints = 0; $stTotalCoef = 0;

            foreach ($subjects as $subject) {
                $coefRecord = $subject->classTeacherSubjects->first();
                $coef = $coefRecord->coefficient ?? 1;
                $subjectGrades = $stGrades->where('subject_id', $subject->id);

                $interroNotes = $subjectGrades->where('type', 'interrogation')
                    ->pluck('value')->filter(fn($v) => $v !== null)->values()->toArray();
                $devoir1 = $subjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                $devoir2 = $subjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

                $moyenneInterro = !empty($interroNotes) ? array_sum($interroNotes) / count($interroNotes) : null;
                $notesPourMoyenne = array_filter([$moyenneInterro, $devoir1, $devoir2], fn($v) => $v !== null);

                if (!empty($notesPourMoyenne)) {
                    $moy = array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                    $stTotalPoints += $moy * $coef;
                    $stTotalCoef += $coef;
                }
            }

            if ($stConduiteSur20 > 0) {
                $stTotalPoints += $stConduiteSur20;
                $stTotalCoef += 1;
            }

            if ($stTotalCoef > 0) {
                $moyennesGeneralesClasse[$stId] = round($stTotalPoints / $stTotalCoef, 2);
            }
        }

        $plusForte  = !empty($moyennesGeneralesClasse) ? max($moyennesGeneralesClasse) : 0;
        $plusFaible = !empty($moyennesGeneralesClasse) ? min($moyennesGeneralesClasse) : 0;
        $moyClasse  = !empty($moyennesGeneralesClasse) ? round(array_sum($moyennesGeneralesClasse) / count($moyennesGeneralesClasse), 2) : 0;

        $rang = '-';
        if (isset($moyennesGeneralesClasse[$studentId])) {
            arsort($moyennesGeneralesClasse);
            $positions = array_keys($moyennesGeneralesClasse);
            $position = array_search($studentId, $positions);
            $rang = $position !== false ? ($position + 1) . 'e' : '-';
        }

        $felicitation = $encouragement = $tableauHonneur = $avertissement = false;
        if ($moyenneGenerale !== null && $conduiteSur20 > 0) {
            if ($moyenneGenerale >= 16 && $conduiteSur20 >= 14) $felicitation = true;
            elseif ($moyenneGenerale >= 14 && $conduiteSur20 >= 12) $encouragement = true;
            elseif ($moyenneGenerale >= 12 && $conduiteSur20 >= 10) $tableauHonneur = true;
            elseif ($conduiteSur20 < 10 || $moyenneGenerale < 10) $avertissement = true;
        }

        $fmt = fn($v) => ($v === null || $v === 0) ? '0,00' : number_format($v, 2, ',', '');

        return [
            'student'              => $student,
            'classe'               => $classe,
            'bulletin'             => $bulletin,
            'trimestre'            => $trimestre,
            'moyenneGenerale'      => $fmt($moyenneGenerale),
            'moyenneLitteraire'    => $fmt($moyenneLitteraire),
            'moyenneScientifique'  => $fmt($moyenneScientifique),
            'moyenneAutres'        => $fmt($moyenneAutresVal),
            'appreciationGenerale' => $appreciationGenerale,
            'conduite'             => $fmt($conduiteSur20),
            'rang'                 => $rang,
            'plusForte'            => $fmt($plusForte),
            'plusFaible'           => $fmt($plusFaible),
            'moyClasse'            => $fmt($moyClasse),
            'felicitation'         => $felicitation,
            'encouragement'        => $encouragement,
            'tableauHonneur'       => $tableauHonneur,
            'avertissement'        => $avertissement,
            'totalMoyCoeff'        => $fmt($totalMoyCoeff),
            'totalCoeff'           => $totalCoeff,
            'activeYear'           => $activeYear,
        ];
    }

    public function pointAnnee(int $classId) {
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($activeYear) {
            $q->where('is_validated', 1)
            ->where('academic_year_id', $activeYear->id)
            ->orderBy('last_name')
            ->orderBy('first_name');
        }])->findOrFail($classId);

        $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        })->with(['classTeacherSubjects' => function ($q) use ($classId, $activeYear) {
            $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
        }])->orderBy('name')->get();

        // Calcul des moyennes par trimestre pour chaque élève
        $trimestres = [1, 2, 3];

        // Pour chaque trimestre, calculer les moyennes de tous les élèves (pour le rang)
        $moyennesParTrimestreTousEleves = [];
        $moyennesParEleveParTrimestre   = [];

        foreach ($trimestres as $t) {
            foreach ($classe->students as $student) {
                $moy = $this->calculerMoyenneEleve($student->id, $classId, $t, $activeYear, $subjects);
                $moyennesParEleveParTrimestre[$student->id][$t] = $moy;
                if ($moy !== null) {
                    $moyennesParTrimestreTousEleves[$t][$student->id] = $moy;
                }
            }
        }

        // Rangs par trimestre
        $rangsParTrimestre = [];
        foreach ($trimestres as $t) {
            if (!empty($moyennesParTrimestreTousEleves[$t])) {
                $sorted = $moyennesParTrimestreTousEleves[$t];
                arsort($sorted);
                $rang = 1;
                $prev = null;
                $sameCount = 1;
                $tempRangs = [];

                foreach ($sorted as $stId => $moy) {
                    if ($prev !== null && $moy == $prev) {
                        $sameCount++;
                    } else {
                        $rang += ($sameCount - 1);
                        $sameCount = 1;
                    }
                    $tempRangs[$stId] = $rang . 'e/' . count($sorted);
                    $prev = $moy;
                    $rang++;
                }
                $rangsParTrimestre[$t] = $tempRangs;
            }
        }

        // Conduites par élève par trimestre
        $conductesParEleve = [];
        foreach ($classe->students as $student) {
            foreach ($trimestres as $t) {
                $conduct = Conduct::where('student_id', $student->id)
                    ->where('trimestre', $t)
                    ->where('academic_year_id', $activeYear->id)
                    ->first();

                $punishments = Punishment::where('student_id', $student->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                $punishHours = $punishments->sum('hours');
                $conduiteSur20 = max(0, ($conduct ? $conduct->grade : 0) - ($punishHours / 2));
                $conductesParEleve[$student->id][$t] = round($conduiteSur20, 2);
            }
        }

        // Calcul de la moyenne annuelle et rang annuel
        $moyennesAnnuellesTousEleves = [];
        foreach ($classe->students as $student) {
            $moysTrimestres = array_filter(
                array_map(fn($t) => $moyennesParEleveParTrimestre[$student->id][$t] ?? null, $trimestres),
                fn($v) => $v !== null
            );

            if (!empty($moysTrimestres)) {
                $moyennesAnnuellesTousEleves[$student->id] = round(array_sum($moysTrimestres) / count($moysTrimestres), 2);
            }
        }

        // Rang annuel
        $rangsAnnuels = [];
        if (!empty($moyennesAnnuellesTousEleves)) {
            $sorted = $moyennesAnnuellesTousEleves;
            arsort($sorted);
            $rang = 1; $prev = null; $sameCount = 1;
            foreach ($sorted as $stId => $moy) {
                if ($prev !== null && $moy == $prev) { $sameCount++; }
                else { $rang += ($sameCount - 1); $sameCount = 1; }
                $rangsAnnuels[$stId] = $rang . 'e/' . count($sorted);
                $prev = $moy;
                $rang++;
            }
        }

        // Assemblage du tableau final par élève (ordre alphabétique)
        $tableauEleves = [];
        $numOrdre = 1;
        foreach ($classe->students as $student) {
            $moyT1 = $moyennesParEleveParTrimestre[$student->id][1] ?? null;
            $moyT2 = $moyennesParEleveParTrimestre[$student->id][2] ?? null;
            $moyT3 = $moyennesParEleveParTrimestre[$student->id][3] ?? null;
            $moyAnn = $moyennesAnnuellesTousEleves[$student->id] ?? null;

            $tableauEleves[] = [
                'num'          => $numOrdre++,
                'student'      => $student,
                'conduite_t1'  => $conductesParEleve[$student->id][1] ?? 0,
                'conduite_t2'  => $conductesParEleve[$student->id][2] ?? 0,
                'conduite_t3'  => $conductesParEleve[$student->id][3] ?? 0,
                'moy_t1'       => $moyT1,
                'moy_t2'       => $moyT2,
                'moy_t3'       => $moyT3,
                'rang_t1'      => $rangsParTrimestre[1][$student->id] ?? '-',
                'rang_t2'      => $rangsParTrimestre[2][$student->id] ?? '-',
                'rang_t3'      => $rangsParTrimestre[3][$student->id] ?? '-',
                'moy_annuelle' => $moyAnn,
                'rang_annuel'  => $rangsAnnuels[$student->id] ?? '-',
                'statut'       => $moyAnn !== null ? ($moyAnn >= 10 ? 'Passé' : 'Redouble') : '-',
            ];
        }

        return view('censeur.classes.notes.point_annee', compact(
            'classe', 'activeYear', 'tableauEleves'
        ));
    }

    public function bulletinModal(int $classId, int $studentId, int $trimestre) {
        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
            $data = $this->getBulletinData($studentId, $classId, $trimestre, $activeYear);

            $html = view('censeur.classes.notes.bulletin_modal_content', $data)->render();

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    private function getBulletinFinAnneeData(int $studentId, int $classId, $activeYear, $subjects): array {
        $student = Student::findOrFail($studentId);
        $classe  = Classe::with(['students' => function ($q) use ($activeYear) {
            $q->where('is_validated', 1)
              ->where('academic_year_id', $activeYear->id)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classId);

        // ── Calcul des moyennes par trimestre pour TOUS les élèves ──────────────
        $moyennesParTrimestreTousEleves = [];
        foreach ([1, 2, 3] as $t) {
            foreach ($classe->students as $st) {
                $moy = $this->calculerMoyenneEleve($st->id, $classId, $t, $activeYear, $subjects);
                if ($moy !== null) {
                    $moyennesParTrimestreTousEleves[$t][$st->id] = $moy;
                }
            }
        }

        // ── Rangs par trimestre ─────────────────────────────────────────────────
        $rangsParTrimestre = [];
        foreach ([1, 2, 3] as $t) {
            if (!empty($moyennesParTrimestreTousEleves[$t])) {
                $sorted = $moyennesParTrimestreTousEleves[$t];
                arsort($sorted);
                $rang = 1; $prev = null; $sameCount = 1;
                foreach ($sorted as $stId => $moy) {
                    if ($prev !== null && $moy == $prev) { $sameCount++; }
                    else { $rang += ($sameCount - 1); $sameCount = 1; }
                    $rangsParTrimestre[$t][$stId] = $rang . 'e/' . count($sorted);
                    $prev = $moy;
                    $rang++;
                }
            }
        }

        // ── Moyennes annuelles pour TOUS les élèves ─────────────────────────────
        $moyennesAnnuelles = [];
        foreach ($classe->students as $st) {
            $moys = array_filter(
                array_map(fn($t) => $moyennesParTrimestreTousEleves[$t][$st->id] ?? null, [1, 2, 3]),
                fn($v) => $v !== null
            );
            if (!empty($moys)) {
                $moyennesAnnuelles[$st->id] = round(array_sum($moys) / count($moys), 2);
            }
        }

        // ── Rangs annuels ───────────────────────────────────────────────────────
        $rangsAnnuels = [];
        if (!empty($moyennesAnnuelles)) {
            $sorted = $moyennesAnnuelles;
            arsort($sorted);
            $rang = 1; $prev = null; $sameCount = 1;
            foreach ($sorted as $stId => $moy) {
                if ($prev !== null && $moy == $prev) { $sameCount++; }
                else { $rang += ($sameCount - 1); $sameCount = 1; }
                $rangsAnnuels[$stId] = $rang . 'e/' . count($sorted);
                $prev = $moy;
                $rang++;
            }
        }

        // ── Données spécifiques à l'élève courant ───────────────────────────────
        $moyT1  = $moyennesParTrimestreTousEleves[1][$studentId] ?? null;
        $moyT2  = $moyennesParTrimestreTousEleves[2][$studentId] ?? null;
        $moyT3  = $moyennesParTrimestreTousEleves[3][$studentId] ?? null;
        $moyAnn = $moyennesAnnuelles[$studentId] ?? null;

        $rangT1  = $rangsParTrimestre[1][$studentId] ?? '-';
        $rangT2  = $rangsParTrimestre[2][$studentId] ?? '-';
        $rangT3  = $rangsParTrimestre[3][$studentId] ?? '-';
        $rangAnn = $rangsAnnuels[$studentId] ?? '-';

        // ── Bulletin T3 : moyennes par matière ──────────────────────────────────
        // Pré-calcul des moyennes par matière T3 pour tous les élèves (pour les rangs)
        $allStudentsMoyennesParMatiereT3 = [];
        foreach ($classe->students as $st) {
            $stGrades = Grade::where('student_id', $st->id)
                ->where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', 3)
                ->get();

            foreach ($subjects as $subject) {
                $subjectGrades = $stGrades->where('subject_id', $subject->id);
                $interroNotes = $subjectGrades->where('type', 'interrogation')
                    ->pluck('value')->filter(fn($v) => $v !== null)->values()->toArray();
                $devoir1 = $subjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
                $devoir2 = $subjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;
                $moyenneInterro = !empty($interroNotes) ? array_sum($interroNotes) / count($interroNotes) : null;
                $notesPourMoyenne = array_filter([$moyenneInterro, $devoir1, $devoir2], fn($v) => $v !== null);
                if (!empty($notesPourMoyenne)) {
                    $allStudentsMoyennesParMatiereT3[$subject->id][$st->id] =
                        array_sum($notesPourMoyenne) / count($notesPourMoyenne);
                }
            }

            // Conduite T3
            $conduct = Conduct::where('student_id', $st->id)
                ->where('trimestre', 3)->where('academic_year_id', $activeYear->id)->first();
            $punishments = Punishment::where('student_id', $st->id)
                ->where('academic_year_id', $activeYear->id)->get();
            $conduiteSur20 = max(0, ($conduct ? $conduct->grade : 0) - ($punishments->sum('hours') / 2));
            $allStudentsMoyennesParMatiereT3['CONDUITE'][$st->id] = $conduiteSur20;
        }

        // Rangs par matière T3
        $rangsParMatiereT3 = [];
        foreach ($allStudentsMoyennesParMatiereT3 as $subjectId => $moyennes) {
            arsort($moyennes);
            $rang = 1; $prev = null; $sameCount = 1;
            foreach ($moyennes as $stId => $value) {
                if ($prev !== null && $value == $prev) { $sameCount++; }
                else { $rang += ($sameCount - 1); $sameCount = 1; }
                $rangsParMatiereT3[$subjectId][$stId] = $rang . 'e';
                $prev = $value;
                $rang++;
            }
        }

        // ── Notes T3 de l'élève courant ─────────────────────────────────────────
        $gradesT3 = Grade::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('trimestre', 3)
            ->get();

        $conductT3 = Conduct::where('student_id', $studentId)
            ->where('trimestre', 3)->where('academic_year_id', $activeYear->id)->first();
        $punishments = Punishment::where('student_id', $studentId)
            ->where('academic_year_id', $activeYear->id)->get();
        $conduiteSur20 = round(max(0, ($conductT3 ? $conductT3->grade : 0) - ($punishments->sum('hours') / 2)), 2);

        $matieresLitteraires   = ['COMMUNICATION ECRITE','LECTURE','ANGLAIS','HISTOIRE-GEOGRAPHIE','FRANÇAIS','PHILOSOPHIE','ESPAGNOL','HGGSP'];
        $matieresScientifiques = ['MATHEMATIQUES','PHYSIQUE CHIMIE ET TECHNOLOGIE (PCT)','SCIENCE DE LA VIE ET DE LA TERRE (SVT)','ENSEIGNEMENTS SCIENTIFIQUES'];
        $Autrematiere          = ['EDUCATION PHYSIQUE ET SPORTIVE (EPS)','CONDUITE'];

        $bulletin        = [];
        $totalMoyCoeff   = 0;
        $totalCoeff      = 0;
        $moyennesLitt    = []; $moyennesSci = []; $moyennesAutres = [];

        foreach ($subjects as $subject) {
            $coefRecord = $subject->classTeacherSubjects->first();
            $coef       = $coefRecord->coefficient ?? 1;

            $subjectGrades = $gradesT3->where('subject_id', $subject->id);
            $interroNotes  = $subjectGrades->where('type', 'interrogation')
                ->sortBy('sequence')->pluck('value')
                ->filter(fn($v) => $v !== null)->values()->toArray();

            $devoir1 = $subjectGrades->where('type', 'devoir')->where('sequence', 1)->first()->value ?? null;
            $devoir2 = $subjectGrades->where('type', 'devoir')->where('sequence', 2)->first()->value ?? null;

            $moyenneInterro  = !empty($interroNotes) ? round(array_sum($interroNotes) / count($interroNotes), 2) : null;
            $notesPourMoyenne = array_filter([$moyenneInterro, $devoir1, $devoir2], fn($v) => $v !== null);
            $moyenneMatiere  = !empty($notesPourMoyenne) ? round(array_sum($notesPourMoyenne) / count($notesPourMoyenne), 2) : null;

            $moyCoeff    = null;
            $appreciation = '-';

            if ($moyenneMatiere !== null) {
                $moyCoeff = round($moyenneMatiere * $coef, 2);
                $totalMoyCoeff += $moyCoeff;
                $totalCoeff    += $coef;

                if ($moyenneMatiere > 16)      $appreciation = 'Très Bien';
                elseif ($moyenneMatiere >= 14) $appreciation = 'Bien';
                elseif ($moyenneMatiere >= 12) $appreciation = 'Assez Bien';
                elseif ($moyenneMatiere >= 10) $appreciation = 'Passable';
                elseif ($moyenneMatiere >= 8)  $appreciation = 'Insuffisant';
                elseif ($moyenneMatiere >= 6)  $appreciation = 'Faible';
                elseif ($moyenneMatiere >= 4)  $appreciation = 'Médiocre';
                else $appreciation = 'Très Faible';

                $nom = strtoupper($subject->name);
                if (in_array($nom, $matieresLitteraires))        $moyennesLitt[]    = $moyenneMatiere;
                elseif (in_array($nom, $matieresScientifiques))  $moyennesSci[]     = $moyenneMatiere;
                elseif (in_array($nom, $Autrematiere))           $moyennesAutres[]  = $moyenneMatiere;
            }

            $bulletin[] = [
                'subject'        => strtoupper($subject->name),
                'coef'           => $coef,
                'moyenneInterro' => $moyenneInterro !== null ? number_format($moyenneInterro, 2, ',', '') : '-',
                'devoirs'        => [
                    1 => $devoir1 !== null ? number_format($devoir1, 2, ',', '') : '-',
                    2 => $devoir2 !== null ? number_format($devoir2, 2, ',', '') : '-',
                ],
                'moyenne'        => $moyenneMatiere !== null ? number_format($moyenneMatiere, 2, ',', '') : '-',
                'moyCoeff'       => $moyCoeff !== null ? number_format($moyCoeff, 2, ',', '') : '-',
                'rang'           => $rangsParMatiereT3[$subject->id][$studentId] ?? '-',
                'appreciation'   => $appreciation,
            ];
        }

        // Conduite T3
        $conduiteApp = '-';
        if ($conduiteSur20 >= 14) $conduiteApp = 'Très Bien';
        elseif ($conduiteSur20 >= 12) $conduiteApp = 'Bien';
        elseif ($conduiteSur20 >= 10) $conduiteApp = 'Passable';
        elseif ($conduiteSur20 >= 8)  $conduiteApp = 'Insuffisante';
        elseif ($conduiteSur20 >= 6)  $conduiteApp = 'Faible';
        elseif ($conduiteSur20 >= 4)  $conduiteApp = 'Médiocre';
        elseif ($conduiteSur20 > 0)   $conduiteApp = 'Très Faible';

        if ($conduiteSur20 > 0) {
            $totalCoeff    += 1;
            $totalMoyCoeff += $conduiteSur20;
            $moyennesAutres[] = $conduiteSur20;
        }

        $bulletin[] = [
            'subject'        => 'CONDUITE',
            'coef'           => 1,
            'moyenneInterro' => '-',
            'devoirs'        => [1 => '-', 2 => number_format($conduiteSur20, 2, ',', '')],
            'moyenne'        => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
            'moyCoeff'       => $conduiteSur20 > 0 ? number_format($conduiteSur20, 2, ',', '') : '-',
            'rang'           => $rangsParMatiereT3['CONDUITE'][$studentId] ?? '-',
            'appreciation'   => $conduiteApp,
        ];

        // Moyennes par domaine (T3)
        $moyLitt  = !empty($moyennesLitt) ? round(array_sum($moyennesLitt) / count($moyennesLitt), 2) : 0;
        $moySci   = !empty($moyennesSci)  ? round(array_sum($moyennesSci) / count($moyennesSci), 2) : 0;
        $moyAutre = !empty($moyennesAutres) ? round(array_sum($moyennesAutres) / count($moyennesAutres), 2) : 0;

        // Moyennes T3 de la classe (pour stats)
        $moyT3Classe = [];
        foreach ($classe->students as $st) {
            if (isset($moyennesParTrimestreTousEleves[3][$st->id])) {
                $moyT3Classe[$st->id] = $moyennesParTrimestreTousEleves[3][$st->id];
            }
        }
        $plusForteT3  = !empty($moyT3Classe) ? max($moyT3Classe) : 0;
        $plusFaibleT3 = !empty($moyT3Classe) ? min($moyT3Classe) : 0;
        $moyClasseT3  = !empty($moyT3Classe) ? round(array_sum($moyT3Classe) / count($moyT3Classe), 2) : 0;

        // Décision du conseil (basée sur moyenne annuelle)
        $felicitation = $encouragement = $tableauHonneur = $avertissement = false;
        if ($moyAnn !== null && $conduiteSur20 > 0) {
            if ($moyAnn >= 16 && $conduiteSur20 >= 14)                           $felicitation = true;
            elseif ($moyAnn >= 14 && $moyAnn < 16 && $conduiteSur20 >= 12)      $encouragement = true;
            elseif ($moyAnn >= 12 && $moyAnn < 14 && $conduiteSur20 >= 10)      $tableauHonneur = true;
            elseif ($conduiteSur20 < 10 || $moyAnn < 10)                         $avertissement = true;
        }

        $fmt = fn($v) => ($v === null || $v === 0) ? '0,00' : number_format($v, 2, ',', '');

        return [
            'student'              => $student,
            'classe'               => $classe,
            'activeYear'           => $activeYear,
            'bulletin'             => $bulletin,
            'totalCoeff'           => $totalCoeff,
            'totalMoyCoeff'        => $fmt($totalMoyCoeff),
            'moyenneLitteraire'    => $fmt($moyLitt),
            'moyenneScientifique'  => $fmt($moySci),
            'moyenneAutres'        => $fmt($moyAutre),
            // Moyennes trimestrielles
            'moyT1'                => $fmt($moyT1),
            'moyT2'                => $fmt($moyT2),
            'moyT3'                => $fmt($moyT3),
            'moyAnnuelle'          => $fmt($moyAnn),
            'rangT1'               => $rangT1,
            'rangT2'               => $rangT2,
            'rangT3'               => $rangT3,
            'rangAnnuel'           => $rangAnn,
            // Stats classe T3
            'plusForte'            => $fmt($plusForteT3),
            'plusFaible'           => $fmt($plusFaibleT3),
            'moyClasse'            => $fmt($moyClasseT3),
            // Décision conseil
            'felicitation'         => $felicitation,
            'encouragement'        => $encouragement,
            'tableauHonneur'       => $tableauHonneur,
            'avertissement'        => $avertissement,
            // Appréciation générale (basée sur moyenne annuelle)
            'appreciationGenerale' => $moyAnn === null ? '-' : (
                $moyAnn > 16 ? 'Très Bien' : ($moyAnn >= 14 ? 'Bien' : ($moyAnn >= 12 ? 'Assez Bien' :
                ($moyAnn >= 10 ? 'Passable' : ($moyAnn >= 8 ? 'Insuffisant' : ($moyAnn >= 6 ? 'Faible' :
                ($moyAnn >= 4 ? 'Médiocre' : 'Très Faible'))))))
            ),
        ];
    }

    public function downloadBulletinFinAnneePdf(int $classId, int $studentId) {
        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
            $subjects   = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $activeYear) {
                $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
            })->with(['classTeacherSubjects' => function ($q) use ($classId, $activeYear) {
                $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
            }])->orderBy('name')->get();

            $data = $this->getBulletinFinAnneeData($studentId, $classId, $activeYear, $subjects);

            $pdf = Pdf::loadView('censeur.classes.notes.bulletin_fin_annee_pdf', ['data' => $data])
                      ->setPaper('a4', 'portrait');

            $nom = $data['student']->last_name . '_' . $data['student']->first_name;
            return $pdf->download("Bulletin_FinAnnee_{$nom}.pdf");

        } catch (\Exception $e) {
            return back()->with('error', 'Impossible de générer le bulletin : ' . $e->getMessage());
        }
    }

    public function downloadAllBulletinsFinAnneePdf(int $classId) {
        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();

            $classe = Classe::with(['students' => function ($q) use ($activeYear) {
                $q->where('is_validated', 1)
                  ->where('academic_year_id', $activeYear->id)
                  ->orderBy('last_name')->orderBy('first_name');
            }])->findOrFail($classId);

            $subjects = Subject::whereHas('classTeacherSubjects', function ($q) use ($classId, $activeYear) {
                $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
            })->with(['classTeacherSubjects' => function ($q) use ($classId, $activeYear) {
                $q->where('class_id', $classId)->where('academic_year_id', $activeYear->id);
            }])->orderBy('name')->get();

            $allBulletins = [];
            foreach ($classe->students as $student) {
                $allBulletins[] = $this->getBulletinFinAnneeData($student->id, $classId, $activeYear, $subjects);
            }

            $pdf = Pdf::loadView('censeur.classes.notes.all_bulletins_fin_annee_pdf', [
                'allBulletins' => $allBulletins,
            ])->setPaper('a4', 'portrait');

            $nomClasse = str_replace([' ', '/'], '_', $classe->name);
            return $pdf->download("Bulletins_FinAnnee_{$nomClasse}.pdf");

        } catch (\Exception $e) {
            return back()->with('error', 'Impossible de générer les bulletins : ' . $e->getMessage());
        }
    }

    public function exportListeElevesPDF(int $classId, int $trimestre, int $subjectId) {
        try {
            // 1) Année académique active
            $activeYear = AcademicYear::where('active', true)->firstOrFail();

            // 2) Classe avec élèves validés, triés alphabétiquement
            $classe = Classe::with(['students' => function ($q) use ($activeYear) {
                $q->where('is_validated', 1)
                  ->where('academic_year_id', $activeYear->id)
                  ->orderBy('last_name')
                  ->orderBy('first_name');
            }])->findOrFail($classId);

            // 3) Matière (pivot pour le coefficient)
            $subjectPivot = ClassTeacherSubject::where('subject_id', $subjectId)
                ->where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->first();

            if (!$subjectPivot) {
                // Fallback sans filtre année
                $subjectPivot = ClassTeacherSubject::where('subject_id', $subjectId)
                    ->where('class_id', $classId)
                    ->first();
            }

            if (!$subjectPivot) {
                return back()->with('error', 'Matière non associée à cette classe.');
            }

            $subject = Subject::findOrFail($subjectId);

            // 4) Construction des données par élève
            $listeEleves = [];

            foreach ($classe->students as $student) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();

                // Initialisation
                $interros = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null];
                $devoirs   = [1 => null, 2 => null];

                foreach ($grades as $grade) {
                    if ($grade->type === 'interrogation' && isset($interros[$grade->sequence])) {
                        $interros[$grade->sequence] = $grade->value;
                    } elseif ($grade->type === 'devoir' && isset($devoirs[$grade->sequence])) {
                        $devoirs[$grade->sequence] = $grade->value;
                    }
                }

                $listeEleves[] = [
                    'student'   => $student,
                    'interros'  => $interros,
                    'devoirs'   => $devoirs,
                ];
            }

            // 5) Génération PDF
            $pdf = Pdf::loadView('censeur.notes.pdf.liste_eleves_pdf', [
                'classe'       => $classe,
                'subject'      => $subject,
                'subjectPivot' => $subjectPivot,
                'trimestre'    => $trimestre,
                'activeYear'   => $activeYear,
                'listeEleves'  => $listeEleves,
                'dateDownload' => now()->locale('fr')->isoFormat('D MMMM YYYY'),
            ])->setPaper('a4', 'landscape');

            $nomClasse  = str_replace([' ', '/'], '_', $classe->name);
            $nomMatiere = str_replace([' ', '/'], '_', $subject->name);

            return $pdf->download("Liste_Eleves_{$nomClasse}_{$nomMatiere}_T{$trimestre}.pdf");

        } catch (\Exception $e) {
            return back()->with('error', 'Impossible de générer la liste : ' . $e->getMessage());
        }
    }

    public function exportListeRecursivePDF(int $trimestre) {
        try {
            // 1) Année académique active
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
 
            // 2) Toutes les affectations enseignant-matière-classe de cette année
            $assignments = ClassTeacherSubject::with(['teacher', 'subject', 'classe'])
                ->where('academic_year_id', $activeYear->id)
                ->whereNotNull('teacher_id')
                ->get();
 
            // 3) Regroupement à 2 niveaux : enseignant → classe → matières
            //
            //    Structure :
            //    [
            //      teacher_id => [
            //        'teacher'  => User,
            //        'classes'  => [
            //          class_id => [
            //            'classe'   => Classe,
            //            'matieres' => [
            //              [ 'subject' => Subject, 'details' => string ]
            //            ]
            //          ]
            //        ]
            //      ]
            //    ]
 
            $enseignantsManquants = [];
 
            foreach ($assignments as $assignment) {
                if (! $assignment->teacher || ! $assignment->classe || ! $assignment->subject) {
                    continue;
                }
 
                $teacherId = $assignment->teacher_id;
                $classId   = $assignment->class_id;
                $subjectId = $assignment->subject_id;
 
                // Compter les interrogations distinctes saisies
                $nbInterros = Grade::where('academic_year_id', $activeYear->id)
                    ->where('class_id',   $classId)
                    ->where('subject_id', $subjectId)
                    ->where('trimestre',  $trimestre)
                    ->where('type',       'interrogation')
                    ->distinct('sequence')
                    ->count('sequence');
 
                // Compter les devoirs distincts saisis
                $nbDevoirs = Grade::where('academic_year_id', $activeYear->id)
                    ->where('class_id',   $classId)
                    ->where('subject_id', $subjectId)
                    ->where('trimestre',  $trimestre)
                    ->where('type',       'devoir')
                    ->distinct('sequence')
                    ->count('sequence');
 
                // Tout complet → on passe
                if ($nbInterros >= 2 && $nbDevoirs >= 2) {
                    continue;
                }
 
                // Construire la description des notes manquantes
                $details = [];
                if ($nbInterros === 0 && $nbDevoirs === 0) {
                    $details[] = 'Aucune note saisie';
                } else {
                    if ($nbInterros < 2) {
                        $m = 2 - $nbInterros;
                        $details[] = $m . ' interrogation' . ($m > 1 ? 's' : '') . ' manquante' . ($m > 1 ? 's' : '');
                    }
                    if ($nbDevoirs < 2) {
                        $m = 2 - $nbDevoirs;
                        $details[] = $m . ' devoir' . ($m > 1 ? 's' : '') . ' manquant' . ($m > 1 ? 's' : '');
                    }
                }
                $detailStr = implode(', ', $details);
 
                // Initialiser l'entrée enseignant
                if (! isset($enseignantsManquants[$teacherId])) {
                    $enseignantsManquants[$teacherId] = [
                        'teacher' => $assignment->teacher,
                        'classes' => [],
                    ];
                }
 
                // Initialiser la sous-entrée classe
                if (! isset($enseignantsManquants[$teacherId]['classes'][$classId])) {
                    $enseignantsManquants[$teacherId]['classes'][$classId] = [
                        'classe'   => $assignment->classe,
                        'matieres' => [],
                    ];
                }
 
                // Ajouter la matière manquante pour cette classe
                $enseignantsManquants[$teacherId]['classes'][$classId]['matieres'][] = [
                    'subject' => $assignment->subject,
                    'details' => $detailStr,
                ];
            }
 
            // 4) Trier par nom d'enseignant (alphabétique)
            uasort($enseignantsManquants, function ($a, $b) {
                return strcmp(
                    strtolower($a['teacher']->name ?? ''),
                    strtolower($b['teacher']->name ?? '')
                );
            });
 
            // Réindexer + convertir les sous-tableaux 'classes' en tableaux ordonnés
            $listeFinale = [];
            foreach (array_values($enseignantsManquants) as $entry) {
                $entry['classes'] = array_values($entry['classes']);
                $listeFinale[] = $entry;
            }
 
            // 5) Génération du PDF
            $pdf = Pdf::loadView('censeur.notes.pdf.liste_recursive_pdf', [
                'listeFinale'  => $listeFinale,
                'trimestre'    => $trimestre,
                'activeYear'   => $activeYear,
                'dateDownload' => now()->locale('fr')->isoFormat('D MMMM YYYY'),
            ])->setPaper('a4', 'portrait');
 
            return $pdf->download("Liste_Recursive_Notes_Manquantes_T{$trimestre}.pdf");
 
        } catch (\Exception $e) {
            Log::error('exportListeRecursivePDF error: ' . $e->getMessage());
            return back()->with('error', 'Impossible de générer la liste récursive : ' . $e->getMessage());
        }
    }

}


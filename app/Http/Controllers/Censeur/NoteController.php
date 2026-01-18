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


    class NoteController extends Controller{
        // Liste toutes les classes du secondaire
        public function index(){
            $classes = Classe::where('entity_id', 3)->get();

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

                // 🔹 Récupération des notes
                $grades = Grade::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();

                // 🔹 Conduite et punitions
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

                // Liste des matières littéraires
                $matieresLitteraires = ['COMMUNICATION ECRITE', 'LECTURE', 'ANGLAIS', 'HISTOIRE-GEOGRAPHIE'];
                // Liste des matières scientifiques
                $matieresScientifiques = ['MATHEMATIQUES', 'PCT', 'SVT'];

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
                        ->filter()
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
                        } else {
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

                    $bulletin[] = [
                        'subject' => strtoupper($subject->name),
                        'coef' => $coef,
                        'interros' => $interrosFormatted,
                        'devoirs' => $devoirsFormatted,
                        'moyenneInterro' => $moyenneInterro !== null ? number_format($moyenneInterro, 2, ',', '') : '-',
                        'moyenne' => $moyenneMatiere !== null ? number_format($moyenneMatiere, 2, ',', '') : '-',
                        'moyCoeff' => $moyCoeff !== null ? number_format($moyCoeff, 2, ',', '') : '-',
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
                $moyenneAutres = !empty($moyennesAutres) ? 
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

                // 🔹 Calcul du rang et statistiques de la classe
                $classStudents = $classe->students;
                $moyennesGeneralesClasse = [];

                foreach ($classStudents as $st) {
                    $stGrades = Grade::where('student_id', $st->id)
                        ->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id)
                        ->where('trimestre', $trimestre)
                        ->get();

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
                            ->filter()
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

                    // Conduite de l'élève
                    $stConduct = Conduct::where('student_id', $st->id)
                        ->where('trimestre', $trimestre)
                        ->where('academic_year_id', $activeYear->id)
                        ->first();

                    $stPunishments = Punishment::where('student_id', $st->id)
                        ->where('academic_year_id', $activeYear->id)
                        ->get();
                    $stPunishHours = $stPunishments->sum('hours');

                    $stConductGrade = $stConduct ? $stConduct->grade : 0;
                    $stConductFinal = max(0, $stConductGrade - ($stPunishHours / 2));

                    // Ajouter la conduite
                    if ($stConductFinal > 0) {
                        $stTotalPoints += $stConductFinal;
                        $stTotalCoef += 1;
                    }

                    // Calcul moyenne générale élève
                    if ($stTotalCoef > 0) {
                        $moyennesGeneralesClasse[$st->id] = round($stTotalPoints / $stTotalCoef, 2);
                    }
                }

                // Calcul des statistiques de la classe
                $plusForte = !empty($moyennesGeneralesClasse) ? max($moyennesGeneralesClasse) : 0;
                $plusFaible = !empty($moyennesGeneralesClasse) ? min($moyennesGeneralesClasse) : 0;
                $moyClasse = !empty($moyennesGeneralesClasse) ? 
                    round(array_sum($moyennesGeneralesClasse) / count($moyennesGeneralesClasse), 2) : 0;

                // Calcul du rang
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
                    'moyenneAutres' => $formatNumber($moyenneAutres),
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
                //\Log::error('Erreur PDF Bulletin: ' . $e->getMessage());
            // \Log::error($e->getTraceAsString());
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
                            ->filter()
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
        
        public function trimestres($id){
            $classe = Classe::findOrFail($id);
            $coef = ClassTeacherSubject::where('class_id', $id)->first();
            $matieres = $classe->matieres;
            $trimestres = [1, 2, 3];

            return view('censeur.classes.notes.trimestres', compact('classe', 'trimestres', 'matieres', 'coef'));
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
                return back()->with('error', 'Association classe–matière non trouvée pour cette année académique.');
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
                                                ->filter()
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
            // 🔹 Année académique active
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
            
            // 🔹 Classe avec tous les élèves validés
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
            $matieresLitteraires = ['COMMUNICATION ECRITE', 'LECTURE', 'ANGLAIS', 'HISTOIRE-GEOGRAPHIE'];
            $matieresScientifiques = ['MATHEMATIQUES', 'PCT', 'SVT'];
            
            // Formatage des nombres
            $formatNumber = function($value) {
                if ($value === null || $value === 0) {
                    return '0,00';
                }
                return number_format($value, 2, ',', '');
            };
            
            // 🔹 Préparer les données pour tous les élèves
            $allBulletinsData = [];
            
            foreach ($classe->students as $student) {
                $studentId = $student->id;
                
                // 🔹 Récupération des notes pour cet élève
                $grades = Grade::where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();
                
                // 🔹 Conduite et punitions
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
                        ->filter()
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
                        } else {
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
                    
                    $bulletin[] = [
                        'subject' => strtoupper($subject->name),
                        'coef' => $coef,
                        'interros' => $interrosFormatted,
                        'devoirs' => $devoirsFormatted,
                        'moyenneInterro' => $moyenneInterro !== null ? number_format($moyenneInterro, 2, ',', '') : '-',
                        'moyenne' => $moyenneMatiere !== null ? number_format($moyenneMatiere, 2, ',', '') : '-',
                        'moyCoeff' => $moyCoeff !== null ? number_format($moyCoeff, 2, ',', '') : '-',
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
                $moyenneAutres = !empty($moyennesAutres) ? 
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
                
                // 🔹 Calcul du rang et statistiques de la classe pour TOUS les élèves
                $moyennesGeneralesClasse = [];
                
                foreach ($classe->students as $st) {
                    $stGrades = Grade::where('student_id', $st->id)
                        ->where('class_id', $classId)
                        ->where('academic_year_id', $activeYear->id)
                        ->where('trimestre', $trimestre)
                        ->get();
                    
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
                            ->filter()
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
                    
                    // Conduite de l'élève
                    $stConduct = Conduct::where('student_id', $st->id)
                        ->where('trimestre', $trimestre)
                        ->where('academic_year_id', $activeYear->id)
                        ->first();
                    
                    $stPunishments = Punishment::where('student_id', $st->id)
                        ->where('academic_year_id', $activeYear->id)
                        ->get();
                    $stPunishHours = $stPunishments->sum('hours');
                    
                    $stConductGrade = $stConduct ? $stConduct->grade : 0;
                    $stConductFinal = max(0, $stConductGrade - ($stPunishHours / 2));
                    
                    // Ajouter la conduite
                    if ($stConductFinal > 0) {
                        $stTotalPoints += $stConductFinal;
                        $stTotalCoef += 1;
                    }
                    
                    // Calcul moyenne générale élève
                    if ($stTotalCoef > 0) {
                        $moyennesGeneralesClasse[$st->id] = round($stTotalPoints / $stTotalCoef, 2);
                    }
                }
                
                // Calcul des statistiques de la classe
                $plusForte = !empty($moyennesGeneralesClasse) ? max($moyennesGeneralesClasse) : 0;
                $plusFaible = !empty($moyennesGeneralesClasse) ? min($moyennesGeneralesClasse) : 0;
                $moyClasse = !empty($moyennesGeneralesClasse) ? 
                    round(array_sum($moyennesGeneralesClasse) / count($moyennesGeneralesClasse), 2) : 0;
                
                // Calcul du rang pour cet élève
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
                    'moyenneAutres' => $formatNumber($moyenneAutres),
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
            
        } catch (\Exception $e) {
        // \Log::error('Erreur génération tous les bulletins: ' . $e->getMessage());
        // \Log::error($e->getTraceAsString());
            return back()->with('error', 'Impossible de générer le PDF de tous les bulletins: ' . $e->getMessage());
        }
    }

}


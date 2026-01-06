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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Conduct;
use App\Models\Punishment;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NotesTrimestreExport;
use Carbon\Carbon;
use App\Models\NoteEditPermission;
 


    class NoteController extends Controller{
        // Liste toutes les classes du secondaire
        public function index(){
            $classes = Classe::where('entity_id', 3)->get();

            return view('censeur.classes.notes.index', compact('classes'));
        }

        public function listeEleves($classId, $trimestre){
            try {
                // 1) Année académique active
                $activeYear = AcademicYear::where('active', true)->firstOrFail();

                // 2) Classe et étudiants validés
                $classe = Classe::with(['students' => function ($query) use ($activeYear) {
                    $query->where('is_validated', 1)
                        ->where('academic_year_id', $activeYear->id)
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])->findOrFail($classId);

                // 3) Matières de la classe pour cette année
                $subjects = Subject::where('classe_id', $classId)
                                    ->where('academic_year_id', $activeYear->id)
                                    ->get();

                // 4) Notes (grades)
                $grades = Grade::whereIn('student_id', $classe->students->pluck('id'))
                                ->whereIn('subject_id', $subjects->pluck('id'))
                                ->where('trimestre', $trimestre)
                                ->where('academic_year_id', $activeYear->id)
                                ->get();

                // 5) Conduites et punitions
                $conducts = Conduct::where('academic_year_id', $activeYear->id)
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

                // 7) Préparer les notes
                $gradesData = [];
                foreach ($classe->students as $student) {
                    foreach ($subjects as $subject) {
                        $studentGrades = $grades->where('student_id', $student->id)
                                                ->where('subject_id', $subject->id);

                        $interros = $studentGrades->where('type', 'interrogation')
                                                ->pluck('value', 'sequence')->toArray();

                        $devoirs = $studentGrades->where('type', 'devoir')
                                                ->pluck('value', 'sequence')->toArray();

                        $moyenneInterro = count($interros) ? round(array_sum($interros)/count($interros), 2) : null;
                        $moyenneDevoir = count($devoirs) ? round(array_sum($devoirs)/count($devoirs), 2) : null;

                        $coef = $subject->coefficient ?? 1;
                        $moyenne = $moyenneMat = null;

                        if ($moyenneInterro !== null && $moyenneDevoir !== null) {
                            $moyenne = round(($moyenneInterro + $moyenneDevoir) / 2, 2);
                            $moyenneMat = round($moyenne * $coef, 2);
                        }

                        $gradesData[$student->id][$subject->id] = [
                            'interros' => $interros,
                            'devoirs' => $devoirs,
                            'moyenneInterro' => $moyenneInterro,
                            'moyenneDevoir' => $moyenneDevoir,
                            'moyenne' => $moyenne,
                            'coef' => $coef,
                            'moyenneMat' => $moyenneMat,
                        ];
                    }

                    // Intégrer la conduite
                    $gradesData[$student->id]['conduite'] = [
                        'moyenne' => $conductData[$student->id] ?? 0,
                        'coef' => 1,
                        'moyenneMat' => ($conductData[$student->id] ?? 0) * 1,
                    ];
                }

                // 8) Calcul des moyennes générales et rangs
                $moyennes = [];
                foreach ($classe->students as $student) {
                    $totalCoef = 0;
                    $totalPoints = 0;

                    foreach ($gradesData[$student->id] as $mat) {
                        if (!empty($mat['moyenneMat']) && !empty($mat['coef'])) {
                            $totalCoef += $mat['coef'];
                            $totalPoints += $mat['moyenneMat'];
                        }
                    }

                    $moyGen = $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : 0;
                    $gradesData[$student->id]['moyenne_generale'] = $moyGen;
                    $moyennes[$student->id] = $moyGen;
                }

                arsort($moyennes);
                $rang = 1;
                foreach ($moyennes as $studentId => $moy) {
                    $gradesData[$studentId]['rang_general'] = $rang++;
                }

                // 9) Retour à la vue
                return view('censeur.classes.notes.liste_eleves', compact('classe', 'subjects', 'gradesData', 'conductData', 'trimestre', 'activeYear'));
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

                // Récupération de l'élève + ses notes du trimestre et de l'année active
                $student = Student::with(['grades' => function ($q) use ($activeYear, $trimestre) {
                        $q->where('academic_year_id', $activeYear->id)
                        ->where('trimestre', $trimestre);
                }])->findOrFail($studentId);

                $effectif = Classe::with(['students' => function ($query) use ($activeYear) {
                    $query->where('is_validated', 1)
                        ->where('academic_year_id', $activeYear->id)
                        ->orderBy('last_name')
                        ->orderBy('first_name');
                }])->findOrFail($classId);

                // Récupération de la classe et des matières
                $classe = Classe::findOrFail($classId);
                $subjects = Subject::where('classe_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                // Conduite et punitions
                $conduct = Conduct::where('student_id', $student->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->value('grade') ?? 0;

                $punishHours = Punishment::where('student_id', $student->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->sum('hours');

                $conduiteFinale = max(0, $conduct - ($punishHours / 2));

                $bulletin = [];
                $totalMoyCoeff = 0;
                $totalCoeff = 0;

                foreach ($subjects as $subject) {
                    $coef = $subject->coefficient ?? 1;

                    // Récupérer toutes les notes d’interrogations (1 à 5)
                    $notesInterro = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $note = $student->grades->firstWhere(fn($n)
                            => $n->subject_id == $subject->id && $n->type == 'interrogation' && $n->sequence == $i);
                        $notesInterro[$i] = $note->value ?? null;
                    }

                    // Récupérer les devoirs (1 et 2)
                    $notesDevoir = [];
                    for ($i = 1; $i <= 2; $i++) {
                        $note = $student->grades->firstWhere(fn($n)
                            => $n->subject_id == $subject->id && $n->type == 'devoir' && $n->sequence == $i);
                        $notesDevoir[$i] = $note->value ?? null;
                    }

                    // Moyennes
                    $moyInterro = collect($notesInterro)->filter()->avg();
                    $moyDevoir = collect($notesDevoir)->filter()->avg();
                    $moyenne = collect(array_merge($notesInterro, $notesDevoir))->filter()->avg();

                    $moyenne = $moyenne ? round($moyenne, 2) : null;
                    $moyCoeff = $moyenne ? round($moyenne * $coef, 2) : 0;

                    // Moyenne générale matière
                    $interroValues = collect($notesInterro)->filter();
                    $devoirValues = collect($notesDevoir)->filter();

                    $moyenne = null;

                    if ($interroValues->isNotEmpty() || $devoirValues->isNotEmpty()) {

                        $notesFinales = collect();

                        // Moyenne des interrogations (comptée comme une seule note)
                        if ($interroValues->isNotEmpty()) {
                            $notesFinales->push($interroValues->avg());
                        }

                        // Ajouter toutes les notes de devoirs
                        foreach ($devoirValues as $d) {
                            $notesFinales->push($d);
                        }

                        $moyenne = round($notesFinales->avg(), 2);
                    }

                    $moyCoeff = $moyenne !== null ? round($moyenne * $coef, 2) : 0;

                    // Appréciation par matière
                    $appreciation = '-';
                    if ($moyenne !== null) {
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
                        'moyenne' => $moyenne,
                        'moyCoeff' => $moyCoeff,
                        'appreciation' => $appreciation,
                    ];

                    $totalCoeff += $coef;
                    $totalMoyCoeff += $moyCoeff;
                }

                // Moyenne générale
                $moyenneGenerale = $totalCoeff > 0 ? round($totalMoyCoeff / $totalCoeff, 2) : null;

                // Appréciation générale
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
                else {
                    $appreciationGenerale = '-';
                }

                // Calcul du rang général
                $classStudents = Student::where('class_id', $classId)
                                        ->where('is_validated', 1)
                                        ->where('academic_year_id', $activeYear->id)
                                        ->get();
                $classeMoyennes = [];
                foreach ($classStudents as $st) {
                    $moy = $this->calculMoyenne($st->id, $classId, $trimestre, $activeYear->id);
                    if ($moy) $classeMoyennes[$st->id] = $moy;
                }
                arsort($classeMoyennes);
                $rang = array_search($studentId, array_keys($classeMoyennes)) + 1;

                return view('censeur.classes.notes.bulletin', compact(
                    'student', 'classe', 'bulletin', 'moyenneGenerale',
                    'appreciationGenerale', 'conduiteFinale', 'rang', 'trimestre', 'effectif'
                ));

            } 
            catch (\Exception $e) {
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

        public function showClassNote($classId, $trimestre, $subjectId){
            // 1 Année académique active
            $activeYear = AcademicYear::where('active', true)->first();
            if (!$activeYear) {
                return back()->with('error', 'Aucune année académique active trouvée.');
            }
            
            // 2 Récupérer la classe et ses étudiants valides pour l’année active
            $classe = Classe::with(['students' => function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id)
                ->where('is_validated', 1)
                ->orderBy('last_name')
                ->orderBy('first_name');
            }])->find($classId);

            if (!$classe) {
                return back()->with('error', "Classe introuvable.");
            }
            
            // 3 Récupérer la matière concernée
            $subject = ClassTeacherSubject::where('subject_id', $subjectId)
                ->where('class_id', $classId)
                ->first();

            if (!$subject) {
                return back()->with('error', "Matière introuvable.");
            }

            // 4 Préparation des données de notes
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

                // Moyennes
                $moyenneInterro = count($interros) > 0 ? round(array_sum($interros) / count($interros), 2) : null;
                $moyenneDevoir = count($devoirs) > 0 ? round(array_sum($devoirs) / count($devoirs), 2) : null;

                $coef = $subject->coefficient ?? 1;

                $moyenne = null;
                $moyenneMat = null;

                if ($moyenneInterro !== null && $moyenneDevoir !== null) {
                    $moyenne = round(($moyenneInterro + $moyenneDevoir) / 2, 2);
                    $moyenneMat = round($moyenne * $coef, 2);
                }

                $gradesData[$student->id][$subjectId] = [
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenneInterro' => $moyenneInterro,
                    'moyenneDevoir' => $moyenneDevoir,
                    'moyenne' => $moyenne,
                    'coef' => $coef,
                    'moyenneMat' => $moyenneMat,
                    'subject' => $subject,
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
                'subjects' => $subject,
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
            // 1 Récupération de l’année académique active
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

            // 3 Matière concernée
            $subject = ClassTeacherSubject::where('subject_id', $subjectId)
                ->where('class_id', $classId)
                ->first();

            if (!$subject) {
                return back()->with('error', "Matière introuvable.");
            }

            // 4 Récupération et calcul des notes
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

                $moyenneInterro = count($interros) > 0
                    ? round(array_sum($interros) / count($interros), 2)
                    : null;
                $moyenneDevoir = count($devoirs) > 0 ? round(array_sum($devoirs) / count($devoirs), 2) : null;
                $coef = $subject->coefficient ?? 1;

                $moyenne = null;
                $moyenneMat = null;

                $notesFinales = [];

                // Moyenne des interrogations (1 seule note)
                if ($moyenneInterro !== null) {
                    $notesFinales[] = $moyenneInterro;
                }

                // Ajouter chaque devoir individuellement
                foreach ($devoirs as $note) {
                    $notesFinales[] = $note;
                }

                if (count($notesFinales) > 0) {
                    $moyenne = round(array_sum($notesFinales) / count($notesFinales), 2);
                    $moyenneMat = round($moyenne * $coef, 2);
                }


                $gradesData[$student->id][$subjectId] = [
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenneInterro' => $moyenneInterro,
                    'moyenneDevoir' => $moyenneDevoir,
                    'moyenne' => $moyenne,
                    'coef' => $coef,
                    'moyenneMat' => $moyenneMat,
                    'subject' => $subject,
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
                'subjects' => $subject,
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

                // Matières
                $subjects = Subject::where('classe_id', $classId)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                // Notes
                $grades = Grade::whereIn('student_id', $classe->students->pluck('id'))
                    ->whereIn('subject_id', $subjects->pluck('id'))
                    ->where('trimestre', $trimestre)
                    ->where('academic_year_id', $activeYear->id)
                    ->get();

                // Conduites & punitions
                $conducts = Conduct::where('academic_year_id', $activeYear->id)
                    ->whereIn('student_id', $classe->students->pluck('id'))
                    ->get()
                    ->keyBy('student_id');

                $punishments = Punishment::where('academic_year_id', $activeYear->id)
                    ->whereIn('student_id', $classe->students->pluck('id'))
                    ->selectRaw('student_id, SUM(hours) as total_hours')
                    ->groupBy('student_id')
                    ->get()
                    ->keyBy('student_id');

                // Calculs
                $conductData = [];
                $gradesData = [];
                $moyennes = [];

                foreach ($classe->students as $student) {
                    $studentId = $student->id;
                    $conduct = $conducts[$studentId]->grade ?? 0;
                    $punishHours = $punishments[$studentId]->total_hours ?? 0;
                    $conductFinal = max(0, $conduct - ($punishHours / 2));
                    $conductData[$studentId] = round($conductFinal, 2);

                    foreach ($subjects as $subject) {
                        $studentGrades = $grades->where('student_id', $studentId)
                                                ->where('subject_id', $subject->id);

                        $interros = $studentGrades->where('type', 'interrogation')->pluck('value')->toArray();
                        $devoirs = $studentGrades->where('type', 'devoir')->pluck('value')->toArray();

                        $moyInterro = count($interros) ? round(array_sum($interros)/count($interros), 2) : null;
                        $moyDevoir = count($devoirs) ? round(array_sum($devoirs)/count($devoirs), 2) : null;

                        $coef = $subject->coefficient ?? 1;
                        $moyenne = $moyenneMat = null;

                        if ($moyInterro !== null && $moyDevoir !== null) {
                            $moyenne = round(($moyInterro + $moyDevoir) / 2, 2);
                            $moyenneMat = round($moyenne * $coef, 2);
                        }

                        $gradesData[$studentId][$subject->id] = [
                            'moyInterro' => $moyInterro,
                            'moyDevoir' => $moyDevoir,
                            'moyenne' => $moyenne,
                            'coef' => $coef,
                            'moyenneMat' => $moyenneMat,
                        ];
                    }

                    // Ajouter la conduite
                    $gradesData[$studentId]['conduite'] = [
                        'moyenne' => $conductData[$studentId],
                        'coef' => 1,
                        'moyenneMat' => $conductData[$studentId] * 1,
                    ];
                }

                // Calcul moyennes générales & rangs
                foreach ($classe->students as $student) {
                    $totalCoef = 0;
                    $totalPoints = 0;

                    foreach ($gradesData[$student->id] as $matiere) {
                        if (!empty($matiere['moyenneMat']) && !empty($matiere['coef'])) {
                            $totalCoef += $matiere['coef'];
                            $totalPoints += $matiere['moyenneMat'];
                        }
                    }

                    $moyGen = $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : 0;
                    $gradesData[$student->id]['moyenne_generale'] = $moyGen;
                    $moyennes[$student->id] = $moyGen;
                }

                arsort($moyennes);
                $rang = 1;
                foreach ($moyennes as $sid => $moy) {
                    $gradesData[$sid]['rang_general'] = $rang++;
                }

                // Génération du PDF
                $pdf = Pdf::loadView('censeur.notes.pdf.notes_trimestre', [
                    'classe' => $classe,
                    'subjects' => $subjects,
                    'gradesData' => $gradesData,
                    'conductData' => $conductData,
                    'trimestre' => $trimestre,
                    'activeYear' => $activeYear,
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
        
    public function downloadPdf($classId, $studentId, $trimestre){
        try {
            // 🔹 Année académique active
            $activeYear = AcademicYear::where('active', true)->firstOrFail();

            // 🔹 Élève et classe
            $student = Student::findOrFail($studentId);
            $classe = Classe::with('students')->findOrFail($classId);

            // 🔹 Récupérer les matières
            $subjects = Subject::whereHas('classes', fn($q) => $q->where('classes.id', $classId))->get();

            // 🔹 Récupération des notes (tu peux adapter selon ton modèle)
            $grades = $student->grades()
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $trimestre)
                ->get();

            // 🔹 Calcul des moyennes par matière
            $bulletin = [];
            $totalPoints = 0;
            $totalCoef = 0;

            foreach ($subjects as $subject) {
                $coef = $subject->coefficient ?? 1;

                // Récupération interrogations
                $interros = [];
                for ($i = 1; $i <= 5; $i++) {
                    $note = $grades->first(fn($n) => $n->subject_id == $subject->id && $n->type == 'interrogation' && $n->sequence == $i);
                    $interros[$i] = $note->value ?? null;
                }

                // Devoirs
                $devoirs = [];
                for ($i = 1; $i <= 2; $i++) {
                    $note = $grades->first(fn($n) => $n->subject_id == $subject->id && $n->type == 'devoir' && $n->sequence == $i);
                    $devoirs[$i] = $note->value ?? null;
                }

                // Moyenne générale matière
                $interroValues = collect($interros)->filter();
                $devoirValues = collect($devoirs)->filter();

                $moyenne = null;

                if ($interroValues->isNotEmpty() || $devoirValues->isNotEmpty()) {

                    $notesFinales = collect();

                    // Moyenne des interrogations (comptée comme une seule note)
                    if ($interroValues->isNotEmpty()) {
                        $notesFinales->push($interroValues->avg());
                    }

                    // Ajouter toutes les notes de devoirs
                    foreach ($devoirValues as $d) {
                        $notesFinales->push($d);
                    }

                    $moyenne = round($notesFinales->avg(), 2);
                }

                $moyCoeff = $moyenne !== null ? round($moyenne * $coef, 2) : 0;


                // Appréciation par matière
                $appreciation = $this->getAppreciation($moyenne);

                $bulletin[] = [
                    'subject' => $subject->name,
                    'coef' => $coef,
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenne' => $moyenne,
                    'moyCoeff' => $moyCoeff,
                    'appreciation' => $appreciation,
                ];

                $totalPoints += $moyCoeff;
                $totalCoef += $coef;
            }

            $moyenneGenerale = $totalCoef ? round($totalPoints / $totalCoef, 2) : null;
            $appreciationGenerale = $this->getAppreciation($moyenneGenerale);

            // 🔹 Conduite
            $conduct = Conduct::where('student_id', $studentId)
                ->where('academic_year_id', $activeYear->id)
                ->first();
            $punishment = Punishment::where('student_id', $studentId)
                ->where('academic_year_id', $activeYear->id)
                ->sum('hours');

            $conduiteFinale = $conduct ? max(0, $conduct->grade - ($punishment / 2)) : '-';

            // 🔹 Rang (optionnel)
            $rang = '-';

            // 🔹 Rendu PDF
            $pdf = Pdf::loadView('censeur.classes.notes.bulletin_pdf', [
                'student' => $student,
                'classe' => $classe,
                'bulletin' => $bulletin,
                'trimestre' => $trimestre,
                'moyenneGenerale' => $moyenneGenerale,
                'appreciationGenerale' => $appreciationGenerale,
                'conduiteFinale' => $conduiteFinale,
                'rang' => $rang,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("Bulletin_{$student->last_name}_T{$trimestre}.pdf");

        } 
        catch (\Exception $e) {
            //Log::error('Erreur PDF Bulletin : ' . $e->getMessage());
           
            return back()->with('error', 'Impossible de générer le PDF du bulletin.');
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




}


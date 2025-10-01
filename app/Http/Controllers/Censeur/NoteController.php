<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\NotePermission;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Grade;

    class NoteController extends Controller{
        // Liste toutes les classes du secondaire
        public function index(){
            $classes = Classe::where('entity_id', 3)->get();

            return view('censeur.classes.notes.index', compact('classes'));
        }

        public function listeEleves($classId, $trimestre){
            $classe = Classe::with('students')->findOrFail($classId);

            return view('censeur.classes.notes.liste_eleves', compact('classe', 'trimestre'));
        }

        // Affiche les trimestres d’une classe
        public function trimestres($id){
            $classe = Classe::findOrFail($id);

            // Récupérer les matières associées à la classe
            $matieres = $classe->matieres; // collection de matières

            // Préparer les 3 trimestres
            $trimestres = [1, 2, 3];

            return view('censeur.classes.notes.trimestres', compact('classe', 'trimestres', 'matieres'));
        }


        // Gérer les permissions de saisie des notes pour une classe
        public function permissions($classId){
            $classe = Classe::findOrFail($classId);

            // On charge ou crée par défaut les permissions
            $permissions = NotePermission::firstOrCreate(
                ['class_id' => $classId, 'trimestre' => 1],
                ['is_open' => false]
            );
            for ($i = 1; $i <= 3; $i++) {
                NotePermission::firstOrCreate(['class_id' => $classId, 'trimestre' => $i]);
            }

            $permissions = NotePermission::where('class_id', $classId)->get();

            return view('censeur.permissions.index', compact('classe', 'permissions'));
        }

        // Toggle autorisation/revocation
        public function toggle($classId, $trimestre){
            $permission = NotePermission::where('class_id', $classId)
                ->where('trimestre', $trimestre)
                ->firstOrFail();

            $permission->is_open = !$permission->is_open;
            $permission->save();

            return back()->with('success', 'Mise à jour effectuée avec succès.');
        }

        public function bulletin($classId, $studentId, $trimestre){
            $activeYear = AcademicYear::where('active', true)->firstOrFail();

            $student = Student::with(['grades' => function($q) use ($activeYear, $trimestre) {
                $q->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $trimestre);
            }])->findOrFail($studentId);

            // Récupérer toutes les matières de la classe
            //$subjects = \App\Models\Subject::whereHas('classes', fn($q) => $q->where('classes.id', $classId))->get();
            $subjects = Subject::whereHas('classes', function ($q) use ($classId) {
                $q->where('classes.id', $classId);
            })->get();

            $bulletin = [];
            $totalMoyCoeff = 0;
            $totalCoeff = 0;

            foreach ($subjects as $subject) {
                $notesInterro = [];
                $notesDevoir = [];
                $coef = $subject->coefficient ?? 1;

                // Récupérer les notes d’interrogations 1 → 5
                for ($i=1; $i<=5; $i++) {
                    $note = $student->grades->firstWhere(fn($n) => $n->subject_id == $subject->id && $n->type == 'interrogation' && $n->sequence == $i);
                    $notesInterro[$i] = $note->value ?? null;
                }

                // Moyenne des interros
                $moyInterro = collect($notesInterro)->filter()->avg();

                // Devoirs 1 → 2
                for ($i=1; $i<=2; $i++) {
                    $note = $student->grades->firstWhere(fn($n) => $n->subject_id == $subject->id && $n->type == 'devoir' && $n->sequence == $i);
                    $notesDevoir[$i] = $note->value ?? null;
                }

                // Moyenne générale matière
                $allNotes = collect(array_merge($notesInterro, $notesDevoir))->filter();
                $moyenne = $allNotes->isNotEmpty() ? round($allNotes->avg(), 2) : null;

                // Moyenne pondérée
                $moyCoeff = $moyenne ? $moyenne * $coef : 0;

                $bulletin[] = [
                    'subject' => $subject->name,
                    'coef' => $coef,
                    'interros' => $notesInterro,
                    'moyInterro' => $moyInterro,
                    'devoirs' => $notesDevoir,
                    'moyenne' => $moyenne,
                    'moyCoeff' => $moyCoeff,
                ];

                $totalMoyCoeff += $moyCoeff;
                $totalCoeff += $coef;
            }

            $moyenneGenerale = $totalCoeff ? round($totalMoyCoeff / $totalCoeff, 2) : null;

            // Calcul du rang dans la classe
            $classStudents = Student::where('class_id', $classId)->get();
            $classeMoyennes = [];
            foreach ($classStudents as $st) {
                $moy = $this->calculMoyenne($st->id, $classId, $trimestre, $activeYear->id);
                if ($moy) $classeMoyennes[$st->id] = $moy;
            }
            arsort($classeMoyennes); // tri décroissant
            $rang = array_search($studentId, array_keys($classeMoyennes)) + 1;

            return view('censeur.classes.notes.bulletin', compact('student', 'bulletin', 'moyenneGenerale', 'rang', 'trimestre'));
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
                // Si aucune année active, renvoie une vue vide avec un message
                return view('censeur.classes.index', [
                    'classes' => collect(), // collection vide
                    'activeYear' => null,
                    'error' => "Aucune année scolaire active n’a été trouvée."
                ]);
            }
        
            // Récupère uniquement les matières de l'année active
            $subjects = Subject::where('classe_id', $classe)->get();

            $classe = Classe::findOrFail($classe);
            $trimestre = $t;
        
            return view('censeur.classes.subject', compact('subjects', 'activeYear', 'classe', 'trimestre'));
        }

    public function notes_trimestre($classId, $trimestre){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $classe = Classe::with(['students.grades' => function ($q) use ($activeYear, $trimestre) {
            $q->where('academic_year_id', $activeYear->id)
            ->where('trimestre', $trimestre);
        }])->findOrFail($classId);

        // Vérifier si des notes existent
        $hasNotes = $classe->students->flatMap->grades->isNotEmpty();
        $subjects = Subject::where('classe_id', $classId)->get();
        $subject = ($subjects->first()->name);
        return view('censeur.notes.notes_trimestre', compact('classe', 'subject', 'activeYear', 'trimestre', 'hasNotes'));
    }

    public function showClassNote($classId, $trimestre, $subjectId){
        // 1) Récupération de l’année académique active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // 2) Récupérer la classe et ses étudiants
        $classe = Classe::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
        }])->find($classId);

        if (!$classe) {
            return back()->with('error', "Classe introuvable.");
        }

        // 3) Récupérer la matière
        
        $subject = Subject::where('name', $subjectId)->first();
        
        if (!$subject) {
            return back()->with('error', "Matière '$subjectId' introuvable.");
        }


        // 4) Préparation des données de notes par élève × matière
        $gradesData = [];

        foreach ($classe->students as $student) {
            $studentGrades = [];

            $grades = Grade::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
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
            
            $moyenneInterro = count($interros) > 0 ? round(array_sum($interros) / count($interros), 2) : null;
            $moyenneDevoir = count($devoirs) > 0 ? round(array_sum($devoirs) / count($devoirs), 2) : null;

            $coef = $subject->coefficient ?? 1;

            $moyenne = null;
            $moyenneMat = null;

            if ($moyenneInterro !== null && $moyenneDevoir !== null) {
                $moyenne = round(($moyenneInterro + $moyenneDevoir) / 2, 2);
                $moyenneMat = round($moyenne * $coef, 2);
            }

            $studentGrades[$subject->id] = [
                'interros' => $interros,
                'devoirs' => $devoirs,
                'moyenneInterro' => $moyenneInterro,
                'moyenneDevoir' => $moyenneDevoir,
                'moyenne' => $moyenne,
                'coef' => $coef,
                'moyenneMat' => $moyenneMat,
                'subject' => $subject,
            ];

            $gradesData[$student->id] = $studentGrades;
        }

        // 5) Envoi à la vue
        return view('censeur.notes.class_notes', [
            'classe' => $classe,
            'subject' => $subject,
            'gradesData' => $gradesData,
            'activeYear' => $activeYear,
            'trimestre'  => $trimestre,
        ]);
    }


}


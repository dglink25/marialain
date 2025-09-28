<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\NotePermission;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\Student;

    class NoteController extends Controller{
        // Liste toutes les classes du secondaire
        public function index()
        {
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

            // On prépare les 3 trimestres
            $trimestres = [1, 2, 3];

            return view('censeur.classes.notes.trimestres', compact('classe', 'trimestres'));
        }

        // Gérer les permissions de saisie des notes pour une classe
        public function permissions($classId)
        {
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
        public function toggle($classId, $trimestre)
        {
            $permission = NotePermission::where('class_id', $classId)
                ->where('trimestre', $trimestre)
                ->firstOrFail();

            $permission->is_open = !$permission->is_open;
            $permission->save();

            return back()->with('success', 'Mise à jour effectuée avec succès.');
        }

        public function bulletin($classId, $studentId, $trimestre)
    {
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
    private function calculMoyenne($studentId, $classId, $trimestre, $yearId)
    {
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

}


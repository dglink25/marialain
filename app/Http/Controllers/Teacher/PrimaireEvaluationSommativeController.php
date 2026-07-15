<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\PrimaireComposition;
use App\Models\PrimaireSommativeEvaluation;
use App\Models\PrimaireSommativeNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PrimaireEvaluationSommativeController extends Controller
{
    /** Configuration d'affichage par étape de la composition */
    private array $etapeConfig = [
        'a_venir'     => ['color' => 'blue',   'icon' => 'fas fa-clock',        'label' => 'Composition à venir'],
        'composition' => ['color' => 'amber',  'icon' => 'fas fa-pencil-ruler', 'label' => 'Composition en cours'],
        'saisie'      => ['color' => 'orange', 'icon' => 'fas fa-edit',         'label' => 'Saisie des notes en cours'],
        'termine'     => ['color' => 'green',  'icon' => 'fas fa-check-circle', 'label' => 'Terminé'],
    ];

    /**
     * Calcule l'étape actuelle d'une composition (à venir / composition / saisie / terminé)
     * et attache les infos d'affichage + le nombre de jours restants.
     */
    private function annoterComposition(PrimaireComposition $comp): PrimaireComposition
    {
        $today = Carbon::today();
        $cd = Carbon::parse($comp->composition_debut)->startOfDay();
        $cf = Carbon::parse($comp->composition_fin)->startOfDay();
        $sd = Carbon::parse($comp->saisie_debut)->startOfDay();
        $sf = Carbon::parse($comp->saisie_fin)->startOfDay();

        if ($today->lt($cd)) {
            $etape = 'a_venir';
            $joursRestants = $today->diffInDays($cd);
        } elseif ($today->between($cd, $cf)) {
            $etape = 'composition';
            $joursRestants = $today->diffInDays($cf);
        } elseif ($today->between($sd, $sf)) {
            $etape = 'saisie';
            $joursRestants = $today->diffInDays($sf);
        } else {
            $etape = 'termine';
            $joursRestants = 0;
        }

        $conf = $this->etapeConfig[$etape];

        $comp->etape         = $etape;
        $comp->etapeLabel    = $conf['label'];
        $comp->couleur       = $conf['color'];
        $comp->icone         = $conf['icon'];
        $comp->joursRestants = (int) $joursRestants;
        $comp->saisieOuverte = ($etape === 'saisie');

        return $comp;
    }

    /** Page principale : compositions programmées pour la classe + avancement de saisie par matière */
    public function index(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        // Compositions programmées qui concernent cette classe
        $compositions = PrimaireComposition::where('academic_year_id', $annee->id)
            ->get()
            ->filter(fn ($c) => in_array($classeId, $c->classe_ids ?? []))
            ->map(fn ($c) => $this->annoterComposition($c))
            ->sortBy('mois')
            ->values();

        // Matières de la classe
        $subjects = Subject::where('classe_id', $classeId)->orderBy('name')->get();

        // Évaluations déjà créées, groupées par "composition_id-subject_id" pour un accès rapide en vue
        $evaluations = PrimaireSommativeEvaluation::with('notes')
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->get()
            ->groupBy(fn ($e) => $e->composition_id . '-' . $e->subject_id);

        return view('teacher.primaire.sommative.index', compact(
            'classe', 'annee', 'compositions', 'subjects', 'evaluations'
        ));
    }

    /** Afficher le formulaire de saisie (ou la vue lecture seule) pour une matière + composition */
    public function saisie(int $classeId, int $compositionId, int $subjectId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        $composition = $this->annoterComposition(
            PrimaireComposition::where('academic_year_id', $annee->id)->findOrFail($compositionId)
        );

        if (!in_array($classeId, $composition->classe_ids ?? [])) {
            abort(403, "Cette composition ne concerne pas votre classe.");
        }

        $subject = Subject::where('classe_id', $classeId)->findOrFail($subjectId);

        $evaluation = PrimaireSommativeEvaluation::with('notes')
            ->where('composition_id', $compositionId)
            ->where('classe_id', $classeId)
            ->where('subject_id', $subjectId)
            ->first();

        $notesParEleve = $evaluation ? $evaluation->notes->keyBy('student_id') : collect();

        return view('teacher.primaire.sommative.saisie', compact(
            'classe', 'annee', 'composition', 'subject', 'evaluation', 'notesParEleve'
        ));
    }

    /**
     * Lecture seule des notes d'une matière pour une composition — accessible à tout moment
     * (même pendant la saisie), sans risque de modification accidentelle.
     */
    public function show(int $classeId, int $compositionId, int $subjectId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        $composition = $this->annoterComposition(
            PrimaireComposition::where('academic_year_id', $annee->id)->findOrFail($compositionId)
        );

        if (!in_array($classeId, $composition->classe_ids ?? [])) {
            abort(403, "Cette composition ne concerne pas votre classe.");
        }

        $subject = Subject::where('classe_id', $classeId)->findOrFail($subjectId);

        $evaluation = PrimaireSommativeEvaluation::with(['notes.student'])
            ->where('composition_id', $compositionId)
            ->where('classe_id', $classeId)
            ->where('subject_id', $subjectId)
            ->firstOrFail();

        $notesParEleve = $evaluation->notes->keyBy('student_id');

        return view('teacher.primaire.sommative.show', compact(
            'classe', 'annee', 'composition', 'subject', 'evaluation', 'notesParEleve'
        ));
    }

    /**
     * Récapitulatif : toutes les matières x tous les élèves, pour une composition donnée.
     */
    public function recap(int $classeId, int $compositionId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        $composition = $this->annoterComposition(
            PrimaireComposition::where('academic_year_id', $annee->id)->findOrFail($compositionId)
        );

        if (!in_array($classeId, $composition->classe_ids ?? [])) {
            abort(403, "Cette composition ne concerne pas votre classe.");
        }

        $subjects = Subject::where('classe_id', $classeId)->orderBy('name')->get();

        $evaluations = PrimaireSommativeEvaluation::with('notes')
            ->where('composition_id', $compositionId)
            ->where('classe_id', $classeId)
            ->get()
            ->keyBy('subject_id');

        // matrice[student_id][subject_id] = note brute (ou null)
        $matrice = [];
        foreach ($classe->students as $student) {
            foreach ($subjects as $subject) {
                $evaluation = $evaluations->get($subject->id);
                $note = null;
                if ($evaluation) {
                    $noteObj = $evaluation->notes->firstWhere('student_id', $student->id);
                    $note = $noteObj?->note;
                }
                $matrice[$student->id][$subject->id] = $note;
            }
        }

        return view('teacher.primaire.sommative.recap', compact(
            'classe', 'annee', 'composition', 'subjects', 'evaluations', 'matrice'
        ));
    }

    /** Enregistrer les notes saisies (création à la première saisie, puis mise à jour) */
    public function store(Request $request, int $classeId, int $compositionId, int $subjectId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::where('teacher_id', $user->id)->findOrFail($classeId);

        $composition = $this->annoterComposition(
            PrimaireComposition::where('academic_year_id', $annee->id)->findOrFail($compositionId)
        );

        if (!in_array($classeId, $composition->classe_ids ?? [])) {
            abort(403, "Cette composition ne concerne pas votre classe.");
        }

        // ── Verrou serveur : impossible de saisir/modifier hors période de saisie ──
        // (protection même si quelqu'un contourne l'interface ou soumet un formulaire obsolète)
        if (!$composition->saisieOuverte) {
            return back()->withErrors([
                'periode' => $composition->etape === 'termine'
                    ? "La période de saisie des notes pour cette composition est clôturée."
                    : "La période de saisie des notes pour cette composition n'est pas encore ouverte.",
            ]);
        }

        $subject = Subject::where('classe_id', $classeId)->findOrFail($subjectId);

        // Barème : figé dès la première saisie. Si l'évaluation existe déjà, on réutilise
        // son barème (le champ note_min du formulaire est alors ignoré/désactivé côté vue).
        $evaluation = PrimaireSommativeEvaluation::where('composition_id', $compositionId)
            ->where('classe_id', $classeId)
            ->where('subject_id', $subjectId)
            ->first();

        if ($evaluation) {
            $noteMax = (float) $evaluation->note_max;
        } else {
            $request->validate([
                'note_min' => 'required|in:5,10',
            ], [
                'note_min.required' => 'Veuillez choisir la note minimale.',
                'note_min.in'       => 'La note minimale doit être 5 ou 10.',
            ]);
            $noteMin = (int) $request->note_min;
            $noteMax = $noteMin * 2;
        }

        $request->validate([
            'notes'   => 'required|array',
            'notes.*' => "nullable|numeric|min:0|max:{$noteMax}",
        ], [
            'notes.*.max' => "Une note dépasse le barème /{$noteMax}.",
            'notes.*.min' => 'Une note ne peut pas être négative.',
        ]);

        if (!$evaluation) {
            $evaluation = PrimaireSommativeEvaluation::create([
                'composition_id'   => $compositionId,
                'classe_id'        => $classeId,
                'subject_id'       => $subjectId,
                'teacher_id'       => $user->id,
                'academic_year_id' => $annee->id,
                'note_min'         => $noteMin,
                'note_max'         => $noteMax,
            ]);
        }

        foreach ($request->notes as $studentId => $note) {
            PrimaireSommativeNote::updateOrCreate(
                ['evaluation_id' => $evaluation->id, 'student_id' => (int) $studentId],
                ['note' => ($note !== null && $note !== '') ? (float) $note : null]
            );
        }

        return redirect()
            ->route('teacher.classes.sommative', $classeId)
            ->with('success', "Notes de {$subject->name} enregistrées avec succès.");
    }
}
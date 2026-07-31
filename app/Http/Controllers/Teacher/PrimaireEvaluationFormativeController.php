<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\PrimaireFormativeEvaluation;
use App\Models\PrimaireFormativeNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PrimaireEvaluationFormativeController extends Controller
{
    /** Jours de la semaine ouvrés (Lundi → Samedi), dans l'ordre du calendrier */
    private array $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

    /**
     * Pour une classe donnée, construit la liste des matières programmées à l'emploi du
     * temps pour chaque jour de la semaine (Lundi → Samedi), afin de piloter dynamiquement
     * le champ "Matière" en fonction de la date choisie par l'enseignant.
     */
    private function matieresParJour(int $classeId)
    {
        $matieres = [];
        foreach ($this->joursSemaine as $jour) {
            $matieres[$jour] = Schedule::where('classe_id', $classeId)
                ->where('day_of_week', $jour)
                ->with('subject')
                ->get()
                ->map(fn ($s) => $s->subject)
                ->filter()
                ->unique('id')
                ->values();
        }
        return $matieres;
    }

    /** Page principale : liste des évaluations formatives de la classe */
    public function index(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        // Toutes les évaluations formatives de cette classe cette année
        $evaluations = PrimaireFormativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->orderByDesc('date_evaluation')
            ->get();

        // ── Semaine courante : Lundi → Samedi (la saisie n'est permise que sur cette plage) ──
        $lundi  = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $samedi = $lundi->copy()->addDays(5)->endOfDay();
        $today  = Carbon::now();

        // Jour du jour courant, tel que stocké dans l'emploi du temps ("Lundi".."Samedi")
        $todayFr = ucfirst($today->locale('fr')->dayName);

        // Date par défaut du champ : aujourd'hui si on est dans la semaine ouvrée, sinon Lundi
        $dateParDefaut = $today->between($lundi, $samedi) ? $today->format('Y-m-d') : $lundi->format('Y-m-d');

        // Matières programmées à l'emploi du temps, pour chaque jour de la semaine (JS s'en sert
        // pour ne proposer que les matières du jour sélectionné dans le champ Date)
        $matieresParJour = $this->matieresParJour($classeId);

        // Matières initiales affichées côté serveur (jour correspondant à la date par défaut)
        $jourParDefaut      = ucfirst(Carbon::parse($dateParDefaut)->locale('fr')->dayName);
        $matieresAujourdhui = $matieresParJour[$jourParDefaut] ?? collect();

        // Version allégée (id/name) pour alimenter le <select> Matière en JS selon la date choisie
        $matieresParJourJs = collect($matieresParJour)->map(
            fn ($subs) => $subs->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()
        );

        return view('teacher.primaire.formative.index', compact(
            'classe', 'annee', 'evaluations', 'matieresAujourdhui', 'todayFr',
            'matieresParJourJs', 'dateParDefaut', 'lundi', 'samedi'
        ));
    }

    /** Enregistrer une nouvelle évaluation avec les notes */
    public function store(Request $request, int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::where('teacher_id', $user->id)->findOrFail($classeId);

        // ── Verrou serveur : la date doit tomber dans la semaine courante (Lundi → Samedi) ──
        $lundi  = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $samedi = $lundi->copy()->addDays(5)->endOfDay();

        $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'titre'            => 'nullable|string|max:255',
            'date_evaluation'  => 'required|date|after_or_equal:'.$lundi->format('Y-m-d').'|before_or_equal:'.$samedi->format('Y-m-d'),
            'note_min'         => 'required|in:5,10',
            'notes'            => 'required|array',
            'notes.*'          => 'nullable|numeric|min:0',
        ], [
            'subject_id.required'          => 'Veuillez choisir une matière.',
            'date_evaluation.required'     => 'La date est obligatoire.',
            'date_evaluation.after_or_equal'  => "La date doit être comprise dans la semaine en cours (Lundi ".$lundi->format('d/m')." → Samedi ".$samedi->format('d/m').").",
            'date_evaluation.before_or_equal' => "La date doit être comprise dans la semaine en cours (Lundi ".$lundi->format('d/m')." → Samedi ".$samedi->format('d/m').").",
            'note_min.required'        => 'Choisissez la note minimale.',
            'note_min.in'              => 'La note minimale doit être 5 ou 10.',
            'notes.*.max'              => 'Une note dépasse le maximum autorisé.',
        ]);

        // ── Verrou serveur : la matière doit être au programme du jour correspondant à la date ──
        // (protection même si quelqu'un contourne le JS ou soumet un formulaire obsolète)
        $jourDeLaDate = ucfirst(Carbon::parse($request->date_evaluation)->locale('fr')->dayName);

        $matiereProgrammee = Schedule::where('classe_id', $classeId)
            ->where('day_of_week', $jourDeLaDate)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$matiereProgrammee) {
            return back()->withErrors([
                'subject_id' => "Cette matière n'est pas au programme de l'emploi du temps pour le {$jourDeLaDate} " . Carbon::parse($request->date_evaluation)->format('d/m/Y') . ".",
            ])->withInput();
        }

        $noteMin = (int) $request->note_min;
        $noteMax = $noteMin * 2; // 5→10 ou 10→20

        // Valider chaque note individuellement
        foreach ($request->notes as $studentId => $note) {
            if ($note !== null && $note !== '' && ((float)$note > $noteMax || (float)$note < 0)) {
                return back()->withErrors([
                    'notes' => "Une note dépasse le maximum autorisé ({$noteMax})."
                ])->withInput();
            }
        }

        // Créer l'évaluation
        $evaluation = PrimaireFormativeEvaluation::create([
            'classe_id'        => $classeId,
            'subject_id'       => $request->subject_id,
            'teacher_id'       => $user->id,
            'academic_year_id' => $annee->id,
            'titre'            => $request->titre,
            'date_evaluation'  => $request->date_evaluation,
            'note_min'         => $noteMin,
            'note_max'         => $noteMax,
        ]);

        // Enregistrer les notes
        foreach ($request->notes as $studentId => $note) {
            PrimaireFormativeNote::create([
                'evaluation_id' => $evaluation->id,
                'student_id'    => (int) $studentId,
                'note'          => ($note !== null && $note !== '') ? (float) $note : null,
            ]);
        }

        return redirect()->route('teacher.classes.formative', $classeId)
            ->with('success', 'Évaluation enregistrée avec succès.');
    }

    /**
     * Récapitulatif : toutes les matières x tous les élèves, moyenne des évaluations
     * formatives (ramenée sur /20 pour rester comparable entre matières), sur l'année
     * académique active.
     */
    public function recap(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        $subjects = Subject::where('classe_id', $classeId)->orderBy('name')->get();

        $evaluations = PrimaireFormativeEvaluation::with('notes')
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->get()
            ->groupBy('subject_id');

        // matrice[student_id][subject_id] = moyenne /20 des évaluations formatives (ou null)
        // nbEvalParMatiere[subject_id] = nombre d'évaluations déjà saisies pour cette matière
        $matrice          = [];
        $nbEvalParMatiere = [];

        foreach ($subjects as $subject) {
            $evalsSubject = $evaluations->get($subject->id, collect());
            $nbEvalParMatiere[$subject->id] = $evalsSubject->count();

            foreach ($classe->students as $student) {
                $notesNormalisees = [];
                foreach ($evalsSubject as $eval) {
                    $noteObj = $eval->notes->firstWhere('student_id', $student->id);
                    if ($noteObj && $noteObj->note !== null && (float) $eval->note_max > 0) {
                        $notesNormalisees[] = ((float) $noteObj->note / (float) $eval->note_max) * 20;
                    }
                }
                $matrice[$student->id][$subject->id] = count($notesNormalisees) > 0
                    ? array_sum($notesNormalisees) / count($notesNormalisees)
                    : null;
            }
        }

        return view('teacher.primaire.formative.recap', compact(
            'classe', 'annee', 'subjects', 'nbEvalParMatiere', 'matrice'
        ));
    }

    /** Voir les notes d'une évaluation */
    public function show(int $classeId, int $evaluationId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        $evaluation = PrimaireFormativeEvaluation::with(['subject', 'notes.student'])
            ->where('classe_id', $classeId)
            ->findOrFail($evaluationId);

        // Indexer les notes par student_id pour accès rapide
        $notesParEleve = $evaluation->notes->keyBy('student_id');

        return view('teacher.primaire.formative.show', compact(
            'classe', 'annee', 'evaluation', 'notesParEleve'
        ));
    }
}
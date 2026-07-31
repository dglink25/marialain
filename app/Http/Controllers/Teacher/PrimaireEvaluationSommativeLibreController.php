<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\PrimaireSommativeEvaluation;
use App\Models\PrimaireSommativeNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PrimaireEvaluationSommativeLibreController extends Controller
{
    /** Jours de la semaine ouvrés (Lundi → Samedi), dans l'ordre du calendrier */
    private array $joursSemaine = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

    /**
     * Pour une classe donnée, construit la liste des matières programmées à l'emploi du
     * temps pour chaque jour de la semaine (Lundi → Samedi).
     */
    private function matieresParJour(int $classeId): array
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

    /**
     * Page principale : liste des évaluations sommatives libres de la classe
     * + matières du jour via schedules + liste évals groupées par matière.
     */
    public function index(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        // Toutes les évaluations sommatives libres (sans composition_id) de cette classe cette année
        $evaluations = PrimaireSommativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->whereNull('composition_id')
            ->orderByDesc('date_evaluation')
            ->orderByDesc('created_at')
            ->get();

        // Évaluations groupées par matière (pour affichage/navigation)
        $evaluationsParMatiere = $evaluations->groupBy('subject_id');

        // Matières programmées à l'emploi du temps, pour chaque jour
        $matieresParJour = $this->matieresParJour($classeId);

        // Date par défaut : aujourd'hui
        $today          = Carbon::now();
        $dateParDefaut  = $today->format('Y-m-d');
        $todayFr        = ucfirst($today->locale('fr')->dayName);

        // Matières initiales pour la date par défaut
        $matieresAujourdhui = $matieresParJour[$todayFr] ?? collect();

        // Version allégée pour JS
        $matieresParJourJs = collect($matieresParJour)->map(
            fn ($subs) => $subs->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()
        );

        // Toutes les matières (fallback si emploi du temps vide)
        $toutesLessMatieres = Subject::where('classe_id', $classeId)->orderBy('name')->get();

        return view('teacher.primaire.sommative-libre.index', compact(
            'classe', 'annee', 'evaluations', 'evaluationsParMatiere',
            'matieresAujourdhui', 'todayFr', 'matieresParJourJs',
            'dateParDefaut', 'toutesLessMatieres'
        ));
    }

    /**
     * Enregistrer une nouvelle évaluation sommative libre avec les notes.
     * Barème par matière : note_min=5→note_max=10 OU note_min=10→note_max=20.
     * Validation que chaque note ≤ note_max.
     */
    public function store(Request $request, int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::where('teacher_id', $user->id)->findOrFail($classeId);

        $request->validate([
            'subject_id'      => 'required|exists:subjects,id',
            'titre'           => 'nullable|string|max:255',
            'date_evaluation' => 'required|date',
            'note_min'        => 'required|in:5,10',
            'notes'           => 'required|array',
            'notes.*'         => 'nullable|numeric|min:0',
        ], [
            'subject_id.required'      => 'Veuillez choisir une matière.',
            'date_evaluation.required' => 'La date est obligatoire.',
            'date_evaluation.date'     => 'La date est invalide.',
            'note_min.required'        => 'Choisissez le barème.',
            'note_min.in'              => 'Le barème doit être 5/10 ou 10/20.',
            'notes.*.min'              => 'Une note ne peut pas être négative.',
        ]);

        $noteMin = (int) $request->note_min;
        $noteMax = $noteMin * 2; // 5→10 ou 10→20

        // Valider chaque note contre le barème
        foreach ($request->notes as $studentId => $note) {
            if ($note !== null && $note !== '' && ((float) $note > $noteMax || (float) $note < 0)) {
                return back()->withErrors([
                    'notes' => "Une note dépasse le barème maximum autorisé (/{$noteMax})."
                ])->withInput();
            }
        }

        // Créer l'évaluation (composition_id est null = libre)
        $evaluation = PrimaireSommativeEvaluation::create([
            'composition_id'   => null,
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
            PrimaireSommativeNote::create([
                'evaluation_id' => $evaluation->id,
                'student_id'    => (int) $studentId,
                'note'          => ($note !== null && $note !== '') ? (float) $note : null,
            ]);
        }

        return redirect()
            ->route('teacher.primaire.sommative-libre.index', $classeId)
            ->with('success', 'Évaluation sommative enregistrée avec succès.');
    }

    /**
     * Afficher les notes d'une évaluation avec rang calculé.
     */
    public function show(int $classeId, int $evaluationId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        $evaluation = PrimaireSommativeEvaluation::with(['subject', 'notes.student'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->findOrFail($evaluationId);

        $notesParEleve = $evaluation->notes->keyBy('student_id');

        // Calcul du rang (tri décroissant des notes, même rang si égalité)
        $rangs = $this->calculerRangs($notesParEleve->pluck('note', 'student_id')->toArray());

        return view('teacher.primaire.sommative-libre.show', compact(
            'classe', 'annee', 'evaluation', 'notesParEleve', 'rangs'
        ));
    }

    /**
     * Récapitulatif toutes matières :
     * tableau avec colonnes fixes (N° Matricule, Nom, Prénoms, Sexe),
     * puis pour chaque matière (Note, Rang), puis Moyenne et Rang général.
     */
    public function showAllSubjects(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        // Matières de la classe
        $subjects = Subject::where('classe_id', $classeId)->orderBy('name')->get();

        // Pour chaque matière, la dernière évaluation sommative libre créée (ou la moyenne de toutes)
        // On prend toutes les évals libres et on calcule la moyenne /20 par matière par élève
        $evaluations = PrimaireSommativeEvaluation::with('notes')
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->whereNull('composition_id')
            ->get()
            ->groupBy('subject_id');

        // matrice[student_id][subject_id] = ['note_sur20' => float|null, 'count' => int]
        // Moyenne des évaluations de chaque matière ramenée sur /20
        $matrice = [];
        foreach ($classe->students as $student) {
            foreach ($subjects as $subject) {
                $evalsSubject = $evaluations->get($subject->id, collect());
                $notesNorm    = [];
                foreach ($evalsSubject as $eval) {
                    $noteObj = $eval->notes->firstWhere('student_id', $student->id);
                    if ($noteObj && $noteObj->note !== null && (float) $eval->note_max > 0) {
                        $notesNorm[] = ((float) $noteObj->note / (float) $eval->note_max) * 20;
                    }
                }
                $matrice[$student->id][$subject->id] = count($notesNorm) > 0
                    ? array_sum($notesNorm) / count($notesNorm)
                    : null;
            }
        }

        // Rang par matière
        $rangsParMatiere = [];
        foreach ($subjects as $subject) {
            $notesMatiere = [];
            foreach ($classe->students as $student) {
                $notesMatiere[$student->id] = $matrice[$student->id][$subject->id] ?? null;
            }
            $rangsParMatiere[$subject->id] = $this->calculerRangs($notesMatiere);
        }

        // Moyenne générale et rang général
        $moyennesGenerales = [];
        foreach ($classe->students as $student) {
            $notesVal = [];
            foreach ($subjects as $subject) {
                $v = $matrice[$student->id][$subject->id] ?? null;
                if ($v !== null) {
                    $notesVal[] = $v;
                }
            }
            $moyennesGenerales[$student->id] = count($notesVal) > 0
                ? array_sum($notesVal) / count($notesVal)
                : null;
        }
        $rangsGeneraux = $this->calculerRangs($moyennesGenerales);

        // Nombre d'évaluations par matière
        $nbEvalParMatiere = $evaluations->map(fn ($e) => $e->count())->toArray();

        return view('teacher.primaire.sommative-libre.recap', compact(
            'classe', 'annee', 'subjects', 'matrice',
            'rangsParMatiere', 'moyennesGenerales', 'rangsGeneraux',
            'nbEvalParMatiere', 'evaluations'
        ));
    }

    /**
     * Calcule les rangs à partir d'un tableau [id => note|null].
     * Même rang si égalité (dense ranking).
     * Retourne [id => rang|null].
     */
    private function calculerRangs(array $notes): array
    {
        // Extraire uniquement les notes non nulles, tri décroissant
        $notesValides = array_filter($notes, fn ($n) => $n !== null);
        arsort($notesValides);

        $rangs    = [];
        $rang     = 1;
        $lastNote = null;
        $lastRang = 1;
        $i        = 0;

        foreach ($notesValides as $id => $note) {
            if ($i === 0 || (float) $note !== (float) $lastNote) {
                $lastRang = $rang;
                $lastNote = $note;
            }
            $rangs[$id] = $lastRang;
            $rang++;
            $i++;
        }

        // Les notes nulles n'ont pas de rang
        foreach ($notes as $id => $note) {
            if ($note === null && !isset($rangs[$id])) {
                $rangs[$id] = null;
            }
        }

        return $rangs;
    }
}

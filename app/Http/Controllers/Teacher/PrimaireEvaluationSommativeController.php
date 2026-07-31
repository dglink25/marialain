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

class PrimaireEvaluationSommativeController extends Controller
{
    /** Liste des évaluations sommatives de la classe */
    public function index(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        $evaluations = PrimaireSommativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->orderByDesc('date_evaluation')
            ->get();

        // Matières du jour via emploi du temps
        $todayFr = ucfirst(Carbon::now()->locale('fr')->dayName);
        $matieresAujourdhui = Schedule::where('classe_id', $classeId)
            ->where('day_of_week', $todayFr)
            ->with('subject')->get()
            ->map(fn($s) => $s->subject)->filter()->unique('id')->values();

        if ($matieresAujourdhui->isEmpty()) {
            $matieresAujourdhui = Subject::where('classe_id', $classeId)->orderBy('name')->get();
        }

        return view('teacher.primaire.sommative.index', compact(
            'classe', 'annee', 'evaluations', 'matieresAujourdhui', 'todayFr'
        ));
    }

    /** Enregistrer une nouvelle évaluation sommative */
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
            'note_min.required'        => 'Choisissez le barème.',
        ]);

        $noteMin = (int) $request->note_min;
        $noteMax = $noteMin * 2;

        foreach ($request->notes as $studentId => $note) {
            if ($note !== null && $note !== '' && (float)$note > $noteMax) {
                return back()->withErrors(['notes' => "Une note dépasse le maximum ({$noteMax})."])->withInput();
            }
        }

        $evaluation = PrimaireSommativeEvaluation::create([
            'classe_id'        => $classeId,
            'subject_id'       => $request->subject_id,
            'teacher_id'       => $user->id,
            'academic_year_id' => $annee->id,
            'titre'            => $request->titre,
            'date_evaluation'  => $request->date_evaluation,
            'note_min'         => $noteMin,
            'note_max'         => $noteMax,
        ]);

        foreach ($request->notes as $studentId => $note) {
            PrimaireSommativeNote::create([
                'evaluation_id' => $evaluation->id,
                'student_id'    => (int) $studentId,
                'note'          => ($note !== null && $note !== '') ? (float) $note : null,
            ]);
        }

        return redirect()->route('teacher.classes.sommative', $classeId)
            ->with('success', 'Évaluation sommative enregistrée.');
    }

    /** Notes d'une évaluation avec rang */
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
            ->where('classe_id', $classeId)->findOrFail($evaluationId);

        $notesParEleve = $evaluation->notes->keyBy('student_id');

        // Calcul des rangs
        $notesValides = $evaluation->notes->whereNotNull('note')
            ->sortByDesc('note')->values();
        $rangs = [];
        $rang = 1;
        foreach ($notesValides as $i => $n) {
            if ($i > 0 && $n->note == $notesValides[$i-1]->note) {
                $rangs[$n->student_id] = $rangs[$notesValides[$i-1]->student_id];
            } else {
                $rangs[$n->student_id] = $rang;
            }
            $rang++;
        }

        return view('teacher.primaire.sommative.show', compact(
            'classe', 'annee', 'evaluation', 'notesParEleve', 'rangs'
        ));
    }

    /** Récapitulatif toutes matières */
    public function showAllSubjects(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        // Toutes les évaluations sommatives de cette classe
        $evaluations = PrimaireSommativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->get();

        // Grouper par matière — prendre la dernière évaluation par matière
        $evalParMatiere = $evaluations->sortByDesc('date_evaluation')->groupBy('subject_id');

        $matieres = $evalParMatiere->map(fn($evals) => $evals->first())->values();

        // Construire matrice [student_id][subject_id] = note
        $students = $classe->students;
        $matrix = [];
        foreach ($students as $student) {
            $matrix[$student->id] = [];
        }

        foreach ($evalParMatiere as $subjectId => $evals) {
            $lastEval = $evals->first(); // éval la plus récente
            $noteMax  = $lastEval->note_max;
            $notesMap = $lastEval->notes->keyBy('student_id');
            foreach ($students as $student) {
                $n = $notesMap->get($student->id);
                $matrix[$student->id][$subjectId] = [
                    'note'     => $n ? $n->note : null,
                    'note_max' => $noteMax,
                ];
            }
        }

        // Calculer moyenne générale et rang
        $moyennes = [];
        foreach ($students as $student) {
            $total = 0; $totalMax = 0; $count = 0;
            foreach ($matieres as $eval) {
                $entry = $matrix[$student->id][$eval->subject_id] ?? null;
                if ($entry && $entry['note'] !== null) {
                    // Normaliser sur 20 pour la moyenne
                    $total    += ($entry['note'] / $entry['note_max']) * 20;
                    $totalMax += 20;
                    $count++;
                }
            }
            $moyennes[$student->id] = $count > 0 ? round($total / $count, 2) : null;
        }

        // Rangs généraux
        $rangGeneraux = [];
        $sorted = collect($moyennes)->filter()->sortDesc()->values()->toArray();
        foreach ($moyennes as $studentId => $moy) {
            if ($moy === null) { $rangGeneraux[$studentId] = '—'; continue; }
            $pos = array_search($moy, $sorted);
            $rangGeneraux[$studentId] = $pos + 1;
        }

        // Rangs par matière
        $rangsParMatiere = [];
        foreach ($matieres as $eval) {
            $subId = $eval->subject_id;
            $notesSubject = [];
            foreach ($students as $student) {
                $entry = $matrix[$student->id][$subId] ?? null;
                if ($entry && $entry['note'] !== null) {
                    $notesSubject[$student->id] = $entry['note'];
                }
            }
            arsort($notesSubject);
            $rang = 1;
            $prev = null;
            foreach ($notesSubject as $sid => $note) {
                if ($prev !== null && $note == $prev) {
                    $rangsParMatiere[$subId][$sid] = $rangsParMatiere[$subId][array_key_last($rangsParMatiere[$subId] ?? [])] ?? $rang;
                } else {
                    $rangsParMatiere[$subId][$sid] = $rang;
                }
                $prev = $note;
                $rang++;
            }
        }

        return view('teacher.primaire.sommative.recap', compact(
            'classe', 'annee', 'matieres', 'matrix', 'moyennes', 'rangGeneraux', 'rangsParMatiere'
        ));
    }
}

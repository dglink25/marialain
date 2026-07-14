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

        // Matières de l'emploi du temps du jour courant pour cette classe
        $today = Carbon::now()->locale('fr')->dayName; // ex: "lundi"
        // Capitaliser pour correspondre au format stocké : "Lundi"
        $todayFr = ucfirst($today);

        $matieresAujourdhui = Schedule::where('classe_id', $classeId)
            ->where('day_of_week', $todayFr)
            ->with('subject')
            ->get()
            ->map(fn($s) => $s->subject)
            ->filter()
            ->unique('id')
            ->values();

        // Si aucune matière aujourd'hui, proposer toutes les matières de la classe
        if ($matieresAujourdhui->isEmpty()) {
            $matieresAujourdhui = Subject::where('classe_id', $classeId)->orderBy('name')->get();
        }

        return view('teacher.primaire.formative.index', compact(
            'classe', 'annee', 'evaluations', 'matieresAujourdhui', 'todayFr'
        ));
    }

    /** Enregistrer une nouvelle évaluation avec les notes */
    public function store(Request $request, int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::where('teacher_id', $user->id)->findOrFail($classeId);

        $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'titre'            => 'nullable|string|max:255',
            'date_evaluation'  => 'required|date',
            'note_min'         => 'required|in:5,10',
            'notes'            => 'required|array',
            'notes.*'          => 'nullable|numeric|min:0',
        ], [
            'subject_id.required'      => 'Veuillez choisir une matière.',
            'date_evaluation.required' => 'La date est obligatoire.',
            'note_min.required'        => 'Choisissez la note minimale.',
            'note_min.in'              => 'La note minimale doit être 5 ou 10.',
            'notes.*.max'              => 'Une note dépasse le maximum autorisé.',
        ]);

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

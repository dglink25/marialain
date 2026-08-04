<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotesEvaluationController extends Controller{
    public function index() {
        $annee_academique = AcademicYear::where('active', 1)->first();

        $classes = Classe::with(['entity', 'students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)->where('is_validated', 1);
        }])
            ->where('academic_year_id', $annee_academique->id)
            ->whereIn('entity_id', [1, 2])
            ->orderByRaw('entity_id DESC')
            ->orderBy('name')
            ->get();

        $compositions = DB::table('primaire_compositions')
            ->where('academic_year_id', $annee_academique->id)
            ->orderBy('mois')
            ->get()
            ->map(function ($c) {
                $c->classe_ids = json_decode($c->classe_ids, true) ?? [];
                return $c;
            });

        return view('primaire.notes.index', compact('classes', 'annee_academique', 'compositions'));
    }

    public function programmerComposition(Request $request)
    {
        $annee = AcademicYear::where('active', true)->firstOrFail();

        // Déduire les deux années civiles (ex: "2025-2026" → 2025 & 2026)
        [$annee1, $annee2] = array_map('intval', explode('-', $annee->name));

        $mois = (int) $request->input('mois');
        // Mois 1-6 = janvier-juin → année2 ; mois 7-12 = sept-déc → année1
        // Mais dans notre mapping : 1=sept, 2=oct, ... 4=déc, 5=jan, ... 10=juin
        // Mois scolaires 1-4 (sept–déc) → annee1 ; 5-10 (jan–juin) → annee2
        $anneeDesMois = ($mois >= 5) ? $annee2 : $annee1;

        // Numéro de mois calendaire réel
        $moisCalendaire = [
            1 => 9, 2 => 10, 3 => 11, 4 => 12,
            5 => 1, 6 => 2,  7 => 3,  8 => 4, 9 => 5, 10 => 6,
        ];
        $moisReel = $moisCalendaire[$mois] ?? $mois;

        $minDate = sprintf('%04d-%02d-01', $anneeDesMois, $moisReel);
        $maxDate = date('Y-m-t', strtotime($minDate));

        $request->validate([
            'classe_ids'        => 'required|array|min:1',
            'classe_ids.*'      => 'exists:classes,id',
            'mois'              => 'required|integer|between:1,10',
            'composition_debut' => "required|date|after_or_equal:{$minDate}|before_or_equal:{$maxDate}",
            'composition_fin'   => "required|date|after_or_equal:composition_debut|before_or_equal:{$maxDate}",
            'saisie_debut'      => "required|date|after_or_equal:{$minDate}|before_or_equal:{$maxDate}",
            'saisie_fin'        => "required|date|after_or_equal:saisie_debut|before_or_equal:{$maxDate}",
        ], [
            'classe_ids.required'              => 'Veuillez sélectionner au moins une classe.',
            'mois.required'                    => 'Veuillez choisir le mois.',
            'composition_debut.required'       => 'La date de début de composition est obligatoire.',
            'composition_debut.after_or_equal' => "La date doit être dans le mois sélectionné (à partir du {$minDate}).",
            'composition_debut.before_or_equal'=> "La date doit être dans le mois sélectionné (avant le {$maxDate}).",
            'composition_fin.required'         => 'La date de fin de composition est obligatoire.',
            'composition_fin.after_or_equal'   => 'La fin doit être après le début.',
            'composition_fin.before_or_equal'  => "La date doit être dans le mois sélectionné.",
            'saisie_debut.required'            => 'La date de début de saisie est obligatoire.',
            'saisie_fin.required'              => 'La date de fin de saisie est obligatoire.',
            'saisie_fin.after_or_equal'        => 'La fin de saisie doit être après le début.',
        ]);

        DB::table('primaire_compositions')->insert([
            'academic_year_id'  => $annee->id,
            'classe_ids'        => json_encode($request->classe_ids),
            'mois'              => $mois,
            'composition_debut' => $request->composition_debut,
            'composition_fin'   => $request->composition_fin,
            'saisie_debut'      => $request->saisie_debut,
            'saisie_fin'        => $request->saisie_fin,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()->route('primaire.notes.index')
            ->with('success', 'Composition programmée avec succès.');
    }

    /** AJAX — liste des élèves validés d'une classe (alphabétique) */
    public function getElevesClasse(int $classeId): \Illuminate\Http\JsonResponse
    {
        $annee = AcademicYear::where('active', true)->firstOrFail();
        $eleves = \App\Models\Student::where('class_id', $classeId)
            ->where('academic_year_id', $annee->id)
            ->where('is_validated', 1)
            ->orderBy('last_name')->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name', 'num_educ', 'gender']);

        return response()->json($eleves);
    }

    /** AJAX — classes disponibles dans un cycle (entity_id) pour une année */
    public function getClassesDestination(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $entityId = (int) $request->query('entity_id');
        $yearId   = (int) $request->query('year_id');

        $classes = Classe::where('entity_id', $entityId)
            ->where('academic_year_id', $yearId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($classes);
    }

    /** Délibération primaire/maternelle — transfert simple sans calcul de notes */
    public function delibererPrimaire(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'source_class_id'        => 'required|exists:classes,id',
            'target_academic_year_id'=> 'required|exists:academic_years,id',
            'target_entity_id'       => 'required|integer|in:1,2,3',
            'target_class_id'        => 'required|exists:classes,id',
            'student_ids'            => 'required|array|min:1',
            'student_ids.*'          => 'exists:students,id',
        ]);

        $activeYear  = AcademicYear::where('active', true)->firstOrFail();
        $targetYear  = AcademicYear::findOrFail($request->target_academic_year_id);
        $targetClass = Classe::findOrFail($request->target_class_id);

        DB::beginTransaction();
        try {
            foreach ($request->student_ids as $studentId) {
                $student = \App\Models\Student::findOrFail($studentId);

                // Snapshot archive
                \App\Models\StudentAcademicRecord::updateOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $activeYear->id],
                    [
                        'class_id'             => $student->class_id,
                        'entity_id'            => $student->entity_id,
                        'first_name'           => $student->first_name,
                        'last_name'            => $student->last_name,
                        'birth_date'           => $student->birth_date,
                        'birth_place'          => $student->birth_place,
                        'gender'               => $student->gender,
                        'num_educ'             => $student->num_educ,
                        'parent_full_name'     => $student->parent_full_name,
                        'parent_email'         => $student->parent_email,
                        'parent_phone'         => $student->parent_phone,
                        'registration_type'    => $student->registration_type,
                        'total_fees'           => $student->total_fees,
                        'amount_paid'          => $student->payments()->where('academic_year_id', $activeYear->id)->sum('amount'),
                        'statut_deliberation'  => 'passed',
                        'next_class_id'        => $targetClass->id,
                        'next_academic_year_id'=> $targetYear->id,
                        'is_validated'         => $student->is_validated,
                        'archived_at'          => now(),
                    ]
                );

                // Déplacer l'élève
                $student->update([
                    'class_id'          => $targetClass->id,
                    'entity_id'         => $targetClass->entity_id,
                    'academic_year_id'  => $targetYear->id,
                    'registration_type' => 're_registration',
                    'total_fees'        => 0,
                    'amount_paid'       => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'count'   => count($request->student_ids),
                'message' => count($request->student_ids) . ' élève(s) transféré(s) vers ' . $targetClass->name . ' (' . $targetYear->name . ').',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Délibération primaire échouée', [
                'message' => $e->getMessage(), 'line' => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /** Liste des évaluations formatives par classe (vue directeur) */
    public function evaluationFormative(int $classeId)
    {
        $annee_academique = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classeId);

        $evaluations = \App\Models\PrimaireFormativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee_academique->id)
            ->orderByDesc('date_evaluation')
            ->get();

        return view('primaire.notes.formative', compact('classe', 'annee_academique', 'evaluations'));
    }

    public function evaluationSommative(int $classeId)
    {
        $annee_academique = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classeId);

        $evaluations = \App\Models\PrimaireSommativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee_academique->id)
            ->orderByDesc('date_evaluation')
            ->get();

        return view('primaire.notes.sommative', compact('classe', 'annee_academique', 'evaluations'));
    }

    /** Détail notes d'une évaluation formative (vue directeur) */
    public function showFormative(int $classeId, int $evaluationId)
    {
        $annee_academique = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classeId);

        $evaluation = \App\Models\PrimaireFormativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)->findOrFail($evaluationId);

        $notesParEleve = $evaluation->notes->keyBy('student_id');

        // Rangs
        $notesValides = $evaluation->notes->whereNotNull('note')->sortByDesc('note')->values();
        $rangs = []; $rang = 1;
        foreach ($notesValides as $i => $n) {
            $rangs[$n->student_id] = ($i > 0 && $n->note == $notesValides[$i-1]->note)
                ? $rangs[$notesValides[$i-1]->student_id] : $rang;
            $rang++;
        }

        return view('primaire.notes.show_evaluation', compact('classe', 'annee_academique', 'evaluation', 'notesParEleve', 'rangs'));
    }

    /** Détail notes d'une évaluation sommative (vue directeur) */
    public function showSommative(int $classeId, int $evaluationId)
    {
        $annee_academique = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classeId);

        $evaluation = \App\Models\PrimaireSommativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)->findOrFail($evaluationId);

        $notesParEleve = $evaluation->notes->keyBy('student_id');

        $notesValides = $evaluation->notes->whereNotNull('note')->sortByDesc('note')->values();
        $rangs = []; $rang = 1;
        foreach ($notesValides as $i => $n) {
            $rangs[$n->student_id] = ($i > 0 && $n->note == $notesValides[$i-1]->note)
                ? $rangs[$notesValides[$i-1]->student_id] : $rang;
            $rang++;
        }

        return view('primaire.notes.show_evaluation', compact('classe', 'annee_academique', 'evaluation', 'notesParEleve', 'rangs'));
    }

    /** Récapitulatif toutes matières sommatives (vue directeur) */
    public function recapSommative(int $classeId)
    {
        $annee_academique = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classeId);

        $evaluations = \App\Models\PrimaireSommativeEvaluation::with(['subject', 'notes'])
            ->where('classe_id', $classeId)
            ->where('academic_year_id', $annee_academique->id)
            ->get();

        $evalParMatiere = $evaluations->sortByDesc('date_evaluation')->groupBy('subject_id');
        $matieres = $evalParMatiere->map(fn($e) => $e->first())->values();
        $students = $classe->students;

        $matrix = [];
        foreach ($students as $s) $matrix[$s->id] = [];
        foreach ($evalParMatiere as $subId => $evals) {
            $last = $evals->first();
            $notesMap = $last->notes->keyBy('student_id');
            foreach ($students as $s) {
                $n = $notesMap->get($s->id);
                $matrix[$s->id][$subId] = ['note' => $n?->note, 'note_max' => $last->note_max];
            }
        }

        $moyennes = [];
        foreach ($students as $s) {
            $total = $cnt = 0;
            foreach ($matieres as $e) {
                $entry = $matrix[$s->id][$e->subject_id] ?? null;
                if ($entry && $entry['note'] !== null) { $total += ($entry['note'] / $entry['note_max']) * 20; $cnt++; }
            }
            $moyennes[$s->id] = $cnt > 0 ? round($total / $cnt, 2) : null;
        }

        $rangGeneraux = [];
        $sorted = collect($moyennes)->filter()->sortDesc()->values()->toArray();
        foreach ($moyennes as $sid => $m) {
            $rangGeneraux[$sid] = $m !== null ? (array_search($m, $sorted) + 1) : '—';
        }

        $rangsParMatiere = [];
        foreach ($matieres as $e) {
            $subId = $e->subject_id;
            $ns = [];
            foreach ($students as $s) { $entry = $matrix[$s->id][$subId] ?? null; if ($entry && $entry['note'] !== null) $ns[$s->id] = $entry['note']; }
            arsort($ns); $rang = 1; $prev = null;
            foreach ($ns as $sid => $note) {
                $rangsParMatiere[$subId][$sid] = ($prev !== null && $note == $prev) ? ($rangsParMatiere[$subId][array_key_last($rangsParMatiere[$subId])] ?? $rang) : $rang;
                $prev = $note; $rang++;
            }
        }

        return view('primaire.notes.recap_sommative', compact('classe', 'annee_academique', 'matieres', 'matrix', 'moyennes', 'rangGeneraux', 'rangsParMatiere'));
    }
}

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

    public function evaluationFormative(int $classeId)
    {
        $annee_academique = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classeId);

        return view('primaire.notes.evaluation', [
            'classe'           => $classe,
            'annee_academique' => $annee_academique,
            'type'             => 'formative',
        ]);
    }

    public function evaluationSommative(int $classeId)
    {
        $annee_academique = AcademicYear::where('active', true)->firstOrFail();
        $classe = Classe::with(['students' => function ($q) use ($annee_academique) {
            $q->where('academic_year_id', $annee_academique->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->findOrFail($classeId);

        return view('primaire.notes.evaluation', [
            'classe'           => $classe,
            'annee_academique' => $annee_academique,
            'type'             => 'sommative',
        ]);
    }
}

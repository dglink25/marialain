<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrimaireClasseController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $annee  = AcademicYear::where('active', true)->firstOrFail();

        // Classe de l'enseignant (primaire OU maternelle)
        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)->where('is_validated', 1);
        }, 'entity'])
            ->where('teacher_id', $user->id)
            ->whereIn('entity_id', [1, 2])
            ->first();

        if (!$classe) {
            return view('teacher.primaire.classes', [
                'classe'       => null,
                'compositions' => collect(),
                'annee'        => $annee,
                'error'        => "Aucune classe primaire ou maternelle n'est assignée à votre compte.",
            ]);
        }

        // Récupérer les compositions programmées par le directeur pour cette classe
        $today = Carbon::today();

        $compositions = DB::table('primaire_compositions')
            ->where('academic_year_id', $annee->id)
            ->orderBy('mois')
            ->get()
            ->filter(fn($c) => in_array($classe->id, json_decode($c->classe_ids, true) ?? []))
            ->map(function ($c) use ($today) {
                $compDebut  = Carbon::parse($c->composition_debut);
                $compFin    = Carbon::parse($c->composition_fin);
                $saisieDebut = Carbon::parse($c->saisie_debut);
                $saisieFin   = Carbon::parse($c->saisie_fin);

                // Déterminer l'étape en cours
                if ($today->lt($compDebut)) {
                    $etape         = 'à_venir';
                    $etapeLabel    = 'À venir';
                    $joursRestants = $today->diffInDays($compDebut);
                    $etapeMessage  = "La composition commence dans {$joursRestants} jour(s)";
                    $couleur       = 'blue';
                } elseif ($today->between($compDebut, $compFin)) {
                    $etape         = 'composition';
                    $etapeLabel    = 'Composition en cours';
                    $joursRestants = $today->diffInDays($compFin);
                    $etapeMessage  = "Composition en cours — se termine dans {$joursRestants} jour(s)";
                    $couleur       = 'amber';
                } elseif ($today->between($saisieDebut, $saisieFin)) {
                    $etape         = 'saisie';
                    $etapeLabel    = 'Saisie des notes';
                    $joursRestants = $today->diffInDays($saisieFin);
                    $etapeMessage  = "Saisie des notes en cours — {$joursRestants} jour(s) restant(s)";
                    $couleur       = 'orange';
                } elseif ($today->gt($saisieFin)) {
                    $etape         = 'terminé';
                    $etapeLabel    = 'Terminé';
                    $joursRestants = 0;
                    $etapeMessage  = 'Composition et saisie terminées';
                    $couleur       = 'green';
                } else {
                    $etape         = 'entre_phases';
                    $etapeLabel    = 'En attente de saisie';
                    $joursRestants = $today->diffInDays($saisieDebut);
                    $etapeMessage  = "La saisie des notes commence dans {$joursRestants} jour(s)";
                    $couleur       = 'purple';
                }

                $c->etape         = $etape;
                $c->etapeLabel    = $etapeLabel;
                $c->etapeMessage  = $etapeMessage;
                $c->joursRestants = $joursRestants;
                $c->couleur       = $couleur;
                $c->classe_ids    = json_decode($c->classe_ids, true) ?? [];

                return $c;
            })
            ->values();

        return view('teacher.primaire.classes', compact('classe', 'compositions', 'annee'));
    }

    public function evaluationSommative(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        return view('teacher.primaire.evaluation', [
            'classe' => $classe,
            'annee'  => $annee,
            'type'   => 'sommative',
        ]);
    }

    public function evaluationFormative(int $classeId)
    {
        $user  = Auth::user();
        $annee = AcademicYear::where('active', true)->firstOrFail();

        $classe = Classe::with(['students' => function ($q) use ($annee) {
            $q->where('academic_year_id', $annee->id)
              ->where('is_validated', 1)
              ->orderBy('last_name')->orderBy('first_name');
        }])->where('teacher_id', $user->id)->findOrFail($classeId);

        return view('teacher.primaire.evaluation', [
            'classe' => $classe,
            'annee'  => $annee,
            'type'   => 'formative',
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrimaireComposition extends Model
{
    protected $table = 'primaire_compositions';

    protected $fillable = [
        'academic_year_id', 
        'classe_ids', 
        'mois',
        'composition_debut', 
        'composition_fin',
        'saisie_debut', 
        'saisie_fin',
    ];

    protected $casts = [
        'classe_ids'        => 'array',
        'composition_debut' => 'date',
        'composition_fin'   => 'date',
        'saisie_debut'      => 'date',
        'saisie_fin'        => 'date',
    ];

    public function annee() { 
        return $this->belongsTo(AcademicYear::class, 'academic_year_id'); 
    }

    public function sommativeEvaluations() { 
        return $this->hasMany(PrimaireSommativeEvaluation::class, 'composition_id'); 
    }
}
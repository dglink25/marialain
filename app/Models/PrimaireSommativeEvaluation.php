<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrimaireSommativeEvaluation extends Model{
    protected $table = 'primaire_sommative_evaluations';

    protected $fillable = [
        'composition_id', 'classe_id', 'subject_id',
        'teacher_id', 'academic_year_id',
        'note_min', 'note_max', 'titre', 'date_evaluation',
    ];

    protected $casts = [
        'note_min'         => 'decimal:2',
        'note_max'         => 'decimal:2',
        'date_evaluation'  => 'date',
    ];

    public function composition() { return $this->belongsTo(PrimaireComposition::class, 'composition_id'); }
    public function classe()      { return $this->belongsTo(Classe::class); }
    public function subject()     { return $this->belongsTo(Subject::class); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }
    public function annee()       { return $this->belongsTo(AcademicYear::class, 'academic_year_id'); }
    public function notes()       { return $this->hasMany(PrimaireSommativeNote::class, 'evaluation_id'); }
}

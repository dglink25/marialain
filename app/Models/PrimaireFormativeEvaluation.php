<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrimaireFormativeEvaluation extends Model
{
    protected $table = 'primaire_formative_evaluations';

    protected $fillable = [
        'classe_id', 'subject_id', 'teacher_id',
        'academic_year_id', 'titre', 'date_evaluation',
        'note_min', 'note_max',
    ];

    protected $casts = [
        'date_evaluation' => 'date',
        'note_min'        => 'decimal:2',
        'note_max'        => 'decimal:2',
    ];

    public function classe()   { return $this->belongsTo(Classe::class); }
    public function subject()  { return $this->belongsTo(Subject::class); }
    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function annee()    { return $this->belongsTo(AcademicYear::class, 'academic_year_id'); }
    public function notes()    { return $this->hasMany(PrimaireFormativeNote::class, 'evaluation_id'); }
}

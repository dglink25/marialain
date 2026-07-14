<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrimaireFormativeNote extends Model
{
    protected $table = 'primaire_formative_notes';

    protected $fillable = ['evaluation_id', 'student_id', 'note'];

    protected $casts = ['note' => 'decimal:2'];

    public function evaluation() { return $this->belongsTo(PrimaireFormativeEvaluation::class, 'evaluation_id'); }
    public function student()    { return $this->belongsTo(Student::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model{
    protected $fillable = [
        'classe_id', 'teacher_id', 'subject_id',
        'day_of_week', 'start_time', 'end_time'
    ];

    public function classe() { return $this->belongsTo(Classe::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
}


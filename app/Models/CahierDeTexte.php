<?php 

// app/Models/CahierDeTexte.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CahierDeTexte extends Model
{
    protected $table = 'cahier_de_texte';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'timetable_id',
        'day',
        'content',
        'academic_year_id',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }
}

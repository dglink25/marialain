<?php 

// app/Models/CahierDeTexte.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CahierDeTexte extends Model{
    protected $table = 'cahier_de_texte';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'timetable_id',
        'day',
        'content',
        'academic_year_id',
        'motif_retard',
        'duration_minutes',
        'is_late',
    ];


    public function subject(){
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

    public function isCurrentLessonTime(){
        if (!$this->currentLesson) return false;

        $now = now();
        $start = \Carbon\Carbon::parse($this->currentLesson->start_time);
        $end = \Carbon\Carbon::parse($this->currentLesson->end_time);

        return $now->between($start, $end);
    }


}

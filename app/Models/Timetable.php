<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model{
    protected $fillable = [
        'class_id', 
        'subject_id', 
        'teacher_id', 
        'day', 
        'start_time', 
        'end_time', 
        'academic_year_id'
    ];

    public function class()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    // Scope pour filtrer automatiquement par année active
    public function scopeForCurrentYear($query) {
        $yearId = session('academic_year_id') ?? AcademicYear::where('active', 1)->first()->id;
        return $query->where('academic_year_id', $yearId);
    }
}

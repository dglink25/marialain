<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'type',
        'value',
        'trimestre',
        'sequence',
        'academic_year_id',
    ];
    
    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

}

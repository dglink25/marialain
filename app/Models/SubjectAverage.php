<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectAverage extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'average',
        'weighted_average',
        'trimestre',
        'rank',
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

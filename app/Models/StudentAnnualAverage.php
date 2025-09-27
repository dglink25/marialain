<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnnualAverage extends Model
{
    protected $fillable = [
        'student_id',
        'average',
        'rank',
        'academic_year_id',
    ];
    
    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

}

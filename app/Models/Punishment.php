<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Punishment extends Model{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'entity_id',
        'reason',
        'hours',
        'date_punishment'
    ];

    protected $dates = [
        'date_punishment',
        'created_at',
        'updated_at',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    public function entity() {
        return $this->belongsTo(Entity::class);
    }
}


    



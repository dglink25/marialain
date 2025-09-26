<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model{
    protected $fillable = ['name','academic_year_id', 'classe_id'];

    public function teachers(){
        return $this->belongsToMany(User::class, 'class_teacher_subject', 'subject_id', 'teacher_id')
                    ->withPivot('class_id')
                    ->withTimestamps();
    }

    public function academicYear(){
        return $this->belongsTo(AcademicYear::class);
    }
    public function schedules(){
        return $this->hasMany(Schedule::class);
    }


}

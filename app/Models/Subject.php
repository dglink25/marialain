<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model{

    protected $fillable = ['name','academic_year_id', 'coefficient', 'classe_id'];

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

    public function teacher() {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classe() {
        return $this->belongsTo(Classe::class);
    }

    public function grades() {
        return $this->hasMany(Grade::class);
    }

    public function averages() {
        return $this->hasMany(SubjectAverage::class);
    }

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'class_teacher_subject', 'subject_id', 'class_id');
    }




}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classe extends Model{
    protected $fillable = [
        'name',
        'academic_year_id',
        'entity_id',
        'school_fees',
        'description',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function teachers(){
        return $this->belongsToMany(User::class, 'class_teacher_subject', 'class_id', 'teacher_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }
    public function teacher(){
        return $this-> belongsTo(User::class, 'teacher_id');
    }

    public function students(){
        return $this->hasMany(Student::class, 'class_id');
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'class_id');
    }

    public function classTeacherSubjects(): HasMany{
        return $this->hasMany(ClassTeacherSubject::class, 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'classe_id'); 
        // 'classe_id' est la colonne de la table subjects qui référence la classe
    }
    // Dans Classe.php
    public function subject()
    {
        return $this->belongsToMany(Subject::class, 'class_teacher_subject', 'class_id', 'subject_id')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    public function subjectss(){
        return $this->belongsToMany(Subject::class, 'subjects')
                    ->withPivot('coefficient')
                    ->withTimestamps();
    }




    
}

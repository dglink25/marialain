<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['name','entity_id','academic_year_id','teacher_id'];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class,'academic_year_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class,'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class,'enrollments','class_id','student_id');
    }
}

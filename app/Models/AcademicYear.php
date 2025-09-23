<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['name','active'];

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function timetables() {
        return $this->hasMany(Timetable::class);
    }

    public function classTeacherSubjects() {
        return $this->hasMany(ClassTeacherSubject::class);
    }

    public function studentPayments() {
        return $this->hasMany(StudentPayment::class);
    }

    public function teacherInvitations() {
        return $this->hasMany(TeacherInvitation::class);
    }

    public function scopeActive($query){
        return $query->where('active', true);
    }

    public function scopeArchives($query){
        return $query->where('active', false);
    }
}

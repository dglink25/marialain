<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','role_id'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function invited()
    {
        return $this->hasMany(Invitation::class,'invited_by');
    }

    public function subjects() {
        return $this->belongsToMany(Subject::class, 'class_teacher_subject')
                    ->withPivot('class_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'class_teacher_subject', 'teacher_id', 'class_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(TeacherInvitation::class, 'user_id');
    }



}

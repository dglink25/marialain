<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'gender',
        'phone',
        'marital_status',
        'address',
        'birth_date',
        'birth_place',
        'nationality',
        'profile_photo',

        // fichiers PDF stockés
        'id_card_file',
        'birth_certificate_file',
        'diploma_file',
        'ifu_file',
        'rib_file',

        // infos extraites
        'ifu_number',
        'id_card_number',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];
    public function classePrimaire()
    {
        return $this->hasOne(Classe::class, 'teacher_id');
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function invited()
    {
        return $this->hasMany(Invitation::class,'invited_by');
    }

    public function subject()
    {
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

    public function classe()
    {
        return $this->hasOne(Classe::class, 'teacher_id');
    }

    public function schedules(){
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function invitationTeacher(){
        return $this->hasOne(TeacherInvitation::class, 'user_id');
    }


}

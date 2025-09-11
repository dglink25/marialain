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

    public function classes()
    {
        return $this->hasMany(SchoolClass::class,'teacher_id');
    }

    public function invited()
    {
        return $this->hasMany(Invitation::class,'invited_by');
    }
}

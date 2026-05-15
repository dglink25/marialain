<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ParentUser extends Authenticatable{
    protected $table = 'parents';

    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'is_active',
        'last_login_at',
        // Vérification téléphone
        'is_verifie_phone',
        'verifie_phone_at',
        // OTP
        'phone_otp',
        'phone_otp_expires_at',
        'phone_otp_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'phone_otp', 
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'is_verifie_phone'     => 'boolean',
        'last_login_at'        => 'datetime',
        'verifie_phone_at'     => 'datetime',
        'phone_otp_expires_at' => 'datetime',
        'phone_otp_sent_at'    => 'datetime',
    ];

    /**
     * Un parent peut avoir plusieurs élèves.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'parent_phone', 'phone');
    }

    /**
     * Obtenir tous les enfants du parent.
     */
    public function getChildrenAttribute()
    {
        return $this->students;
    }
}
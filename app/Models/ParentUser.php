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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Un parent peut avoir plusieurs élèves
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'parent_phone', 'phone');
    }

    /**
     * Obtenir tous les enfants du parent
     */
    public function getChildrenAttribute()
    {
        return $this->students;
    }
}
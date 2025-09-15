<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'censeur_id', 'token', 'accepted',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function censeur() {
        return $this->belongsTo(User::class, 'censeur_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherInvitation extends Model
{
    use HasFactory;

    protected $casts = [
        'accepted_at' => 'datetime',
    ];
    
    protected $fillable = [
        'user_id', 'censeur_id', 'token', 'accepted', 'accepted_at'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function accept(){
        $this->forceFill([
            'accepted' => true,
            'accepted_at' => now(),
        ])->save();
    }

    public function censeur() {
        return $this->belongsTo(User::class, 'censeur_id');
    }
}

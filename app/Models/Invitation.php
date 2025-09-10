<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'role',
        'year_id',
        'phone',
        'token',
        'expires_at',
        'temporary_password',
        'created_by',
        'accepted',
    ];

    protected $dates = ['expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

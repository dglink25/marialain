<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Invitation extends Model{
    use HasFactory;

    protected $fillable = ['email','phone','role','token','temporary_password','expires_at','created_by','accepted'];
    protected $dates = ['expires_at'];

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

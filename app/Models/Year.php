<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Year extends Model{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date'];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}

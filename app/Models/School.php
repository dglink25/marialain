<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class School extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','description'];

    public function classes(){
        return $this->hasMany(SchoolClass::class);
    }
}

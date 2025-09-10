<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolClass extends Model{
    use HasFactory;

    protected $fillable = [
        'name', 'level', 'sector', 'year_id', 'series'
    ];

    public function year()
    {
        return $this->belongsTo(Year::class);
    }
}

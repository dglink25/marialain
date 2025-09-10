<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicYear extends Model{
    protected $fillable = ['name', 'starts_at', 'ends_at', 'is_active'];
}

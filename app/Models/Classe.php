<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $fillable = [
        'name',
        'academic_year_id',
        'entity_id',
        'school_fees',
    ];

    // Relation vers Entity
    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    // Relation vers AcademicYear
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}

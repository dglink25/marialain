<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conduct extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'entity_id',
        'grade',
        'comment',
        'trimestre'
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'trimestre' => 'integer',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    public function entity() {
        return $this->belongsTo(Entity::class);
    }

    public static function boot()
    {
        parent::boot();
        
        static::saving(function ($conduct) {
            // Valider que le grade est entre 0 et 10
            if ($conduct->grade < 0 || $conduct->grade > 20) {
                throw new \Exception('La note de conduite doit être entre 0 et 20');
            }
            
            // Valider que le trimestre est 1, 2 ou 3
            if (!in_array($conduct->trimestre, [1, 2, 3])) {
                throw new \Exception('Le trimestre doit être 1, 2 ou 3');
            }
        });
    }
}

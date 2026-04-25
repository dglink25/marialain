<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliberationStudent extends Model
{
    protected $fillable = [
        'deliberation_id',
        'student_id',
        'old_class_id',
        'old_academic_year_id',
        'old_registration_type',
        'new_class_id',
        'new_academic_year_id',
        'new_registration_type',
        'status',
        'annual_average',
    ];

    protected $casts = [
        'annual_average' => 'decimal:2',
    ];

    public function deliberation(): BelongsTo
    {
        return $this->belongsTo(Deliberation::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function oldClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'old_class_id');
    }

    public function newClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'new_class_id');
    }

    public function oldAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'old_academic_year_id');
    }

    public function newAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'new_academic_year_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deliberation extends Model
{
    protected $fillable = [
        'source_class_id',
        'source_academic_year_id',
        'target_class_id',
        'target_academic_year_id',
        'deliberated_by',
        'keep_timetable',
        'passed_count',
        'repeated_count',
        'deliberated_at',
        'is_cancelled',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'keep_timetable'  => 'boolean',
        'is_cancelled'    => 'boolean',
        'deliberated_at'  => 'datetime',
        'cancelled_at'    => 'datetime',
    ];

    public function sourceClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'source_class_id');
    }

    public function targetClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'target_class_id');
    }

    public function sourceAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'source_academic_year_id');
    }

    public function targetAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'target_academic_year_id');
    }

    public function deliberatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deliberated_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function deliberationStudents(): HasMany
    {
        return $this->hasMany(DeliberationStudent::class);
    }
}
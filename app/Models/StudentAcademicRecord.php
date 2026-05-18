<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Snapshot immuable de l'état d'un élève pour une année académique.
 *
 * @property int         $id
 * @property int         $student_id
 * @property int         $academic_year_id
 * @property int         $class_id
 * @property int         $entity_id
 * @property string      $first_name
 * @property string      $last_name
 * @property string|null $birth_date
 * @property string|null $birth_place
 * @property string|null $gender
 * @property string|null $num_educ
 * @property string|null $parent_full_name
 * @property string|null $parent_email
 * @property string|null $parent_phone
 * @property string|null $registration_type
 * @property float|null  $total_fees
 * @property float|null  $amount_paid
 * @property float|null  $moy_trimestre_1
 * @property float|null  $moy_trimestre_2
 * @property float|null  $moy_trimestre_3
 * @property float|null  $moy_annuelle
 * @property int|null    $rang_annuel
 * @property string      $statut_deliberation  passed|repeated|pending
 * @property int|null    $next_class_id
 * @property int|null    $next_academic_year_id
 * @property bool        $is_validated
 * @property \Carbon\Carbon|null $archived_at
 */
class StudentAcademicRecord extends Model
{
    protected $table = 'student_academic_records';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'class_id',
        'entity_id',
        'first_name',
        'last_name',
        'birth_date',
        'birth_place',
        'gender',
        'num_educ',
        'parent_full_name',
        'parent_email',
        'parent_phone',
        'registration_type',
        'total_fees',
        'amount_paid',
        'moy_trimestre_1',
        'moy_trimestre_2',
        'moy_trimestre_3',
        'moy_annuelle',
        'rang_annuel',
        'statut_deliberation',
        'next_class_id',
        'next_academic_year_id',
        'is_validated',
        'archived_at',
    ];

    protected $casts = [
        'birth_date'    => 'date',
        'archived_at'   => 'datetime',
        'is_validated'  => 'boolean',
        'total_fees'    => 'decimal:2',
        'amount_paid'   => 'decimal:2',
        'moy_trimestre_1' => 'decimal:2',
        'moy_trimestre_2' => 'decimal:2',
        'moy_trimestre_3' => 'decimal:2',
        'moy_annuelle'    => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function nextClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'next_class_id');
    }

    public function nextAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'next_academic_year_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForYear($query, int $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopePassed($query)
    {
        return $query->where('statut_deliberation', 'passed');
    }

    public function scopeRepeated($query)
    {
        return $query->where('statut_deliberation', 'repeated');
    }

    // ── Accesseurs utiles ─────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->last_name} {$this->first_name}";
    }

    public function getRemainingFeesAttribute(): float
    {
        return max(0, ($this->total_fees ?? 0) - ($this->amount_paid ?? 0));
    }

    public function getPaymentRateAttribute(): int
    {
        if (!$this->total_fees || $this->total_fees <= 0) {
            return 0;
        }
        return (int) round(($this->amount_paid / $this->total_fees) * 100);
    }

    // ── Méthode statique de création/mise à jour d'un snapshot ──────────────

    /**
     * Crée ou met à jour le snapshot d'un élève pour une année donnée.
     * Calcule les moyennes trimestrielles si non fournies.
     */
    public static function createOrUpdateSnapshot(
        Student $student,
        AcademicYear $year,
        array $moyennes = [],
        string $statut = 'pending',
        ?int $nextClassId = null,
        ?int $nextYearId = null
    ): self {
        $totalPaid = $student->payments()
            ->where('academic_year_id', $year->id)
            ->sum('amount');

        $data = [
            'class_id'             => $student->class_id,
            'entity_id'            => $student->entity_id,
            'first_name'           => $student->first_name,
            'last_name'            => $student->last_name,
            'birth_date'           => $student->birth_date,
            'birth_place'          => $student->birth_place,
            'gender'               => $student->gender,
            'num_educ'             => $student->num_educ,
            'parent_full_name'     => $student->parent_full_name,
            'parent_email'         => $student->parent_email,
            'parent_phone'         => $student->parent_phone,
            'registration_type'    => $student->registration_type,
            'total_fees'           => $student->total_fees,
            'amount_paid'          => $totalPaid,
            'moy_trimestre_1'      => $moyennes[1] ?? null,
            'moy_trimestre_2'      => $moyennes[2] ?? null,
            'moy_trimestre_3'      => $moyennes[3] ?? null,
            'moy_annuelle'         => $moyennes['annuelle'] ?? null,
            'rang_annuel'          => $moyennes['rang'] ?? null,
            'statut_deliberation'  => $statut,
            'next_class_id'        => $nextClassId,
            'next_academic_year_id'=> $nextYearId,
            'is_validated'         => $student->is_validated,
            'archived_at'          => now(),
        ];

        return self::updateOrCreate(
            ['student_id' => $student->id, 'academic_year_id' => $year->id],
            $data
        );
    }
}
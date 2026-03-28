<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'birth_place',
        'age',
        'gender',
        'num_educ',
        'entity_id',
        'academic_year_id',
        'class_id',
        'birth_certificate',
        'vaccination_card',
        'previous_report_card',
        'diploma_certificate',
        'parent_full_name',
        'parent_email',
        'parent_phone',
        'school_fees',
        'is_validated',
        'amount_paid',
        'school_fees_paid',
        'registration_type',
        'total_fees',
    ];

    public function calculateTotalFees(){
        if (!$this->classe) {
            return 0;
        }

        $fees = $this->classe->school_fees ?? 0;
        
        if ($this->registration_type === 'new') {
            $fees += $this->classe->registration_fee ?? 0;
        } elseif ($this->registration_type === 're_registration') {
            $fees += $this->classe->re_registration_fee ?? 0;
        }
        
        return $fees;
    }

    public function getRemainingFeesAttribute(){
        return $this->total_fees - $this->total_paid;
    }


    public function entity() {
        return $this->belongsTo(Entity::class);
    }

    public function classe() {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function payments(){
        return $this->hasMany(StudentPayment::class);
    }

    public function getTotalPaidAttribute(){
        return $this->payments->sum('amount');
    }

    public function getPaidAttribute(){
        return $this->classe->school_fees;
    }

    public function getIsFullyPaidAttribute() {
        return ($this->total_fees - $this->total_paid) <= 0;
    }

    public function academicYear(){
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function punishments(){
        return $this->hasMany(Punishment::class);
    }

    public function conducts()
    {
        return $this->hasMany(Conduct::class);
    }


    // Accesseur pour le nom complet
    public function getFullNameAttribute(): string{
        return "{$this->last_name} {$this->first_name}";
    }


    public function grades() {
        return $this->hasMany(Grade::class);
    }

    public function subjectAverages() {
        return $this->hasMany(SubjectAverage::class);
    }

    public function trimestreAverages() {
        return $this->hasMany(StudentTrimestreAverage::class);
    }

    public function annualAverage() {
        return $this->hasOne(StudentAnnualAverage::class);
    }
    public function class()
    {
        return $this->belongsTo(Classe::class);
    }

    
}

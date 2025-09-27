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
    ];


    public function entity() {
        return $this->belongsTo(Entity::class);
    }

    public function classe() {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function payments()
    {
        return $this->hasMany(StudentPayment::class);
    }

    // Calcul montant total payé
    public function getTotalPaidAttribute()
    {
        return $this->payments->sum('amount');
    }

    // Calcul montant restant
    public function getRemainingFeesAttribute(){
        return $this->classe->school_fees - $this->total_paid;
    }

    // Vérifier si tout payé
    public function getIsFullyPaidAttribute(){
        return $this->remaining_fees <= 0;
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function punishments()
    {
        return $this->hasMany(Punishment::class);
    }

    public function conducts()
    {
        return $this->hasMany(Conduct::class);
    }


    // 🔹 Accesseur pour le nom complet
    public function getFullNameAttribute(): string
    {
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

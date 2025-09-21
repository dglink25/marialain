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

}

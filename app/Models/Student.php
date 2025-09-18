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
    ];


    public function entity() {
        return $this->belongsTo(Entity::class);
    }

    public function classe() {
        return $this->belongsTo(Classe::class, 'class_id');
    }
}

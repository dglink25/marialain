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
        'age',
        'entity_id',
        'classe_id',
        'vaccination_card',
        'birth_certificate',
        'previous_report_card',
        'diploma_certificate',
        'parent_full_name',
        'parent_email',
        'school_fees',
    ];

    public function entity() {
        return $this->belongsTo(Entity::class);
    }

    public function classe() {
        return $this->belongsTo(Classe::class);
    }
}

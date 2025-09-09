<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class SchoolClass extends Model{
    use HasFactory;

    protected $table = 'classes';
    protected $fillable = ['school_id','name','level','series','academic_year_id'];

    public function school() { return $this->belongsTo(School::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = ['email', 'token', 'academic_year_id', 'entity'];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
    

}

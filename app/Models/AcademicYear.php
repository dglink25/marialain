<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['name','active'];

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}

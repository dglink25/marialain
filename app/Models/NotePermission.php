<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotePermission extends Model
{
    protected $fillable = ['class_id', 'trimestre', 'is_open'];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }
}

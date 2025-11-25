<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotePermission extends Model
{
    protected $fillable = [
        'class_id',
        'trimestre',
        'is_open',
        'open_at',
        'close_at',
    ];

    protected $dates = ['open_at', 'close_at'];

    public function classe(){
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function checkAutoClose(){
        if ($this->close_at && now()->greaterThan($this->close_at)) {
            if ($this->is_open) {
                $this->is_open = false;
                $this->save();
            }
        }
    }

}


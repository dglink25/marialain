<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotePermission extends Model{
    protected $fillable = [
        'class_id',
        'trimestre',
        'open_at',
        'close_at',
    ];

    protected $dates = ['open_at', 'close_at'];

    protected $appends = ['is_open'];

    public function getIsOpenAttribute(){
        $now = now();

        if ($this->open_at && $now->lt($this->open_at)) {
            return false;
        }

        if ($this->close_at && $now->gt($this->close_at)) {
            return false;
        }

        if ($this->close_at == null) {
            return false;
        }

        if ($this->open_at == null) {
            return false;
        }

        return true;
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}

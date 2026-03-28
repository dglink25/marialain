<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class NoteEditPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'academic_year_id',
        'trimestre',
        'type',
        'is_active',
        'expires_at',
    ];

    protected $dates = ['expires_at'];

    // Vérifie si l’autorisation est encore valable
    public function isValid(): bool
    {
        return $this->is_active && $this->expires_at->isFuture();
    }

    // Relations utiles
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function classe() { return $this->belongsTo(Classe::class, 'class_id'); }
    public function subject() { return $this->belongsTo(Subject::class, 'subject_id'); }
}

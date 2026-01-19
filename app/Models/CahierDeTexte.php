<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CahierDeTexte extends Model{
    protected $table = 'cahier_de_texte';

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'timetable_id',
        'day',
        'content',
        'academic_year_id',
        'course_start_date',
        'course_end_date',
        'is_validated',
        'validated_at',
        'validated_by',
        'validation_notes',
    ];

    protected $casts = [
        'course_start_date' => 'datetime',
        'course_end_date' => 'datetime',
        'validated_at' => 'datetime',
        'is_validated' => 'boolean',
    ];

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function validator() {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function teacher(){
        return $this->belongsTo(User::class);
    }

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

    // Accessor pour la durée formatée
    public function getFormattedDurationAttribute(){
        if (!$this->course_start_date || !$this->course_end_date) {
            return 'N/A';
        }
        
        $start = Carbon::parse($this->course_start_date);
        $end = Carbon::parse($this->course_end_date);
        
        $totalMinutes = $end->diffInMinutes($start);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        return $hours . 'h' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    // Vérifier si le cours est en cours
    public function isCourseOngoing(){
        if (!$this->course_start_date || !$this->course_end_date) {
            return false;
        }
        
        $now = Carbon::now();
        $start = Carbon::parse($this->course_start_date);
        $end = Carbon::parse($this->course_end_date);
        
        return $now->between($start, $end);
    }

    // Vérifier si le cours est terminé
    public function isCourseFinished() {
        if (!$this->course_end_date) {
            return false;
        }
        
        return Carbon::now()->greaterThan(Carbon::parse($this->course_end_date));
    }
}
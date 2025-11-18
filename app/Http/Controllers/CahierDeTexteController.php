<?php 

// app/Http/Controllers/CahierDeTexteController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CahierDeTexte;
use App\Models\Timetable;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\TeacherInvitation;
use Illuminate\Support\Facades\DB;


class CahierDeTexteController extends Controller{
    // Afficher les créneaux du jour pour l'enseignant
    public function show($classeId){
        $teacherId = auth()->id();
        $dayOfWeek = Carbon::now()->format('l'); // ex: Monday
        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        $timetable = Timetable::with(['subject'])
            ->where('class_id', $classeId)
            ->where('teacher_id', $teacherId)
            ->where('day', $dayOfWeek)
            ->where('academic_year_id', $academicYear->id)
            ->get();

        return view('teacher.cahier_de_texte', compact('timetable', 'classeId'));
    }

    // Enregistrer le cahier de texte
    public function store(Request $request){
        $request->validate([
            'class_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'timetable_id' => 'required|integer',
            'day' => 'required|string',
            'content' => 'required|string',
            'motif_retard' => 'nullable|string',
        ]);

        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        // Récupérer l'heure du cours
        $timetable = Timetable::findOrFail($request->timetable_id);

        // Calcul automatique de la durée
        $start = \Carbon\Carbon::parse($timetable->start_time);
        $end   = \Carbon\Carbon::parse($timetable->end_time);

        $durationMinutes = $start->diffInMinutes($end);

        $durationMinutes = $durationMinutes/60;

        $now = Carbon::now();
        $isLate = !$now->between($start, $end);

        // Vérifier si c’est l’heure du cours
        $now = \Carbon\Carbon::now();
        $isDuringClass = $now->between($start, $end);

        // Si hors période → motif obligatoire
        if (!$isDuringClass && !$request->motif_retard) {
            return back()
                ->withErrors(['motif_retard' => 'Veuillez indiquer un motif de retard, vous n’êtes pas dans l’heure du cours.'])
                ->withInput();
        }

        CahierDeTexte::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'timetable_id' => $request->timetable_id,
            'day' => $request->day,
            'content' => $request->content,
            'academic_year_id' => $academicYear->id,
            'motif_retard' => $request->motif_retard,
            'is_late' => $isLate ? 1 : 0,
            'duration_minutes' => $durationMinutes,
        ]);

        return back()->with('success', 'Cahier de texte enregistré avec succès.');
    }

    public function checkCurrentLesson($classeId){
        $teacherId = auth()->id();
        $now = Carbon::now();
        $today = $now->format('l'); // 'Monday', 'Tuesday', etc.
        $timeNow = $now->format('H:i:s');

        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        $currentLesson = Timetable::with('subject')
            ->where('class_id', $classeId)
            ->where('teacher_id', $teacherId)
            ->where('day', $today)
            ->where('academic_year_id', $academicYear->id)
            ->where('start_time', '<=', $timeNow)
            ->where('end_time', '>=', $timeNow)
            ->first();

        return $currentLesson; // null si aucun cours
    }

    public function history($classId){
        $teacherId = Auth::id();
        $now = now();
        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        $class = auth()->user()->classes()
            ->where('classes.id', $classId)
            ->firstOrFail();

        // Cours actuel ?
        $currentLesson = Timetable::with('subject')
            ->where('class_id', $class->id)
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $isDuringLesson = false;
        if ($currentLesson) {
            $start = Carbon::parse($currentLesson->start_time);
            $end   = Carbon::parse($currentLesson->end_time);
            $isDuringLesson = $now->between($start, $end);
        }

        $class->currentLesson = $currentLesson;
        $class->isDuringLesson = $isDuringLesson;
        DB::statement('DISCARD ALL');
        // Cahiers
        $entries = CahierDeTexte::with(['subject', 'timetable'])
            ->where('class_id', $classId)
            ->where('teacher_id', $teacherId)
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.cahier.history', compact('entries', 'class'));
    }


    public function activeTeachers() {
        // On récupère l'année académique active
        $academicYear = AcademicYear::where('active', true)->first();

        if (!$academicYear) {
            return redirect()->back()->with('error', "Aucune année académique active trouvée.");
        }

        // 🔹 2. Récupération des IDs des enseignants invités par un censeur (censeur_id ≠ 0)
        $teacherIds = TeacherInvitation::where('academic_year_id', $academicYear->id)
            ->where('censeur_id', '!=', 0)
            ->pluck('user_id'); // récupère uniquement la colonne teacher_id


        // Récupérer tous les enseignants liés à cette année
        $teachers = User::where('role_id', 8)
        ->get();

        return view('teacher.active', compact('teachers', 'academicYear'));
    }

    public function update(Request $request, $id){
        $entry = CahierDeTexte::findOrFail($id);

        if (now()->diffInMinutes($entry->created_at) > 10) {
            return back()->with('error', "Impossible de modifier ce cahier, délai dépassé.");
        }

        $entry->update([
            'content' => $request->content,
            'motif_retard' => $request->motif_retard,
        ]);

        return back()->with('success', "Cahier de texte modifié.");
    }
    



}

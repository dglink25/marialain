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


class CahierDeTexteController extends Controller
{
    // Afficher les créneaux du jour pour l'enseignant
    public function show($classeId)
    {
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
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        CahierDeTexte::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'timetable_id' => $request->timetable_id,
            'day' => $request->day,
            'content' => $request->content,
            'academic_year_id' => $academicYear->id,
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
        $teacherId = Auth::user()->id;
        $classes = auth()->user()->classes; // relation Teacher->classes

        $today = \Carbon\Carbon::now()->format('l');
        $now = \Carbon\Carbon::now()->format('H:i:s');
        $academicYear = \App\Models\AcademicYear::where('active', 1)->firstOrFail();

        // Pour chaque classe de l'enseignant, déterminer le cours en cours
        foreach ($classes as $class) {
            $class->currentLesson = \App\Models\Timetable::with('subject')
                ->where('class_id', $class->id)
                ->where('teacher_id', $teacherId)
                ->where('day', $today)
                ->where('academic_year_id', $academicYear->id)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>=', $now)
                ->first();
        }

        $entries = \App\Models\CahierDeTexte::with(['subject', 'timetable'])
            ->where('class_id', $classId)
            ->where('teacher_id', $teacherId)
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.cahier.history', compact('entries', 'classes'));
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


}

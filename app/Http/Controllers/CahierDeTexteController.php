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
use App\Models\Subject;
use App\Models\Classe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;



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

    public function activeTeachers($subjectId){
        // Récupérer l'année académique active
        $academicYear = AcademicYear::where('active', true)->first();

        if (!$academicYear) {
            return redirect()->back()->with('error', "Aucune année académique active trouvée.");
        }

        // Charger la matière avec ses enseignants
        $subject = Subject::with(['teachers' => function($query) use ($subjectId, $academicYear) {
            $query->with(['classes' => function($q) use ($subjectId, $academicYear) {
                // Préciser le nom de la table pour éviter l'ambiguïté
                $q->where('classes.academic_year_id', $academicYear->id)
                ->wherePivot('subject_id', $subjectId)
                ->withPivot('amount_brut', 'subject_id');
            }]);
        }])->findOrFail($subjectId);

        return view('teacher.active', compact('subject'));
    }


    public function subjects(){
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            // Si aucune année active, renvoyer une collection vide + message
            return view('censeur.subjects.index', [
                'subjects' => collect(),
                'activeYear' => null,
                'error' => "Aucune année scolaire active n’a été trouvée."
            ]);
        }

        // Récupère uniquement les matières de l'année active
        $subjects = Subject::where('academic_year_id', $activeYear->id)->get();
        
        return view('admin.subjects.index', compact('subjects', 'activeYear'));
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

    public function showTeacherCahier(User $teacher, Classe $classe, Subject $subject){
        $academicYear = AcademicYear::where('active', true)->firstOrFail();

        // Charger les entrées du cahier de texte pour cet enseignant, cette matière et cette classe
        $entries = CahierDeTexte::where('teacher_id', $teacher->id)
            ->where('class_id', $classe->id)
            ->where('subject_id', $subject->id)
            ->orderBy('day', 'asc')
            ->with('timetable')  // charger les horaires
            ->get();

        return view('teacher.bySubject', [
            'teacher' => $teacher,
            'class'   => $classe,   // ⚠️ Assurez-vous que la variable s'appelle bien $class
            'subject' => $subject,
            'entries' => $entries
        ]);
    }



    public function indexBySubject(Subject $subject, User $teacher, Classe $class){
        // Année académique active
        $academicYear = AcademicYear::where('active', true)->firstOrFail();

        // Charger les enseignants liés à cette matière et cette classe
        $teacher->load([
            'classes' => function ($query) use ($academicYear, $class) {
                $query->where('academic_year_id', $academicYear->id)
                    ->where('id', $class->id);
            },
            'cahierDeTexte' => function ($query) use ($subject, $class) {
                $query->where('subject_id', $subject->id)
                    ->where('class_id', $class->id)
                    ->orderBy('day', 'asc');
            },
            'cahierDeTexte.timetable',  // Charger les horaires
            'classes.currentLesson.subject' // Charger la matière actuelle si nécessaire
        ]);

        // Récupérer les entrées pour ce cahier
        $entries = $teacher->cahierDeTexte->where('class_id', $class->id)->where('subject_id', $subject->id);

        return view('teacher.bySubject', compact('subject', 'teacher', 'class', 'entries'));
    }


    public function setBrutAmount(Request $request, User $teacher, Classe $class, Subject $subject){
        $request->validate([
            'amount' => 'required|numeric|min:0'
        ]);

        // Mettre à jour le pivot pour la bonne classe et la bonne matière
        $teacher->class()
            ->wherePivot('subject_id', $subject->id)
            ->updateExistingPivot($class->id, [
                'amount_brut' => $request->amount
            ]);

        return redirect()->back()->with('success', 'Montant brut enregistré pour ' . $teacher->name . 
            ' dans la classe ' . $class->name . ' pour la matière ' . $subject->name);
    }

    public function downloadPdf(Request $request, $subjectId){
        try {
            // ---------------------------------------------------------
            // 1) VALIDATION DE BASE
            // ---------------------------------------------------------
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $start = $request->start_date;
            $end = date('Y-m-d 23:59:59', strtotime($request->end_date));

            // ---------------------------------------------------------
            // 2) ANNÉE ACADÉMIQUE ACTIVE
            // ---------------------------------------------------------
            $academicYear = DB::table('academic_years')->where('active', true)->first();
            if (!$academicYear) {
                return back()->with('error', "Aucune année académique active trouvée.");
            }

            // ---------------------------------------------------------
            // 3) MATIÈRE
            // ---------------------------------------------------------
            $subject = DB::table('subjects')->where('id', $subjectId)->first();
            if (!$subject) {
                return back()->with('error', "Matière introuvable.");
            }

            // ---------------------------------------------------------
            // 4) RÉCUPÉRER LES ENSEIGNANTS QUI ONT DES CAHIERS SUR CETTE MATIÈRE
            // ---------------------------------------------------------
            $teachers = DB::table('users')
                ->join('cahier_de_texte', 'users.id', '=', 'cahier_de_texte.teacher_id')
                ->where('cahier_de_texte.subject_id', $subjectId)
                ->whereBetween('cahier_de_texte.updated_at', [$start, $end])
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.gender',
                    'users.phone',
                    'users.id_card_number'
                )
                ->distinct()
                ->get();

            if ($teachers->isEmpty()) {
                return back()->with('error', "Aucun enseignant trouvé pour la période sélectionnée.");
            }

            // ---------------------------------------------------------
            // 5) POUR CHAQUE ENSEIGNANT : CALCUL DES MINUTES + CLASSES + MONTANTS
            // ---------------------------------------------------------
            foreach ($teachers as $teacher) {
                // ---- TOTAL DES MINUTES ----
                $totalMinutes = DB::table('cahier_de_texte')
                    ->where('teacher_id', $teacher->id)
                    ->where('subject_id', $subjectId)
                    ->whereBetween('updated_at', [$start, $end])
                    ->sum('duration_minutes');

                $teacher->total_minutes = $totalMinutes;
                $teacher->total_hours = $totalMinutes; // conversion en heures

                // ---- LES CLASSES LIÉES (table pivot) ----
                $classes = DB::table('class_teacher_subject')
                    ->join('classes', 'classes.id', '=', 'class_teacher_subject.class_id')
                    ->where('class_teacher_subject.teacher_id', $teacher->id)
                    ->where('class_teacher_subject.subject_id', $subjectId)
                    ->where('classes.academic_year_id', $academicYear->id)
                    ->select(
                        'classes.id as class_id',
                        'classes.name as class_name',
                        'class_teacher_subject.amount_brut'
                    )
                    ->get();

                // ---- CALCULS FINANCIERS ----
                foreach ($classes as $classe) {
                    $rate = $classe->amount_brut ?? 0;
                    $totalBrut = round($teacher->total_hours * $rate, 2);
                    $classe->total_brut  = $totalBrut;
                    $classe->aib         = round($totalBrut * 0.05, 2);
                    $classe->emmagement  = '';
                }

                $teacher->classes_for_subject = $classes;
            }

            // ---------------------------------------------------------
            // 6) GÉNÉRATION DU PDF
            // ---------------------------------------------------------
            $pdf = Pdf::loadView('teacher.pdf', [
                'subject'  => $subject,
                'teachers' => $teachers,
                'start'    => $start,
                'end'      => $end,
            ]);

            return $pdf->download('paiement_enseignants_'.$subject->name.'.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur PDF enseignants : '.$e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération du PDF. '.$e->getMessage());
        }
    }






}

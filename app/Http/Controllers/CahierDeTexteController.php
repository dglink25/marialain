<?php 

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
        $dayOfWeek = Carbon::now()->format('l');
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
            'course_start_date' => 'required|date',
            'course_end_date' => 'required|date|after:course_start_date',
        ]);

        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        // Vérifier que la date de fin est après la date de début
        $startDate = Carbon::parse($request->course_start_date);
        $endDate = Carbon::parse($request->course_end_date);
        
        if ($endDate->lessThanOrEqualTo($startDate)) {
            return back()
                ->withErrors(['course_end_date' => 'La date de fin doit être après la date de début.'])
                ->withInput();
        }

        // Vérifier que la durée n'excède pas 8 heures (480 minutes)
        $durationMinutes = $startDate->diffInMinutes($endDate);
        if ($durationMinutes > 480) {
            return back()
                ->withErrors(['course_end_date' => 'La durée du cours ne peut excéder 8 heures.'])
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
            'course_start_date' => $startDate,
            'course_end_date' => $endDate,
        ]);

        return back()->with('success', 'Cahier de texte enregistré avec succès.');
    }

    // Mettre à jour le cahier de texte
    public function update(Request $request, $id){
        $request->validate([
            'content' => 'required|string',
            'course_start_date' => 'required|date',
            'course_end_date' => 'required|date|after:course_start_date',
        ]);

        $cahier = CahierDeTexte::findOrFail($id);
        
        // Vérifier que l'enseignant peut modifier (dans les 10 minutes suivant la création)
        $canEdit = Carbon::now()->diffInMinutes($cahier->created_at) <= 10;
        
        if (!$canEdit) {
            return back()->with('error', 'Le délai de modification est expiré (10 minutes maximum).');
        }

        $startDate = Carbon::parse($request->course_start_date);
        $endDate = Carbon::parse($request->course_end_date);
        
        if ($endDate->lessThanOrEqualTo($startDate)) {
            return back()
                ->withErrors(['course_end_date' => 'La date de fin doit être après la date de début.'])
                ->withInput();
        }

        // Vérifier que la durée n'excède pas 8 heures
        $durationMinutes = $startDate->diffInMinutes($endDate);
        if ($durationMinutes > 480) {
            return back()
                ->withErrors(['course_end_date' => 'La durée du cours ne peut excéder 8 heures.'])
                ->withInput();
        }

        $cahier->update([
            'content' => $request->content,
            'course_start_date' => $startDate,
            'course_end_date' => $endDate,
        ]);

        return back()->with('success', 'Cahier de texte mis à jour avec succès.');
    }

    public function history($classId, $subjectId){
        $teacherId = Auth::id();
        $now = now();

        $academicYear = AcademicYear::where('active', 1)->firstOrFail();

        // Vérifier que la ligne existe dans class_teacher_subject
        $exists = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYear->id)
            ->exists();

        if (!$exists) {
            abort(404, "Vous n'enseignez pas cette matière dans cette classe.");
        }

        // Charger la classe
        $class = DB::table('classes')->where('id', $classId)->first();
        if (!$class) abort(404);

        // Charger la matière
        $subject = DB::table('subjects')->where('id', $subjectId)->first();
        if (!$subject) abort(404);

        // Cours actuel
        $currentLesson = DB::table('timetables')
            ->where('class_id', $classId)
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        // Cahier filtré par matière + enseignant
        $entries = CahierDeTexte::with(['subject', 'timetable'])
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', $teacherId)
            ->orderByDesc('course_start_date')
            ->get();

        return view('teacher.cahier.history', [
            'entries'        => $entries,
            'class'          => $class,
            'subject'        => $subject,
            'currentLesson'  => $currentLesson,
        ]);
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
    
    // Afficher les cahiers de texte d'un enseignant spécifique (pour admin/chef)
    public function showTeacherCahier(User $teacher, Classe $classe, Subject $subject){
        $academicYear = AcademicYear::where('active', true)->firstOrFail();

        // Charger les entrées du cahier de texte pour cet enseignant, cette matière et cette classe
        $entries = CahierDeTexte::where('teacher_id', $teacher->id)
            ->where('class_id', $classe->id)
            ->where('subject_id', $subject->id)
            ->orderBy('course_start_date', 'desc')
            ->with(['timetable', 'validator'])
            ->get();

        // Statistiques
        $stats = [
            'total' => $entries->count(),
            'validated' => $entries->where('is_validated', true)->count(),
            'pending' => $entries->where('is_validated', false)->count(),
            'this_month' => $entries->whereBetween('course_start_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];

        return view('teacher.bySubject', [
            'teacher' => $teacher,
            'class'   => $classe,
            'subject' => $subject,
            'entries' => $entries,
            'stats'   => $stats,
            'canValidate' => "Censeur"
        ]);
    }

    // Valider un cahier de texte individuel
    public function validateEntry(Request $request, CahierDeTexte $cahier){
        $request->validate([
            'validation_notes' => 'nullable|string|max:500',
            'action' => 'required|in:validate,reject'
        ]);

        if ($request->action === 'validate') {
            $cahier->update([
                'is_validated' => true,
                'validated_at' => now(),
                'validated_by' => Auth::id(),
                'validation_notes' => $request->validation_notes
            ]);
            
            $message = 'Cahier de texte validé avec succès.';
        } else {
            $cahier->update([
                'is_validated' => false,
                'validated_at' => null,
                'validated_by' => null,
                'validation_notes' => $request->validation_notes
            ]);
            
            $message = 'Cahier de texte rejeté avec succès.';
        }

        return back()->with('success', $message);
    }

    // Valider plusieurs cahiers de texte en une fois
    public function validateMultiple(Request $request){
        $request->validate([
            'entry_ids' => 'required|array',
            'entry_ids.*' => 'exists:cahier_de_texte,id',
            'validation_notes' => 'nullable|string|max:500',
            'action' => 'required|in:validate,reject'
        ]);

        $count = 0;
        $notes = $request->validation_notes ?: 'Validation groupée';

        foreach ($request->entry_ids as $entryId) {
            $cahier = CahierDeTexte::find($entryId);
            
            if ($request->action === 'validate') {
                $cahier->update([
                    'is_validated' => true,
                    'validated_at' => now(),
                    'validated_by' => Auth::id(),
                    'validation_notes' => $notes
                ]);
            } else {
                $cahier->update([
                    'is_validated' => false,
                    'validated_at' => null,
                    'validated_by' => null,
                    'validation_notes' => $notes . ' (rejeté)'
                ]);
            }
            
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' cahier(s) ' . ($request->action === 'validate' ? 'validé(s)' : 'rejeté(s)') . ' avec succès.',
            'count' => $count
        ]);
    }

    // Télécharger le rapport PDF
    public function downloadReport(User $teacher, Classe $classe, Subject $subject){
        $academicYear = AcademicYear::where('active', true)->firstOrFail();

        $entries = CahierDeTexte::where('teacher_id', $teacher->id)
            ->where('class_id', $classe->id)
            ->where('subject_id', $subject->id)
            ->orderBy('course_start_date', 'desc')
            ->with(['timetable', 'validator'])
            ->get();

        $stats = [
            'total' => $entries->count(),
            'validated' => $entries->where('is_validated', true)->count(),
            'pending' => $entries->where('is_validated', false)->count(),
        ];

        $pdf = Pdf::loadView('pdf.teacher-cahier-report', [
            'teacher' => $teacher,
            'class' => $classe,
            'subject' => $subject,
            'entries' => $entries,
            'stats' => $stats,
            'generated_at' => now(),
            'generated_by' => Auth::user()->name
        ]);

        $filename = "cahier-texte-{$teacher->name}-{$classe->name}-{$subject->name}-" . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
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
                    ->whereBetween('updated_at', [$end, $start])
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

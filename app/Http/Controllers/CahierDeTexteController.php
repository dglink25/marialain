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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;

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

    private function findConflict(int $teacherId, int $classId, int $subjectId, Carbon $start, Carbon $end, ?int $ignoreId = null)    {
        return CahierDeTexte::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('course_start_date', '<', $end)
            ->where('course_end_date', '>', $start)
            ->first();
    }

    private function duplicateMessage(CahierDeTexte $c): string    {
        return 'Un cahier de texte existe déjà pour ce créneau ('
            . Carbon::parse($c->course_start_date)->format('d/m/Y H:i') . ' - '
            . Carbon::parse($c->course_end_date)->format('H:i')
            . ') pour cette classe et cette matière.';
    }

    // Vérification AJAX avant soumission
    public function checkDuplicate(Request $request)    {
        $data = $request->validate([
            'class_id'          => 'required|integer',
            'subject_id'        => 'required|integer',
            'course_start_date' => 'required|date',
            'course_end_date'   => 'required|date',
            'ignore_id'         => 'nullable|integer',
        ]);

        $conflict = $this->findConflict(
            Auth::id(),
            (int) $data['class_id'],
            (int) $data['subject_id'],
            Carbon::parse($data['course_start_date']),
            Carbon::parse($data['course_end_date']),
            $data['ignore_id'] ?? null
        );

        return response()->json([
            'duplicate' => (bool) $conflict,
            'message'   => $conflict ? $this->duplicateMessage($conflict) : null,
        ]);
    }

    // Enregistrer le cahier de texte
    public function store(Request $request)    {
        $request->validate([
            'class_id'          => 'required|integer',
            'subject_id'        => 'required|integer',
            'timetable_id'      => 'required|integer',
            'day'               => 'required|string',
            'content'           => 'required|string',
            'course_start_date' => 'required|date',
            'course_end_date'   => 'required|date|after:course_start_date',
            'idempotency_key'   => 'required|uuid',
        ]);

        // L'enseignant vient TOUJOURS de la session, jamais du formulaire
        $teacherId = Auth::id();
        $key       = $request->idempotency_key;

        // 1) Rejeu de la même requête (double clic, F5, bouton retour) : on répond OK sans recréer
        if (CahierDeTexte::where('idempotency_key', $key)->exists()) {
            return back()->with('success', 'Cahier de texte déjà enregistré.');
        }

        $academicYear = AcademicYear::where('active', 1)->firstOrFail();
        $startDate = Carbon::parse($request->course_start_date);
        $endDate   = Carbon::parse($request->course_end_date);

        if ($endDate->lessThanOrEqualTo($startDate)) {
            return back()->withErrors(['course_end_date' => 'La date de fin doit être après la date de début.'])->withInput();
        }

        if ($startDate->diffInMinutes($endDate, true) > 480) {
            return back()->withErrors(['course_end_date' => 'La durée du cours ne peut excéder 8 heures.'])->withInput();
        }

        // 2) Verrou : bloque deux requêtes simultanées sur le même créneau
        $lock = Cache::lock(
            "cahier-store:{$teacherId}:{$request->class_id}:{$request->subject_id}:" . $startDate->format('YmdHi'),
            10
        );

        if (!$lock->get()) {
            return back()->with('error', 'Une soumission est déjà en cours, veuillez patienter.');
        }

        try {
            // 3) Doublon / chevauchement
            $conflict = $this->findConflict(
                $teacherId, (int) $request->class_id, (int) $request->subject_id, $startDate, $endDate
            );

            if ($conflict) {
                return back()
                    ->withErrors(['course_start_date' => $this->duplicateMessage($conflict)])
                    ->withInput();
            }

            CahierDeTexte::create([
                'class_id'          => $request->class_id,
                'subject_id'        => $request->subject_id,
                'teacher_id'        => $teacherId,
                'timetable_id'      => $request->timetable_id,
                'day'               => $request->day,
                'content'           => $request->content,
                'academic_year_id'  => $academicYear->id,
                'course_start_date' => $startDate,
                'course_end_date'   => $endDate,
                'is_validated'      => false,
                'idempotency_key'   => $key,
            ]);
        } catch (QueryException $e) {
            // 4) Dernier rempart : contrainte unique en base (code 23000)
            if ($e->getCode() === '23000') {
                return back()->with('error', 'Ce cahier de texte existe déjà (doublon refusé).')->withInput();
            }
            throw $e;
        } finally {
            $lock->release();
        }

        return back()->with('success', 'Cahier de texte enregistré avec succès.');
    }

    // Mettre à jour le cahier de texte
    public function update(Request $request, $id)    {
        $request->validate([
            'content'           => 'required|string',
            'course_start_date' => 'required|date',
            'course_end_date'   => 'required|date|after:course_start_date',
        ]);

        // L'enseignant ne peut modifier que SES cahiers
        $cahier = CahierDeTexte::where('teacher_id', Auth::id())->findOrFail($id);

        $canEdit = Carbon::now()->diffInMinutes($cahier->created_at, true) <= 10;
        if (!$canEdit) {
            return back()->with('error', 'Le délai de modification est expiré (10 minutes maximum).');
        }

        $startDate = Carbon::parse($request->course_start_date);
        $endDate   = Carbon::parse($request->course_end_date);

        if ($endDate->lessThanOrEqualTo($startDate)) {
            return back()->withErrors(['course_end_date' => 'La date de fin doit être après la date de début.'])->withInput();
        }

        if ($startDate->diffInMinutes($endDate, true) > 480) {
            return back()->withErrors(['course_end_date' => 'La durée du cours ne peut excéder 8 heures.'])->withInput();
        }

        // Doublon avec un AUTRE cahier (on s'exclut soi-même)
        $conflict = $this->findConflict(
            Auth::id(), (int) $cahier->class_id, (int) $cahier->subject_id, $startDate, $endDate, $cahier->id
        );

        if ($conflict) {
            return back()
                ->withErrors(['course_start_date' => $this->duplicateMessage($conflict)])
                ->withInput();
        }

        try {
            $cahier->update([
                'content'           => $request->content,
                'course_start_date' => $startDate,
                'course_end_date'   => $endDate,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->with('error', 'Un cahier existe déjà sur ce créneau.')->withInput();
            }
            throw $e;
        }

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

        // Charger la matière avec ses enseignants DISTINCTS
        $subject = Subject::with(['teachers' => function($query) use ($subjectId, $academicYear) {
            $query->distinct()->with(['classes' => function($q) use ($subjectId, $academicYear) {
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

    public function downloadPdf(Request $request, $subjectId){
        try {
            // Validation
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            // Récupérer les données de base
            $subject = Subject::findOrFail($subjectId);
            $academicYear = AcademicYear::where('active', true)->firstOrFail();
            
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Récupérer tous les cahiers de texte pour la période
            $cahiers = CahierDeTexte::with(['teacher', 'classe'])
                ->where('subject_id', $subjectId)
                ->where('is_validated', true)
                ->whereBetween('course_start_date', [$startDate, $endDate])
                ->get();

            if ($cahiers->isEmpty()) {
                return back()->with('error', "Aucun cahier de texte validé par le censeur trouvé pour la période sélectionnée.");
            }

            // Grouper par enseignant
            $cahiersByTeacher = $cahiers->groupBy('teacher_id');

            // Préparer les données pour chaque enseignant
            $teachersData = [];
            
            foreach ($cahiersByTeacher as $teacherId => $teacherCahiers) {
                $teacher = $teacherCahiers->first()->teacher;
                
                // Calculer la durée totale pour cet enseignant
                $totalMinutes = 0;
                $classesMap = [];
                
                foreach ($teacherCahiers as $cahier) {
                    if ($cahier->course_start_date && $cahier->course_end_date) {
                        $totalMinutes += $cahier->course_end_date->diffInMinutes($cahier->course_start_date);
                    }
                    
                    // Collecter les classes uniques
                    if ($cahier->classe) {
                        $classId = $cahier->classe->id;
                        if (!isset($classesMap[$classId])) {
                            $classesMap[$classId] = (object)[
                                'class_id' => $classId,
                                'class_name' => $cahier->classe->name,
                                'amount_brut' => 0,
                                'total_brut' => 0,
                                'aib' => 0,
                                'emmagement' => ''
                            ];
                        }
                    }
                }
                
                // Convertir la map en collection
                $classes = collect(array_values($classesMap));
                
                // Récupérer les taux pour chaque classe
                if (!$classes->isEmpty()) {
                    try {
                        // Essayer de récupérer les taux avec la table pivot
                        $classRates = DB::table('class_teacher_subject')
                            ->where('teacher_id', $teacherId)
                            ->where('subject_id', $subjectId)
                            ->whereIn('class_id', $classes->pluck('class_id'))
                            ->select('class_id', 'amount_brut')
                            ->get()
                            ->keyBy('class_id');
                            
                        foreach ($classes as $classe) {
                            if (isset($classRates[$classe->class_id])) {
                                $classe->amount_brut = $classRates[$classe->class_id]->amount_brut;
                            } else {
                                // Valeur par défaut si aucun taux trouvé
                                $classe->amount_brut = 0;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::info("Erreur récupération taux : " . $e->getMessage());
                        // Si erreur, initialiser tous les taux à 0
                        foreach ($classes as $classe) {
                            $classe->amount_brut = 0;
                        }
                    }
                }
                
                // Calculer les heures totales pour cet enseignant
                $totalHours = round($totalMinutes / 60, 2);
                
                // Calculer les montants pour chaque classe de cet enseignant
                foreach ($classes as $classe) {
                    $rate = $classe->amount_brut ;
                    $classe->total_brut = round($totalHours * $rate, 2);
                    $classe->aib = round($classe->total_brut * 0.05, 2);
                    // Emmagement reste vide comme dans la vue
                    $classe->emmagement = '';
                    $classe->amount_brut = $classe->aib - $classe->total_brut;
                }
                
                // Créer l'objet enseignant avec les propriétés attendues par la vue
                $teacherData = (object)[
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email ?? '--',
                    'phone' => $teacher->phone ?? '--',
                    'id_card_number' => $teacher->id_card_number ?? '--',
                    'total_hours' => $totalHours,
                    'total_minutes' => $totalMinutes,
                    // La vue attend une propriété 'classes_for_subject' qui est un tableau d'objets
                    'classes_for_subject' => $classes->toArray(),
                    'cahiers_count' => $teacherCahiers->count(),
                    // Propriétés calculées pour l'enseignant (utilisées pour les totaux)
                    'teacher_total_hours' => $totalHours,
                    'teacher_total_brut_heure' => $classes->avg('amount_brut'),
                    'teacher_total_brut_total' => $classes->sum('total_brut'),
                    'teacher_total_aib' => $classes->sum('aib')
                ];
                
                $teachersData[] = $teacherData;
            }

            // Ajouter les totaux globaux pour la vue
            $grand_total_heures = collect($teachersData)->sum('teacher_total_hours');
            $grand_total_brut_heure = collect($teachersData)->sum('teacher_total_brut_heure');
            $grand_total_brut_total = collect($teachersData)->sum('teacher_total_brut_total');
            $grand_total_aib = collect($teachersData)->sum('teacher_total_aib');

            // Générer le PDF

            $pdf = PDF::loadView('teacher.pdf', [
                'subject' => $subject,
                'teachers' => $teachersData,
                'start' => $startDate, // Note: la vue utilise 'start' pas 'start_date'
                'end' => $endDate,     // Note: la vue utilise 'end' pas 'end_date'
                'academic_year' => $academicYear,
                'generated_at' => now(),
                'generated_by' => auth()->user()->name,
                // Variables globales pour les totaux
                'grand_total_heures' => $grand_total_heures,
                'grand_total_brut_heure' => $grand_total_brut_heure,
                'grand_total_brut_total' => $grand_total_brut_total,
                'grand_total_aib' => $grand_total_aib
            ]);


            $filename = 'rapport_enseignants_' . Str::slug($subject->name) . '_' . now()->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);

        } 
        catch (\Exception $e) {
            Log::error('Erreur PDF : ' . $e->getMessage());
            Log::error('Stack trace : ' . $e->getTraceAsString());
            return back()->with('error', 'Une erreur est survenue lors de la génération du PDF : ' . $e->getMessage());
        }
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



}

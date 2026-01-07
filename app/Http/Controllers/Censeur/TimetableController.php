<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Timetable;
use App\Models\Subject;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Validator;

class TimetableController extends Controller
{
    public function index($classId) {
        try {
            // Vérifie s'il existe une année active
            $activeYear = AcademicYear::where('active', true)->first();

            if (!$activeYear) {
                return back()->with('error', 'Aucune année scolaire active trouvée.');
            }

            // Vérifie si la classe existe et appartient à l'année active
            $class = Classe::where('id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->firstOrFail();

            // Récupération des emplois du temps pour cette classe et année
            $timetables = Timetable::where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->with(['teacher', 'subject'])
                ->get()
                ->groupBy('day'); // Grouper par jour pour faciliter le traitement

            // Jours de la semaine dans l'ordre
            $daysOrder = ['Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 3, 'Jeudi' => 4, 'Vendredi' => 5, 'Samedi' => 6];
            
            // Trier les emplois du temps par jour et heure
            $sortedTimetables = collect($timetables)->map(function($dayCourses) {
                return $dayCourses->sortBy('start_time');
            })->sortBy(function($value, $key) use ($daysOrder) {
                return $daysOrder[$key] ?? 99;
            });

            // Génération des heures (7h à 18h)
            $hours = [];
            for ($h = 7; $h < 18; $h++) {
                $hours[] = [
                    'slot' => sprintf('%02dh-%02dh', $h, $h + 1),
                    'start' => sprintf('%02d:00', $h),
                    'end' => sprintf('%02d:00', $h + 1)
                ];
            }

            // Récupère seulement les matières et profs de l'année active
            $subjects = Subject::where('academic_year_id', $activeYear->id)
                ->where('coefficient', '>', 0)
                ->orderBy('name')
                ->get();

            $teachers = User::whereHas('role', fn($q) => $q->where('id', 8))
                ->whereHas('invitationTeacher', fn($q) => $q->where('censeur_id', 4))
                ->orderBy('name')
                ->get();

            return view('censeur.timetables.index', compact(
                'class', 
                'hours', 
                'sortedTimetables', 
                'subjects', 
                'teachers', 
                'activeYear',
                'daysOrder'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Classe non trouvée: ' . $e->getMessage());
            return back()->with('error', 'Classe introuvable.');
        } catch (\Exception $e) {
            Log::error('Erreur chargement emploi du temps: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement de l\'emploi du temps: ' . $e->getMessage());
        }
    }

    public function edit($classId, $id) {
        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
            $class = Classe::findOrFail($classId);
            $timetable = Timetable::where('id', $id)
                ->where('class_id', $classId)
                ->firstOrFail();

            $teachers = User::whereHas('role', fn($q) => $q->where('id', 8))
                ->whereHas('invitationTeacher', fn($q) => $q->where('censeur_id', 4))
                ->orderBy('name')
                ->get();

            $subjects = Subject::where('academic_year_id', $activeYear->id)
                ->where('coefficient', '>', 0)
                ->orderBy('name')
                ->get();

            return view('censeur.timetables.edit', compact('class', 'timetable', 'teachers', 'subjects'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Données non trouvées pour édition: ' . $e->getMessage());
            return redirect()->route('censeur.timetables.index', $classId)
                ->with('error', 'Créneau introuvable.');
        } catch (\Exception $e) {
            Log::error('Erreur édition emploi du temps: ' . $e->getMessage());
            return redirect()->route('censeur.timetables.index', $classId)
                ->with('error', 'Erreur lors du chargement de l\'édition: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $classId, $id){
        DB::beginTransaction();

        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:users,id',
                'subject_id' => 'required|exists:subjects,id',
                'day' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
            ], [
                'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
                'start_time.date_format' => 'Format d\'heure invalide.',
                'end_time.date_format' => 'Format d\'heure invalide.',
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
            }

            // Vérifier les conflits d'horaire
            $conflict = Timetable::where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->where('day', $request->day)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                    });
                })
                ->exists();

            if ($conflict) {
                return back()
                    ->withInput()
                    ->with('error', 'Conflit d\'horaire : un cours existe déjà sur ce créneau.');
            }

            $timetable = Timetable::where('id', $id)
                ->where('class_id', $classId)
                ->firstOrFail();

            // Sauvegarder les anciennes valeurs
            $oldData = $timetable->toArray();

            // Mettre à jour le créneau
            $timetable->update([
                'teacher_id' => $request->teacher_id,
                'subject_id' => $request->subject_id,
                'day' => $request->day,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            // Mettre à jour la table de relation class_teacher_subject
            $this->updateClassTeacherSubject($classId, $oldData, $request->all());

            DB::commit();

            return redirect()->route('censeur.timetables.index', $classId)
                ->with('success', 'Créneau modifié avec succès.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Créneau non trouvé pour modification: ' . $e->getMessage());
            return back()->with('error', 'Créneau introuvable.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur modification emploi du temps: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification du créneau: ' . $e->getMessage());
        }
    }

    private function updateClassTeacherSubject($classId, $oldData, $newData) {
        try {
            // Supprimer l'ancienne relation si elle existe
            DB::table('class_teacher_subject')
                ->where('class_id', $classId)
                ->where('teacher_id', $oldData['teacher_id'])
                ->where('subject_id', $oldData['subject_id'])
                ->delete();

            // Vérifier si la nouvelle relation existe déjà
            $existing = DB::table('class_teacher_subject')
                ->where('class_id', $classId)
                ->where('teacher_id', $newData['teacher_id'])
                ->where('subject_id', $newData['subject_id'])
                ->exists();

            if (!$existing) {
                // Ajouter la nouvelle relation
                DB::table('class_teacher_subject')->insert([
                    'class_id' => $classId,
                    'teacher_id' => $newData['teacher_id'],
                    'subject_id' => $newData['subject_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour class_teacher_subject: ' . $e->getMessage());
            throw $e;
        }
    }

    public function destroy($classId, $timetableId){
        DB::beginTransaction();

        try {
            $timetable = Timetable::where('id', $timetableId)
                ->where('class_id', $classId)
                ->firstOrFail();

            // Sauvegarder les données pour suppression de la relation
            $teacherId = $timetable->teacher_id;
            $subjectId = $timetable->subject_id;

            $timetable->delete();

            // Vérifier si d'autres créneaux utilisent la même relation
            $otherTimetables = Timetable::where('class_id', $classId)
                ->where('teacher_id', $teacherId)
                ->where('subject_id', $subjectId)
                ->exists();

            // Si aucun autre créneau n'utilise cette relation, la supprimer
            if (!$otherTimetables) {
                DB::table('class_teacher_subject')
                    ->where('class_id', $classId)
                    ->where('teacher_id', $teacherId)
                    ->where('subject_id', $subjectId)
                    ->delete();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Emploi du temps supprimé avec succès.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Emploi du temps non trouvé pour suppression: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Emploi du temps introuvable.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression emploi du temps: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function store(Request $request, $classId){
        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();

            // Validation avant transaction
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:users,id',
                'subject_id' => 'required|exists:subjects,id',
                'day' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
            ], [
                'teacher_id.exists' => 'L\'enseignant sélectionné n\'existe pas.',
                'subject_id.exists' => 'La matière sélectionnée n\'existe pas.',
                'end_time.after' => 'L\'heure de fin doit être après l\'heure de début.',
                'start_time.date_format' => 'Format d\'heure invalide (HH:MM).',
                'end_time.date_format' => 'Format d\'heure invalide (HH:MM).',
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Veuillez corriger les erreurs ci-dessous.');
            }

            DB::beginTransaction();

            // Vérifier si l'utilisateur est bien un enseignant
            $teacher = User::find($request->teacher_id);
            if (!$teacher || $teacher->role !== 'teacher') {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'L\'utilisateur sélectionné n\'est pas un enseignant.');
            }

            // Vérifier si la matière existe
            $subject = Subject::find($request->subject_id);
            if (!$subject) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'La matière sélectionnée n\'existe pas.');
            }

            // Vérifier les conflits d'horaire
            $conflict = Timetable::where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->where('day', $request->day)
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                        ->where('end_time', '>', $request->start_time);
                    });
                })
                ->exists();

            if ($conflict) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'Conflit d\'horaire : un cours existe déjà sur ce créneau.');
            }

            // Créer le nouveau créneau
            Timetable::create([
                'class_id' => $classId,
                'teacher_id' => $request->teacher_id,
                'subject_id' => $request->subject_id,
                'day' => $request->day,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'academic_year_id' => $activeYear->id,
            ]);

            // Vérifier si la relation existe déjà
            $existingRelation = DB::table('class_teacher_subject')
                ->where('class_id', $classId)
                ->where('teacher_id', $request->teacher_id)
                ->where('subject_id', $request->subject_id)
                ->where('academic_year_id', $activeYear->id)
                ->exists();

            if (!$existingRelation) {
                DB::table('class_teacher_subject')->insert([
                    'academic_year_id' => $activeYear->id,
                    'class_id' => $classId,
                    'teacher_id' => $request->teacher_id,
                    'subject_id' => $request->subject_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Créneau ajouté avec succès.');

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            Log::error('Erreur ajout créneau: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            
            // Message d'erreur générique pour l'utilisateur
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'ajout du créneau. Veuillez réessayer.');
        }
    }

    public function download($classId){
        try {
            $activeYear = AcademicYear::where('active', true)->firstOrFail();
            $class = Classe::findOrFail($classId);

            // Utilisation de la même logique de tri que dans index()
            $timetables = Timetable::where('class_id', $classId)
                ->where('academic_year_id', $activeYear->id)
                ->with(['teacher', 'subject'])
                ->orderByRaw("
                    CASE 
                        WHEN day = 'Lundi' THEN 1
                        WHEN day = 'Mardi' THEN 2
                        WHEN day = 'Mercredi' THEN 3
                        WHEN day = 'Jeudi' THEN 4
                        WHEN day = 'Vendredi' THEN 5
                        WHEN day = 'Samedi' THEN 6
                        ELSE 7
                    END
                ")
                ->orderBy('start_time')
                ->get();

            $pdf = Pdf::loadView('censeur.timetables.pdf', compact('class', 'timetables', 'activeYear'));

            return $pdf->download('emploi-du-temps-' . $class->name . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Méthode alternative utilisant une collection pour le tri
     * Utile si vous préférez trier côté PHP plutôt que côté base de données
     */
    private function getOrderedTimetables($classId, $activeYearId) {
        $timetables = Timetable::where('class_id', $classId)
            ->where('academic_year_id', $activeYearId)
            ->with(['teacher', 'subject'])
            ->get();

        // Définir l'ordre des jours
        $dayOrder = [
            'Lundi' => 1,
            'Mardi' => 2,
            'Mercredi' => 3,
            'Jeudi' => 4,
            'Vendredi' => 5,
            'Samedi' => 6
        ];

        // Trier la collection
        return $timetables->sortBy(function ($timetable) use ($dayOrder) {
            return [
                $dayOrder[$timetable->day] ?? 7, // Ordre du jour
                $timetable->start_time // Puis par heure de début
            ];
        })->values();
    }

    public function downloadPDF($classId){

        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        // Vérifie si la classe existe et appartient à l’année active
        $class = Classe::where('id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->first();

        if (!$class) {
            return back()->with('error', 'Classe introuvable pour l’année scolaire active.');
        }


        $class = Classe::findOrFail($classId);

        $timetables = Timetable::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->with('teacher', 'subject')
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Générer les créneaux horaires
        $hours = [];
        $start = 7; // 07:00
        $end = 19;  // 19:00

        for ($h = $start; $h < $end; $h++) {
            $hours[] = sprintf('%02dh-%02dh', $h, $h + 1);
        }

        $dateDownload = now()->format('d/m/Y');
        
        $pdf = Pdf::loadView('censeur.timetables.pdf', compact('class','timetables','hours', 'dateDownload'));
        return $pdf->download("Emploi_du_temps_{$class->name}.pdf");
    }

}
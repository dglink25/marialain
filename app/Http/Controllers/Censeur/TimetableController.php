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
use App\Models\AcademicYear;

class TimetableController extends Controller{
    public function index($classId){
        // Vérifie s'il existe une année active
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

        // Récupération des emplois du temps pour cette classe et année
        $timetables = Timetable::where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->with('teacher', 'subject')
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Génération des heures sous forme "07h-08h", "08h-09h", ...
        $hours = [];
        for ($h = 7; $h < 19; $h++) {
            $hours[] = sprintf('%02dh-%02dh', $h, $h + 1);
        }

        // Récupère seulement les matières et profs de l’année active
        $subjects = Subject::where('academic_year_id', $activeYear->id)
                            ->where('coefficient', '>', 0)
                            ->get();
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))
                        ->whereHas('invitationTeacher', fn($q) => $q->where('censeur_id', 6))
                        ->get();

        return view('censeur.timetables.index', compact('class', 'hours', 'timetables', 'subjects', 'teachers', 'activeYear'));
    }


    public function edit($classId, $id){

        $activeYear = AcademicYear::where('active', true)->first();
        $class = Classe::findOrFail($classId);
        $timetable = Timetable::findOrFail($id);
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))
                        ->whereHas('invitationTeacher', fn($q) => $q->where('censeur_id', 6))
                        ->get();

        $subjects = Subject::where('academic_year_id', $activeYear->id)
                            ->where('coefficient', '>', 0)
                            ->get();

        return view('censeur.timetables.edit', compact('class','timetable','teachers','subjects'));
    }

    public function update(Request $request, $classId, $id){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'day' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
            'start_time' => 'nullable',
            'end_time' => 'nullable|after:start_time',
        ]);

        $timetable = Timetable::findOrFail($id);

        // anciennes valeurs avant modification
        $oldTeacherId = $timetable->teacher_id;
        $oldSubjectId = $timetable->subject_id;

        DB::beginTransaction();
        try {

            $timetable->update($request->only('teacher_id','subject_id','day','start_time','end_time'));

            $affected = DB::table('class_teacher_subject')
                ->where('class_id', $classId)
                ->where('teacher_id', $oldTeacherId)
                ->where('subject_id', $oldSubjectId)
                ->update([
                    'teacher_id' => $request->teacher_id,
                    'subject_id' => $request->subject_id,
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                // Rien trouvé : soit la ligne n'existait pas, soit elle a déjà été modifiée.
                // Pour éviter doublons on supprime d'abord toute éventuelle ligne identique (nouvelle combinaison)
                DB::table('class_teacher_subject')
                    ->where('class_id', $classId)
                    ->where('teacher_id', $request->teacher_id)
                    ->where('subject_id', $request->subject_id)
                    ->delete();

                // Puis on insère la nouvelle relation
                DB::table('class_teacher_subject')->insert([
                    'class_id'   => $classId,
                    'teacher_id' => $request->teacher_id,
                    'subject_id' => $request->subject_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } 
        catch (\Throwable $e) {
            DB::rollBack();
            // log pour debug
            Log::error('Timetable update error: '.$e->getMessage(), [
                'class_id' => $classId,
                'timetable_id' => $id,
                'oldTeacher' => $oldTeacherId,
                'oldSubject' => $oldSubjectId,
                'newTeacher' => $request->teacher_id,
                'newSubject' => $request->subject_id,
            ]);
            return back()->with('error', 'Erreur lors de la mise à jour : '.$e->getMessage());
        }

        return redirect()->route('censeur.timetables.index', $classId)
                        ->with('success','Créneau modifié avec succès.');
    }

    public function store(Request $request, $classId){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        Timetable::create([
            'class_id' => $classId,
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'academic_year_id' => $activeYear->id,
        ]);

        DB::table('class_teacher_subject')->insert([
            'academic_year_id' => $activeYear->id,
            'class_id'   => $classId,
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Créneau ajouté avec succès.');
    }

    public function destroy($id){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }

        Timetable::findOrFail($id)->delete();
        DB::table('class_teacher_subject')
        ->where('class_id', $classId)
        ->where('teacher_id', $teacher_id)
        ->where('subject_id', $subjectId)
        ->delete();
        return back()->with('success', 'Créneau supprimé.');
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
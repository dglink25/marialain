<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Timetable;
use App\Models\Subject;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class TimetableController extends Controller
{
    public function index($classId)
    {
        $class = Classe::findOrFail($classId);

        $timetables = Timetable::where('class_id', $classId)
            ->with('teacher', 'subject')
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Génération des heures sous forme "07h-08h", "08h-09h", ...
        $hours = [];
        $start = 7; // 07:00
        $end = 19;  // 19:00

        for ($h = $start; $h < $end; $h++) {
            $hours[] = sprintf('%02dh-%02dh', $h, $h + 1);
        }

        $subjects = Subject::all();
        $teachers = User::whereHas('role', fn($q) => $q->where('name', 'teacher'))->get();

        return view('censeur.timetables.index', compact('class', 'hours', 'timetables', 'subjects', 'teachers'));
    }

    // Affichage du formulaire d'édition
    public function edit($classId, $id)
    {
        $class = Classe::findOrFail($classId);
        $timetable = Timetable::findOrFail($id);
        $teachers = User::whereHas('role', fn($q) => $q->where('name','teacher'))->get();
        $subjects = Subject::all();

        return view('censeur.timetables.edit', compact('class','timetable','teachers','subjects'));
    }

    // Mise à jour du créneau
    public function update(Request $request, $classId, $id)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'day' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
        ]);

        $timetable = Timetable::findOrFail($id);
        $timetable->update($request->only('teacher_id','subject_id','day','start_time','end_time'));

        return redirect()->route('censeur.timetables.index', $classId)
                        ->with('success','Créneau modifié avec succès.');
    }

    public function store(Request $request, $classId)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'day' => 'required',
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
        ]);

        return back()->with('success', 'Créneau ajouté avec succès.');
    }

    public function destroy($id)
    {
        Timetable::findOrFail($id)->delete();
        return back()->with('success', 'Créneau supprimé.');
    }

    

    public function downloadPDF($classId){

        $class = Classe::findOrFail($classId);

        $timetables = Timetable::where('class_id', $classId)
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
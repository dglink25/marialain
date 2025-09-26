<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Classe;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Barryvdh\DomPDF\Facade\Pdf;

class PrimaireScheduleController extends Controller{
    public function index(){
        $teacher = Auth::user();

        $classe = Classe::where('teacher_id', $teacher->id)
            ->whereHas('entity', fn($q) => $q->where('name', 'Primaire'))
            ->first();

        if (!$classe) {
            return back()->with('error', 'Vous n’êtes assigné à aucune classe primaire.');
        }

        $schedules = $classe->schedules()->with('subject')->get();

        $days = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $timeRanges = [];
        for($hour=7;$hour<18;$hour++){
            $start = str_pad($hour,2,'0',STR_PAD_LEFT).':00';
            $end   = str_pad($hour+1,2,'0',STR_PAD_LEFT).':00';
            $timeRanges[] = "$start - $end";
        }

        // Tableau pour savoir quelles cellules sont déjà “remplies” pour rowspan
        $planning = [];
        foreach($days as $day){
            $planning[$day] = [];
            foreach($timeRanges as $range){
                $planning[$day][$range] = null;
            }
        }

        foreach($schedules as $schedule){
            $start = strtotime($schedule->start_time);
            $end   = strtotime($schedule->end_time);
            $durationHours = ($end-$start)/3600;

            $startHour = intval(date('H',$start));
            $endHour   = intval(date('H',$end));

            for($h=$startHour; $h<$endHour; $h++){
                $range = str_pad($h,2,'0',STR_PAD_LEFT).':00 - '.str_pad($h+1,2,'0',STR_PAD_LEFT).':00';
                $planning[$schedule->day_of_week][$range] = [
                    'schedule' => $schedule,
                    'is_start' => $h==$startHour,
                    'rowspan'  => $durationHours
                ];
            }
        }

        return view('teacher.primaire.schedules.index', compact('classe','days','timeRanges','planning'));
    }


    public function directeur(Classe $classe){
        // Vérifier que la classe appartient à l'entité Primaire
        if($classe->entity->name !== 'Primaire'){
            return back()->with('error', 'Classe non valide pour le primaire.');
        }

        $schedules = $classe->schedules()->with('subject', 'teacher')->get();

        $days = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $timeRanges = [];
        for($hour=7;$hour<18;$hour++){
            $start = str_pad($hour,2,'0',STR_PAD_LEFT).':00';
            $end   = str_pad($hour+1,2,'0',STR_PAD_LEFT).':00';
            $timeRanges[] = "$start - $end";
        }

        $planning = [];
        foreach($days as $day){
            $planning[$day] = [];
            foreach($timeRanges as $range){
                $planning[$day][$range] = null;
            }
        }

        foreach($schedules as $schedule){
            $start = strtotime($schedule->start_time);
            $end   = strtotime($schedule->end_time);
            $durationHours = ($end-$start)/3600;

            $startHour = intval(date('H',$start));
            $endHour   = intval(date('H',$end));

            for($h=$startHour; $h<$endHour; $h++){
                $range = str_pad($h,2,'0',STR_PAD_LEFT).':00 - '.str_pad($h+1,2,'0',STR_PAD_LEFT).':00';
                $planning[$schedule->day_of_week][$range] = [
                    'schedule' => $schedule,
                    'is_start' => $h==$startHour,
                    'rowspan'  => $durationHours
                ];
            }
        }

        return view('teacher.primaire.schedules.vue_directeur', compact('classe','days','timeRanges','planning'));
    }


    public function create(){
        $teacher = Auth::user();
        $classe = Classe::where('teacher_id', $teacher->id)->first();
        $subjects = Subject::where('classe_id', $classe->id)->get();

        return view('teacher.primaire.schedules.create', compact('classe', 'subjects'));
    }

    public function store(Request $request){
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ]);

        $teacher = Auth::user();
        $classe = Classe::where('teacher_id', $teacher->id)->first();

        Schedule::create([
            'classe_id'  => $classe->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $request->subject_id,
            'day_of_week'=> $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        return redirect()->route('schedules.index')
                         ->with('success', 'Cours ajouté à l’emploi du temps.');
    }

    public function edit(Schedule $schedule){
        $classe = $schedule->classe;

        if (!$classe || $classe->teacher_id !== auth()->id()) {
            abort(403, 'Cette action n\'est pas autorisée.');
        }

        // Récupérer les matières de la classe, ou collection vide
        $subjects = Subject::where('classe_id', $classe->id)->get();
        return view('teacher.primaire.schedules.edit', compact('schedule', 'classe', 'subjects'));
    }


    public function update(Request $request, Schedule $schedule){
        if ($schedule->classe->teacher_id !== auth()->id()) {
            abort(403, 'Cette action n\'est pas autorisée.');
        }

        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time'   => 'required|after:start_time',
        ]);

        $schedule->update($request->all());

        return redirect()->route('schedules.index')
            ->with('success', 'Cours modifié avec succès.');
    }

    public function destroy(Schedule $schedule){
        if ($schedule->classe->teacher_id !== auth()->id()) {
            abort(403, 'Cette action n\'est pas autorisée.');
        }

        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Cours supprimé avec succès.');
    }

    public function downloadPdf(){
        $teacher = Auth::user();

        $classe = Classe::where('teacher_id', $teacher->id)
            ->whereHas('entity', fn($q) => $q->where('name', 'Primaire'))
            ->firstOrFail();

        $schedules = $classe->schedules()->with('subject')->get();

        $days = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $timeRanges = [];
        for($hour=7;$hour<18;$hour++){
            $start = str_pad($hour,2,'0',STR_PAD_LEFT).':00';
            $end   = str_pad($hour+1,2,'0',STR_PAD_LEFT).':00';
            $timeRanges[] = "$start - $end";
        }

        // Préparer le planning comme pour la vue
        $planning = [];
        foreach($days as $day){
            $planning[$day] = [];
            foreach($timeRanges as $range){
                $planning[$day][$range] = null;
            }
        }

        foreach($schedules as $schedule){
            $start = strtotime($schedule->start_time);
            $end   = strtotime($schedule->end_time);
            $durationHours = ($end-$start)/3600;
            $startHour = intval(date('H',$start));
            $endHour   = intval(date('H',$end));

            for($h=$startHour; $h<$endHour; $h++){
                $range = str_pad($h,2,'0',STR_PAD_LEFT).':00 - '.str_pad($h+1,2,'0',STR_PAD_LEFT).':00';
                $planning[$schedule->day_of_week][$range] = [
                    'schedule' => $schedule,
                    'is_start' => $h==$startHour,
                    'rowspan'  => $durationHours
                ];
            }
        }

        $pdf = Pdf::loadView('teacher.primaire.schedules.pdf', compact('classe','days','timeRanges','planning'))
                ->setPaper('a4', 'landscape');

        return $pdf->download("emploi_du_temps_{$classe->name}.pdf");
    }


}

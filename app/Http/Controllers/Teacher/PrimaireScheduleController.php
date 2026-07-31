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
    // ── Helpers ────────────────────────────────────────────────────

    private function getClasse(): ?Classe {
        return Classe::where('teacher_id', Auth::id())
            ->whereIn('entity_id', [1, 2])
            ->first();
    }

    private function timeSlots(): array {
        $slots = [];
        for ($h = 7; $h < 18; $h++) {
            $slots[] = sprintf('%02d:00', $h);
        }
        return $slots;
    }

    private function buildGrid(array $days, array $timeSlots, $schedules): array {
        $grid = [];
        foreach ($days as $day) {
            foreach ($timeSlots as $slot) {
                // 'entries' est un TABLEAU : plusieurs cours (ex: créneaux de 15 min)
                // peuvent partager la même case au lieu de s'écraser l'un l'autre.
                $grid[$day][$slot] = ['entries' => [], 'span' => 1, 'skip' => false];
            }
        }

        // 1) Regrouper les cours par jour + créneau de départ (heure entière)
        $byCell = [];
        foreach ($schedules as $schedule) {
            $start     = substr($schedule->start_time, 0, 5);
            $end       = substr($schedule->end_time,   0, 5);
            $startMins = (int)substr($start, 0, 2) * 60 + (int)substr($start, 3, 2);
            $endMins   = (int)substr($end,   0, 2) * 60 + (int)substr($end,   3, 2);

            // La ligne de départ = heure entière en dessous du début
            // Ex: 07:45 → ligne 07:00 ; 08:15 → ligne 08:00
            $startHour = intdiv($startMins, 60);
            $startSlot = sprintf('%02d:00', $startHour);

            // Nombre de lignes d'une heure occupées
            // Ex: 07:45–08:15 → occupe 07h ET 08h → rowspan=2
            // Ex: 08:00–10:00 → occupe 08h, 09h → rowspan=2
            // Ex: 07:30–09:00 → occupe 07h, 08h → rowspan=2
            $endHour     = intdiv($endMins, 60);
            $endMinExtra = $endMins % 60;
            // Si la fin est exactement sur une heure entière, on ne déborde pas sur cette heure
            $lastHour    = $endMinExtra > 0 ? $endHour : $endHour - 1;
            $spanSlots   = max(1, $lastHour - $startHour + 1);

            $day = $schedule->day_of_week;

            if (!isset($grid[$day][$startSlot])) continue;

            $byCell[$day][$startSlot][] = ['entry' => $schedule, 'span' => $spanSlots];
        }

        // 2) Remplir la grille : plusieurs cours possibles par case, triés par heure de début
        foreach ($byCell as $day => $slots) {
            foreach ($slots as $startSlot => $items) {
                usort($items, fn($a, $b) => strcmp($a['entry']->start_time, $b['entry']->start_time));

                // La case s'étend sur le plus grand nombre de lignes requis parmi les cours empilés
                $maxSpan = max(array_column($items, 'span'));

                $grid[$day][$startSlot] = [
                    'entries' => array_column($items, 'entry'),
                    'span'    => $maxSpan,
                    'skip'    => false,
                ];

                $slotKeys = array_keys($grid[$day]);
                $idx      = array_search($startSlot, $slotKeys, true);
                for ($i = 1; $i < $maxSpan; $i++) {
                    if (isset($slotKeys[$idx + $i])) {
                        $grid[$day][$slotKeys[$idx + $i]]['skip'] = true;
                    }
                }
            }
        }

        return $grid;
    }

    /**
     * Vérifie si un créneau [newStart, newEnd[ chevauche un cours existant.
     * Deux créneaux se touchent (ex: 07:45-08:15 et 08:15-09:00) → PAS de chevauchement.
     * Chevauchement réel : newStart < existingEnd ET newEnd > existingStart
     *
     * @param int|null $excludeId  ID à exclure (pour update)
     */
    private function hasOverlap(int $classeId, string $day, string $start, string $end, ?int $excludeId = null): bool {
        return Schedule::where('classe_id', $classeId)
            ->where('day_of_week', $day)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where('start_time', '<', $end)    // existant commence avant la fin du nouveau
            ->where('end_time',   '>', $start)  // existant finit après le début du nouveau
            ->exists();
    }

    // ── Actions ────────────────────────────────────────────────────

    public function index()
    {
        $classe = $this->getClasse();

        if (!$classe) {
            return view('teacher.primaire.schedules.index', [
                'classe'    => null,
                'error'     => "Vous n'êtes assigné à aucune classe primaire.",
                'days'      => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                'subjects'  => collect(),
                'schedules' => collect(),
            ]);
        }

        $days      = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $schedules = $classe->schedules()->with('subject')->orderBy('day_of_week')->orderBy('start_time')->get();
        $subjects  = Subject::where('classe_id', $classe->id)->orderBy('name')->get();

        return view('teacher.primaire.schedules.index',
            compact('classe', 'days', 'subjects', 'schedules'));
    }

    /** Retourne les données d'un cours en JSON (pour le modal modifier) */
    public function show(Schedule $schedule)
    {
        if ($schedule->classe->teacher_id !== Auth::id()) {
            abort(403);
        }
        $schedule->load('subject');
        return response()->json($schedule);
    }

    /** Ajout via modal AJAX */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id'  => 'required|exists:subjects,id',
            'day_of_week' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ], [
            'subject_id.required'    => 'Veuillez choisir une matière.',
            'subject_id.exists'      => 'Cette matière est invalide.',
            'day_of_week.required'   => 'Veuillez choisir un jour.',
            'day_of_week.in'         => 'Jour invalide.',
            'start_time.required'    => "L'heure de début est obligatoire.",
            'start_time.date_format' => "Format d'heure invalide (HH:MM).",
            'end_time.required'      => "L'heure de fin est obligatoire.",
            'end_time.after'         => "L'heure de fin doit être après l'heure de début.",
        ]);

        $classe = $this->getClasse();
        if (!$classe) {
            return response()->json(['message' => "Aucune classe primaire assignée."], 403);
        }

        if ($this->hasOverlap($classe->id, $validated['day_of_week'], $validated['start_time'], $validated['end_time'])) {
            $msg = "Ce créneau chevauche un cours existant.";
            return response()->json(['message' => $msg, 'errors' => ['start_time' => [$msg]]], 422);
        }

        $schedule = Schedule::create([
            'classe_id'   => $classe->id,
            'teacher_id'  => Auth::id(),
            'subject_id'  => $validated['subject_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time'  => $validated['start_time'],
            'end_time'    => $validated['end_time'],
        ]);

        $schedule->load('subject');

        return response()->json(['message' => 'Cours ajouté avec succès.', 'schedule' => $schedule], 201);
    }

    /** Modification via modal AJAX */
    public function update(Request $request, Schedule $schedule)
    {
        if ($schedule->classe->teacher_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'subject_id'  => 'required|exists:subjects,id',
            'day_of_week' => 'required|in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ], [
            'subject_id.required'    => 'Veuillez choisir une matière.',
            'start_time.date_format' => "Format d'heure invalide (HH:MM).",
            'end_time.after'         => "L'heure de fin doit être après l'heure de début.",
        ]);

        if ($this->hasOverlap($schedule->classe_id, $validated['day_of_week'], $validated['start_time'], $validated['end_time'], $schedule->id)) {
            $msg = "Ce créneau chevauche un cours existant.";
            return response()->json(['message' => $msg, 'errors' => ['start_time' => [$msg]]], 422);
        }

        $schedule->update($validated);
        $schedule->load('subject');

        return response()->json(['message' => 'Cours modifié avec succès.', 'schedule' => $schedule]);
    }

    public function destroy(Schedule $schedule)
    {
        if ($schedule->classe->teacher_id !== Auth::id()) {
            abort(403);
        }
        $schedule->delete();
        return response()->json(['message' => 'Cours supprimé.']);
    }

    public function directeur(Classe $classe)
    {
        if (!in_array($classe->entity_id, [1, 2])) {
            return back()->with('error', 'Classe non valide pour le primaire/maternelle.');
        }
        $days      = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $timeSlots = $this->timeSlots();
        $schedules = $classe->schedules()->with('subject', 'teacher')->orderBy('day_of_week')->orderBy('start_time')->get();
        $grid      = $this->buildGrid($days, $timeSlots, $schedules);
        return view('teacher.primaire.schedules.vue_directeur', compact('classe', 'days', 'timeSlots', 'grid', 'schedules'));
    }

    public function directeurPdf(Classe $classe)
    {
        if (!in_array($classe->entity_id, [1, 2])) {
            return back()->with('error', 'Classe non valide pour le primaire/maternelle.');
        }
        $days      = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $timeSlots = $this->timeSlots();
        $schedules = $classe->schedules()->with('subject')->get();
        $grid      = $this->buildGrid($days, $timeSlots, $schedules);

        $pdf = Pdf::loadView('teacher.primaire.schedules.pdf',
            compact('classe', 'days', 'timeSlots', 'grid'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("emploi_du_temps_{$classe->name}.pdf");
    }

    public function downloadPdf()
    {
        $classe = $this->getClasse();
        if (!$classe) return back()->with('error', "Aucune classe primaire assignée.");

        $days      = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $timeSlots = $this->timeSlots();
        $schedules = $classe->schedules()->with('subject')->get();
        $grid      = $this->buildGrid($days, $timeSlots, $schedules);

        $pdf = Pdf::loadView('teacher.primaire.schedules.pdf', compact('classe', 'days', 'timeSlots', 'grid'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("emploi_du_temps_{$classe->name}.pdf");
    }

    public function create()
    {
        return redirect()->route('schedules.index');
    }

    public function edit(Schedule $schedule)
    {
        return redirect()->route('schedules.index');
    }
}
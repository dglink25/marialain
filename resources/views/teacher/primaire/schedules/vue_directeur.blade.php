@extends('layouts.app')

@section('content')
@php
    // ── Palette de couleurs (identique à la vue enseignant) ──
    $colorPalette = [
        ['bg'=>'rgba(59,130,246,0.12)',  'border'=>'#2563EB', 'badge'=>'#1D4ED8', 'text'=>'#1E40AF'],
        ['bg'=>'rgba(34,197,94,0.12)',   'border'=>'#16A34A', 'badge'=>'#15803D', 'text'=>'#166534'],
        ['bg'=>'rgba(168,85,247,0.12)',  'border'=>'#9333EA', 'badge'=>'#7E22CE', 'text'=>'#6B21A8'],
        ['bg'=>'rgba(249,115,22,0.12)',  'border'=>'#EA580C', 'badge'=>'#C2410C', 'text'=>'#9A3412'],
        ['bg'=>'rgba(236,72,153,0.12)',  'border'=>'#DB2777', 'badge'=>'#BE185D', 'text'=>'#9D174D'],
        ['bg'=>'rgba(20,184,166,0.12)',  'border'=>'#0D9488', 'badge'=>'#0F766E', 'text'=>'#115E59'],
    ];

    // Map subject_id → index couleur (ordre d'apparition dans $schedules)
    $colorMap = [];
    $ci = 0;
    foreach ($schedules as $s) {
        if (!isset($colorMap[$s->subject_id])) {
            $colorMap[$s->subject_id] = $ci % count($colorPalette);
            $ci++;
        }
    }

    // ── Constantes du calendrier (identiques à la vue enseignant) ──
    $DAY_START = 7;   // 07:00
    $DAY_END   = 18;  // 18:00
    $PPM       = 4;   // pixels par minute → 1h = 240px
    $TOTAL_H   = ($DAY_END - $DAY_START) * 60 * $PPM;

    // ── Regrouper les cours par jour + pré-calculer les positions ──
    $schedulesByDay = isset($schedules) ? $schedules->groupBy('day_of_week') : collect();

    $positionedByDay = [];
    foreach ($schedulesByDay as $day => $daySchedules) {
        $positionedByDay[$day] = [];
        foreach ($daySchedules as $sched) {
            $startStr = substr($sched->start_time, 0, 5); // HH:MM
            $endStr   = substr($sched->end_time,   0, 5);
            $sH = (int) substr($startStr, 0, 2);
            $sM = (int) substr($startStr, 3, 2);
            $eH = (int) substr($endStr, 0, 2);
            $eM = (int) substr($endStr, 3, 2);

            $startMin = ($sH - $DAY_START) * 60 + $sM;
            $endMin   = ($eH - $DAY_START) * 60 + $eM;
            $durMin   = max(15, $endMin - $startMin); // minimum 15 min affichés

            $top    = $startMin * $PPM;
            $height = max(28, $durMin * $PPM); // minimum 28px pour rester visible

            $cidx  = $colorMap[$sched->subject_id] ?? 0;
            $color = $colorPalette[$cidx];

            $fStart = str_replace(':', 'h', $startStr);
            $fEnd   = str_replace(':', 'h', $endStr);

            $positionedByDay[$day][] = [
                'sched'  => $sched,
                'top'    => $top,
                'height' => $height,
                'color'  => $color,
                'fStart' => $fStart,
                'fEnd'   => $fEnd,
            ];
        }
    }
@endphp

<style>
/* ════════════════════════════════════
   TIMETABLE CALENDAR LAYOUT (identique à la vue enseignant)
════════════════════════════════════ */
.timetable-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.timetable-grid {
    display: flex;
    min-width: 700px;
    width: 100%;
    position: relative;
}

/* Time axis */
.time-axis {
    width: 64px;
    flex-shrink: 0;
    position: relative;
    height: {{ $TOTAL_H }}px;
    margin-top: 44px;
    border-right: 1px solid #E5E7EB;
    background: #F9FAFB;
}

.time-label {
    position: absolute;
    right: 10px;
    font-size: 11px;
    font-weight: 600;
    color: #6B7280;
    transform: translateY(-50%);
    white-space: nowrap;
    line-height: 1;
}

/* Day column wrapper */
.day-col-wrapper {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #E5E7EB;
    overflow: visible;
}
.day-col-wrapper:last-child {
    border-right: none;
}

.day-header {
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F8FAFC;
    border-bottom: 2px solid #E5E7EB;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    letter-spacing: 0.03em;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Scheduling area */
.day-col {
    position: relative;
    height: {{ $TOTAL_H }}px;
    background: #FFFFFF;
    overflow: visible;
}

/* Hour grid lines */
.grid-hour-bg {
    position: absolute;
    left: 0;
    right: 0;
    height: 0;
    border-top: 1px solid #E5E7EB;
    pointer-events: none;
    z-index: 1;
}
.grid-half-bg {
    position: absolute;
    left: 0;
    right: 0;
    height: 0;
    border-top: 1px dashed #F0F0F0;
    pointer-events: none;
    z-index: 1;
}

/* Course cards — lecture seule (pas de boutons) */
.course-card {
    position: absolute;
    left: 4px;
    right: 4px;
    border-radius: 8px;
    border-left: 5px solid;
    padding: 4px 6px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.04);
    overflow: hidden;
    transition: box-shadow 0.15s, transform 0.15s;
    z-index: 2;
}
.course-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.14), 0 0 0 1px rgba(0,0,0,0.07);
    transform: translateX(1px);
    z-index: 5;
}

.course-badge {
    display: inline-block;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 9999px;
    line-height: 1.5;
    align-self: flex-start;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex-shrink: 0;
}

.course-time {
    font-size: 10px;
    font-weight: 600;
    flex-shrink: 0;
    white-space: nowrap;
}

.course-teacher {
    font-size: 10px;
    color: #6B7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

    @if(session('error'))
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-4 shadow-sm">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <p class="text-sm font-semibold">{{ session('error') }}</p>
    </div>
    @endif

    {{-- ══ PAGE HEADER ══ --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Emploi du temps &mdash; <span class="text-indigo-700">{{ $classe->name }}</span>
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Vue directeur · Lecture seule</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('schedules.directeur.pdf', $classe) }}"
                   class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition-all duration-200">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Télécharger PDF
                </a>
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    {{-- ══ TIMETABLE CALENDAR ══ --}}
    <div class="bg-white rounded-b-2xl shadow-md border border-t-0 border-gray-200 overflow-hidden">
        <div class="timetable-wrapper">
            <div class="timetable-grid">

                {{-- ── Time axis ── --}}
                <div class="time-axis">
                    @for($h = $DAY_START; $h <= $DAY_END; $h++)
                    @php $topPx = ($h - $DAY_START) * 60 * $PPM; @endphp
                    <div class="time-label" style="top: {{ $topPx }}px;">
                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}h00
                    </div>
                    @endfor
                </div>

                {{-- ── Day columns ── --}}
                @foreach($days as $day)
                <div class="day-col-wrapper">

                    <div class="day-header">{{ $day }}</div>

                    <div class="day-col">

                        {{-- Background grid lines --}}
                        @for($h = $DAY_START; $h <= $DAY_END; $h++)
                        @php $lineTop = ($h - $DAY_START) * 60 * $PPM; @endphp
                        <div class="grid-hour-bg" style="top: {{ $lineTop }}px;"></div>
                        @if($h < $DAY_END)
                        <div class="grid-half-bg" style="top: {{ $lineTop + 60 * $PPM / 2 }}px;"></div>
                        @endif
                        @endfor

                        {{-- Course cards (lecture seule) --}}
                        @if(isset($positionedByDay[$day]))
                        @foreach($positionedByDay[$day] as $item)
                        @php
                            $sched  = $item['sched'];
                            $top    = $item['top'];
                            $height = $item['height'];
                            $c      = $item['color'];
                            $fStart = $item['fStart'];
                            $fEnd   = $item['fEnd'];
                        @endphp

                        <div class="course-card"
                             style="top: {{ $top }}px; height: {{ $height }}px; background: {{ $c['bg'] }}; border-left-color: {{ $c['border'] }};">

                            <span class="course-badge" style="background-color: {{ $c['badge'] }};">
                                {{ $sched->subject->name ?? '—' }}
                            </span>

                            <span class="course-time" style="color: {{ $c['text'] }};">
                                🕐 {{ $fStart }} – {{ $fEnd }}
                            </span>

                            @if($sched->teacher)
                            <span class="course-teacher">
                                {{ $sched->teacher->name }}
                            </span>
                            @endif
                        </div>

                        @endforeach
                        @endif

                    </div>{{-- /day-col --}}
                </div>{{-- /day-col-wrapper --}}
                @endforeach

            </div>{{-- /timetable-grid --}}
        </div>{{-- /timetable-wrapper --}}
    </div>{{-- /calendar card --}}

</div>{{-- /min-h-screen --}}

@endsection
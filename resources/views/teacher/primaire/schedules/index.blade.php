@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Emploi du temps';

    // ── Color palette (6 colors cycling by subject) ──
    $colorPalette = [
        ['bg'=>'rgba(59,130,246,0.12)',  'border'=>'#2563EB', 'badge'=>'#1D4ED8', 'text'=>'#1E40AF'],
        ['bg'=>'rgba(34,197,94,0.12)',   'border'=>'#16A34A', 'badge'=>'#15803D', 'text'=>'#166534'],
        ['bg'=>'rgba(168,85,247,0.12)',  'border'=>'#9333EA', 'badge'=>'#7E22CE', 'text'=>'#6B21A8'],
        ['bg'=>'rgba(249,115,22,0.12)',  'border'=>'#EA580C', 'badge'=>'#C2410C', 'text'=>'#9A3412'],
        ['bg'=>'rgba(236,72,153,0.12)',  'border'=>'#DB2777', 'badge'=>'#BE185D', 'text'=>'#9D174D'],
        ['bg'=>'rgba(20,184,166,0.12)',  'border'=>'#0D9488', 'badge'=>'#0F766E', 'text'=>'#115E59'],
    ];

    // Map subject id → color index
    $colorMap = [];
    $ci = 0;
    foreach ($subjects as $subj) {
        $colorMap[$subj->id] = $ci % count($colorPalette);
        $ci++;
    }

    // ── Calendar constants ──
    $DAY_START   = 7;   // 07:00
    $DAY_END     = 18;  // 18:00
    $PPM         = 4;   // pixels per minute → 1h = 240px, très lisible
    $TOTAL_H     = ($DAY_END - $DAY_START) * 60 * $PPM; // 2640px

    // ── Group schedules by day + pre-compute positions ──
    $schedulesByDay = isset($schedules) ? $schedules->groupBy('day_of_week') : collect();

    // Build positioned schedule data
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

            $cidx   = $colorMap[$sched->subject_id] ?? 0;
            $color  = $colorPalette[$cidx];

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
   TIMETABLE CALENDAR LAYOUT
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
    margin-top: 44px; /* align with day header height */
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

/* Day column wrapper (header + relative area) */
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

/* The actual scheduling area */
.day-col {
    position: relative;
    height: {{ $TOTAL_H }}px;
    background: #FFFFFF;
    overflow: visible;
}

/* Hour grid lines */
.hour-line {
    position: absolute;
    left: 0;
    right: 0;
    height: 1px;
    pointer-events: none;
}
.hour-line.full  { background: #E5E7EB; }
.hour-line.half  { background: #F3F4F6; border-top: 1px dashed #E5E7EB; height: 0; }

/* Course cards */
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

.course-name-small {
    font-size: 10px;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Action buttons container */
.course-actions {
    display: flex;
    gap: 4px;
    margin-top: 4px;
    flex-shrink: 0;
}

.course-actions .btn-edit,
.course-actions .btn-delete {
    flex: 1;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 4px 6px;
    cursor: pointer;
    line-height: 1.4;
    transition: background-color 0.15s, transform 0.1s;
}
.course-actions .btn-edit         { background-color: #2563EB; }
.course-actions .btn-edit:hover   { background-color: #1D4ED8; transform: scale(1.03); }
.course-actions .btn-delete       { background-color: #DC2626; }
.course-actions .btn-delete:hover { background-color: #B91C1C; transform: scale(1.03); }

/* Suppression du hide-on-no-hover — boutons toujours visibles */

/* Horizontal dashed lines (from time axis across all columns) */
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
</style>

<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- ── Toast notification ── --}}
    <div id="toast-success"
         class="fixed top-5 right-5 z-[9999] hidden items-center gap-3 bg-white border border-green-200 text-green-800 shadow-xl rounded-xl px-5 py-4 max-w-sm transition-all duration-300">
        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p id="toast-msg" class="text-sm font-medium">Opération réussie.</p>
        <button onclick="document.getElementById('toast-success').classList.add('hidden');document.getElementById('toast-success').classList.remove('flex')"
                class="ml-auto text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Session flash ── --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-4 shadow-sm">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
    @endif

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
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                        Emploi du temps
                        @if($classe)
                            <span class="text-blue-700">{{ $classe->name }}</span>
                        @endif
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Gestion des horaires hebdomadaires</p>
                </div>
            </div>

            @if($classe)
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('schedules.download') }}"
                   class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition-all duration-200">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Télécharger PDF
                </a>
                <button onclick="openModal()"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter un cours
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- ══ NO CLASS STATE ══ --}}
    @if(!$classe)
    <div class="flex flex-col items-center justify-center py-24">
        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-6 shadow-inner">
            <svg class="w-12 h-12 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-700 mb-2">Aucune classe assignée</h2>
        <p class="text-gray-400 text-sm text-center max-w-sm">
            {{ $error ?? "Vous n'êtes assigné à aucune classe primaire pour le moment. Contactez votre directeur." }}
        </p>
    </div>

    @else
    {{-- ══ STATS HEADER ══ --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-2xl px-6 py-4 flex flex-wrap items-center gap-8 shadow-md">
        <div class="text-white">
            <p class="text-xs font-medium text-blue-200 uppercase tracking-wider">Classe</p>
            <p class="text-lg font-bold">{{ $classe->name }}</p>
        </div>
        <div class="text-white">
            <p class="text-xs font-medium text-blue-200 uppercase tracking-wider">Cours planifiés</p>
            <p class="text-lg font-bold">{{ isset($schedules) ? $schedules->count() : 0 }}</p>
        </div>
        <div class="text-white">
            <p class="text-xs font-medium text-blue-200 uppercase tracking-wider">Matières</p>
            <p class="text-lg font-bold">{{ $subjects->count() }}</p>
        </div>
        <div class="ml-auto hidden sm:block">
            <span class="text-xs text-blue-200 font-medium">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
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

                    {{-- Sticky day header --}}
                    <div class="day-header">{{ $day }}</div>

                    {{-- Scheduling area --}}
                    <div class="day-col">

                        {{-- Background grid lines --}}
                        @for($h = $DAY_START; $h <= $DAY_END; $h++)
                        @php $lineTop = ($h - $DAY_START) * 60 * $PPM; @endphp
                        <div class="grid-hour-bg" style="top: {{ $lineTop }}px;"></div>
                        @if($h < $DAY_END)
                        <div class="grid-half-bg" style="top: {{ $lineTop + 60 * $PPM / 2 }}px;"></div>
                        @endif
                        @endfor

                        {{-- Course cards --}}
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

                        {{-- Carte de cours — badge + horaire + boutons toujours visibles --}}
                        <div class="course-card"
                             style="top: {{ $top }}px; height: {{ $height }}px; background: {{ $c['bg'] }}; border-left-color: {{ $c['border'] }};">

                            {{-- Nom de la matière --}}
                            <span class="course-badge" style="background-color: {{ $c['badge'] }};">
                                {{ $sched->subject->name ?? '—' }}
                            </span>

                            {{-- Horaire --}}
                            <span class="course-time" style="color: {{ $c['text'] }};">
                                🕐 {{ $fStart }} – {{ $fEnd }}
                            </span>

                            {{-- Boutons toujours visibles --}}
                            <div class="course-actions">
                                <button class="btn-edit" onclick="openEditModal({{ $sched->id }})">Modifier</button>
                                <button class="btn-delete" onclick="deleteSchedule({{ $sched->id }})">Supprimer</button>
                            </div>
                        </div>

                        @endforeach
                        @endif

                    </div>{{-- /day-col --}}
                </div>{{-- /day-col-wrapper --}}
                @endforeach

            </div>{{-- /timetable-grid --}}
        </div>{{-- /timetable-wrapper --}}
    </div>{{-- /calendar card --}}
    @endif

</div>{{-- /min-h-screen --}}

{{-- ══════════════════════════════════════════════════════
     ADD COURSE MODAL
══════════════════════════════════════════════════════ --}}
@if($classe)
<div id="modal-overlay"
     class="fixed inset-0 z-[8000] hidden"
     onclick="closeModalIfBackdrop(event)">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="relative flex items-center justify-center min-h-full px-4 py-8">
        <div id="modal-panel"
             class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-gray-100 transform transition-all duration-300 translate-y-8 opacity-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Ajouter un cours</h2>
                        <p class="text-xs text-gray-400">{{ $classe->name }}</p>
                    </div>
                </div>
                <button onclick="closeModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <form id="add-form" class="px-6 py-5 space-y-5" novalidate>
                @csrf
                <div id="add-global-error"
                     class="hidden flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span id="add-global-error-msg"></span>
                </div>

                {{-- Matière --}}
                <div>
                    <label for="add-subject" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Matière <span class="text-red-500">*</span>
                    </label>
                    <select id="add-subject" name="subject_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">— Choisir une matière —</option>
                        @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                        @endforeach
                    </select>
                    <p id="add-err-subject" class="hidden mt-1 text-xs text-red-600"></p>
                </div>

                {{-- Jour --}}
                <div>
                    <label for="add-day" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Jour <span class="text-red-500">*</span>
                    </label>
                    <select id="add-day" name="day_of_week"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">— Choisir un jour —</option>
                        @foreach($days as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                    <p id="add-err-day" class="hidden mt-1 text-xs text-red-600"></p>
                </div>

                {{-- Times --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add-start" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Heure début <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="add-start" name="start_time" min="07:00" max="18:00"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <p id="add-err-start" class="hidden mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label for="add-end" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Heure fin <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="add-end" name="end_time" min="07:00" max="18:30"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <p id="add-err-end" class="hidden mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit" id="add-submit"
                            class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold text-sm py-3 px-6 rounded-xl shadow-md transition-all duration-200 active:scale-95 disabled:cursor-not-allowed">
                        <svg id="add-submit-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg id="add-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span id="add-submit-label">Enregistrer le cours</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     EDIT COURSE MODAL
══════════════════════════════════════════════════════ --}}
@if($classe)
<div id="edit-modal-overlay"
     class="fixed inset-0 z-[8000] hidden"
     onclick="closeEditModalIfBackdrop(event)">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="relative flex items-center justify-center min-h-full px-4 py-8">
        <div id="edit-modal-panel"
             class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-gray-100 transform transition-all duration-300 translate-y-8 opacity-0">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Modifier le cours</h2>
                        <p class="text-xs text-gray-400">{{ $classe->name }}</p>
                    </div>
                </div>
                <button onclick="closeEditModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Loading state --}}
            <div id="edit-loading" class="px-6 py-12 flex flex-col items-center gap-3">
                <svg class="w-8 h-8 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <p class="text-sm text-gray-400">Chargement...</p>
            </div>

            {{-- Body --}}
            <form id="edit-form" class="px-6 py-5 space-y-5 hidden" novalidate>
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="edit-schedule-id" name="schedule_id">

                <div id="edit-global-error"
                     class="hidden flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span id="edit-global-error-msg"></span>
                </div>

                {{-- Matière --}}
                <div>
                    <label for="edit-subject" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Matière <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-subject" name="subject_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <option value="">— Choisir une matière —</option>
                        @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                        @endforeach
                    </select>
                    <p id="edit-err-subject" class="hidden mt-1 text-xs text-red-600"></p>
                </div>

                {{-- Jour --}}
                <div>
                    <label for="edit-day" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Jour <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-day" name="day_of_week"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <option value="">— Choisir un jour —</option>
                        @foreach($days as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                    <p id="edit-err-day" class="hidden mt-1 text-xs text-red-600"></p>
                </div>

                {{-- Times --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit-start" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Heure début <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="edit-start" name="start_time" min="07:00" max="18:00"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <p id="edit-err-start" class="hidden mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label for="edit-end" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Heure fin <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="edit-end" name="end_time" min="07:00" max="18:30"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <p id="edit-err-end" class="hidden mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit" id="edit-submit"
                            class="w-full flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 disabled:bg-amber-300 text-white font-semibold text-sm py-3 px-6 rounded-xl shadow-md transition-all duration-200 active:scale-95 disabled:cursor-not-allowed">
                        <svg id="edit-submit-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg id="edit-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span id="edit-submit-label">Mettre à jour</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
// ════════════════════════════════════════════════════
//  TOAST
// ════════════════════════════════════════════════════
function showToast(msg) {
    const el = document.getElementById('toast-success');
    document.getElementById('toast-msg').textContent = msg;
    el.classList.remove('hidden');
    el.classList.add('flex');
    setTimeout(() => {
        el.classList.remove('flex');
        el.classList.add('hidden');
    }, 4000);
}

// ════════════════════════════════════════════════════
//  DELETE  — simple window.confirm, then fetch
// ════════════════════════════════════════════════════
async function deleteSchedule(id) {
    if (!confirm('Supprimer ce cours ?')) return;
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const resp = await fetch(`/teacher/primaire/schedules/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ '_method': 'DELETE' })
    });
    const data = await resp.json();
    if (resp.ok) {
        showToast(data.message || 'Cours supprimé.');
        window.location.reload();
    } else {
        alert(data.message || 'Erreur lors de la suppression.');
    }
}

// ════════════════════════════════════════════════════
//  ADD MODAL
// ════════════════════════════════════════════════════
function openModal() {
    const overlay = document.getElementById('modal-overlay');
    const panel   = document.getElementById('modal-panel');
    if (!overlay) return;
    overlay.classList.remove('hidden');
    requestAnimationFrame(() => {
        panel.classList.remove('translate-y-8', 'opacity-0');
        panel.classList.add('translate-y-0', 'opacity-100');
    });
    document.body.classList.add('overflow-hidden');
}

function closeModal() {
    const overlay = document.getElementById('modal-overlay');
    const panel   = document.getElementById('modal-panel');
    if (!overlay) return;
    panel.classList.remove('translate-y-0', 'opacity-100');
    panel.classList.add('translate-y-8', 'opacity-0');
    setTimeout(() => {
        overlay.classList.add('hidden');
        document.getElementById('add-form').reset();
        clearAddErrors();
        document.getElementById('add-global-error').classList.add('hidden');
    }, 250);
    document.body.classList.remove('overflow-hidden');
}

function closeModalIfBackdrop(e) {
    if (e.target === document.getElementById('modal-overlay')) closeModal();
}

function openModalForSlot(day, slot) {
    const daySelect  = document.getElementById('add-day');
    const startInput = document.getElementById('add-start');
    if (daySelect)  daySelect.value  = day;
    if (startInput) startInput.value = slot;
    openModal();
}

function clearAddErrors() {
    ['subject', 'day', 'start', 'end'].forEach(f => {
        const el = document.getElementById('add-err-' + f);
        if (el) { el.textContent = ''; el.classList.add('hidden'); }
    });
}

function showAddFieldError(field, msg) {
    const el = document.getElementById('add-err-' + field);
    if (el) { el.textContent = msg; el.classList.remove('hidden'); }
}

document.addEventListener('DOMContentLoaded', () => {
    const addForm = document.getElementById('add-form');
    if (!addForm) return;

    addForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearAddErrors();
        document.getElementById('add-global-error').classList.add('hidden');

        const submitBtn     = document.getElementById('add-submit');
        const submitIcon    = document.getElementById('add-submit-icon');
        const submitSpinner = document.getElementById('add-spinner');
        const submitLabel   = document.getElementById('add-submit-label');

        submitBtn.disabled = true;
        submitIcon.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
        submitLabel.textContent = 'Enregistrement...';

        const token    = document.querySelector('meta[name="csrf-token"]').content;
        const formData = new FormData(addForm);

        try {
            const resp = await fetch('{{ route("schedules.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await resp.json();

            if (resp.status === 422 && data.errors) {
                const map = { subject_id: 'subject', day_of_week: 'day', start_time: 'start', end_time: 'end' };
                Object.entries(data.errors).forEach(([key, msgs]) => {
                    showAddFieldError(map[key] || key, msgs[0]);
                });
            } else if (resp.ok) {
                closeModal();
                showToast(data.message || 'Cours ajouté avec succès.');
                setTimeout(() => window.location.reload(), 800);
            } else {
                const errEl = document.getElementById('add-global-error');
                document.getElementById('add-global-error-msg').textContent = data.message || 'Une erreur est survenue.';
                errEl.classList.remove('hidden');
            }
        } catch (err) {
            const errEl = document.getElementById('add-global-error');
            document.getElementById('add-global-error-msg').textContent = 'Erreur réseau. Veuillez réessayer.';
            errEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitIcon.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
            submitLabel.textContent = 'Enregistrer le cours';
        }
    });
});

// ════════════════════════════════════════════════════
//  EDIT MODAL
// ════════════════════════════════════════════════════
function openEditModal(id) {
    const overlay = document.getElementById('edit-modal-overlay');
    const panel   = document.getElementById('edit-modal-panel');
    const loading = document.getElementById('edit-loading');
    const form    = document.getElementById('edit-form');
    if (!overlay) return;

    overlay.classList.remove('hidden');
    requestAnimationFrame(() => {
        panel.classList.remove('translate-y-8', 'opacity-0');
        panel.classList.add('translate-y-0', 'opacity-100');
    });
    document.body.classList.add('overflow-hidden');

    loading.classList.remove('hidden');
    form.classList.add('hidden');
    clearEditErrors();
    document.getElementById('edit-global-error').classList.add('hidden');

    const token = document.querySelector('meta[name="csrf-token"]').content;
    fetch(`/teacher/primaire/schedules/${id}`, {
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('edit-schedule-id').value = data.id;
        document.getElementById('edit-subject').value     = data.subject_id;
        document.getElementById('edit-day').value         = data.day_of_week;
        document.getElementById('edit-start').value       = (data.start_time || '').substring(0, 5);
        document.getElementById('edit-end').value         = (data.end_time   || '').substring(0, 5);
        loading.classList.add('hidden');
        form.classList.remove('hidden');
    })
    .catch(() => {
        loading.innerHTML = '<p class="text-sm text-red-500 px-6 py-8">Impossible de charger les données.</p>';
    });
}

function closeEditModal() {
    const overlay = document.getElementById('edit-modal-overlay');
    const panel   = document.getElementById('edit-modal-panel');
    if (!overlay) return;
    panel.classList.remove('translate-y-0', 'opacity-100');
    panel.classList.add('translate-y-8', 'opacity-0');
    setTimeout(() => {
        overlay.classList.add('hidden');
        document.getElementById('edit-form').reset();
        clearEditErrors();
        document.getElementById('edit-global-error').classList.add('hidden');
    }, 250);
    document.body.classList.remove('overflow-hidden');
}

function closeEditModalIfBackdrop(e) {
    if (e.target === document.getElementById('edit-modal-overlay')) closeEditModal();
}

function clearEditErrors() {
    ['subject', 'day', 'start', 'end'].forEach(f => {
        const el = document.getElementById('edit-err-' + f);
        if (el) { el.textContent = ''; el.classList.add('hidden'); }
    });
}

function showEditFieldError(field, msg) {
    const el = document.getElementById('edit-err-' + field);
    if (el) { el.textContent = msg; el.classList.remove('hidden'); }
}

document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.getElementById('edit-form');
    if (!editForm) return;

    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearEditErrors();
        document.getElementById('edit-global-error').classList.add('hidden');

        const submitBtn     = document.getElementById('edit-submit');
        const submitIcon    = document.getElementById('edit-submit-icon');
        const submitSpinner = document.getElementById('edit-spinner');
        const submitLabel   = document.getElementById('edit-submit-label');

        submitBtn.disabled = true;
        submitIcon.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
        submitLabel.textContent = 'Mise à jour...';

        const id       = document.getElementById('edit-schedule-id').value;
        const token    = document.querySelector('meta[name="csrf-token"]').content;
        const formData = new FormData(editForm);

        try {
            const resp = await fetch(`/teacher/primaire/schedules/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await resp.json();

            if (resp.status === 422 && data.errors) {
                const map = { subject_id: 'subject', day_of_week: 'day', start_time: 'start', end_time: 'end' };
                Object.entries(data.errors).forEach(([key, msgs]) => {
                    showEditFieldError(map[key] || key, msgs[0]);
                });
            } else if (resp.ok) {
                closeEditModal();
                showToast(data.message || 'Cours mis à jour avec succès.');
                setTimeout(() => window.location.reload(), 800);
            } else {
                const errEl = document.getElementById('edit-global-error');
                document.getElementById('edit-global-error-msg').textContent = data.message || 'Une erreur est survenue.';
                errEl.classList.remove('hidden');
            }
        } catch (err) {
            const errEl = document.getElementById('edit-global-error');
            document.getElementById('edit-global-error-msg').textContent = 'Erreur réseau. Veuillez réessayer.';
            errEl.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitIcon.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
            submitLabel.textContent = 'Mettre à jour';
        }
    });
});
</script>
@endsection

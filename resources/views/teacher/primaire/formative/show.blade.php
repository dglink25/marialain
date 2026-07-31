@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-4 px-3 sm:py-6 sm:px-6 lg:px-8">

    {{-- En-tête --}}
    <div class="mb-6 sm:mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-blue-600 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-eye text-white text-base sm:text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                    Notes — {{ $evaluation->subject->name ?? '—' }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5 break-words">
                    {{ $classe->name }} — {{ $annee->name }}
                    @if($evaluation->titre)
                        — <span class="font-medium text-gray-700">{{ $evaluation->titre }}</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('teacher.primaire.formative.recap', $classe->id) }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition">
                <i class="fas fa-table text-xs"></i>
                <span class="hidden sm:inline">Récapitulatif toutes matières</span>
                <span class="sm:hidden">Récap</span>
            </a>
            <a href="{{ route('teacher.classes.formative', $classe->id) }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Retour aux évaluations
            </a>
        </div>
    </div>

    {{-- Résumé --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 sm:p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Date</p>
            <p class="text-sm sm:text-base font-bold text-gray-800">
                {{ $evaluation->date_evaluation->locale('fr')->isoFormat('ddd D MMM YYYY') }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 sm:p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Barème</p>
            <p class="text-xl sm:text-2xl font-black text-emerald-600">/{{ number_format($evaluation->note_max, 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 sm:p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notés</p>
            <p class="text-xl sm:text-2xl font-black text-blue-600">
                {{ $notesParEleve->whereNotNull('note')->count() }}/{{ $classe->students->count() }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 sm:p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Moyenne</p>
            @php
                $notesValides = $notesParEleve->whereNotNull('note')->pluck('note');
                $moyenne = $notesValides->count() > 0 ? round($notesValides->avg(), 2) : null;
            @endphp
            <p class="text-xl sm:text-2xl font-black text-purple-600">
                {{ $moyenne !== null ? number_format($moyenne, 2, ',', '') : '—' }}
            </p>
        </div>
    </div>

    {{-- Tableau des notes --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-users text-blue-500"></i>
            <h2 class="text-base font-bold text-gray-800">Notes des élèves</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[560px]">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left w-10">N°</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Nom & Prénoms</th>
                        <th class="px-4 sm:px-6 py-3 text-center w-16">Sexe</th>
                        <th class="px-4 sm:px-6 py-3 text-center w-28">Note /{{ number_format($evaluation->note_max, 0) }}</th>
                        <th class="px-4 sm:px-6 py-3 text-center w-32">Appréciation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($classe->students as $i => $student)
                    @php
                        $noteEntry = $notesParEleve->get($student->id);
                        $note      = $noteEntry ? $noteEntry->note : null;
                        $noteMax   = $evaluation->note_max;
                        $pct       = $note !== null ? ($note / $noteMax) * 100 : null;
                        if ($pct === null)       { $apprColor = 'gray';   $apprLabel = 'Absent'; }
                        elseif ($pct >= 80)      { $apprColor = 'green';  $apprLabel = 'Très bien'; }
                        elseif ($pct >= 60)      { $apprColor = 'blue';   $apprLabel = 'Bien'; }
                        elseif ($pct >= 50)      { $apprColor = 'yellow'; $apprLabel = 'Passable'; }
                        else                     { $apprColor = 'red';    $apprLabel = 'Insuffisant'; }
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 sm:px-6 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 sm:px-6 py-3">
                            <span class="font-semibold text-gray-800">{{ strtoupper($student->last_name) }}</span>
                            <span class="text-gray-600"> {{ $student->first_name }}</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $student->gender === 'M' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                {{ $student->gender }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-center">
                            @if($note !== null)
                            <span class="text-lg font-black
                                {{ $pct >= 50 ? 'text-green-700' : 'text-red-600' }}">
                                {{ number_format($note, 1, ',', '') }}
                            </span>
                            @else
                            <span class="text-gray-400 text-sm italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                bg-{{ $apprColor }}-100 text-{{ $apprColor }}-800">
                                {{ $apprLabel }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
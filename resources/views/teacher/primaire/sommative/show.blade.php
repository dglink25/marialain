@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-4 px-3 sm:py-6 sm:px-6 lg:px-8">

    {{-- En-tête --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-eye text-white"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
                    Notes — {{ $evaluation->subject->name ?? '—' }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    {{ $classe->name }} — {{ $annee->name }}
                    @if($evaluation->titre) — <span class="font-medium text-gray-700">{{ $evaluation->titre }}</span> @endif
                </p>
            </div>
        </div>
        <a href="{{ route('teacher.classes.sommative', $classe->id) }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

    {{-- Résumé --}}
    @php
        $notesValides = $notesParEleve->whereNotNull('note');
        $moyenne = $notesValides->count() > 0 ? round($notesValides->avg('note'), 2) : null;
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Date</p>
            <p class="text-sm font-bold text-gray-800">
                {{ $evaluation->date_evaluation ? $evaluation->date_evaluation->locale('fr')->isoFormat('D MMM YYYY') : '—' }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Barème</p>
            <p class="text-2xl font-black text-orange-500">/{{ number_format($evaluation->note_max, 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notés</p>
            <p class="text-2xl font-black text-blue-600">{{ $notesValides->count() }}/{{ $classe->students->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Moyenne</p>
            <p class="text-2xl font-black text-purple-600">{{ $moyenne !== null ? number_format($moyenne, 2, ',', '') : '—' }}</p>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-users text-blue-500"></i>
            <h2 class="font-bold text-gray-800">Notes des élèves</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[500px]">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold">
                    <tr>
                        <th class="px-5 py-3 text-left w-8">N°</th>
                        <th class="px-5 py-3 text-left">N° Matricule</th>
                        <th class="px-5 py-3 text-left">Nom & Prénoms</th>
                        <th class="px-5 py-3 text-center w-14">Sexe</th>
                        <th class="px-5 py-3 text-center">Note /{{ number_format($evaluation->note_max, 0) }}</th>
                        <th class="px-5 py-3 text-center">Rang</th>
                        <th class="px-5 py-3 text-center">Appréciation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($classe->students as $i => $student)
                    @php
                        $entry = $notesParEleve->get($student->id);
                        $note  = $entry ? $entry->note : null;
                        $pct   = $note !== null ? ($note / $evaluation->note_max) * 100 : null;
                        $rang  = $rangs[$student->id] ?? null;
                        if($pct === null)   { $aColor='gray';   $aLabel='Absent'; }
                        elseif($pct >= 80)  { $aColor='green';  $aLabel='Très bien'; }
                        elseif($pct >= 60)  { $aColor='blue';   $aLabel='Bien'; }
                        elseif($pct >= 50)  { $aColor='yellow'; $aLabel='Passable'; }
                        else                { $aColor='red';    $aLabel='Insuffisant'; }
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $i+1 }}</td>
                        <td class="px-5 py-3 text-gray-600 text-xs font-mono">{{ $student->num_educ ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="font-semibold text-gray-800">{{ strtoupper($student->last_name) }}</span>
                            <span class="text-gray-600"> {{ $student->first_name }}</span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $student->gender==='M'?'bg-blue-100 text-blue-700':'bg-pink-100 text-pink-700' }}">{{ $student->gender }}</span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($note !== null)
                            <span class="text-lg font-black {{ $pct >= 50 ? 'text-green-700' : 'text-red-600' }}">{{ number_format($note, 1, ',', '') }}</span>
                            @else
                            <span class="text-gray-400 italic text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center font-bold text-gray-700">{{ $rang ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $aColor }}-100 text-{{ $aColor }}-800">{{ $aLabel }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

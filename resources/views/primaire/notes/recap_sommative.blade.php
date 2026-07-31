@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-4 px-3 sm:py-6 sm:px-6 lg:px-8">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-table text-white"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Récapitulatif — Toutes matières</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    <span class="font-semibold text-purple-700">{{ $classe->name }}</span> — {{ $annee_academique->name }}
                </p>
            </div>
        </div>
        <a href="{{ route('primaire.notes.sommative', $classe->id) }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

    @if($matieres->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <i class="fas fa-table text-gray-300 text-4xl mb-3"></i>
        <p class="text-gray-500 font-medium">Aucune évaluation sommative enregistrée.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800">
                {{ $classe->students->count() }} élève(s) — {{ $matieres->count() }} matière(s)
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="text-sm border-collapse" style="min-width: {{ 400 + $matieres->count() * 130 }}px;">
                <thead>
                    {{-- Ligne 1 : colonnes fixes + matières (span 2) + Moy + Rang --}}
                    <tr class="bg-gray-800 text-white text-xs">
                        <th rowspan="2" class="px-3 py-3 text-center border-r border-gray-700 w-8">#</th>
                        <th rowspan="2" class="px-3 py-3 text-left border-r border-gray-700 whitespace-nowrap w-24">N° Matricule</th>
                        <th rowspan="2" class="px-4 py-3 text-left border-r border-gray-700 min-w-[120px]">Nom</th>
                        <th rowspan="2" class="px-4 py-3 text-left border-r border-gray-700 min-w-[110px]">Prénoms</th>
                        <th rowspan="2" class="px-3 py-3 text-center border-r border-gray-700 w-12">Sexe</th>
                        @foreach($matieres as $eval)
                        <th colspan="2" class="px-3 py-2 text-center border-r border-gray-600 bg-indigo-800 whitespace-nowrap">
                            {{ $eval->subject->name ?? '—' }}
                            <span class="block text-[10px] font-normal text-indigo-300">/{{ number_format($eval->note_max, 0) }}</span>
                        </th>
                        @endforeach
                        <th rowspan="2" class="px-3 py-3 text-center border-r border-gray-600 bg-green-800 whitespace-nowrap">Moy /20</th>
                        <th rowspan="2" class="px-3 py-3 text-center bg-green-800 whitespace-nowrap">Rang</th>
                    </tr>
                    <tr class="bg-gray-700 text-gray-200 text-xs">
                        @foreach($matieres as $eval)
                        <th class="px-2 py-2 text-center border-r border-gray-600 font-medium bg-indigo-700">Note</th>
                        <th class="px-2 py-2 text-center border-r border-gray-600 font-medium bg-indigo-700">Rang</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($classe->students as $i => $student)
                    @php
                        $moy  = $moyennes[$student->id] ?? null;
                        $rang = $rangGeneraux[$student->id] ?? '—';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' }}">
                        <td class="px-3 py-2.5 text-center text-gray-400 text-xs border-r border-gray-100">{{ $i+1 }}</td>
                        <td class="px-3 py-2.5 text-xs text-gray-500 font-mono border-r border-gray-100">{{ $student->num_educ ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-semibold text-gray-800 border-r border-gray-100">{{ strtoupper($student->last_name) }}</td>
                        <td class="px-4 py-2.5 text-gray-600 border-r border-gray-100">{{ $student->first_name }}</td>
                        <td class="px-3 py-2.5 text-center border-r border-gray-100">
                            <span class="text-xs px-1.5 py-0.5 rounded-full font-medium {{ $student->gender==='M'?'bg-blue-100 text-blue-700':'bg-pink-100 text-pink-700' }}">
                                {{ $student->gender }}
                            </span>
                        </td>
                        @foreach($matieres as $eval)
                        @php
                            $entry   = $matrix[$student->id][$eval->subject_id] ?? null;
                            $note    = $entry ? $entry['note'] : null;
                            $noteMax = $entry ? $entry['note_max'] : $eval->note_max;
                            $rangMat = $rangsParMatiere[$eval->subject_id][$student->id] ?? null;
                            $pct     = ($note !== null && $noteMax > 0) ? ($note / $noteMax) * 100 : null;
                        @endphp
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            @if($note !== null)
                                <span class="font-bold {{ $pct >= 50 ? 'text-green-700' : 'text-red-600' }}">
                                    {{ number_format($note, 1, ',', '') }}
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100 text-xs font-medium text-gray-600">
                            {{ $rangMat ?? '—' }}
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center border-r border-gray-100">
                            @if($moy !== null)
                                <span class="font-black text-base {{ $moy >= 10 ? 'text-green-700' : 'text-red-600' }}">
                                    {{ number_format($moy, 2, ',', '') }}
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            <span class="font-bold text-purple-700">{{ $rang }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@php
    $pageTitle = 'Fiche complète — ' . $student->last_name . ' ' . $student->first_name;
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- En-tête --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}"
               class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                <i class="fas fa-arrow-left text-slate-600 text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800">
                    {{ strtoupper($student->last_name) }} {{ $student->first_name }}
                </h1>
                <p class="text-sm text-slate-500">
                    <i class="fas fa-school mr-1"></i>{{ $classe->name }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-id-card mr-1"></i>{{ $student->num_educ ?? '—' }}
                    &nbsp;·&nbsp;
                    {{ $activeYear->name }}
                </p>
            </div>
        </div>
        {{-- Moyenne annuelle --}}
        @if($moyAnnuelle !== null)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-6 py-3 text-center">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Moyenne annuelle</p>
            <p class="text-3xl font-bold {{ $moyAnnuelle >= 10 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($moyAnnuelle, 2, ',', '') }}<span class="text-base font-normal text-slate-400">/20</span>
            </p>
        </div>
        @endif
    </div>

    {{-- Résumé des 3 trimestres --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        @foreach([1,2,3] as $t)
        @php $moy = $data[$t]['moyGen'] ?? null; @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4 text-center">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Trimestre {{ $t }}</p>
            @if($moy !== null)
            <p class="text-2xl font-bold {{ $moy >= 10 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($moy, 2, ',', '') }}
            </p>
            <p class="text-xs text-slate-400 mt-0.5">Conduite : {{ $data[$t]['conduite'] }}/20</p>
            @else
            <p class="text-slate-300 text-lg font-semibold">—</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Tableau des notes par trimestre --}}
    @foreach([1,2,3] as $trimestre)
    @php $tData = $data[$trimestre]; @endphp
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 bg-indigo-600 text-white rounded-full text-xs font-bold">{{ $trimestre }}</span>
                Trimestre {{ $trimestre }}
            </h2>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-slate-500">Conduite : <strong>{{ $tData['conduite'] }}/20</strong></span>
                @if($tData['moyGen'] !== null)
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $tData['moyGen'] >= 10 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    Moy. : {{ number_format($tData['moyGen'], 2, ',', '') }}/20
                </span>
                @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Aucune note</span>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs min-w-[700px]">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 w-40">Matière</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-600 w-12">Coef</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-500 w-10">I1</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-500 w-10">I2</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-500 w-10">I3</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-500 w-10">I4</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-500 w-10">I5</th>
                        <th class="px-3 py-3 text-center font-semibold text-indigo-600 w-16">Moy.I</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-500 w-12">D1</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-500 w-12">D2</th>
                        <th class="px-3 py-3 text-center font-semibold text-indigo-700 w-16">Moy./20</th>
                        <th class="px-3 py-3 text-center font-semibold text-slate-600 w-16">×Coef</th>
                        <th class="px-3 py-3 text-left font-semibold text-slate-500">Appréciation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($tData['rows'] as $row)
                    @php
                        $hasMoy = $row['moy'] !== null;
                        $good   = $hasMoy && $row['moy'] >= 10;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors {{ !$hasMoy ? 'opacity-50' : '' }}">
                        <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $row['subject'] }}</td>
                        <td class="px-3 py-2.5 text-center text-slate-600">{{ $row['coef'] }}</td>
                        @foreach([1,2,3,4,5] as $i)
                        <td class="px-3 py-2.5 text-center {{ $row['interros'][$i] !== null ? 'text-slate-700' : 'text-slate-300' }}">
                            {{ $row['interros'][$i] !== null ? number_format($row['interros'][$i], 2, ',', '') : '—' }}
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center font-semibold text-indigo-600">
                            {{ $row['moyInterro'] !== null ? number_format($row['moyInterro'], 2, ',', '') : '—' }}
                        </td>
                        @foreach([1,2] as $i)
                        <td class="px-3 py-2.5 text-center {{ $row['devoirs'][$i] !== null ? 'text-slate-700' : 'text-slate-300' }}">
                            {{ $row['devoirs'][$i] !== null ? number_format($row['devoirs'][$i], 2, ',', '') : '—' }}
                        </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center font-bold {{ $hasMoy ? ($good ? 'text-green-700' : 'text-red-600') : 'text-slate-300' }}">
                            {{ $hasMoy ? number_format($row['moy'], 2, ',', '') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-center text-slate-600">
                            {{ $row['moyCoef'] !== null ? number_format($row['moyCoef'], 2, ',', '') : '—' }}
                        </td>
                        <td class="px-3 py-2.5 text-slate-500 text-xs">{{ $row['appreciation'] }}</td>
                    </tr>
                    @endforeach
                    {{-- Ligne totaux --}}
                    <tr class="bg-slate-100 font-bold text-slate-700 border-t-2 border-slate-200">
                        <td class="px-4 py-2.5" colspan="11">
                            Total coeff : {{ $tData['totalCoef'] }}
                            &nbsp;|&nbsp;
                            Total points : {{ number_format($tData['totalPts'], 2, ',', '') }}
                        </td>
                        <td class="px-3 py-2.5 text-center">{{ number_format($tData['totalPts'], 2, ',', '') }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    {{-- Boutons bulletin par trimestre --}}
    <div class="flex flex-wrap gap-3 mt-4">
        <p class="text-sm text-slate-500 w-full mb-1">Télécharger les bulletins :</p>
        @foreach([1,2,3] as $t)
        <a href="{{ route('censeur.classes.notes.bulletin.pdf', [$classe->id, $student->id, $t]) }}"
           target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow transition">
            <i class="fas fa-file-pdf text-xs"></i>
            Bulletin T{{ $t }}
        </a>
        @endforeach
    </div>

</div>
@endsection

@extends('layouts.app')
@section('content')
@php $pageTitle = 'Notes archivées – ' . $class->name . ' (' . $year->name . ')'; @endphp

<div class="bg-white p-6 rounded-xl shadow">

    {{-- En-tête ----------------------------------------------------------------}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Notes – {{ $class->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Année : {{ $year->name }}</p>
        </div>
        <a href="{{ route('archives.show', $year->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
            <i class="fas fa-arrow-left"></i> Retour aux classes
        </a>
    </div>

    @if(count($tableauNotes) === 0)
        <p class="text-gray-500 text-center py-12">
            <i class="fas fa-chart-bar text-4xl mb-3 block text-gray-300"></i>
            Aucune donnée de notes disponible pour cette classe.
        </p>
    @else

    {{-- Tableau principal -------------------------------------------------------}}
    <div class="overflow-x-auto border rounded-xl shadow-sm">
        <table class="min-w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-800 text-white text-xs">
                    <th class="border border-gray-600 px-3 py-3 text-left w-8">N°</th>
                    <th class="border border-gray-600 px-3 py-3 text-left min-w-[160px]">Nom & Prénoms</th>
                    {{-- Trimestres --}}
                    @foreach([1, 2, 3] as $t)
                        <th class="border border-gray-600 px-3 py-3 text-center" colspan="3">
                            Trimestre {{ $t }}
                        </th>
                    @endforeach
                    <th class="border border-gray-600 px-3 py-3 text-center" colspan="2">Annuel</th>
                </tr>
                <tr class="bg-gray-700 text-gray-100 text-xs">
                    <th class="border border-gray-600 px-2 py-2"></th>
                    <th class="border border-gray-600 px-2 py-2"></th>
                    @foreach([1, 2, 3] as $t)
                        <th class="border border-gray-600 px-2 py-2 text-center">Moy.</th>
                        <th class="border border-gray-600 px-2 py-2 text-center">Conduite</th>
                        <th class="border border-gray-600 px-2 py-2 text-center">Rang</th>
                    @endforeach
                    <th class="border border-gray-600 px-2 py-2 text-center">Moy. Ann.</th>
                    <th class="border border-gray-600 px-2 py-2 text-center">Rang Ann.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tableauNotes as $i => $row)
                    @php
                        $moyAnn = $row['moy_annuelle'];
                        $bgRow  = ($i % 2 == 0) ? 'bg-white' : 'bg-gray-50';
                        $statut = $moyAnn !== null ? ($moyAnn >= 10 ? 'text-green-700' : 'text-red-600') : 'text-gray-400';
                    @endphp
                    <tr class="{{ $bgRow }} hover:bg-blue-50 transition">
                        <td class="border px-3 py-2 text-center text-gray-500">{{ $i + 1 }}</td>
                        <td class="border px-3 py-2 font-medium text-gray-800">
                            {{ $row['student']->last_name }} {{ $row['student']->first_name }}
                        </td>

                        @foreach([1, 2, 3] as $t)
                            @php
                                $td   = $row['trimestres'][$t];
                                $moy  = $td['moyenne'];
                                $bgMoy = $moy === null ? '' : ($moy >= 10 ? 'text-green-700 font-semibold' : 'text-red-600 font-semibold');
                            @endphp
                            <td class="border px-2 py-2 text-center {{ $bgMoy }}">
                                {{ $moy !== null ? number_format($moy, 2, ',', '') : '–' }}
                            </td>
                            <td class="border px-2 py-2 text-center text-gray-600">
                                {{ number_format($td['conduite'], 2, ',', '') }}
                            </td>
                            <td class="border px-2 py-2 text-center text-gray-500 text-xs">
                                {{ $td['rang'] !== null ? $td['rang'] . 'ᵉ' : '–' }}
                            </td>
                        @endforeach

                        {{-- Annuel --}}
                        <td class="border px-2 py-2 text-center {{ $statut }} font-bold">
                            {{ $moyAnn !== null ? number_format($moyAnn, 2, ',', '') : '–' }}
                        </td>
                        <td class="border px-2 py-2 text-center text-gray-500 text-xs">
                            {{ isset($row['rang_annuel']) && $row['rang_annuel'] !== '-' ? $row['rang_annuel'] . 'ᵉ' : '–' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Statistiques de synthèse -----------------------------------------------}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach([1, 2, 3] as $t)
            @php
                $moys   = array_filter(array_column(array_column($tableauNotes, 'trimestres'), $t), fn($x) => $x['moyenne'] !== null);
                $moys   = array_map(fn($x) => $x['moyenne'], $moys);
                $admis  = count(array_filter($moys, fn($m) => $m >= 10));
                $effec  = count($moys);
                $moyC   = $effec > 0 ? round(array_sum($moys) / $effec, 2) : null;
            @endphp
            <div class="border rounded-xl p-4 bg-gray-50">
                <h3 class="font-semibold text-gray-700 mb-3 text-sm"> Trimestre {{ $t }}</h3>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Effectif noté</span>
                        <span class="font-medium">{{ $effec }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Moyenne classe</span>
                        <span class="font-medium {{ $moyC !== null && $moyC >= 10 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $moyC !== null ? number_format($moyC, 2, ',', '') : '–' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Admis (≥10)</span>
                        <span class="font-medium text-green-700">{{ $admis }}</span>
                    </div>
                    @if($effec > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Taux de réussite</span>
                            <span class="font-medium">{{ round($admis / $effec * 100) }}%</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
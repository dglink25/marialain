@extends('layouts.app')
@section('content')
@php $pageTitle = 'Paiements archivés – ' . $class->name . ' (' . $year->name . ')'; @endphp

<div class="bg-white p-6 rounded-xl shadow">

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Paiements – {{ $class->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Année archivée : {{ $year->name }}</p>
        </div>
        <a href="{{ route('archives.show', $year->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    {{-- Résumé --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-700">{{ $stats['total_students'] }}</div>
            <div class="text-xs text-blue-600 mt-1">Élèves</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-green-700">{{ number_format($stats['total_paid'], 0, ',', ' ') }}</div>
            <div class="text-xs text-green-600 mt-1">FCFA encaissés</div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <div class="text-xl font-bold text-red-700">{{ number_format($stats['total_remaining'], 0, ',', ' ') }}</div>
            <div class="text-xs text-red-600 mt-1">FCFA restants</div>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-purple-700">{{ $stats['rate'] }}%</div>
            <div class="text-xs text-purple-600 mt-1">Taux de recouvrement</div>
            <div class="w-full bg-purple-200 rounded-full h-1.5 mt-2">
                <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $stats['rate'] }}%"></div>
            </div>
        </div>
    </div>

    {{-- Tableau détaillé par record archivé --}}
    <div class="overflow-x-auto border rounded-xl shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 text-xs font-semibold text-gray-700">
                <tr>
                    <th class="border px-4 py-3 text-left">N°</th>
                    <th class="border px-4 py-3 text-left">Élève</th>
                    <th class="border px-4 py-3 text-left">Type inscription</th>
                    <th class="border px-4 py-3 text-right">Total dû</th>
                    <th class="border px-4 py-3 text-right">Payé</th>
                    <th class="border px-4 py-3 text-right">Reste</th>
                    <th class="border px-4 py-3 text-center">Moy. Ann.</th>
                    <th class="border px-4 py-3 text-center">Statut paiement</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $idx => $record)
                    @php
                        $totalFees  = $record->total_fees ?? 0;
                        $paid       = $record->amount_paid ?? 0;
                        $remaining  = max(0, $totalFees - $paid);
                        $rate       = $totalFees > 0 ? round($paid / $totalFees * 100) : 0;
                        $bgRow      = $idx % 2 == 0 ? 'bg-white' : 'bg-gray-50';
                        $moy        = $record->moy_annuelle;
                    @endphp
                    <tr class="{{ $bgRow }} hover:bg-blue-50 transition">
                        <td class="border px-4 py-2 text-gray-500">{{ $idx + 1 }}</td>
                        <td class="border px-4 py-2 font-medium text-gray-800">
                            {{ $record->last_name }} {{ $record->first_name }}
                        </td>
                        <td class="border px-4 py-2 text-gray-600 text-xs">
                            {{ $record->registration_type == 'new' ? 'Nouvelle inscription' : 'Réinscription' }}
                        </td>
                        <td class="border px-4 py-2 text-right font-medium">
                            {{ number_format($totalFees, 0, ',', ' ') }}
                        </td>
                        <td class="border px-4 py-2 text-right text-green-700 font-medium">
                            {{ number_format($paid, 0, ',', ' ') }}
                        </td>
                        <td class="border px-4 py-2 text-right {{ $remaining > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            {{ number_format($remaining, 0, ',', ' ') }}
                        </td>
                        <td class="border px-4 py-2 text-center font-semibold {{ $moy !== null && $moy >= 10 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $moy !== null ? number_format($moy, 2, ',', '') : '–' }}
                        </td>
                        <td class="border px-4 py-2 text-center">
                            @if($rate >= 100)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Soldé</span>
                            @elseif($rate >= 50)
                                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-medium">{{ $rate }}% payé</span>
                            @else
                                <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">{{ $rate }}% payé</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-400">Aucun enregistrement archivé.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-200 font-semibold text-sm">
                <tr>
                    <td colspan="3" class="border px-4 py-3 text-right text-gray-700">TOTAUX</td>
                    <td class="border px-4 py-3 text-right">{{ number_format($stats['total_fees'], 0, ',', ' ') }}</td>
                    <td class="border px-4 py-3 text-right text-green-700">{{ number_format($stats['total_paid'], 0, ',', ' ') }}</td>
                    <td class="border px-4 py-3 text-right text-red-600">{{ number_format($stats['total_remaining'], 0, ',', ' ') }}</td>
                    <td class="border px-4 py-3"></td>
                    <td class="border px-4 py-3 text-center">{{ $stats['rate'] }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
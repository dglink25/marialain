@extends('layouts.app')

@section('content')
@php $pageTitle = 'Élèves archivés – ' . $class->name . ' (' . $year->name . ')'; @endphp

<div class="bg-white p-6 rounded-xl shadow">

    {{-- En-tête ----------------------------------------------------------------}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $class->name }}
                <span class="text-sm font-normal text-gray-500 ml-2">{{ $year->name }}</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $records->total() }} élève(s) archivé(s)
                @if($class->entity)
                    · {{ ucfirst($class->entity->name) }}
                @endif
            </p>
        </div>
        <a href="{{ route('archives.show', $year->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
            <i class="fas fa-arrow-left"></i> Retour aux classes
        </a>
    </div>

    {{-- Statistiques de paiement (admin/sec) ----------------------------------}}
    @if($canViewPayments && $classPaymentStats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-blue-700">{{ $classPaymentStats['total_students'] }}</div>
                <div class="text-xs text-blue-600">Élèves</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-center">
                <div class="text-sm font-bold text-green-700">{{ number_format($classPaymentStats['total_paid'], 0, ',', ' ') }}</div>
                <div class="text-xs text-green-600">FCFA payés</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center">
                <div class="text-sm font-bold text-red-700">{{ number_format($classPaymentStats['total_remaining'], 0, ',', ' ') }}</div>
                <div class="text-xs text-red-600">FCFA restants</div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-3 text-center">
                <div class="text-xl font-bold text-purple-700">{{ $classPaymentStats['rate'] }}%</div>
                <div class="text-xs text-purple-600">Recouvrement</div>
            </div>
        </div>
    @endif

    @if($records->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-users text-5xl mb-4 block"></i>
            <p>Aucun élève archivé pour cette classe et cette année.</p>
        </div>
    @else
        <div class="overflow-x-auto border rounded-xl shadow-sm">
            <table class="min-w-max w-full text-sm">
                <thead class="bg-gray-800 text-white text-xs">
                    <tr>
                        <th class="border border-gray-600 px-3 py-3 text-left">N°</th>
                        <th class="border border-gray-600 px-3 py-3 text-left">N° Éduc</th>
                        <th class="border border-gray-600 px-3 py-3 text-left">Nom & Prénoms</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">Sexe</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">T1</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">T2</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">T3</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">Moy. Ann.</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">Rang</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">Statut</th>
                        @if($canViewPayments)
                            <th class="border border-gray-600 px-3 py-3 text-right">Total dû</th>
                            <th class="border border-gray-600 px-3 py-3 text-right">Payé</th>
                            <th class="border border-gray-600 px-3 py-3 text-right">Reste</th>
                        @endif
                        <th class="border border-gray-600 px-3 py-3 text-left">Parent/Tuteur</th>
                        <th class="border border-gray-600 px-3 py-3 text-center">Date naiss.</th>
                        <th class="border border-gray-600 px-3 py-3 text-left">Classe suivante</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $i => $record)
                        @php
                            $sid      = $record->student_id;
                            $data     = $moyennesData[$sid] ?? null;
                            $moyAnn   = $data['moy_annuelle'] ?? null;
                            $rang     = $rangAnnMap[$sid] ?? null;
                            $bgRow    = $i % 2 == 0 ? 'bg-white' : 'bg-gray-50';

                            $moyColor = fn($m) => $m === null
                                ? 'text-gray-400'
                                : ($m >= 10 ? 'text-green-700 font-semibold' : 'text-red-600 font-semibold');
                        @endphp
                        <tr class="{{ $bgRow }} hover:bg-blue-50 transition">
                            <td class="border px-3 py-2 text-gray-500">{{ $records->firstItem() + $i }}</td>
                            <td class="border px-3 py-2 text-gray-600">{{ $record->num_educ ?? '--' }}</td>
                            <td class="border px-3 py-2 font-medium text-gray-800">
                                {{ $record->last_name }} {{ $record->first_name }}
                            </td>
                            <td class="border px-3 py-2 text-center text-gray-600">{{ $record->gender ?? '--' }}</td>

                            {{-- Moyennes T1 / T2 / T3 calculées --}}
                            @foreach($trimestres as $t)
                                @php $mT = $data['trimestres'][$t]['moyenne'] ?? null; @endphp
                                <td class="border px-3 py-2 text-center {{ $moyColor($mT) }}">
                                    {{ $mT !== null ? number_format($mT, 2, ',', '') : '–' }}
                                </td>
                            @endforeach

                            {{-- Moyenne annuelle --}}
                            <td class="border px-3 py-2 text-center {{ $moyColor($moyAnn) }}">
                                {{ $moyAnn !== null ? number_format($moyAnn, 2, ',', '') : '–' }}
                            </td>

                            {{-- Rang annuel --}}
                            <td class="border px-3 py-2 text-center text-gray-500 text-xs">
                                {{ $rang ? $rang . 'ᵉ' : '–' }}
                            </td>

                            {{-- Statut délibération --}}
                            <td class="border px-3 py-2 text-center">
                                @if($record->statut_deliberation === 'passed')
                                    <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">✓ Admis</span>
                                @elseif($record->statut_deliberation === 'repeated')
                                    <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">↻ Redouble</span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">En cours</span>
                                @endif
                            </td>

                            {{-- Paiements --}}
                            @if($canViewPayments)
                                <td class="border px-3 py-2 text-right text-gray-700">
                                    {{ number_format($record->total_fees ?? 0, 0, ',', ' ') }}
                                </td>
                                <td class="border px-3 py-2 text-right text-green-700 font-medium">
                                    {{ number_format($record->amount_paid ?? 0, 0, ',', ' ') }}
                                </td>
                                <td class="border px-3 py-2 text-right {{ $record->remaining_fees > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                    {{ number_format($record->remaining_fees, 0, ',', ' ') }}
                                </td>
                            @endif

                            <td class="border px-3 py-2 text-gray-600 text-xs">
                                {{ $record->parent_full_name ?? '--' }}
                                @if($record->parent_phone)
                                    <br><span class="text-gray-400">{{ $record->parent_phone }}</span>
                                @endif
                            </td>
                            <td class="border px-3 py-2 text-center text-gray-600 text-xs">
                                {{ $record->birth_date ? $record->birth_date->format('d/m/Y') : '--' }}
                            </td>
                            <td class="border px-3 py-2 text-xs text-gray-500">
                                @if($record->nextClass && $record->nextAcademicYear)
                                    <span class="text-blue-600 font-medium">{{ $record->nextClass->name }}</span>
                                    <span class="text-gray-400">({{ $record->nextAcademicYear->name }})</span>
                                @else
                                    –
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection
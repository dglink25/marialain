@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-4 px-3 sm:py-6 sm:px-6 lg:px-8">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-file-alt text-white"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Évaluations sommatives</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    Classe : <span class="font-semibold text-orange-700">{{ $classe->name }}</span> — {{ $annee_academique->name }}
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($evaluations->isNotEmpty())
            <a href="{{ route('primaire.notes.sommative.recap', $classe->id) }}"
               class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition active:scale-95">
                <i class="fas fa-table"></i> Récapitulatif
            </a>
            @endif
            <a href="{{ route('primaire.notes.index') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Retour
            </a>
        </div>
    </div>

    @if($evaluations->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <i class="fas fa-file-alt text-orange-300 text-4xl mb-3"></i>
        <p class="text-gray-500 font-medium">Aucune évaluation sommative enregistrée pour cette classe.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-list text-orange-500"></i>
            <h2 class="font-bold text-gray-800">{{ $evaluations->count() }} évaluation(s)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold">
                    <tr>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Matière</th>
                        <th class="px-5 py-3 text-left">Titre</th>
                        <th class="px-5 py-3 text-center">Barème</th>
                        <th class="px-5 py-3 text-center">Notés</th>
                        <th class="px-5 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($evaluations as $eval)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-medium text-gray-700 whitespace-nowrap">
                            {{ $eval->date_evaluation ? $eval->date_evaluation->locale('fr')->isoFormat('ddd D MMM YYYY') : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-800 whitespace-nowrap">
                                {{ $eval->subject->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $eval->titre ?: '—' }}</td>
                        <td class="px-5 py-3 text-center font-bold text-gray-700">/{{ number_format($eval->note_max, 0) }}</td>
                        <td class="px-5 py-3 text-center text-xs text-gray-600">
                            {{ $eval->notes->whereNotNull('note')->count() }}/{{ $eval->notes->count() }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <a href="{{ route('primaire.notes.sommative.show', [$classe->id, $eval->id]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition whitespace-nowrap">
                                <i class="fas fa-eye text-xs"></i> Voir les notes
                            </a>
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

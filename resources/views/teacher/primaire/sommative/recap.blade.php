@extends('layouts.app')

@section('content')
@php
    $moisNoms = [
        1=>'Septembre', 2=>'Octobre', 3=>'Novembre', 4=>'Décembre',
        5=>'Janvier',   6=>'Février', 7=>'Mars',     8=>'Avril',
        9=>'Mai',       10=>'Juin',
    ];
@endphp

<div class="min-h-screen bg-gray-50 py-4 px-3 sm:py-6 sm:px-6 lg:px-8">

    {{-- En-tête --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-table text-white text-base sm:text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">
                    Récapitulatif — Composition de {{ $moisNoms[$composition->mois] ?? 'Mois '.$composition->mois }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    {{ $classe->name }} — {{ $annee->name }} — Toutes matières
                </p>
            </div>
        </div>
        <a href="{{ route('teacher.classes.sommative', $classe->id) }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition shrink-0">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

    @if($subjects->isEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
        <i class="fas fa-exclamation-triangle text-amber-400 text-3xl mb-3"></i>
        <p class="text-amber-800 font-semibold">Aucune matière configurée pour cette classe.</p>
    </div>
    @else

    {{-- Tableau croisé --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-users text-indigo-500"></i>
            <h2 class="text-base font-bold text-gray-800">Notes de tous les élèves, toutes matières</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold">
                    <tr>
                        <th class="px-4 py-2.5 text-left sticky left-0 bg-gray-50 z-10 w-10">N°</th>
                        <th class="px-4 py-2.5 text-left sticky left-10 bg-gray-50 z-10 min-w-[160px]">Nom & Prénoms</th>
                        @foreach($subjects as $subject)
                        @php $evalSubj = $evaluations->get($subject->id); @endphp
                        <th class="px-4 py-2.5 text-center whitespace-nowrap">
                            {{ $subject->name }}
                            <span class="block text-[10px] font-normal normal-case text-gray-400">
                                {{ $evalSubj ? '/'.number_format($evalSubj->note_max, 0) : 'non saisi' }}
                            </span>
                        </th>
                        @endforeach
                        <th class="px-4 py-2.5 text-center whitespace-nowrap bg-indigo-50 text-indigo-700">Moyenne /20</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($classe->students as $i => $student)
                    @php
                        $normalisees = [];
                        foreach ($subjects as $subject) {
                            $evalSubj = $evaluations->get($subject->id);
                            $note = $matrice[$student->id][$subject->id] ?? null;
                            if ($note !== null && $evalSubj && (float) $evalSubj->note_max > 0) {
                                $normalisees[] = ((float) $note / (float) $evalSubj->note_max) * 20;
                            }
                        }
                        $moyenneEleve = count($normalisees) > 0 ? array_sum($normalisees) / count($normalisees) : null;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-2.5 text-gray-400 text-xs sticky left-0 bg-white z-10">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5 sticky left-10 bg-white z-10 whitespace-nowrap">
                            <span class="font-semibold text-gray-800">{{ strtoupper($student->last_name) }}</span>
                            <span class="text-gray-600"> {{ $student->first_name }}</span>
                        </td>
                        @foreach($subjects as $subject)
                        @php $note = $matrice[$student->id][$subject->id] ?? null; @endphp
                        <td class="px-4 py-2.5 text-center">
                            @if($note !== null)
                                <span class="font-semibold text-gray-700">{{ number_format($note, 1, ',', '') }}</span>
                            @else
                                <span class="text-gray-300 text-xs italic">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-4 py-2.5 text-center bg-indigo-50/50 font-bold {{ $moyenneEleve !== null && $moyenneEleve >= 10 ? 'text-green-700' : ($moyenneEleve !== null ? 'text-red-600' : 'text-gray-300') }}">
                            {{ $moyenneEleve !== null ? number_format($moyenneEleve, 2, ',', '') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 sm:px-6 py-3 border-t border-gray-100 bg-gray-50 text-xs text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>
            La moyenne est calculée sur les matières déjà saisies, ramenées sur /20 pour permettre la comparaison entre matières ayant des barèmes différents (5/10 ou 10/20).
        </div>
    </div>
    @endif

</div>
@endsection
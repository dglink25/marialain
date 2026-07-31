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

    {{-- Toast --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 sm:px-5 py-4 shadow-sm">
        <i class="fas fa-check-circle text-emerald-500 flex-shrink-0"></i>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 sm:px-5 py-4 shadow-sm">
        @foreach($errors->all() as $err)
        <p class="text-sm">• {{ $err }}</p>
        @endforeach
    </div>
    @endif

    {{-- En-tête --}}
    <div class="mb-6 sm:mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-orange-500 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-file-alt text-white text-base sm:text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Évaluations sommatives</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5 break-words">
                    Classe : <span class="font-semibold text-orange-700">{{ $classe->name }}</span> — {{ $annee->name }}
                </p>
            </div>
        </div>
        <a href="{{ route('teacher.classes.primaire') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition shrink-0">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

    {{-- Aucune composition --}}
    @if($compositions->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 sm:p-12 text-center">
        <div class="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-calendar-times text-orange-300 text-3xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-600 mb-2">Aucune composition programmée</h2>
        <p class="text-gray-400 text-sm">Le directeur n'a pas encore programmé de composition pour votre classe.</p>
    </div>

    {{-- Aucune matière --}}
    @elseif($subjects->isEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
        <i class="fas fa-exclamation-triangle text-amber-400 text-3xl mb-3"></i>
        <p class="text-amber-800 font-semibold">Aucune matière configurée pour cette classe.</p>
    </div>

    @else
    <div class="space-y-5">
        @foreach($compositions as $comp)
        @php
            $couleur = $comp->couleur;
            $bg     = "bg-{$couleur}-50";
            $border = "border-{$couleur}-400";
            $badge  = "bg-{$couleur}-100 text-{$couleur}-800";
            $ring   = "ring-{$couleur}-200";
        @endphp
        <div class="bg-white rounded-2xl border-l-4 {{ $border }} shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden ring-1 {{ $ring }}">

            {{-- Header composition --}}
            <div class="px-4 sm:px-6 py-4 {{ $bg }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm shrink-0">
                        <i class="{{ $comp->icone }} text-gray-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-gray-900 truncate">
                            Composition de {{ $moisNoms[$comp->mois] ?? 'Mois '.$comp->mois }}
                        </h2>
                        <span class="inline-flex items-center gap-1.5 mt-0.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                            {{ $comp->etapeLabel }}
                        </span>
                    </div>
                </div>

                {{-- Compte à rebours / statut --}}
                @if($comp->etape !== 'termine' && $comp->joursRestants > 0)
                <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-2 shadow-sm shrink-0">
                    <i class="fas fa-hourglass-half text-gray-400 text-sm"></i>
                    <div class="text-center">
                        <span class="text-2xl font-black text-gray-800">{{ $comp->joursRestants }}</span>
                        <span class="text-xs text-gray-500 block -mt-0.5">jour(s)</span>
                    </div>
                </div>
                @elseif($comp->etape === 'termine')
                <div class="flex items-center gap-2 bg-gray-100 rounded-xl px-4 py-2 shrink-0">
                    <i class="fas fa-lock text-gray-500"></i>
                    <span class="text-sm font-semibold text-gray-600">Saisie clôturée</span>
                </div>
                @endif
            </div>

            {{-- Dates de la période de saisie + Récapitulatif --}}
            <div class="px-4 sm:px-6 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 inline-flex flex-wrap items-center gap-2 text-xs text-gray-600">
                    <i class="fas fa-edit text-orange-500"></i>
                    <span>Saisie des notes :</span>
                    <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($comp->saisie_debut)->locale('fr')->isoFormat('D MMM') }}</span>
                    <i class="fas fa-arrow-right text-gray-400 text-[10px]"></i>
                    <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($comp->saisie_fin)->locale('fr')->isoFormat('D MMM YYYY') }}</span>
                </div>
                @if($comp->etape !== 'a_venir')
                <a href="{{ route('teacher.primaire.sommative.recap', [$classe->id, $comp->id]) }}"
                   class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-sm transition-colors shrink-0">
                    <i class="fas fa-table text-xs"></i>
                    Récapitulatif toutes matières
                </a>
                @endif
            </div>

            {{-- Tableau des matières --}}
            <div class="p-4 sm:p-6">
                <p class="text-[11px] text-gray-400 mb-2 flex items-center gap-1.5">
                    <i class="fas fa-info-circle"></i>
                    Chaque matière peut avoir son propre barème (/10 ou /20). Les moyennes sont automatiquement
                    ramenées sur /20 dans le récapitulatif pour rester comparables.
                </p>
                <div class="border border-gray-200 rounded-xl overflow-x-auto">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Matière</th>
                                <th class="px-4 py-2.5 text-center">Barème</th>
                                <th class="px-4 py-2.5 text-center">Élèves notés</th>
                                <th class="px-4 py-2.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($subjects as $subject)
                            @php
                                $key         = $comp->id.'-'.$subject->id;
                                $evalGroup   = $evaluations->get($key);
                                $evaluation  = $evalGroup ? $evalGroup->first() : null;
                                $notesCount  = $evaluation ? $evaluation->notes->whereNotNull('note')->count() : 0;
                                $totalEleves = $classe->students->count();
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-700">{{ $subject->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($evaluation)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 whitespace-nowrap">
                                        /{{ number_format($evaluation->note_max, 0) }}
                                    </span>
                                    @else
                                    <span class="text-xs text-gray-300 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 whitespace-nowrap">
                                        <i class="fas fa-users text-gray-400"></i>
                                        {{ $notesCount }}/{{ $totalEleves }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        {{-- Saisir / Modifier : uniquement pendant la fenêtre de saisie --}}
                                        @if($comp->etape === 'saisie')
                                        <a href="{{ route('teacher.primaire.sommative.saisie', [$classe->id, $comp->id, $subject->id]) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $evaluation ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-500 hover:bg-orange-600' }} text-white text-xs font-bold rounded-lg transition-colors whitespace-nowrap">
                                            <i class="fas {{ $evaluation ? 'fa-edit' : 'fa-plus' }} text-xs"></i>
                                            {{ $evaluation ? 'Modifier' : 'Saisir les notes' }}
                                        </a>
                                        @endif

                                        {{-- Lire : dès qu'il existe des notes, à tout moment (même pendant la saisie) --}}
                                        @if($evaluation)
                                        <a href="{{ route('teacher.primaire.sommative.show', [$classe->id, $comp->id, $subject->id]) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-lg transition-colors whitespace-nowrap">
                                            <i class="fas fa-eye text-xs"></i> Lire
                                        </a>
                                        @endif

                                        {{-- Rien saisi et hors période de saisie : état verrouillé --}}
                                        @if(!$evaluation && $comp->etape !== 'saisie')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-400 text-xs font-bold rounded-lg whitespace-nowrap">
                                            <i class="fas fa-lock text-xs"></i>
                                            {{ $comp->etape === 'a_venir' ? 'Saisie non ouverte' : 'Saisie clôturée' }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
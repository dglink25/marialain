@extends('layouts.app')

@section('content')
@php
    $moisNoms = [
        1=>'Septembre', 2=>'Octobre', 3=>'Novembre', 4=>'Décembre',
        5=>'Janvier',   6=>'Février', 7=>'Mars',     8=>'Avril',
        9=>'Mai',       10=>'Juin',
    ];

    $couleurConfig = [
        'blue'   => ['bg'=>'bg-blue-50',   'border'=>'border-blue-400',  'badge'=>'bg-blue-100 text-blue-800',   'icon'=>'fas fa-clock',         'ring'=>'ring-blue-200'],
        'amber'  => ['bg'=>'bg-amber-50',  'border'=>'border-amber-400', 'badge'=>'bg-amber-100 text-amber-800', 'icon'=>'fas fa-pencil-ruler',  'ring'=>'ring-amber-200'],
        'orange' => ['bg'=>'bg-orange-50', 'border'=>'border-orange-400','badge'=>'bg-orange-100 text-orange-800','icon'=>'fas fa-edit',         'ring'=>'ring-orange-200'],
        'purple' => ['bg'=>'bg-purple-50', 'border'=>'border-purple-400','badge'=>'bg-purple-100 text-purple-800','icon'=>'fas fa-hourglass-half','ring'=>'ring-purple-200'],
        'green'  => ['bg'=>'bg-green-50',  'border'=>'border-green-400', 'badge'=>'bg-green-100 text-green-800', 'icon'=>'fas fa-check-circle',  'ring'=>'ring-green-200'],
    ];
@endphp

<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- En-tête --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-bell text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Annonces - Notes &amp; Évaluations</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if($classe)
                        Classe : <span class="font-semibold text-indigo-700">{{ $classe->name }}</span> — {{ $annee->name }}
                    @else
                        {{ $annee->name }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Boutons d'accès aux évaluations : affichés UNE SEULE FOIS pour toute la page --}}
        @if($classe)
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <a href="{{ route('teacher.classes.sommative', $classe->id) }}"
               class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm py-2.5 px-5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95">
                <i class="fas fa-file-alt"></i>
                Évaluation sommative
            </a>
            <a href="{{ route('teacher.classes.formative', $classe->id) }}"
               class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm py-2.5 px-5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95">
                <i class="fas fa-pencil-alt"></i>
                Évaluation formative
            </a>
        </div>
        @endif
    </div>

    {{-- Pas de classe assignée --}}
    @if(!$classe)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
        <i class="fas fa-exclamation-triangle text-amber-400 text-4xl mb-3"></i>
        <p class="text-amber-800 font-semibold">{{ $error ?? "Aucune classe assignée." }}</p>
    </div>

    @else

    {{-- Aucune composition programmée --}}
    @if($compositions->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-calendar-times text-indigo-300 text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-600 mb-2">Aucune composition programmée</h2>
        <p class="text-gray-400 text-sm">Le directeur n'a pas encore programmé de composition pour votre classe.</p>
    </div>

    @else

    {{-- Liste des compositions (informative uniquement, sans boutons répétés) --}}
    <div class="space-y-5">
        @foreach($compositions as $comp)
        @php
            $cc = $couleurConfig[$comp->couleur] ?? $couleurConfig['blue'];
        @endphp

        <div class="bg-white rounded-2xl border-l-4 {{ $cc['border'] }} shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden ring-1 {{ $cc['ring'] }}">

            {{-- Header composition --}}
            <div class="px-6 py-4 {{ $cc['bg'] }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                        <i class="{{ $cc['icon'] }} text-gray-600"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">
                            Composition de {{ $moisNoms[$comp->mois] ?? 'Mois '.$comp->mois }}
                        </h2>
                        <span class="inline-flex items-center gap-1.5 mt-0.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $cc['badge'] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                            {{ $comp->etapeLabel }}
                        </span>
                    </div>
                </div>

                {{-- Compte à rebours --}}
                @if($comp->etape !== 'terminé' && $comp->joursRestants > 0)
                <div class="flex items-center gap-2 bg-white rounded-xl px-4 py-2 shadow-sm">
                    <i class="fas fa-hourglass-half text-gray-400 text-sm"></i>
                    <div class="text-center">
                        <span class="text-2xl font-black text-gray-800">{{ $comp->joursRestants }}</span>
                        <span class="text-xs text-gray-500 block -mt-0.5">jour(s)</span>
                    </div>
                </div>
                @elseif($comp->etape === 'terminé')
                <div class="flex items-center gap-2 bg-green-100 rounded-xl px-4 py-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span class="text-sm font-semibold text-green-700">Terminé</span>
                </div>
                @endif
            </div>

            {{-- Corps --}}
            <div class="px-6 py-5">

                {{-- Message étape --}}
                <div class="flex items-start gap-2 mb-5">
                    <i class="fas fa-info-circle text-indigo-400 mt-0.5 flex-shrink-0"></i>
                    <p class="text-sm font-medium text-gray-700">{{ $comp->etapeMessage }}</p>
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Période composition --}}
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-pencil-ruler text-amber-500 text-sm"></i>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Période de composition</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($comp->composition_debut)->locale('fr')->isoFormat('ddd D MMM') }}
                            </span>
                            <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
                            <span class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($comp->composition_fin)->locale('fr')->isoFormat('ddd D MMM YYYY') }}
                            </span>
                        </div>
                    </div>

                    {{-- Période saisie --}}
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-edit text-orange-500 text-sm"></i>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Saisie des notes</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($comp->saisie_debut)->locale('fr')->isoFormat('ddd D MMM') }}
                            </span>
                            <i class="fas fa-arrow-right text-gray-400 text-xs"></i>
                            <span class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($comp->saisie_fin)->locale('fr')->isoFormat('ddd D MMM YYYY') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endif

</div>
@endsection
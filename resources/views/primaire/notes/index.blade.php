@extends('layouts.app')

@section('content')
@php
    $moisNoms = [
        1=>'Septembre', 2=>'Octobre', 3=>'Novembre', 4=>'Décembre',
        5=>'Janvier',   6=>'Février', 7=>'Mars',     8=>'Avril',
        9=>'Mai',       10=>'Juin',
    ];
    $classesPrimaire   = $classes->where('entity_id', 2);
    $classesMaternelle = $classes->where('entity_id', 1);
    $isAuthorized      = in_array(auth()->id(), [6, 7]);
@endphp

<div class="min-h-screen bg-slate-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- ── Toast ── --}}
    @if(session('success'))
    <div class="mb-6 flex items-center gap-3 bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-2xl px-5 py-4 shadow-sm">
        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center shrink-0">
            <i class="fas fa-check text-indigo-600 text-sm"></i>
        </div>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
    @endif

    {{-- ── En-tête ── --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-md shrink-0">
                        <i class="fas fa-clipboard-list text-white text-base"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-800">Gestion des notes</h1>
                </div>
                <p class="text-sm text-slate-500 pl-0.5">Maternelle &amp; Primaire — <span class="font-semibold text-indigo-600">{{ $annee_academique->name }}</span></p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                @if($isAuthorized)
                <button onclick="openDeliModal()"
                        class="inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95">
                    <i class="fas fa-graduation-cap text-xs"></i>
                    Délibération
                </button>
                @endif
                <button onclick="openModal()"
                        class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95">
                    <i class="fas fa-calendar-plus text-xs"></i>
                    Programmer une composition
                </button>
            </div>
        </div>
    </div>

    {{-- ── Stats rapides ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Classes</p>
            <p class="text-2xl font-bold text-slate-800">{{ $classes->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Primaire</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $classesPrimaire->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Maternelle</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $classesMaternelle->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Compositions</p>
            <p class="text-2xl font-bold text-slate-800">{{ $compositions->count() }}</p>
        </div>
    </div>

    {{-- ── Compositions programmées ── --}}
    @if($compositions->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-8 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-indigo-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-check text-indigo-500 text-xs"></i>
            </div>
            <h2 class="text-sm font-bold text-slate-800">Compositions programmées</h2>
            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-xs font-bold rounded-full">{{ $compositions->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Mois</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Classes</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Période composition</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Période saisie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($compositions as $comp)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold">
                                {{ $moisNoms[$comp->mois] ?? 'Mois '.$comp->mois }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php $classesComp = $classes->whereIn('id', $comp->classe_ids)->pluck('name')->join(', '); @endphp
                            <span class="text-slate-600 text-xs">{{ $classesComp ?: '—' }}</span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($comp->composition_debut)->format('d/m/Y') }}</span>
                            <span class="text-slate-300 mx-1.5">→</span>
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($comp->composition_fin)->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($comp->saisie_debut)->format('d/m/Y') }}</span>
                            <span class="text-slate-300 mx-1.5">→</span>
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($comp->saisie_fin)->format('d/m/Y') }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Liste des classes ── --}}
    @php
        $classesPrimaire   = $classes->where('entity_id', 2);
        $classesMaternelle = $classes->where('entity_id', 1);
    @endphp

    {{-- Primaire --}}
    @if($classesPrimaire->count())
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-school text-white text-xs"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800">Primaire</h2>
                <p class="text-xs text-slate-400">{{ $classesPrimaire->count() }} classe(s)</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($classesPrimaire as $classe)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden group">
                <div class="px-5 py-4 border-b border-slate-50">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 text-base truncate group-hover:text-indigo-700 transition-colors">{{ $classe->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5"><i class="fas fa-users mr-1"></i>{{ $classe->students->count() ?? 0 }} élève(s)</p>
                        </div>
                        <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-school text-indigo-500 text-sm"></i>
                        </div>
                    </div>
                </div>
                <div class="p-3 flex flex-col gap-2">
                    <a href="{{ route('primaire.notes.formative', $classe->id) }}"
                       class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 text-sm font-medium transition-all duration-150 group/btn">
                        <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0 group-hover/btn:bg-indigo-600 transition-colors">
                            <i class="fas fa-pencil-alt text-slate-400 text-xs group-hover/btn:text-white transition-colors"></i>
                        </div>
                        <span>Évaluations formatives</span>
                        <i class="fas fa-chevron-right text-xs ml-auto text-slate-300 group-hover/btn:text-indigo-400 transition-colors"></i>
                    </a>
                    <a href="{{ route('primaire.notes.sommative', $classe->id) }}"
                       class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 text-sm font-medium transition-all duration-150 group/btn">
                        <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0 group-hover/btn:bg-indigo-600 transition-colors">
                            <i class="fas fa-file-alt text-slate-400 text-xs group-hover/btn:text-white transition-colors"></i>
                        </div>
                        <span>Évaluations sommatives</span>
                        <i class="fas fa-chevron-right text-xs ml-auto text-slate-300 group-hover/btn:text-indigo-400 transition-colors"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Maternelle --}}
    @if($classesMaternelle->count())
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 bg-violet-600 rounded-lg flex items-center justify-center shrink-0">
                <i class="fas fa-child text-white text-xs"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800">Maternelle</h2>
                <p class="text-xs text-slate-400">{{ $classesMaternelle->count() }} classe(s)</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($classesMaternelle as $classe)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden group">
                <div class="px-5 py-4 border-b border-slate-50">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 text-base truncate group-hover:text-violet-700 transition-colors">{{ $classe->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5"><i class="fas fa-users mr-1"></i>{{ $classe->students->count() ?? 0 }} élève(s)</p>
                        </div>
                        <div class="w-9 h-9 bg-violet-50 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-violet-100 transition-colors">
                            <i class="fas fa-child text-violet-500 text-sm"></i>
                        </div>
                    </div>
                </div>
                <div class="p-3 flex flex-col gap-2">
                    <a href="{{ route('primaire.notes.formative', $classe->id) }}"
                       class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-violet-50 text-slate-700 hover:text-violet-700 text-sm font-medium transition-all duration-150 group/btn">
                        <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0 group-hover/btn:bg-violet-600 transition-colors">
                            <i class="fas fa-pencil-alt text-slate-400 text-xs group-hover/btn:text-white transition-colors"></i>
                        </div>
                        <span>Évaluations formatives</span>
                        <i class="fas fa-chevron-right text-xs ml-auto text-slate-300 group-hover/btn:text-violet-400 transition-colors"></i>
                    </a>
                    <a href="{{ route('primaire.notes.sommative', $classe->id) }}"
                       class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-violet-50 text-slate-700 hover:text-violet-700 text-sm font-medium transition-all duration-150 group/btn">
                        <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0 group-hover/btn:bg-violet-600 transition-colors">
                            <i class="fas fa-file-alt text-slate-400 text-xs group-hover/btn:text-white transition-colors"></i>
                        </div>
                        <span>Évaluations sommatives</span>
                        <i class="fas fa-chevron-right text-xs ml-auto text-slate-300 group-hover/btn:text-violet-400 transition-colors"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($classes->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-5 shadow-sm">
            <i class="fas fa-school text-indigo-300 text-3xl"></i>
        </div>
        <h3 class="text-base font-bold text-slate-700 mb-1">Aucune classe disponible</h3>
        <p class="text-sm text-slate-400">Aucune classe primaire ou maternelle pour {{ $annee_academique->name }}.</p>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════
     MODAL — Programmer une composition
══════════════════════════════════════════════ --}}
<div id="composition-overlay"
     class="fixed inset-0 z-[8000] hidden"
     onclick="closeModalIfBackdrop(event)">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="relative flex items-start sm:items-center justify-center h-full w-full px-0 sm:px-4 py-0 sm:py-8 overflow-y-auto">
        <div id="composition-panel"
             class="relative bg-white w-full sm:max-w-xl sm:rounded-2xl shadow-2xl border border-slate-100
                    flex flex-col h-full sm:h-auto sm:max-h-[90vh]
                    transform transition-all duration-300 translate-y-8 opacity-0">
            <div class="shrink-0 flex items-center justify-between px-5 sm:px-6 pt-5 pb-4 border-b border-slate-100 bg-white sm:rounded-t-2xl">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-calendar-plus text-white text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-slate-800 truncate">Programmer une composition</h2>
                        <p class="text-xs text-slate-400 truncate">{{ $annee_academique->name }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('primaire.notes.composition.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <div class="flex-1 min-h-0 overflow-y-auto px-5 sm:px-6 py-5 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Classes concernées <span class="text-red-500">*</span></label>
                        <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 max-h-44 overflow-y-auto space-y-1">
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-indigo-700 pb-2 border-b border-slate-200 mb-1">
                                <input type="checkbox" id="select-all" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                Toutes les classes
                            </label>
                            @if($classesPrimaire->count())
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold pt-1">Primaire</p>
                            @foreach($classesPrimaire as $classe)
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700 hover:bg-white rounded-lg px-2 py-1 transition-colors">
                                <input type="checkbox" name="classe_ids[]" value="{{ $classe->id }}" class="classe-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $classe->name }}
                            </label>
                            @endforeach
                            @endif
                            @if($classesMaternelle->count())
                            <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold pt-1">Maternelle</p>
                            @foreach($classesMaternelle as $classe)
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-slate-700 hover:bg-white rounded-lg px-2 py-1 transition-colors">
                                <input type="checkbox" name="classe_ids[]" value="{{ $classe->id }}" class="classe-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $classe->name }}
                            </label>
                            @endforeach
                            @endif
                        </div>
                        @error('classe_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="mois" class="block text-sm font-semibold text-slate-700 mb-1.5">Mois de composition <span class="text-red-500">*</span></label>
                        <select id="mois" name="mois" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <option value="">— Choisir un mois —</option>
                            @foreach($moisNoms as $num => $nom)
                            <option value="{{ $num }}" {{ old('mois') == $num ? 'selected' : '' }}>{{ $nom }}</option>
                            @endforeach
                        </select>
                        @error('mois')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Période de composition <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="composition_debut" class="text-xs text-slate-400 mb-1 block">Du</label>
                                <input type="date" id="composition_debut" name="composition_debut" value="{{ old('composition_debut') }}" required
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                @error('composition_debut')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="composition_fin" class="text-xs text-slate-400 mb-1 block">Au</label>
                                <input type="date" id="composition_fin" name="composition_fin" value="{{ old('composition_fin') }}" required
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                @error('composition_fin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Période de saisie des notes <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="saisie_debut" class="text-xs text-slate-400 mb-1 block">Du</label>
                                <input type="date" id="saisie_debut" name="saisie_debut" value="{{ old('saisie_debut') }}" required
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                @error('saisie_debut')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="saisie_fin" class="text-xs text-slate-400 mb-1 block">Au</label>
                                <input type="date" id="saisie_fin" name="saisie_fin" value="{{ old('saisie_fin') }}" required
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                @error('saisie_fin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="shrink-0 flex gap-3 px-5 sm:px-6 py-4 border-t border-slate-100 bg-white sm:rounded-b-2xl">
                    <button type="button" onclick="closeModal()"
                            class="flex-1 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl py-3 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-3 rounded-xl shadow-md transition-all duration-200 active:scale-95">
                        <i class="fas fa-check text-xs"></i>
                        Programmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     MODAL — Délibération Primaire / Maternelle
══════════════════════════════════════════════ --}}
<div id="deli-overlay" class="fixed inset-0 z-[8001] hidden" onclick="closeDeliIfBackdrop(event)">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="relative flex items-start sm:items-center justify-center h-full w-full px-0 sm:px-4 py-0 sm:py-6 overflow-y-auto">
        <div id="deli-panel"
             class="relative bg-white w-full sm:max-w-2xl sm:rounded-2xl shadow-2xl border border-slate-100
                    flex flex-col h-full sm:h-auto sm:max-h-[92vh]
                    transform transition-all duration-300 translate-y-8 opacity-0">

            {{-- Header --}}
            <div class="shrink-0 flex items-center justify-between px-5 pt-5 pb-4 border-b border-slate-100 bg-white sm:rounded-t-2xl">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-graduation-cap text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Délibération</h2>
                        <p class="text-xs text-slate-400">{{ $annee_academique->name }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeDeliModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Corps scrollable --}}
            <div class="flex-1 min-h-0 overflow-y-auto px-5 py-5 space-y-5">

                {{-- Étape 1 : Classe source --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-amber-500 text-white rounded-full text-xs font-bold mr-1.5">1</span>
                        Classe source
                    </label>
                    <select id="deli-source-class"
                            onchange="deliChargerEleves()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <option value="">— Sélectionnez une classe —</option>
                        @foreach($classesPrimaire as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Primaire)</option>
                        @endforeach
                        @foreach($classesMaternelle as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} (Maternelle)</option>
                        @endforeach
                    </select>
                </div>

                {{-- Étape 2 : Année de destination --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-amber-500 text-white rounded-full text-xs font-bold mr-1.5">2</span>
                        Année de destination
                    </label>
                    <select id="deli-target-year"
                            onchange="deliChargerClassesDest()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <option value="">— Sélectionnez une année —</option>
                        @foreach(\App\Models\AcademicYear::where('active', false)->orderBy('name')->get() as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Étape 3 : Sélection des élèves --}}
                <div id="deli-eleves-section" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                            <span class="inline-flex items-center justify-center w-5 h-5 bg-amber-500 text-white rounded-full text-xs font-bold mr-1.5">3</span>
                            Élèves à transférer
                        </label>
                        <div class="flex items-center gap-2">
                            <span id="deli-eleves-count" class="text-xs text-slate-400"></span>
                            <button type="button" onclick="deliToutSelectionner()"
                                    class="text-xs font-semibold text-amber-600 hover:text-amber-700 underline">
                                Tout sélectionner
                            </button>
                        </div>
                    </div>
                    <div id="deli-eleves-list"
                         class="border border-slate-200 rounded-xl bg-slate-50 max-h-48 overflow-y-auto divide-y divide-slate-100">
                        {{-- Rempli via AJAX --}}
                    </div>
                    <div id="deli-eleves-loader" class="hidden py-6 text-center">
                        <div class="w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                        <p class="text-xs text-slate-400">Chargement...</p>
                    </div>
                </div>

                {{-- Étape 4 : Cycle de destination --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-amber-500 text-white rounded-full text-xs font-bold mr-1.5">4</span>
                        Cycle de destination
                    </label>
                    <select id="deli-target-entity"
                            onchange="deliChargerClassesDest()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <option value="">— Sélectionnez le cycle —</option>
                        <option value="1">Maternelle</option>
                        <option value="2">Primaire</option>
                        <option value="3">Secondaire</option>
                    </select>
                </div>

                {{-- Étape 5 : Classe de destination --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">
                        <span class="inline-flex items-center justify-center w-5 h-5 bg-amber-500 text-white rounded-full text-xs font-bold mr-1.5">5</span>
                        Classe de destination
                    </label>
                    <select id="deli-target-class"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                        <option value="">— Choisissez d'abord le cycle et l'année —</option>
                    </select>
                    <div id="deli-classes-loader" class="hidden mt-1 text-xs text-slate-400 flex items-center gap-1">
                        <div class="w-3 h-3 border border-amber-400 border-t-transparent rounded-full animate-spin"></div>
                        Chargement des classes...
                    </div>
                </div>

                {{-- Résumé --}}
                <div id="deli-resume" class="hidden bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
                </div>

            </div>

            {{-- Footer --}}
            <div class="shrink-0 flex gap-3 px-5 py-4 border-t border-slate-100 bg-white sm:rounded-b-2xl">
                <button type="button" onclick="closeDeliModal()"
                        class="flex-1 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl py-3 transition-colors">
                    Annuler
                </button>
                <button type="button" onclick="deliSoumettre()" id="deli-submit-btn"
                        class="flex-1 flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-sm py-3 rounded-xl shadow-md transition-all duration-200 active:scale-95">
                    <i class="fas fa-graduation-cap text-xs"></i>
                    Délibérer
                </button>
            </div>
        </div>
    </div>
</div>

            {{-- Header (fixe) --}}
            <div class="shrink-0 flex items-center justify-between px-4 sm:px-6 pt-5 sm:pt-6 pb-4 border-b border-gray-100 bg-white sm:rounded-t-2xl">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 bg-purple-600 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-calendar-plus text-white text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 truncate">Programmer une composition</h2>
                        <p class="text-xs text-gray-400 truncate">{{ $annee_academique->name }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal()"
                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form action="{{ route('primaire.notes.composition.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf

                {{-- Corps (scrollable) --}}
                <div class="flex-1 min-h-0 overflow-y-auto px-4 sm:px-6 py-5 space-y-5">

                    {{-- Champ 1 : Classes --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-school text-purple-500 mr-1"></i>
                            Classes concernées <span class="text-red-500">*</span>
                        </label>
                        <div class="border border-gray-200 rounded-xl p-3 bg-gray-50 max-h-48 overflow-y-auto space-y-1.5">
                            {{-- Sélectionner tout --}}
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-purple-700 pb-1.5 border-b border-gray-200 mb-1">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                Toutes les classes
                            </label>

                            {{-- Primaire --}}
                            @if($classesPrimaire->count())
                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold pt-1">Primaire</p>
                            @foreach($classesPrimaire as $classe)
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 hover:bg-white rounded-lg px-2 py-1 transition-colors">
                                <input type="checkbox" name="classe_ids[]" value="{{ $classe->id }}"
                                       class="classe-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                {{ $classe->name }}
                            </label>
                            @endforeach
                            @endif

                            {{-- Maternelle --}}
                            @if($classesMaternelle->count())
                            <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold pt-1">Maternelle</p>
                            @foreach($classesMaternelle as $classe)
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 hover:bg-white rounded-lg px-2 py-1 transition-colors">
                                <input type="checkbox" name="classe_ids[]" value="{{ $classe->id }}"
                                       class="classe-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                {{ $classe->name }}
                            </label>
                            @endforeach
                            @endif
                        </div>
                        @error('classe_ids')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Champ 2 : Mois --}}
                    <div>
                        <label for="mois" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-calendar text-purple-500 mr-1"></i>
                            Composition du mois de <span class="text-red-500">*</span>
                        </label>
                        <select id="mois" name="mois" required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                            <option value="">— Choisir un mois —</option>
                            @foreach($moisNoms as $num => $nom)
                            <option value="{{ $num }}" {{ old('mois') == $num ? 'selected' : '' }}>{{ $nom }}</option>
                            @endforeach
                        </select>
                        @error('mois')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Champ 3 : Période de composition — dates --}}
@endsection

@section('scripts')
<script>
// ── Modal Composition ──────────────────────────────────────────────
const overlay = document.getElementById('composition-overlay');
const panel   = document.getElementById('composition-panel');

function openModal() {
    overlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
        panel.classList.remove('translate-y-8', 'opacity-0');
        panel.classList.add('translate-y-0', 'opacity-100');
    });
}
function closeModal() {
    panel.classList.remove('translate-y-0', 'opacity-100');
    panel.classList.add('translate-y-8', 'opacity-0');
    setTimeout(() => { overlay.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }, 250);
}
function closeModalIfBackdrop(e) { if (e.target === overlay) closeModal(); }

document.getElementById('select-all').addEventListener('change', function () {
    document.querySelectorAll('.classe-checkbox').forEach(cb => cb.checked = this.checked);
});

const ANNEE_NAME = '{{ $annee_academique->name }}';
const parts = ANNEE_NAME.split('-');
const annee1 = parseInt(parts[0]);
const annee2 = parseInt(parts[1]);
const moisCalendaire = {
    1:{m:9,a:annee1}, 2:{m:10,a:annee1}, 3:{m:11,a:annee1}, 4:{m:12,a:annee1},
    5:{m:1,a:annee2}, 6:{m:2,a:annee2},  7:{m:3,a:annee2},  8:{m:4,a:annee2},
    9:{m:5,a:annee2}, 10:{m:6,a:annee2}
};
function pad(n) { return String(n).padStart(2,'0'); }

function updateDateConstraints() {
    const mois = parseInt(document.getElementById('mois').value);
    if (!mois || !moisCalendaire[mois]) return;
    const {m,a} = moisCalendaire[mois];
    const minDate = `${a}-${pad(m)}-01`;
    const maxDate = `${a}-${pad(m)}-${pad(new Date(a,m,0).getDate())}`;
    ['composition_debut','composition_fin','saisie_debut','saisie_fin'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.min = minDate; el.max = maxDate;
        if (el.value && (el.value < minDate || el.value > maxDate)) el.value = '';
    });
    document.getElementById('composition_debut').addEventListener('change', function() {
        const fin = document.getElementById('composition_fin');
        if (fin.value && fin.value < this.value) fin.value = this.value;
        fin.min = this.value || minDate;
    });
    document.getElementById('saisie_debut').addEventListener('change', function() {
        const fin = document.getElementById('saisie_fin');
        if (fin.value && fin.value < this.value) fin.value = this.value;
        fin.min = this.value || minDate;
    });
}
document.getElementById('mois').addEventListener('change', updateDateConstraints);
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('mois').value) updateDateConstraints();
});
@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openModal());
@endif

// ── Modal Délibération ─────────────────────────────────────────────
const deliOverlay = document.getElementById('deli-overlay');
const deliPanel   = document.getElementById('deli-panel');
const CSRF        = '{{ csrf_token() }}';
const URL_ELEVES  = '{{ route("primaire.notes.deliberation.eleves", ":id") }}';
const URL_CLASSES = '{{ route("primaire.notes.deliberation.classes-destination") }}';
const URL_DELI    = '{{ route("primaire.notes.deliberation.store") }}';

function openDeliModal() {
    deliOverlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
        deliPanel.classList.remove('translate-y-8','opacity-0');
        deliPanel.classList.add('translate-y-0','opacity-100');
    });
}
function closeDeliModal() {
    deliPanel.classList.remove('translate-y-0','opacity-100');
    deliPanel.classList.add('translate-y-8','opacity-0');
    setTimeout(() => { deliOverlay.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }, 250);
}
function closeDeliIfBackdrop(e) { if (e.target === deliOverlay) closeDeliModal(); }

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(); closeDeliModal(); }
});

// Charger les élèves de la classe source
function deliChargerEleves() {
    const classeId = document.getElementById('deli-source-class').value;
    const section  = document.getElementById('deli-eleves-section');
    const list     = document.getElementById('deli-eleves-list');
    const loader   = document.getElementById('deli-eleves-loader');
    const count    = document.getElementById('deli-eleves-count');

    if (!classeId) { section.classList.add('hidden'); return; }

    section.classList.remove('hidden');
    list.classList.add('hidden');
    loader.classList.remove('hidden');

    fetch(URL_ELEVES.replace(':id', classeId), { headers: {'X-Requested-With':'XMLHttpRequest'} })
        .then(r => r.json())
        .then(eleves => {
            loader.classList.add('hidden');
            list.classList.remove('hidden');
            if (!eleves.length) {
                list.innerHTML = '<p class="px-4 py-3 text-xs text-slate-400">Aucun élève validé dans cette classe.</p>';
                count.textContent = '';
                return;
            }
            count.textContent = eleves.length + ' élève(s)';
            list.innerHTML = eleves.map((e, i) => `
                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-white cursor-pointer transition-colors">
                    <input type="checkbox" class="deli-student-cb rounded border-slate-300 text-amber-500 focus:ring-amber-400" value="${e.id}">
                    <span class="text-xs font-medium text-slate-700">${i+1}. ${e.last_name} ${e.first_name}</span>
                    <span class="ml-auto text-xs text-slate-400">${e.num_educ || ''}</span>
                </label>
            `).join('');
        })
        .catch(() => {
            loader.classList.add('hidden');
            list.innerHTML = '<p class="px-4 py-3 text-xs text-red-500">Erreur de chargement.</p>';
            list.classList.remove('hidden');
        });
}

// Tout sélectionner
function deliToutSelectionner() {
    const cbs = document.querySelectorAll('.deli-student-cb');
    const allChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
    document.querySelector('[onclick="deliToutSelectionner()"]').textContent = allChecked ? 'Tout sélectionner' : 'Tout désélectionner';
}

// Charger les classes de destination selon cycle + année
function deliChargerClassesDest() {
    const entityId = document.getElementById('deli-target-entity').value;
    const yearId   = document.getElementById('deli-target-year').value;
    const sel      = document.getElementById('deli-target-class');
    const loader   = document.getElementById('deli-classes-loader');

    sel.innerHTML = '<option value="">— Chargement... —</option>';
    if (!entityId || !yearId) {
        sel.innerHTML = '<option value="">— Choisissez d\'abord le cycle et l\'année —</option>';
        return;
    }

    loader.classList.remove('hidden');
    fetch(`${URL_CLASSES}?entity_id=${entityId}&year_id=${yearId}`, { headers: {'X-Requested-With':'XMLHttpRequest'} })
        .then(r => r.json())
        .then(classes => {
            loader.classList.add('hidden');
            if (!classes.length) {
                sel.innerHTML = '<option value="">Aucune classe disponible</option>';
                return;
            }
            sel.innerHTML = '<option value="">— Sélectionnez la classe —</option>' +
                classes.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        })
        .catch(() => {
            loader.classList.add('hidden');
            sel.innerHTML = '<option value="">Erreur de chargement</option>';
        });
}

// Soumettre la délibération
function deliSoumettre() {
    const sourceClassId = document.getElementById('deli-source-class').value;
    const targetYearId  = document.getElementById('deli-target-year').value;
    const targetEntity  = document.getElementById('deli-target-entity').value;
    const targetClassId = document.getElementById('deli-target-class').value;
    const studentIds    = [...document.querySelectorAll('.deli-student-cb:checked')].map(cb => parseInt(cb.value));

    if (!sourceClassId)      { alert('Sélectionnez la classe source.'); return; }
    if (!targetYearId)       { alert("Sélectionnez l'année de destination."); return; }
    if (!studentIds.length)  { alert('Sélectionnez au moins un élève.'); return; }
    if (!targetEntity)       { alert('Sélectionnez le cycle de destination.'); return; }
    if (!targetClassId)      { alert('Sélectionnez la classe de destination.'); return; }

    const btn = document.getElementById('deli-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>En cours...';

    fetch(URL_DELI, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'X-Requested-With':'XMLHttpRequest' },
        body: JSON.stringify({
            source_class_id: sourceClassId,
            target_academic_year_id: targetYearId,
            target_entity_id: targetEntity,
            target_class_id: targetClassId,
            student_ids: studentIds,
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-graduation-cap mr-2"></i>Délibérer';
        if (data.success) {
            closeDeliModal();
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('Erreur : ' + (data.error || 'Une erreur est survenue.'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-graduation-cap mr-2"></i>Délibérer';
        alert('Erreur réseau. Veuillez réessayer.');
    });
}
</script>
@endsection
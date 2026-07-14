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

    {{-- ── Toast ── --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 sm:px-5 py-4 shadow-sm">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
    @endif

    {{-- ── En-tête ── --}}
    <div class="mb-6 sm:mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-purple-600 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-clipboard-list text-white text-base sm:text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Gestion des notes & évaluations</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Maternelle &amp; Primaire — {{ $annee_academique->name }}</p>
            </div>
        </div>

        <button onclick="openModal()"
                class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 active:scale-95">
            <i class="fas fa-calendar-plus"></i>
            Programmer une composition
        </button>
    </div>

    {{-- ── Compositions programmées ── --}}
    @if($compositions->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-calendar-check text-purple-500"></i>
            <h2 class="text-base font-bold text-gray-800">Compositions programmées</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left">Mois</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Classes concernées</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Composition (du → au)</th>
                        <th class="px-4 sm:px-6 py-3 text-left">Saisie des notes (du → au)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($compositions as $comp)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 sm:px-6 py-3 font-semibold text-purple-700 whitespace-nowrap">
                            {{ $moisNoms[$comp->mois] ?? 'Mois '.$comp->mois }}
                        </td>
                        <td class="px-4 sm:px-6 py-3">
                            @php
                                $classesComp = $classes->whereIn('id', $comp->classe_ids)->pluck('name')->join(', ');
                            @endphp
                            <span class="text-gray-700">{{ $classesComp ?: '—' }}</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-gray-700 whitespace-nowrap">
                            <span class="font-medium">{{ \Carbon\Carbon::parse($comp->composition_debut)->format('d/m/Y') }}</span>
                            <span class="text-gray-400 mx-1">→</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($comp->composition_fin)->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 sm:px-6 py-3 text-gray-700 whitespace-nowrap">
                            <span class="font-medium">{{ \Carbon\Carbon::parse($comp->saisie_debut)->format('d/m/Y') }}</span>
                            <span class="text-gray-400 mx-1">→</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($comp->saisie_fin)->format('d/m/Y') }}</span>
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
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-2 h-6 bg-blue-500 rounded-full inline-block"></span>
            Primaire
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($classesPrimaire as $classe)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-5 py-4 flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-school text-white text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-white font-bold text-base truncate">{{ $classe->name }}</p>
                        <p class="text-blue-100 text-xs">{{ $classe->students->count() ?? 0 }} élève(s)</p>
                    </div>
                </div>
                <div class="p-4 flex flex-col gap-2">
                    <a href="{{ route('primaire.notes.formative', $classe->id) }}"
                       class="flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-sm py-2.5 px-4 rounded-xl border border-emerald-200 transition-colors duration-150">
                        <i class="fas fa-pencil-alt text-xs"></i>
                        Évaluation formative
                    </a>
                    <a href="{{ route('primaire.notes.sommative', $classe->id) }}"
                       class="flex items-center justify-center gap-2 bg-orange-50 hover:bg-orange-100 text-orange-700 font-semibold text-sm py-2.5 px-4 rounded-xl border border-orange-200 transition-colors duration-150">
                        <i class="fas fa-file-alt text-xs"></i>
                        Évaluation sommative
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
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-2 h-6 bg-pink-500 rounded-full inline-block"></span>
            Maternelle
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($classesMaternelle as $classe)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-5 py-4 flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-child text-white text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-white font-bold text-base truncate">{{ $classe->name }}</p>
                        <p class="text-pink-100 text-xs">{{ $classe->students->count() ?? 0 }} élève(s)</p>
                    </div>
                </div>
                <div class="p-4 flex flex-col gap-2">
                    <a href="{{ route('primaire.notes.formative', $classe->id) }}"
                       class="flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-sm py-2.5 px-4 rounded-xl border border-emerald-200 transition-colors duration-150">
                        <i class="fas fa-pencil-alt text-xs"></i>
                        Évaluation formative
                    </a>
                    <a href="{{ route('primaire.notes.sommative', $classe->id) }}"
                       class="flex items-center justify-center gap-2 bg-orange-50 hover:bg-orange-100 text-orange-700 font-semibold text-sm py-2.5 px-4 rounded-xl border border-orange-200 transition-colors duration-150">
                        <i class="fas fa-file-alt text-xs"></i>
                        Évaluation sommative
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($classes->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 sm:py-24 text-center">
        <div class="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-school text-purple-300 text-3xl"></i>
        </div>
        <p class="text-gray-500">Aucune classe primaire ou maternelle pour cette année académique.</p>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════
     MODAL — Programmer une composition
     Pattern : header fixe / corps scrollable / footer fixe
══════════════════════════════════════════════ --}}
<div id="composition-overlay"
     class="fixed inset-0 z-[8000] hidden"
     onclick="closeModalIfBackdrop(event)">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

    {{-- Conteneur de centrage : hauteur pleine + scroll de secours --}}
    <div class="relative flex items-start sm:items-center justify-center h-full w-full px-0 sm:px-4 py-0 sm:py-8 overflow-y-auto">
        <div id="composition-panel"
             class="relative bg-white w-full sm:max-w-xl sm:rounded-2xl shadow-2xl border border-gray-100
                    flex flex-col
                    h-full sm:h-auto sm:max-h-[90vh]
                    transform transition-all duration-300 translate-y-8 opacity-0">

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
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-calendar-check text-purple-500 mr-1"></i>
                            Période de composition <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="composition_debut" class="text-xs text-gray-500 mb-1 block">Du</label>
                                <input type="date" id="composition_debut" name="composition_debut"
                                       value="{{ old('composition_debut') }}" required
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                                @error('composition_debut')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="composition_fin" class="text-xs text-gray-500 mb-1 block">Au</label>
                                <input type="date" id="composition_fin" name="composition_fin"
                                       value="{{ old('composition_fin') }}" required
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                                @error('composition_fin')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Champ 4 : Période de saisie — dates --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-edit text-purple-500 mr-1"></i>
                            Période de saisie des notes <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="saisie_debut" class="text-xs text-gray-500 mb-1 block">Du</label>
                                <input type="date" id="saisie_debut" name="saisie_debut"
                                       value="{{ old('saisie_debut') }}" required
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                                @error('saisie_debut')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="saisie_fin" class="text-xs text-gray-500 mb-1 block">Au</label>
                                <input type="date" id="saisie_fin" name="saisie_fin"
                                       value="{{ old('saisie_fin') }}" required
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                                @error('saisie_fin')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Boutons (fixe, toujours visible) --}}
                <div class="shrink-0 flex gap-3 px-4 sm:px-6 py-4 border-t border-gray-100 bg-white sm:rounded-b-2xl"
                     style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                    <button type="button" onclick="closeModal()"
                            class="flex-1 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl py-3 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm py-3 rounded-xl shadow-md transition-all duration-200 active:scale-95">
                        <i class="fas fa-check text-xs"></i>
                        Programmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
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
    setTimeout(() => {
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }, 250);
}

function closeModalIfBackdrop(e) {
    if (e.target === overlay) closeModal();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// "Toutes les classes" checkbox
document.getElementById('select-all').addEventListener('change', function () {
    document.querySelectorAll('.classe-checkbox').forEach(cb => cb.checked = this.checked);
});

// ── Mise à jour des min/max des dates selon le mois sélectionné ──
// Mapping mois scolaire → mois calendaire réel
// L'année académique est ex: "2025-2026" → annee1=2025, annee2=2026
const ANNEE_NAME = '{{ $annee_academique->name }}';
const parts = ANNEE_NAME.split('-');
const annee1 = parseInt(parts[0]);
const annee2 = parseInt(parts[1]);

// Mois scolaires 1-4 (sept-déc) → annee1 ; 5-10 (jan-juin) → annee2
const moisCalendaire = {
    1: { m: 9,  a: annee1 },   // Septembre
    2: { m: 10, a: annee1 },   // Octobre
    3: { m: 11, a: annee1 },   // Novembre
    4: { m: 12, a: annee1 },   // Décembre
    5: { m: 1,  a: annee2 },   // Janvier
    6: { m: 2,  a: annee2 },   // Février
    7: { m: 3,  a: annee2 },   // Mars
    8: { m: 4,  a: annee2 },   // Avril
    9: { m: 5,  a: annee2 },   // Mai
   10: { m: 6,  a: annee2 },   // Juin
};

function lastDayOfMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

function pad(n) { return String(n).padStart(2, '0'); }

function updateDateConstraints() {
    const mois = parseInt(document.getElementById('mois').value);
    if (!mois || !moisCalendaire[mois]) return;

    const { m, a } = moisCalendaire[mois];
    const minDate  = `${a}-${pad(m)}-01`;
    const lastDay  = lastDayOfMonth(a, m);
    const maxDate  = `${a}-${pad(m)}-${pad(lastDay)}`;

    const fields = ['composition_debut', 'composition_fin', 'saisie_debut', 'saisie_fin'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.min = minDate;
            el.max = maxDate;
            // Réinitialiser si la valeur actuelle est hors plage
            if (el.value && (el.value < minDate || el.value > maxDate)) {
                el.value = '';
            }
        }
    });

    // Contrainte : fin >= début
    document.getElementById('composition_debut').addEventListener('change', function () {
        const fin = document.getElementById('composition_fin');
        if (fin.value && fin.value < this.value) fin.value = this.value;
        fin.min = this.value || minDate;
    });
    document.getElementById('saisie_debut').addEventListener('change', function () {
        const fin = document.getElementById('saisie_fin');
        if (fin.value && fin.value < this.value) fin.value = this.value;
        fin.min = this.value || minDate;
    });
}

document.getElementById('mois').addEventListener('change', updateDateConstraints);

// Appliquer au chargement si mois déjà sélectionné (retour après erreur)
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('mois').value) updateDateConstraints();
});

// Ouvrir automatiquement si erreurs de validation
@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openModal());
@endif
</script>
@endsection
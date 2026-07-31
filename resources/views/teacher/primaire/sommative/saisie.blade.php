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

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 sm:px-5 py-4 shadow-sm">
        @foreach($errors->all() as $err)
        <p class="text-sm">• {{ $err }}</p>
        @endforeach
    </div>
    @endif

    {{-- En-tête --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-orange-500 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-file-alt text-white text-base sm:text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">{{ $subject->name }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    {{ $classe->name }} — Composition de {{ $moisNoms[$composition->mois] ?? 'Mois '.$composition->mois }}
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @if($evaluation)
            <a href="{{ route('teacher.primaire.sommative.show', [$classe->id, $composition->id, $subject->id]) }}"
               class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                <i class="fas fa-eye text-xs"></i> Lire
            </a>
            @endif
            @if($composition->etape !== 'a_venir')
            <a href="{{ route('teacher.primaire.sommative.recap', [$classe->id, $composition->id]) }}"
               class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                <i class="fas fa-table text-xs"></i>
                <span class="hidden sm:inline">Récapitulatif toutes matières</span>
                <span class="sm:hidden">Récapitulatif</span>
            </a>
            @endif
            <a href="{{ route('teacher.classes.sommative', $classe->id) }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Retour
            </a>
        </div>
    </div>

    {{-- Bandeau d'état de la période de saisie --}}
    @if($composition->etape === 'saisie')
    <div class="mb-5 flex items-center gap-3 bg-orange-50 border border-orange-200 text-orange-800 rounded-xl px-4 py-3 text-sm">
        <i class="fas fa-unlock text-orange-500 flex-shrink-0"></i>
        <span>Saisie ouverte jusqu'au <strong>{{ \Carbon\Carbon::parse($composition->saisie_fin)->locale('fr')->isoFormat('D MMMM YYYY') }}</strong>.</span>
    </div>
    @else
    <div class="mb-5 flex items-center gap-3 bg-gray-100 border border-gray-200 text-gray-600 rounded-xl px-4 py-3 text-sm">
        <i class="fas fa-lock text-gray-400 flex-shrink-0"></i>
        @if($composition->etape === 'a_venir')
            <span>La période de saisie n'est pas encore ouverte pour cette composition (à partir du {{ \Carbon\Carbon::parse($composition->saisie_debut)->locale('fr')->isoFormat('D MMMM YYYY') }}).</span>
        @else
            <span>La période de saisie est clôturée depuis le {{ \Carbon\Carbon::parse($composition->saisie_fin)->locale('fr')->isoFormat('D MMMM YYYY') }} — les notes sont affichées en lecture seule.</span>
        @endif
    </div>
    @endif

    <form action="{{ route('teacher.primaire.sommative.store', [$classe->id, $composition->id, $subject->id]) }}" method="POST">
        @csrf

        {{-- Barème : à choisir uniquement à la toute première saisie, ensuite figé --}}
        @if(!$evaluation && $composition->etape === 'saisie')
        <div class="mb-5 bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-sliders-h text-orange-500 mr-1"></i>
                Note minimale <span class="text-red-500">*</span>
                <span class="block text-xs font-normal text-gray-400 mt-0.5">Choisie une seule fois pour cette matière — la note max sera le double.</span>
            </label>
            <div class="flex gap-3 max-w-sm">
                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 hover:bg-orange-50 hover:border-orange-300 transition-colors has-[:checked]:bg-orange-50 has-[:checked]:border-orange-400">
                    <input type="radio" name="note_min" value="5" {{ old('note_min','10') == '5' ? 'checked' : '' }}
                           class="text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-semibold text-gray-700">5/10</span>
                </label>
                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 hover:bg-orange-50 hover:border-orange-300 transition-colors has-[:checked]:bg-orange-50 has-[:checked]:border-orange-400">
                    <input type="radio" name="note_min" value="10" {{ old('note_min','10') == '10' ? 'checked' : '' }}
                           class="text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-semibold text-gray-700">10/20</span>
                </label>
            </div>
            @error('note_min')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        @endif

        @php
            $noteMaxAffiche = $evaluation ? (float) $evaluation->note_max : 20;
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fas fa-users text-orange-500"></i>
                    <h2 class="text-base font-bold text-gray-800">Notes des élèves</h2>
                </div>
                <span id="bareme-label" class="text-xs font-bold bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full whitespace-nowrap"
                      title="Les notes sont automatiquement ramenées sur /20 dans le récapitulatif pour permettre la comparaison entre matières.">
                    Barème : /{{ number_format($noteMaxAffiche, 0) }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[420px]">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold sticky top-0 z-10">
                        <tr>
                            <th class="px-3 sm:px-4 py-2.5 text-left w-8">#</th>
                            <th class="px-3 sm:px-4 py-2.5 text-left">Nom & Prénoms</th>
                            <th class="px-3 sm:px-4 py-2.5 text-center w-16">Sexe</th>
                            <th class="px-3 sm:px-4 py-2.5 text-center w-28">Note /20</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($classe->students as $i => $student)
                        @php $note = $notesParEleve->get($student->id)?->note; @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 sm:px-4 py-2 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-3 sm:px-4 py-2">
                                <span class="font-semibold text-gray-800">{{ strtoupper($student->last_name) }}</span>
                                <span class="text-gray-600"> {{ $student->first_name }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 text-center">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                    {{ $student->gender === 'M' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $student->gender }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 text-center">
                                @if($composition->etape === 'saisie')
                                <input type="number"
                                       name="notes[{{ $student->id }}]"
                                       id="note_{{ $student->id }}"
                                       value="{{ old('notes.'.$student->id, $note) }}"
                                       min="0" max="{{ $noteMaxAffiche }}" step="0.5" inputmode="decimal"
                                       placeholder="0–{{ number_format($noteMaxAffiche, 0) }}"
                                       class="note-input w-16 sm:w-20 text-center rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-colors">
                                @else
                                <span class="text-sm font-semibold {{ $note !== null ? 'text-gray-800' : 'text-gray-400 italic' }}">
                                    {{ $note !== null ? number_format($note, 1, ',', '') : '—' }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($composition->etape === 'saisie')
        <div class="mt-5 flex justify-end">
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm py-3 px-6 rounded-xl shadow-md transition-all duration-200 active:scale-95">
                <i class="fas fa-save text-xs"></i>
                Enregistrer les notes
            </button>
        </div>
        @endif
    </form>

</div>

@if(!$evaluation && $composition->etape === 'saisie')
<script>
// Met à jour le barème affiché et le max des champs de note selon la note minimale choisie
const radiosNoteMin = document.querySelectorAll('input[name="note_min"]');
const baremeLabel    = document.getElementById('bareme-label');
const noteInputs      = document.querySelectorAll('.note-input');

function updateBaremeSommatif() {
    const checked = document.querySelector('input[name="note_min"]:checked');
    const min = parseInt(checked ? checked.value : 10);
    const max = min * 2;
    baremeLabel.textContent = `Barème : /${max}`;
    noteInputs.forEach(inp => {
        inp.max = max;
        inp.placeholder = `0–${max}`;
        if (inp.value && parseFloat(inp.value) > max) inp.value = '';
    });
}

radiosNoteMin.forEach(r => r.addEventListener('change', updateBaremeSommatif));
updateBaremeSommatif(); // Init
</script>
@endif
@endsection
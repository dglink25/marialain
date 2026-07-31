@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-4 px-3 sm:py-6 sm:px-6 lg:px-8">

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-orange-50 border border-orange-200 text-orange-800 rounded-xl px-5 py-4 shadow-sm">
        <i class="fas fa-check-circle text-orange-500 flex-shrink-0"></i>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
    @endif
    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4">
        @foreach($errors->all() as $err)<p class="text-sm">• {{ $err }}</p>@endforeach
    </div>
    @endif

    {{-- En-tête --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center shadow-md shrink-0">
                <i class="fas fa-file-alt text-white"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Évaluations sommatives</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    <span class="font-semibold text-orange-700">{{ $classe->name }}</span> — {{ $annee->name }} — {{ $todayFr }}
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('teacher.classes.primaire') }}"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left text-xs"></i> Retour
            </a>
            @if($evaluations->isNotEmpty())
            <a href="{{ route('teacher.primaire.sommative.recap', $classe->id) }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition active:scale-95">
                <i class="fas fa-table"></i> Récapitulatif
            </a>
            @endif
            <button onclick="openModal()"
                    class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md transition active:scale-95">
                <i class="fas fa-plus"></i> Nouvelle évaluation
            </button>
        </div>
    </div>

    {{-- Liste --}}
    @if($evaluations->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-file-alt text-orange-300 text-2xl"></i>
        </div>
        <p class="text-gray-500 font-medium mb-3">Aucune évaluation sommative.</p>
        <button onclick="openModal()" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md transition">
            <i class="fas fa-plus"></i> Nouvelle évaluation
        </button>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fas fa-list text-orange-500"></i>
            <h2 class="font-bold text-gray-800">{{ $evaluations->count() }} évaluation(s)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
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
                            <a href="{{ route('teacher.primaire.sommative.show', [$classe->id, $eval->id]) }}"
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

{{-- Modal --}}
<div id="modal-overlay" class="fixed inset-0 z-[8000] hidden" onclick="closeModalIfBackdrop(event)">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="relative flex items-start sm:items-center justify-center h-full px-0 sm:px-4 py-0 sm:py-8 overflow-y-auto">
        <div id="modal-panel"
             class="relative bg-white w-full sm:max-w-2xl sm:rounded-2xl shadow-2xl flex flex-col h-full sm:h-auto sm:max-h-[90vh] transform transition-all duration-300 translate-y-8 opacity-0">

            {{-- Header --}}
            <div class="shrink-0 flex items-center justify-between px-5 pt-5 pb-4 border-b border-gray-100 bg-white sm:rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-file-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Nouvelle évaluation sommative</h2>
                        <p class="text-xs text-gray-400">{{ $classe->name }} — {{ $annee->name }}</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('teacher.primaire.sommative.store', $classe->id) }}" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                {{-- Corps scrollable --}}
                <div class="flex-1 min-h-0 overflow-y-auto px-5 py-5 space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Matière --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><i class="fas fa-book text-orange-500 mr-1"></i>Matière <span class="text-red-500">*</span></label>
                            @if($matieresAujourdhui->isEmpty())
                            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">Aucune matière au programme aujourd'hui.</p>
                            @else
                            <select name="subject_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                                <option value="">— Choisir —</option>
                                @foreach($matieresAujourdhui as $mat)
                                <option value="{{ $mat->id }}" {{ old('subject_id')==$mat->id?'selected':'' }}>{{ $mat->name }}</option>
                                @endforeach
                            </select>
                            @endif
                            @error('subject_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        {{-- Date --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><i class="fas fa-calendar text-orange-500 mr-1"></i>Date <span class="text-red-500">*</span></label>
                            <input type="date" name="date_evaluation" value="{{ old('date_evaluation', date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                            @error('date_evaluation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Titre --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><i class="fas fa-tag text-orange-500 mr-1"></i>Titre (optionnel)</label>
                            <input type="text" name="titre" value="{{ old('titre') }}" placeholder="Ex: Composition n°1"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                        </div>
                        {{-- Barème --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5"><i class="fas fa-sliders-h text-orange-500 mr-1"></i>Barème <span class="text-red-500">*</span></label>
                            <div class="flex gap-3">
                                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 hover:bg-orange-50 hover:border-orange-300 transition-colors has-[:checked]:bg-orange-50 has-[:checked]:border-orange-400">
                                    <input type="radio" name="note_min" value="5" {{ old('note_min','5')=='5'?'checked':'' }} class="text-orange-600 focus:ring-orange-500">
                                    <span class="text-sm font-semibold">5 → /10</span>
                                </label>
                                <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 hover:bg-orange-50 hover:border-orange-300 transition-colors has-[:checked]:bg-orange-50 has-[:checked]:border-orange-400">
                                    <input type="radio" name="note_min" value="10" {{ old('note_min')=='10'?'checked':'' }} class="text-orange-600 focus:ring-orange-500">
                                    <span class="text-sm font-semibold">10 → /20</span>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Note max = double de la note min. Varie par matière.</p>
                            @error('note_min')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Tableau élèves --}}
                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-users text-orange-500"></i> Notes des élèves
                                <span class="text-xs font-normal text-gray-400">(vide = absent)</span>
                            </h3>
                            <span id="bareme-label" class="text-xs font-bold bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full whitespace-nowrap">Barème : /10</span>
                        </div>
                        <div class="border border-gray-200 rounded-xl overflow-x-auto">
                            <table class="w-full text-sm min-w-[380px]">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2.5 text-left w-8">#</th>
                                        <th class="px-3 py-2.5 text-left">Nom & Prénoms</th>
                                        <th class="px-3 py-2.5 text-center w-14">Sexe</th>
                                        <th class="px-3 py-2.5 text-center w-24">Note</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($classe->students as $i => $student)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-3 py-2 text-gray-400 text-xs">{{ $i+1 }}</td>
                                        <td class="px-3 py-2">
                                            <span class="font-semibold text-gray-800">{{ strtoupper($student->last_name) }}</span>
                                            <span class="text-gray-600"> {{ $student->first_name }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $student->gender==='M'?'bg-blue-100 text-blue-700':'bg-pink-100 text-pink-700' }}">{{ $student->gender }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" name="notes[{{ $student->id }}]"
                                                   value="{{ old('notes.'.$student->id) }}"
                                                   min="0" step="0.5" inputmode="decimal" placeholder="—"
                                                   class="note-input w-16 text-center rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-colors">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer fixe --}}
                <div class="shrink-0 flex gap-3 px-5 py-4 border-t border-gray-100 bg-white sm:rounded-b-2xl">
                    <button type="button" onclick="closeModal()" class="flex-1 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl py-3 transition-colors">Annuler</button>
                    <button type="submit" class="flex-1 flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm py-3 rounded-xl shadow-md transition-all active:scale-95">
                        <i class="fas fa-save text-xs"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const overlay = document.getElementById('modal-overlay');
const panel   = document.getElementById('modal-panel');
function openModal() {
    overlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => { panel.classList.remove('translate-y-8','opacity-0'); panel.classList.add('translate-y-0','opacity-100'); });
}
function closeModal() {
    panel.classList.remove('translate-y-0','opacity-100'); panel.classList.add('translate-y-8','opacity-0');
    setTimeout(() => { overlay.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }, 250);
}
function closeModalIfBackdrop(e) { if(e.target===overlay) closeModal(); }
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

function updateBareme() {
    const min = parseInt(document.querySelector('input[name="note_min"]:checked')?.value || 5);
    const max = min * 2;
    document.getElementById('bareme-label').textContent = `Barème : /${max}`;
    document.querySelectorAll('.note-input').forEach(inp => {
        inp.max = max; inp.placeholder = `0–${max}`;
        if(inp.value && parseFloat(inp.value) > max) inp.value = '';
    });
}
document.querySelectorAll('input[name="note_min"]').forEach(r => r.addEventListener('change', updateBareme));
updateBareme();
@if($errors->any()) document.addEventListener('DOMContentLoaded', () => openModal()); @endif
</script>
@endsection

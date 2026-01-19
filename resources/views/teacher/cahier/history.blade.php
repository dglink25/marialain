@extends('layouts.app')

@section('content')

@php
    use Illuminate\Support\Str;
@endphp

<div class="container mx-auto px-4 py-8">

    <h1 class="text-3xl font-bold text-indigo-700 mb-6 text-center sm:text-left">Historique du Cahier de texte</h1>

    {{-- Header --}}
    <div class="bg-white/90 backdrop-blur-lg shadow-lg rounded-xl p-6 border border-gray-200 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="text-center lg:text-left">
                <h2 class="text-xl font-bold text-gray-800">Classe : <span class="text-indigo-600">{{ $class->name }}</span></h2>
                <p class="text-sm text-gray-600 mt-2">
                    Matière :
                    <span class="text-indigo-600 font-semibold">
                        {{ $subject->name ?? 'Non spécifiée' }}
                    </span>
                </p>
            </div>

            <div class="flex justify-center lg:justify-end">
                <button onclick="openModalForCreate()"
                    class="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white px-6 py-3 rounded-xl shadow-lg transition-all duration-300 font-semibold transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ajouter Cahier de texte
                </button>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl shadow-md p-4 mb-6 border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="w-full md:w-auto">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Filtrer les résultats</h3>
                <div class="flex flex-wrap gap-2">
                    <select id="filter-month" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">Tous les mois</option>
                        @for($i = 1; $i <= 12; $i++)
                            @php
                                $date = Carbon\Carbon::create(null, $i, 1);
                                $isCurrentMonth = $date->month == now()->month && $date->year == now()->year;
                            @endphp
                            <option value="{{ $i }}" {{ $isCurrentMonth ? 'selected' : '' }}>
                                {{ $date->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    
                    <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">Tous les statuts</option>
                        <option value="ongoing">En cours</option>
                        <option value="finished">Terminé</option>
                        <option value="planned">Planifié</option>
                    </select>
                    
                    <input type="date" id="filter-date" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600" id="entry-count">{{ $entries->count() }} enregistrements</span>
                <button onclick="resetFilters()" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Réinitialiser
                </button>
            </div>
        </div>
    </div>

    {{-- Modal global --}}
    <div id="cahier-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-auto p-6 relative animate-fadeInUp max-h-[90vh] overflow-y-auto">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full p-1 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h3 id="modal-title" class="text-2xl font-bold mb-6 text-gray-900 border-b pb-3"></h3>

            <form id="modal-form" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="entry_id" id="entry_id">
                <input type="hidden" name="class_id" value="{{ $class->id }}">
                <input type="hidden" name="teacher_id" value="{{ auth()->id() }}">
                <input type="hidden" name="subject_id" id="subject_id" value="{{ $subject->id }}">
                <input type="hidden" name="timetable_id" id="timetable_id" value="{{ $currentLesson->id ?? '' }}">
                <input type="hidden" name="day" id="day" value="{{ $currentLesson->day ?? now()->format('l') }}">

                {{-- Date et heure du cours --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Début du cours
                        </label>
                        <input type="datetime-local" name="course_start_date" id="course_start_date" 
                            class="w-full border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            max="{{ now()->format('Y-m-d\TH:i') }}"
                            required>
                        <p class="text-xs text-gray-500 mt-1">Ne peut pas être une date future</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Fin du cours
                        </label>
                        <input type="datetime-local" name="course_end_date" id="course_end_date" 
                            class="w-full border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            required>
                    </div>
                    <div class="md:col-span-2">
                        <div id="duration-display" class="text-sm text-gray-600 mt-2 p-2 bg-blue-50 rounded-lg hidden">
                            <div class="flex items-center justify-between">
                                <span>Durée : <span id="duration-text" class="font-semibold"></span></span>
                                <span id="duration-warning" class="text-red-600 font-medium hidden">⚠️ Maximum 5h</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Contenu du cours
                    </label>
                    <textarea name="content" id="content" rows="8" 
                        class="w-full border-2 border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"
                        placeholder="Rédigez le contenu du cours ici..." required></textarea>
                </div>

                {{-- Meta Info --}}
                <div id="modal-meta" class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg"></div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-between items-center pt-4 border-t">
                    <button type="button" onclick="closeModal()"
                        class="w-full sm:w-auto px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium">
                        Annuler
                    </button>
                    <button type="submit" id="submit-btn"
                        class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-8 py-2.5 rounded-xl shadow-lg transition-all duration-300 font-semibold transform hover:scale-105">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Entries --}}
    @if ($entries->isEmpty())
        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 text-yellow-800 p-8 rounded-2xl shadow text-center mt-8">
            <svg class="w-16 h-16 mx-auto mb-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h3 class="text-xl font-semibold mb-2">Aucun enregistrement trouvé</h3>
            <p class="text-yellow-600">Commencez par ajouter votre premier cahier de texte.</p>
        </div>
    @else
        {{-- Desktop Table --}}
        <div class="hidden lg:block bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-700">
                    <thead>
                        <tr class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-left">
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Date & Heure</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Durée</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Contenu</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">Statut</th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="entries-table-body">
                        @foreach ($entries as $entry)
                        @php
                            $startDate = \Carbon\Carbon::parse($entry->course_start_date);
                            $endDate = \Carbon\Carbon::parse($entry->course_end_date);
                            $duration = $entry->formatted_duration;
                            $isOngoing = $entry->isCourseOngoing();
                            $isFinished = $entry->isCourseFinished();
                            $canEdit = now()->diffInMonths($entry->created_at) < 1;
                        @endphp
                        <tr class="hover:bg-indigo-50 transition-colors duration-200 entry-row" 
                            data-month="{{ $startDate->month }}"
                            data-status="{{ $isOngoing ? 'ongoing' : ($isFinished ? 'finished' : 'planned') }}"
                            data-date="{{ $startDate->format('Y-m-d') }}">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 whitespace-nowrap">
                                        {{ $startDate->translatedFormat('l d F Y') }}
                                    </span>
                                    <span class="text-sm text-gray-600 whitespace-nowrap">
                                        {{ $startDate->format('H:i') }} - {{ $endDate->format('H:i') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap">
                                    {{ ltrim($duration, '-') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-md">
                                    <p class="text-gray-800 line-clamp-2 content-preview" data-full-content="{{ htmlspecialchars($entry->content) }}">
                                        {{ Str::limit($entry->content, 100) }}
                                    </p>
                                    @if(strlen($entry->content) > 100)
                                        <button onclick="openFullContentModal('{{ addslashes(htmlspecialchars($entry->content)) }}')" 
                                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mt-1 transition-colors see-more-btn">
                                            Voir plus
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($isOngoing)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 whitespace-nowrap">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        En cours
                                    </span>
                                @elseif($isFinished)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Terminé
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Planifié
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($canEdit)
                                    <button onclick='openModalForEdit(@json($entry))' 
                                        class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-yellow-700 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors text-xs font-medium whitespace-nowrap edit-btn">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Modifier
                                    </button>
                                @endif
                                <div class="text-xs text-gray-500 mt-2 whitespace-nowrap">
                                    Créé : {{ $entry->created_at->diffForHumans() }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Cards --}}
        <div class="lg:hidden space-y-4" id="entries-cards-container">
            @foreach ($entries as $entry)
            @php
                $startDate = \Carbon\Carbon::parse($entry->course_start_date);
                $endDate = \Carbon\Carbon::parse($entry->course_end_date);
                $duration = $entry->formatted_duration;
                $isOngoing = $entry->isCourseOngoing();
                $isFinished = $entry->isCourseFinished();
                $canEdit = now()->diffInMonths($entry->created_at) < 1;
            @endphp
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5 hover:shadow-xl transition-shadow duration-300 entry-card"
                data-month="{{ $startDate->month }}"
                data-status="{{ $isOngoing ? 'ongoing' : ($isFinished ? 'finished' : 'planned') }}"
                data-date="{{ $startDate->format('Y-m-d') }}">
                {{-- Header --}}
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $startDate->format('d/m/Y') }}</h3>
                        <p class="text-sm text-gray-600">
                            {{ $startDate->format('H:i') }} - {{ $endDate->format('H:i') }}
                        </p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                        {{ $duration }}
                    </span>
                </div>

                {{-- Content --}}
                <div class="mb-3">
                    <p class="text-gray-800 text-sm line-clamp-3 content-preview" data-full-content="{{ htmlspecialchars($entry->content) }}">
                        {{ $entry->content }}
                    </p>
                    @if(strlen($entry->content) > 150)
                        <button onclick="openFullContentModal('{{ addslashes(htmlspecialchars($entry->content)) }}')" 
                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mt-1 transition-colors see-more-btn">
                            Voir plus
                        </button>
                    @endif
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between mb-3">
                    @if($isOngoing)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            En cours
                        </span>
                    @elseif($isFinished)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Terminé
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Planifié
                        </span>
                    @endif
                    
                    @if($canEdit)
                        <button onclick='openModalForEdit(@json($entry))' 
                            class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-yellow-700 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors text-xs font-medium edit-btn">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </button>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                    Créé : {{ $entry->created_at->diffForHumans() }}
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Pagination ou message vide après filtrage --}}
    <div id="no-results" class="hidden bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 text-gray-800 p-8 rounded-2xl shadow text-center mt-8">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="text-xl font-semibold mb-2">Aucun résultat trouvé</h3>
        <p class="text-gray-600">Aucun enregistrement ne correspond à vos critères de filtrage.</p>
        <button onclick="resetFilters()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            Réinitialiser les filtres
        </button>
    </div>

</div>

{{-- Full content modal --}}
<div id="full-content-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden animate-fadeInUp">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Contenu complet du cours</h3>
            <button onclick="closeFullContentModal()" class="text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full p-1 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <div id="full-content-body" class="text-gray-800 whitespace-pre-wrap text-sm leading-relaxed"></div>
        </div>
        <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
            <button onclick="closeFullContentModal()" 
                class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors font-medium">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
// Variables globales
let allEntries = @json($entries);
let filteredEntries = [...allEntries];

// Fonction pour calculer et afficher la durée
function calculateDuration() {
    const startInput = document.getElementById('course_start_date');
    const endInput = document.getElementById('course_end_date');
    const durationDisplay = document.getElementById('duration-display');
    const durationText = document.getElementById('duration-text');
    const durationWarning = document.getElementById('duration-warning');
    
    if (startInput.value && endInput.value) {
        const startDate = new Date(startInput.value);
        const endDate = new Date(endInput.value);
        
        if (endDate <= startDate) {
            durationText.textContent = 'La fin doit être après le début';
            durationDisplay.classList.remove('hidden');
            durationDisplay.classList.remove('bg-blue-50');
            durationDisplay.classList.add('bg-red-50');
            durationText.classList.add('text-red-600');
            durationWarning.classList.remove('hidden');
            return false;
        }
        
        const diffMs = endDate - startDate;
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        
        durationText.textContent = `${diffHours}h${diffMinutes.toString().padStart(2, '0')}`;
        durationDisplay.classList.remove('hidden');
        durationDisplay.classList.remove('bg-red-50');
        durationDisplay.classList.add('bg-blue-50');
        durationText.classList.remove('text-red-600');
        
        // Vérifier si la durée dépasse 5 heures
        if (diffHours > 5 || (diffHours === 5 && diffMinutes > 0)) {
            durationWarning.classList.remove('hidden');
            durationDisplay.classList.remove('bg-blue-50');
            durationDisplay.classList.add('bg-red-50');
            durationText.classList.add('text-red-600');
            return false;
        } else {
            durationWarning.classList.add('hidden');
        }
        
        return true;
    }
    
    durationDisplay.classList.add('hidden');
    return true;
}

// Vérifier que la date de début n'est pas future
function validateStartDate() {
    const startInput = document.getElementById('course_start_date');
    const now = new Date();
    const selectedDate = new Date(startInput.value);
    
    if (selectedDate > now) {
        alert('La date de début ne peut pas être une date future.');
        startInput.value = now.toISOString().slice(0, 16);
        calculateDuration();
        return false;
    }
    return true;
}

// Form validation complète
function validateForm() {
    const startDate = document.getElementById('course_start_date').value;
    const endDate = document.getElementById('course_end_date').value;
    const content = document.getElementById('content').value.trim();
    
    if (!startDate) {
        alert('Veuillez saisir la date et heure de début du cours.');
        return false;
    }
    
    if (!endDate) {
        alert('Veuillez saisir la date et heure de fin du cours.');
        return false;
    }
    
    if (!content) {
        alert('Veuillez saisir le contenu du cours.');
        return false;
    }
    
    if (!validateStartDate()) {
        return false;
    }
    
    return calculateDuration();
}

// Modal functions
function openModalForCreate() {
    document.getElementById('modal-title').innerText = 'Ajouter un Cahier de Texte';
    document.getElementById('modal-form').action = "{{ route('teacher.cahier.store') }}";
    document.getElementById('entry_id').value = '';
    document.getElementById('content').value = '';
    
    // Set default dates (now and 1 hour later)
    const now = new Date();
    const endTime = new Date(now.getTime() + 60 * 60 * 1000); // 1 hour later
    
    document.getElementById('course_start_date').value = now.toISOString().slice(0, 16);
    document.getElementById('course_end_date').value = endTime.toISOString().slice(0, 16);
    
    document.getElementById('modal-meta').innerText = '';
    
    // Reset form validation
    document.getElementById('modal-form').onsubmit = function() {
        return validateForm();
    };
    
    // Calculate initial duration
    setTimeout(calculateDuration, 100);
    
    document.getElementById('cahier-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openModalForEdit(entry) {
    document.getElementById('modal-title').innerText = 'Modifier le Cahier de Texte';
    document.getElementById('modal-form').action = "{{ url('/teacher/cahier/update') }}/" + entry.id;
    document.getElementById('entry_id').value = entry.id;
    document.getElementById('content').value = entry.content ?? '';
    
    // Set dates from entry
    if (entry.course_start_date) {
        const startDate = new Date(entry.course_start_date);
        document.getElementById('course_start_date').value = startDate.toISOString().slice(0, 16);
    }
    
    if (entry.course_end_date) {
        const endDate = new Date(entry.course_end_date);
        document.getElementById('course_end_date').value = endDate.toISOString().slice(0, 16);
    }
    
    document.getElementById('modal-meta').innerText = "Créé : " + new Date(entry.created_at).toLocaleString() + " • Dernière modif : " + new Date(entry.updated_at).toLocaleString();
    
    // Set form validation
    document.getElementById('modal-form').onsubmit = function() {
        return validateForm();
    };
    
    // Calculate duration
    setTimeout(calculateDuration, 100);
    
    document.getElementById('cahier-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Fonction corrigée pour ouvrir le modal de contenu complet
function openFullContentModal(content) {
    console.log('Opening full content modal with content:', content.substring(0, 50) + '...');
    const contentBody = document.getElementById('full-content-body');
    
    // Décoder le contenu HTML
    const decodedContent = decodeURIComponent(content)
        .replace(/\\'/g, "'")
        .replace(/\\"/g, '"')
        .replace(/\\n/g, '\n')
        .replace(/\\r/g, '\r')
        .replace(/\\t/g, '\t');
    
    contentBody.textContent = decodedContent;
    document.getElementById('full-content-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFullContentModal() {
    document.getElementById('full-content-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function closeModal() {
    document.getElementById('cahier-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fonctions de filtrage
function applyFilters() {
    const monthFilter = document.getElementById('filter-month').value;
    const statusFilter = document.getElementById('filter-status').value;
    const dateFilter = document.getElementById('filter-date').value;
    
    const rows = document.querySelectorAll('.entry-row, .entry-card');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const month = row.getAttribute('data-month');
        const status = row.getAttribute('data-status');
        const date = row.getAttribute('data-date');
        
        let show = true;
        
        // Filtre par mois
        if (monthFilter !== 'all' && month !== monthFilter) {
            show = false;
        }
        
        // Filtre par statut
        if (statusFilter !== 'all' && status !== statusFilter) {
            show = false;
        }
        
        // Filtre par date
        if (dateFilter && date !== dateFilter) {
            show = false;
        }
        
        if (show) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Mettre à jour le compteur
    document.getElementById('entry-count').textContent = `${visibleCount} enregistrements`;
    
    // Afficher/masquer le message "aucun résultat"
    const noResults = document.getElementById('no-results');
    if (visibleCount === 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
}

function resetFilters() {
    document.getElementById('filter-month').value = 'all';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-date').value = '';
    applyFilters();
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Écouteurs pour les filtres
    document.getElementById('filter-month').addEventListener('change', applyFilters);
    document.getElementById('filter-status').addEventListener('change', applyFilters);
    document.getElementById('filter-date').addEventListener('change', applyFilters);
    
    // Écouteurs pour les dates
    const startDateInput = document.getElementById('course_start_date');
    const endDateInput = document.getElementById('course_end_date');
    
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            validateStartDate();
            calculateDuration();
            
            // Ajuster la date de fin si elle est avant la date de début
            const startDate = new Date(this.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate <= startDate) {
                const newEndDate = new Date(startDate.getTime() + 60 * 60 * 1000); // +1 heure
                endDateInput.value = newEndDate.toISOString().slice(0, 16);
                calculateDuration();
            }
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', calculateDuration);
    }
    
    // Fermer les modales en cliquant à l'extérieur
    document.addEventListener('click', function(event) {
        const cahierModal = document.getElementById('cahier-modal');
        const fullContentModal = document.getElementById('full-content-modal');
        
        if (event.target === cahierModal) {
            closeModal();
        }
        if (event.target === fullContentModal) {
            closeFullContentModal();
        }
    });

    // Fermer les modales avec la touche Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
            closeFullContentModal();
        }
    });

    // Gestionnaire de soumission du formulaire
    const form = document.getElementById('modal-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Corriger les boutons "Voir plus" existants
    document.querySelectorAll('.see-more-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const content = this.previousElementSibling.getAttribute('data-full-content');
            openFullContentModal(content);
        });
    });
    
    // Initialiser le compteur
    applyFilters();
});

// Fonction pour formater les dates en français
function formatDateFr(dateString) {
    const date = new Date(dateString);
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('fr-FR', options);
}

// Fonction pour détecter si un cours peut être modifié (moins d'un mois)
function canEditEntry(createdAt) {
    const createdDate = new Date(createdAt);
    const now = new Date();
    const diffMonths = (now.getFullYear() - createdDate.getFullYear()) * 12 + (now.getMonth() - createdDate.getMonth());
    return diffMonths < 1;
}
</script>

<style>
.animate-fadeInUp {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions */
.modal-content {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.modal-content::-webkit-scrollbar {
    width: 6px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .grid-cols-1 {
        grid-template-columns: 1fr;
    }
    
    #cahier-modal {
        padding: 1rem;
    }
    
    .flex-wrap {
        flex-wrap: wrap;
    }
    
    .text-3xl {
        font-size: 1.75rem;
    }
    
    .text-2xl {
        font-size: 1.5rem;
    }
    
    .text-xl {
        font-size: 1.25rem;
    }
}

@media (max-width: 768px) {
    .hidden.lg\:block {
        display: none !important;
    }
    
    .lg\:hidden {
        display: block;
    }
}

/* Animation pour les cartes */
.entry-card {
    transition: all 0.3s ease;
}

.entry-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Style pour les badges de statut */
.bg-yellow-100 { background-color: rgba(254, 243, 199, 0.8); }
.bg-green-100 { background-color: rgba(209, 250, 229, 0.8); }
.bg-blue-100 { background-color: rgba(219, 234, 254, 0.8); }

/* Style pour les boutons de filtre */
select, input[type="date"] {
    min-width: 150px;
}

/* Animation pour l'affichage/ masquage des éléments */
.entry-row, .entry-card {
    transition: opacity 0.3s ease;
}

.entry-row[style*="display: none"], .entry-card[style*="display: none"] {
    opacity: 0;
    height: 0;
    overflow: hidden;
    margin: 0;
    padding: 0;
    border: 0;
}

/* Style pour le texte dans le modal de contenu complet */
#full-content-body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.8;
    font-size: 0.95rem;
}

#full-content-body p {
    margin-bottom: 1rem;
}

/* Style pour les icônes dans les boutons */
button svg {
    flex-shrink: 0;
}

/* Amélioration de l'accessibilité */
button:focus, input:focus, select:focus, textarea:focus {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
}

/* Style pour le message "aucun résultat" */
#no-results {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
@endsection
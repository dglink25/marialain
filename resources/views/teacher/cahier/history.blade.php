@extends('layouts.app')

@section('content')

@php
    use Illuminate\Support\Str;

    // Données préparées côté PHP (évite les problèmes d'apostrophes / fuseau horaire)
    $entriesJs = $entries->map(fn($e) => [
        'id'         => $e->id,
        'content'    => $e->content,
        'start'      => \Carbon\Carbon::parse($e->course_start_date)->format('Y-m-d\TH:i'),
        'end'        => \Carbon\Carbon::parse($e->course_end_date)->format('Y-m-d\TH:i'),
        'created_at' => $e->created_at->format('d/m/Y H:i'),
        'updated_at' => $e->updated_at->format('d/m/Y H:i'),
    ])->values();

    $lessonsJs = collect($todayLessons ?? [])->map(fn($l) => [
        'id'    => $l->id,
        'day'   => $l->day,
        'start' => substr($l->start_time, 0, 5),
        'end'   => substr($l->end_time, 0, 5),
    ])->values();

    $currentLessonJs = !empty($currentLesson) ? [
        'id'    => $currentLesson->id,
        'day'   => $currentLesson->day,
        'start' => substr($currentLesson->start_time, 0, 5),
        'end'   => substr($currentLesson->end_time, 0, 5),
    ] : null;
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

    {{-- Messages flash et erreurs --}}
    @if (session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

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
                {{-- Activé uniquement en modification (route PUT/PATCH). Laissez désactivé si votre route update est un POST --}}
                <input type="hidden" name="_method" id="form_method" value="POST" disabled>
                <input type="hidden" name="idempotency_key" id="idempotency_key">
                <input type="hidden" name="entry_id" id="entry_id">
                <input type="hidden" name="class_id" value="{{ $class->id }}">
                <input type="hidden" name="teacher_id" value="{{ auth()->id() }}">
                <input type="hidden" name="subject_id" id="subject_id" value="{{ $subject->id }}">
                <input type="hidden" name="timetable_id" id="timetable_id" value="{{ $anyLesson->id ?? '' }}">
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
                            required>
                        <p class="text-xs text-gray-500 mt-1">Date du jour + heure de l'emploi du temps</p>
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
                                <span id="duration-warning" class="text-red-600 font-medium hidden">Maximum 8h</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="duplicate-error" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3 font-medium"></div>

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
                        class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-8 py-2.5 rounded-xl shadow-lg transition-all duration-300 font-semibold flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100">
                        <svg id="submit-spinner" class="hidden animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span id="submit-label">Enregistrer</span>
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
                            $canEdit = $entry->created_at->gt(now()->subMonth());
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
                                    <p class="text-gray-800 line-clamp-2 content-preview">
                                        {{ Str::limit($entry->content, 100) }}
                                    </p>
                                    @if(strlen($entry->content) > 100)
                                        <button type="button" onclick="openFullContentModal({{ $entry->id }})"
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
                                    <button type="button" onclick="openModalForEdit({{ $entry->id }})"
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
                $canEdit = $entry->created_at->gt(now()->subMonth());
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
                        {{ ltrim($duration, '-') }}
                    </span>
                </div>

                {{-- Content --}}
                <div class="mb-3">
                    <p class="text-gray-800 text-sm line-clamp-3 content-preview">
                        {{ $entry->content }}
                    </p>
                    @if(strlen($entry->content) > 150)
                        <button type="button" onclick="openFullContentModal({{ $entry->id }})"
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
                        <button type="button" onclick="openModalForEdit({{ $entry->id }})"
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

    {{-- Message vide après filtrage --}}
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
const CHECK_URL = "{{ route('teacher.cahier.check-duplicate') }}";
const STORE_URL = "{{ route('teacher.cahier.store') }}";
const UPDATE_BASE_URL = "{{ url('/teacher/cahier/update') }}";
const CLASS_ID = {{ $class->id }};
const SUBJECT_ID = {{ $subject->id }};

const ENTRIES = @json($entriesJs);
const TODAY_LESSONS = @json($lessonsJs);
const CURRENT_LESSON = @json($currentLessonJs);
const MAX_HOURS = 8;

let isSubmitting = false;      // verrou anti double soumission
let currentEditId = null;      // id du cahier en cours de modification
let duplicateTimer = null;

// ---------- Utilitaires ----------
function toLocalInput(date) {
    const pad = n => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function generateUuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
        const r = Math.random() * 16 | 0;
        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
}

function setSubmitting(state) {
    isSubmitting = state;
    const btn = document.getElementById('submit-btn');
    btn.disabled = state;
    document.getElementById('submit-spinner').classList.toggle('hidden', !state);
    document.getElementById('submit-label').textContent = state ? 'Enregistrement...' : 'Enregistrer';
}

function showDuplicateError(message) {
    const box = document.getElementById('duplicate-error');
    box.textContent = message;
    box.classList.remove('hidden');
}

function hideDuplicateError() {
    document.getElementById('duplicate-error').classList.add('hidden');
}

// ---------- Vérification de doublon (AJAX) ----------
async function checkDuplicate() {
    const start = document.getElementById('course_start_date').value;
    const end = document.getElementById('course_end_date').value;
    if (!start || !end) return { duplicate: false };

    const params = new URLSearchParams({
        class_id: CLASS_ID,
        subject_id: SUBJECT_ID,
        course_start_date: start,
        course_end_date: end,
    });
    if (currentEditId) params.append('ignore_id', currentEditId);

    try {
        const res = await fetch(`${CHECK_URL}?${params.toString()}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) return { duplicate: false }; // le serveur revérifiera de toute façon
        return await res.json();
    } catch (e) {
        return { duplicate: false };
    }
}

function scheduleDuplicateCheck() {
    clearTimeout(duplicateTimer);
    duplicateTimer = setTimeout(async () => {
        const result = await checkDuplicate();
        result.duplicate ? showDuplicateError(result.message) : hideDuplicateError();
    }, 400);
}

// ---------- Durée ----------
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
            durationDisplay.classList.remove('hidden', 'bg-blue-50');
            durationDisplay.classList.add('bg-red-50');
            durationText.classList.add('text-red-600');
            durationWarning.classList.add('hidden');
            return false;
        }

        const diffMs = endDate - startDate;
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

        durationText.textContent = `${diffHours}h${diffMinutes.toString().padStart(2, '0')}`;
        durationDisplay.classList.remove('hidden', 'bg-red-50');
        durationDisplay.classList.add('bg-blue-50');
        durationText.classList.remove('text-red-600');

        if (diffHours > MAX_HOURS || (diffHours === MAX_HOURS && diffMinutes > 0)) {
            durationWarning.classList.remove('hidden');
            durationDisplay.classList.remove('bg-blue-50');
            durationDisplay.classList.add('bg-red-50');
            durationText.classList.add('text-red-600');
            return false;
        }
        durationWarning.classList.add('hidden');
        return true;
    }

    durationDisplay.classList.add('hidden');
    return true;
}

// Refuse uniquement un JOUR futur (l'heure d'un cours prévu plus tard aujourd'hui est acceptée)
function validateStartDate() {
    const startInput = document.getElementById('course_start_date');
    const today = toLocalInput(new Date()).slice(0, 10);
    if (startInput.value && startInput.value.slice(0, 10) > today) {
        alert('La date de début ne peut pas être une date future.');
        startInput.value = today + startInput.value.slice(10);
        calculateDuration();
        return false;
    }
    return true;
}

function validateForm() {
    const startDate = document.getElementById('course_start_date').value;
    const endDate = document.getElementById('course_end_date').value;
    const content = document.getElementById('content').value.trim();

    if (!startDate) { alert('Veuillez saisir la date et heure de début du cours.'); return false; }
    if (!endDate) { alert('Veuillez saisir la date et heure de fin du cours.'); return false; }
    if (!content) { alert('Veuillez saisir le contenu du cours.'); return false; }
    if (!validateStartDate()) return false;
    return calculateDuration();
}

// ---------- Modales ----------
function openModalForCreate() {
    currentEditId = null;
    setSubmitting(false);
    hideDuplicateError();

    document.getElementById('form_method').disabled = true;   // POST normal
    document.getElementById('modal-title').innerText = 'Ajouter un Cahier de Texte';
    document.getElementById('modal-form').action = STORE_URL;
    document.getElementById('entry_id').value = '';
    document.getElementById('content').value = '';
    document.getElementById('idempotency_key').value = generateUuid(); // nouvelle clé à CHAQUE ouverture
    document.getElementById('modal-meta').innerText = '';

    // Date du jour + horaires du créneau d'aujourd'hui (matière / classe / année active)
    const now = new Date();
    const today = toLocalInput(now).slice(0, 10);
    let startVal, endVal;

    if (CURRENT_LESSON) {
        startVal = `${today}T${CURRENT_LESSON.start}`;
        endVal   = `${today}T${CURRENT_LESSON.end}`;
        document.getElementById('timetable_id').value = CURRENT_LESSON.id;
        document.getElementById('day').value = CURRENT_LESSON.day;
    } else {
        // Aucun cours aujourd'hui : heure actuelle + 1h
        startVal = toLocalInput(now);
        endVal   = toLocalInput(new Date(now.getTime() + 3600000));
    }

    document.getElementById('course_start_date').value = startVal;
    document.getElementById('course_end_date').value = endVal;

    setTimeout(() => { calculateDuration(); scheduleDuplicateCheck(); }, 100);

    document.getElementById('cahier-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openModalForEdit(id) {
    const entry = ENTRIES.find(e => e.id === id);
    if (!entry) return;

    currentEditId = entry.id;
    setSubmitting(false);
    hideDuplicateError();

    document.getElementById('form_method').disabled = false;  // PUT
    document.getElementById('modal-title').innerText = 'Modifier le Cahier de Texte';
    document.getElementById('modal-form').action = UPDATE_BASE_URL + '/' + entry.id;
    document.getElementById('entry_id').value = entry.id;
    document.getElementById('idempotency_key').value = generateUuid(); // ignoré par update
    document.getElementById('content').value = entry.content ?? '';
    document.getElementById('course_start_date').value = entry.start;
    document.getElementById('course_end_date').value = entry.end;
    document.getElementById('modal-meta').innerText =
        'Créé : ' + entry.created_at + ' • Dernière modif : ' + entry.updated_at;

    setTimeout(() => { calculateDuration(); scheduleDuplicateCheck(); }, 100);

    document.getElementById('cahier-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openFullContentModal(id) {
    const entry = ENTRIES.find(e => e.id === id);
    document.getElementById('full-content-body').textContent = entry ? entry.content : '';
    document.getElementById('full-content-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFullContentModal() {
    document.getElementById('full-content-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function closeModal() {
    if (isSubmitting) return; // on ne ferme pas pendant l'envoi
    document.getElementById('cahier-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ---------- Filtres ----------
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
        if (monthFilter !== 'all' && month !== monthFilter) show = false;
        if (statusFilter !== 'all' && status !== statusFilter) show = false;
        if (dateFilter && date !== dateFilter) show = false;

        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    // Chaque entrée existe en double (tableau + cartes) : on ne compte qu'une version
    const perView = document.querySelectorAll('.entry-row').length > 0
        ? Array.from(document.querySelectorAll('.entry-row')).filter(r => r.style.display !== 'none').length
        : visibleCount;

    document.getElementById('entry-count').textContent = `${perView} enregistrements`;
    document.getElementById('no-results').classList.toggle('hidden', perView !== 0 || ENTRIES.length === 0);
}

function resetFilters() {
    document.getElementById('filter-month').value = 'all';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-date').value = '';
    applyFilters();
}

// ---------- Initialisation ----------
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('filter-month').addEventListener('change', applyFilters);
    document.getElementById('filter-status').addEventListener('change', applyFilters);
    document.getElementById('filter-date').addEventListener('change', applyFilters);

    const startDateInput = document.getElementById('course_start_date');
    const endDateInput = document.getElementById('course_end_date');

    startDateInput.addEventListener('change', function () {
        validateStartDate();

        const startDate = new Date(this.value);
        const endDate = new Date(endDateInput.value);
        if (isNaN(endDate) || endDate <= startDate) {
            endDateInput.value = toLocalInput(new Date(startDate.getTime() + 60 * 60 * 1000));
        }
        calculateDuration();
        scheduleDuplicateCheck();
    });

    endDateInput.addEventListener('change', function () {
        calculateDuration();
        scheduleDuplicateCheck();
    });

    document.addEventListener('click', function (event) {
        if (event.target === document.getElementById('cahier-modal')) closeModal();
        if (event.target === document.getElementById('full-content-modal')) closeFullContentModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal();
            closeFullContentModal();
        }
    });

    // ===== SOUMISSION SÉCURISÉE (un seul handler) =====
    const form = document.getElementById('modal-form');
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (isSubmitting) return; // anti double-clic / double Entrée
        if (!validateForm()) return;

        setSubmitting(true);

        // Pré-vérification du doublon AVANT d'envoyer
        const result = await checkDuplicate();
        if (result.duplicate) {
            showDuplicateError(result.message);
            setSubmitting(false);
            return;
        }

        hideDuplicateError();
        // form.submit() ne redéclenche pas l'événement "submit" : pas de boucle
        form.submit();
    });

    // Retour arrière (page en cache) : on réactive le bouton
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) setSubmitting(false);
    });

    applyFilters();
});
</script>

<style>
.animate-fadeInUp {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
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

@media (max-width: 640px) {
    #cahier-modal { padding: 1rem; }
    .text-3xl { font-size: 1.75rem; }
    .text-2xl { font-size: 1.5rem; }
    .text-xl  { font-size: 1.25rem; }
}

.entry-card {
    transition: all 0.3s ease;
}

.entry-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.bg-yellow-100 { background-color: rgba(254, 243, 199, 0.8); }
.bg-green-100  { background-color: rgba(209, 250, 229, 0.8); }
.bg-blue-100   { background-color: rgba(219, 234, 254, 0.8); }

select, input[type="date"] {
    min-width: 150px;
}

#full-content-body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.8;
    font-size: 0.95rem;
}

button svg {
    flex-shrink: 0;
}

button:focus, input:focus, select:focus, textarea:focus {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
}

#no-results {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
</style>
@endsection
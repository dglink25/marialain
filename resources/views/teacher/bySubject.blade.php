@extends('layouts.app')

@section('title', "Cahier de texte - $teacher->name - $subject->name")

@section('content')

@php
    use Illuminate\Support\Str;
@endphp

<div class="container mx-auto px-4 py-8">

    <h1 class="text-3xl font-bold text-indigo-700 mb-6 text-center sm:text-left">Cahier de texte - {{ $teacher->name }}</h1>

    {{-- Header avec statistiques --}}
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="text-center lg:text-left">
                <h2 class="text-xl font-bold text-gray-800">Classe : <span class="text-indigo-600">{{ $class->name }}</span></h2>
                <p class="text-sm text-gray-600 mt-2">
                    Matière : <span class="text-indigo-600 font-semibold">{{ $subject->name }}</span>
                </p>
            </div>
            
            {{-- Statistiques --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-3 rounded-xl text-center border border-blue-200">
                    <div class="text-2xl font-bold text-blue-700">{{ $stats['total'] }}</div>
                    <div class="text-xs text-blue-600 font-medium">Total</div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-3 rounded-xl text-center border border-green-200">
                    <div class="text-2xl font-bold text-green-700">{{ $stats['validated'] }}</div>
                    <div class="text-xs text-green-600 font-medium">Validés</div>
                </div>
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-3 rounded-xl text-center border border-yellow-200">
                    <div class="text-2xl font-bold text-yellow-700">{{ $stats['pending'] }}</div>
                    <div class="text-xs text-yellow-600 font-medium">En attente</div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-3 rounded-xl text-center border border-purple-200">
                    <div class="text-2xl font-bold text-purple-700">{{ $stats['this_month'] }}</div>
                    <div class="text-xs text-purple-600 font-medium">Ce mois</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barre de filtres --}}
    <div class="bg-white rounded-xl shadow-md p-5 mb-6 border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Filtrer les résultats</h3>
                <div class="flex flex-wrap gap-3">
                    <div class="relative">
                        <select id="filter-month" class="appearance-none bg-white border border-gray-300 rounded-lg pl-4 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all w-full md:w-48">
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
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                            <i class="fas fa-chevron-down text-sm"></i>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <select id="filter-status" class="appearance-none bg-white border border-gray-300 rounded-lg pl-4 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all w-full md:w-48">
                            <option value="all">Tous les statuts</option>
                            <option value="validated">Validés</option>
                            <option value="pending">En attente</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                            <i class="fas fa-chevron-down text-sm"></i>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <input type="date" id="filter-date" 
                            class="bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all w-full md:w-48">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                            <i class="fas fa-calendar-alt text-sm"></i>
                        </div>
                    </div>
                    
                    <button onclick="resetFilters()" 
                        class="px-4 py-2.5 text-sm text-gray-700 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        <i class="fas fa-redo-alt mr-2"></i>Réinitialiser
                    </button>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div id="selected-count" class="hidden bg-indigo-100 text-indigo-800 px-3 py-1.5 rounded-full text-sm font-medium">
                    <span id="count-number">0</span> sélectionné(s)
                </div>
                
            </div>
        </div>
    </div>

    {{-- Barre d'actions de validation --}}
    @if($canValidate)
    <div id="validation-actions" class="hidden bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-md p-4 mb-6 border border-gray-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-tasks mr-2 text-indigo-600"></i>Actions de validation
                </h3>
                <p class="text-sm text-gray-600 mt-1" id="selection-info"></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button onclick="selectAllEntries()" 
                    class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg transition-colors text-sm font-medium shadow-sm">
                    <i class="fas fa-check-square mr-2"></i>Tout sélectionner
                </button>
                <button onclick="deselectAllEntries()" 
                    class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg transition-colors text-sm font-medium shadow-sm">
                    <i class="far fa-square mr-2"></i>Tout désélectionner
                </button>
                <button onclick="validateSelected('validate')" 
                    class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition-all text-sm font-medium shadow-sm hover:shadow-md">
                    <i class="fas fa-check-circle mr-2"></i>Valider sélection
                </button>
                <button onclick="validateSelected('reject')" 
                    class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all text-sm font-medium shadow-sm hover:shadow-md">
                    <i class="fas fa-times-circle mr-2"></i>Rejeter sélection
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Entries --}}
    @if ($entries->isEmpty())
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 text-gray-800 p-10 rounded-2xl shadow-sm text-center mt-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-200 rounded-full mb-4">
                <i class="fas fa-clipboard-list text-3xl text-gray-500"></i>
            </div>
            <h3 class="text-xl font-semibold mb-3">Aucun enregistrement trouvé</h3>
            <p class="text-gray-600 mb-4">Cet enseignant n'a pas encore rempli de cahier de texte.</p>
        </div>
    @else
        {{-- Desktop Table --}}
        <div class="hidden lg:block bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-700">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-left">
                            @if($canValidate)
                            <th class="px-4 py-4 font-semibold text-center w-12">
                                <input type="checkbox" id="select-all-checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="toggleSelectAll(this)">
                            </th>
                            @endif
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">
                                <i class="fas fa-calendar-alt mr-2"></i>Date & Heure
                            </th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">
                                <i class="fas fa-clock mr-2"></i>Durée
                            </th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">
                                <i class="fas fa-file-alt mr-2"></i>Contenu
                            </th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">
                                <i class="fas fa-info-circle mr-2"></i>Statut cours
                            </th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">
                                <i class="fas fa-check-circle mr-2"></i>Validation
                            </th>
                            <th class="px-6 py-4 font-semibold whitespace-nowrap">
                                <i class="fas fa-cog mr-2"></i>Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="entries-table-body">
                        @foreach ($entries as $entry)
                        @php
                            $startDate = \Carbon\Carbon::parse($entry->course_start_date);
                            $endDate = \Carbon\Carbon::parse($entry->course_end_date);
                            $duration = $entry->formatted_duration;
                            $month = $startDate->month;
                            $status = $entry->is_validated ? 'validated' : 'pending';
                            $date = $startDate->format('Y-m-d');
                        @endphp
                        <tr class="hover:bg-gray-50 transition-all duration-200 entry-row {{ $entry->is_validated ? 'bg-green-50/30' : '' }}"
                            data-month="{{ $month }}"
                            data-status="{{ $status }}"
                            data-date="{{ $date }}">
                            @if($canValidate)
                            <td class="px-4 py-4 text-center align-middle">
                                <input type="checkbox" class="entry-checkbox w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                    value="{{ $entry->id }}" data-entry-id="{{ $entry->id }}">
                            </td>
                            @endif
                            <td class="px-6 py-4 align-middle">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 whitespace-nowrap">
                                        {{ $startDate->translatedFormat('l d F Y') }}
                                    </span>
                                    <span class="text-sm text-gray-600 whitespace-nowrap">
                                        <i class="fas fa-clock mr-1 text-gray-400"></i>{{ $startDate->format('H:i') }} - {{ $endDate->format('H:i') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <span class="inline-flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100 text-blue-800 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-blue-200">
                                    <i class="fas fa-hourglass-half mr-1.5"></i>{{ ltrim($duration, '-') }}
                                </span>
                            </td>

                            <td class="px-6 py-4 align-middle">
                                <div class="max-w-md">
                                    <p class="text-gray-800 line-clamp-2 content-preview mb-1" 
                                       data-full-content="{{ htmlspecialchars($entry->content) }}">
                                        {{ Str::limit($entry->content, 100) }}
                                    </p>
                                    @if(strlen($entry->content) > 100)
                                        <button onclick="openFullContentModal('{{ addslashes(htmlspecialchars($entry->content)) }}')" 
                                            class="inline-flex items-center text-indigo-600 hover:text-indigo-800 text-xs font-medium transition-colors group">
                                            <i class="fas fa-expand-alt mr-1.5 group-hover:scale-110 transition-transform"></i>Voir plus
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 align-middle">
                                @if($entry->isCourseOngoing())
                                    <span class="inline-flex items-center bg-gradient-to-br from-yellow-50 to-yellow-100 text-yellow-800 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-yellow-200">
                                        <i class="fas fa-spinner fa-pulse mr-1.5"></i>En cours
                                    </span>
                                @elseif($entry->isCourseFinished())
                                    <span class="inline-flex items-center bg-gradient-to-br from-green-50 to-green-100 text-green-800 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-green-200">
                                        <i class="fas fa-check-circle mr-1.5"></i>Terminé
                                    </span>
                                @else
                                    <span class="inline-flex items-center bg-gradient-to-br from-blue-50 to-blue-100 text-blue-800 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-blue-200">
                                        <i class="fas fa-calendar-plus mr-1.5"></i>Planifié
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle">
                                @if($entry->is_validated)
                                    <div class="flex flex-col space-y-1.5">
                                        <span class="inline-flex items-center bg-gradient-to-br from-green-50 to-green-100 text-green-800 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-green-200">
                                            <i class="fas fa-check-circle mr-1.5"></i>Validé
                                        </span>
                                        <div class="text-xs text-gray-500 space-y-0.5">
                                            <div class="flex items-center">
                                                <i class="fas fa-user-check mr-1.5 text-gray-400"></i>
                                                <span class="truncate max-w-[120px]">{{ $entry->validator->name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-check mr-1.5 text-gray-400"></i>
                                                <span>{{ $entry->validated_at ? $entry->validated_at->format('d/m/Y H:i') : 'N/A' }}</span>
                                            </div>
                                        </div>
                                        @if($entry->validation_notes)
                                        <div class="text-xs text-gray-600 mt-1.5 flex items-start" title="{{ $entry->validation_notes }}">
                                            <i class="fas fa-sticky-note mr-1.5 mt-0.5 text-gray-400 flex-shrink-0"></i>
                                            <span class="truncate">{{ Str::limit($entry->validation_notes, 40) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center bg-gradient-to-br from-yellow-50 to-yellow-100 text-yellow-800 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-yellow-200">
                                        <i class="fas fa-clock mr-1.5"></i>En attente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-middle">
                                <div class="flex flex-col space-y-2.5">
                                    <div class="flex space-x-2">
                                        @if($canValidate && !$entry->is_validated)
                                            <button onclick="showValidateModal({{ $entry->id }}, 'validate')" 
                                                class="inline-flex items-center justify-center bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-3 py-1.5 rounded-lg transition-all text-xs font-medium shadow-sm hover:shadow-md group">
                                                <i class="fas fa-check mr-1.5 group-hover:scale-110 transition-transform"></i>Valider
                                            </button>
                                        @endif
                                        @if($canValidate && $entry->is_validated)
                                            <button onclick="showValidateModal({{ $entry->id }}, 'reject')" 
                                                class="inline-flex items-center justify-center bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-3 py-1.5 rounded-lg transition-all text-xs font-medium shadow-sm hover:shadow-md group">
                                                <i class="fas fa-times mr-1.5 group-hover:scale-110 transition-transform"></i>Rejeter
                                            </button>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-history mr-1.5 text-gray-400"></i>
                                        <span>Créé {{ $entry->created_at->diffForHumans() }}</span>
                                    </div>
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
                $month = $startDate->month;
                $status = $entry->is_validated ? 'validated' : 'pending';
                $date = $startDate->format('Y-m-d');
            @endphp
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-5 hover:shadow-lg transition-all duration-300 entry-card {{ $entry->is_validated ? 'border-l-4 border-l-green-500' : '' }}"
                data-month="{{ $month }}"
                data-status="{{ $status }}"
                data-date="{{ $date }}">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center space-x-3">
                        @if($canValidate)
                        <input type="checkbox" class="entry-checkbox w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                            value="{{ $entry->id }}" data-entry-id="{{ $entry->id }}">
                        @endif
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm">{{ $startDate->format('d/m/Y') }}</h3>
                            <p class="text-xs text-gray-600 mt-0.5">
                                <i class="fas fa-clock mr-1 text-gray-400"></i>{{ $startDate->format('H:i') }} - {{ $endDate->format('H:i') }}
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center bg-gradient-to-br from-blue-50 to-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs font-medium border border-blue-200">
                        <i class="fas fa-hourglass-half mr-1.5"></i>{{ $duration }}
                    </span>
                </div>

                {{-- Content --}}
                <div class="mb-4">
                    <p class="text-gray-800 text-sm line-clamp-3 content-preview mb-2" 
                       data-full-content="{{ htmlspecialchars($entry->content) }}">
                        {{ $entry->content }}
                    </p>
                    @if(strlen($entry->content) > 150)
                        <button onclick="openFullContentModal('{{ addslashes(htmlspecialchars($entry->content)) }}')" 
                            class="inline-flex items-center text-indigo-600 hover:text-indigo-800 text-xs font-medium transition-colors group">
                            <i class="fas fa-expand-alt mr-1.5 group-hover:scale-110 transition-transform"></i>Voir plus
                        </button>
                    @endif
                </div>

                {{-- Status & Validation --}}
                <div class="flex flex-col space-y-3 mb-4">
                    <div class="flex items-center justify-between">
                        @if($entry->isCourseOngoing())
                            <span class="inline-flex items-center bg-gradient-to-br from-yellow-50 to-yellow-100 text-yellow-800 px-2.5 py-1 rounded-full text-xs font-medium border border-yellow-200">
                                <i class="fas fa-spinner fa-pulse mr-1.5"></i>En cours
                            </span>
                        @elseif($entry->isCourseFinished())
                            <span class="inline-flex items-center bg-gradient-to-br from-green-50 to-green-100 text-green-800 px-2.5 py-1 rounded-full text-xs font-medium border border-green-200">
                                <i class="fas fa-check-circle mr-1.5"></i>Terminé
                            </span>
                        @else
                            <span class="inline-flex items-center bg-gradient-to-br from-blue-50 to-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs font-medium border border-blue-200">
                                <i class="fas fa-calendar-plus mr-1.5"></i>Planifié
                            </span>
                        @endif
                        
                        @if($entry->is_validated)
                            <span class="inline-flex items-center bg-gradient-to-br from-green-50 to-green-100 text-green-800 px-2.5 py-1 rounded-full text-xs font-medium border border-green-200">
                                <i class="fas fa-check-circle mr-1.5"></i>Validé
                            </span>
                        @else
                            <span class="inline-flex items-center bg-gradient-to-br from-yellow-50 to-yellow-100 text-yellow-800 px-2.5 py-1 rounded-full text-xs font-medium border border-yellow-200">
                                <i class="fas fa-clock mr-1.5"></i>En attente
                            </span>
                        @endif
                    </div>
                    
                    @if($entry->is_validated && $entry->validator)
                    <div class="text-xs text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <div class="font-medium text-gray-700 mb-1.5">Validation:</div>
                        <div class="space-y-1.5">
                            <div class="flex items-center">
                                <i class="fas fa-user-check mr-2 text-gray-400 flex-shrink-0"></i>
                                <span class="truncate">{{ $entry->validator->name }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar-check mr-2 text-gray-400 flex-shrink-0"></i>
                                <span>{{ $entry->validated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($entry->validation_notes)
                            <div class="flex items-start mt-1.5 pt-1.5 border-t border-gray-200">
                                <i class="fas fa-sticky-note mr-2 mt-0.5 text-gray-400 flex-shrink-0"></i>
                                <span class="text-gray-500">{{ Str::limit($entry->validation_notes, 60) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <div class="text-xs text-gray-500 flex items-center">
                        <i class="fas fa-history mr-1.5 text-gray-400"></i>
                        <span>Créé {{ $entry->created_at->diffForHumans() }}</span>
                    </div>
                    
                    @if($canValidate)
                    <div class="flex gap-2">
                        @if(!$entry->is_validated)
                            <button onclick="showValidateModal({{ $entry->id }}, 'validate')" 
                                class="inline-flex items-center justify-center bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-3 py-1.5 rounded-lg transition-all text-xs font-medium shadow-sm hover:shadow-md group">
                                <i class="fas fa-check mr-1.5 group-hover:scale-110 transition-transform"></i>
                            </button>
                        @endif
                        @if($entry->is_validated)
                            <button onclick="showValidateModal({{ $entry->id }}, 'reject')" 
                                class="inline-flex items-center justify-center bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-3 py-1.5 rounded-lg transition-all text-xs font-medium shadow-sm hover:shadow-md group">
                                <i class="fas fa-times mr-1.5 group-hover:scale-110 transition-transform"></i>
                            </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        
        {{-- Message aucun résultat après filtrage --}}
        <div id="no-results" class="hidden bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 text-gray-800 p-8 rounded-2xl shadow-sm text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-200 rounded-full mb-4">
                <i class="fas fa-search text-2xl text-gray-500"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">Aucun résultat trouvé</h3>
            <p class="text-gray-600 mb-4">Aucun cahier ne correspond à vos critères de filtrage.</p>
            <button onclick="resetFilters()" 
                class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white rounded-lg transition-all font-medium shadow-sm hover:shadow-md">
                <i class="fas fa-redo-alt mr-2"></i>Réinitialiser les filtres
            </button>
        </div>
    @endif

</div>

{{-- Modals --}}

{{-- Modal de contenu complet --}}
<div id="full-content-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden animate-modalIn">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-gray-800 to-gray-900 text-white">
            <h3 class="text-xl font-bold">
                <i class="fas fa-file-alt mr-3"></i>Contenu complet du cours
            </h3>
            <button onclick="closeFullContentModal()" class="text-white hover:text-gray-300 hover:bg-white/10 rounded-full p-2 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)] bg-gray-50">
            <div id="full-content-body" class="text-gray-800 whitespace-pre-wrap text-base leading-relaxed bg-white p-6 rounded-lg shadow-sm"></div>
        </div>
        <div class="flex justify-end p-6 border-t border-gray-200 bg-gray-50">
            <button onclick="closeFullContentModal()" 
                class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white rounded-lg transition-all font-medium shadow-sm hover:shadow-md">
                <i class="fas fa-times mr-2"></i>Fermer
            </button>
        </div>
    </div>
</div>

{{-- Modal de validation --}}
<div id="validate-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto p-0 overflow-hidden animate-modalIn">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white p-6">
            <h3 id="validate-modal-title" class="text-xl font-bold"></h3>
        </div>
        
        <form id="validate-form" method="POST" class="space-y-5 p-6">
            @csrf
            <input type="hidden" id="validate-entry-id" name="entry_id">
            <input type="hidden" id="validate-action" name="action">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2.5">
                    <i class="fas fa-sticky-note mr-2 text-indigo-600"></i>Notes de validation (optionnel)
                </label>
                <textarea name="validation_notes" id="validation-notes" rows="4" 
                    class="w-full border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none shadow-sm"
                    placeholder="Ajoutez des notes ou commentaires sur cette validation..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeValidateModal()"
                    class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium shadow-sm">
                    <i class="fas fa-times mr-2"></i>Annuler
                </button>
                <button type="submit" id="validate-submit-btn"
                    class="px-6 py-2.5 text-white rounded-xl transition-all font-medium shadow-sm hover:shadow-md">
                    <i class="fas fa-check mr-2"></i><span id="validate-btn-text"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal de validation multiple --}}
<div id="validate-multiple-modal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto p-0 overflow-hidden animate-modalIn">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white p-6">
            <h3 id="validate-multiple-title" class="text-xl font-bold"></h3>
        </div>
        
        <div class="p-6">
            <div class="mb-5">
                <p id="validate-multiple-count" class="text-gray-600 mb-3.5 flex items-center">
                    <i class="fas fa-list-check mr-2.5 text-indigo-600"></i>
                    <span id="selected-items-count"></span>
                </p>
                
                <label class="block text-sm font-semibold text-gray-700 mb-2.5">
                    <i class="fas fa-sticky-note mr-2 text-indigo-600"></i>Notes de validation (optionnel)
                </label>
                <textarea id="validate-multiple-notes" rows="4" 
                    class="w-full border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none shadow-sm"
                    placeholder="Notes communes pour tous les cahiers sélectionnés..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeValidateMultipleModal()"
                    class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-medium shadow-sm">
                    <i class="fas fa-times mr-2"></i>Annuler
                </button>
                <button type="button" onclick="confirmValidateMultiple()" id="validate-multiple-confirm-btn"
                    class="px-6 py-2.5 text-white rounded-xl transition-all font-medium shadow-sm hover:shadow-md">
                    <i class="fas fa-check mr-2"></i><span id="validate-multiple-btn-text"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let currentAction = 'validate';
let selectedEntries = new Set();

// Fonction pour décoder le contenu HTML
function decodeHtmlContent(html) {
    const txt = document.createElement('textarea');
    txt.innerHTML = html;
    return txt.value
        .replace(/\\'/g, "'")
        .replace(/\\"/g, '"')
        .replace(/\\n/g, '\n')
        .replace(/\\r/g, '\r')
        .replace(/\\t/g, '\t');
}

// Fonctions pour le contenu complet
function openFullContentModal(content) {
    const contentBody = document.getElementById('full-content-body');
    const decodedContent = decodeHtmlContent(content);
    
    contentBody.textContent = decodedContent;
    document.getElementById('full-content-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFullContentModal() {
    document.getElementById('full-content-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fonctions de sélection et filtrage
function toggleSelectAll(checkbox) {
    const entryCheckboxes = document.querySelectorAll('.entry-checkbox:not(:disabled)');
    const visibleEntries = getVisibleEntries();
    
    entryCheckboxes.forEach(cb => {
        if (visibleEntries.has(cb.value)) {
            cb.checked = checkbox.checked;
            if (checkbox.checked) {
                selectedEntries.add(cb.value);
            } else {
                selectedEntries.delete(cb.value);
            }
        }
    });
    updateSelectedUI();
}

function selectAllEntries() {
    const visibleEntries = getVisibleEntries();
    const entryCheckboxes = document.querySelectorAll('.entry-checkbox:not(:disabled)');
    
    entryCheckboxes.forEach(cb => {
        if (visibleEntries.has(cb.value)) {
            cb.checked = true;
            selectedEntries.add(cb.value);
        }
    });
    document.getElementById('select-all-checkbox').checked = true;
    updateSelectedUI();
}

function deselectAllEntries() {
    const entryCheckboxes = document.querySelectorAll('.entry-checkbox');
    entryCheckboxes.forEach(cb => {
        cb.checked = false;
        selectedEntries.delete(cb.value);
    });
    document.getElementById('select-all-checkbox').checked = false;
    updateSelectedUI();
}

function getVisibleEntries() {
    const visibleEntries = new Set();
    const visibleRows = document.querySelectorAll('.entry-row:not([style*="display: none"]), .entry-card:not([style*="display: none"])');
    
    visibleRows.forEach(row => {
        const checkbox = row.querySelector('.entry-checkbox');
        if (checkbox) {
            visibleEntries.add(checkbox.value);
        }
    });
    
    return visibleEntries;
}

function updateSelectedUI() {
    const count = selectedEntries.size;
    const selectedCount = document.getElementById('selected-count');
    const countNumber = document.getElementById('count-number');
    const validationActions = document.getElementById('validation-actions');
    const selectionInfo = document.getElementById('selection-info');
    
    countNumber.textContent = count;
    
    if (count > 0) {
        selectedCount.classList.remove('hidden');
        if (validationActions) validationActions.classList.remove('hidden');
        if (selectionInfo) {
            selectionInfo.textContent = `${count} cahier(s) sélectionné(s)`;
            selectionInfo.className = "text-sm text-gray-600 mt-1";
        }
    } else {
        selectedCount.classList.add('hidden');
        if (validationActions) validationActions.classList.add('hidden');
        if (selectionInfo) selectionInfo.textContent = "Sélectionnez les cahiers à valider";
    }
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
        
        if (monthFilter !== 'all' && month !== monthFilter) {
            show = false;
        }
        
        if (statusFilter !== 'all' && status !== statusFilter) {
            show = false;
        }
        
        if (dateFilter && date !== dateFilter) {
            show = false;
        }
        
        if (show) {
            row.style.display = '';
            visibleCount++;
            
            // Animation d'apparition
            row.style.animation = 'fadeIn 0.3s ease-out';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Afficher/masquer le message "aucun résultat"
    const noResults = document.getElementById('no-results');
    const entriesContainer = document.getElementById('entries-cards-container');
    const tableBody = document.getElementById('entries-table-body');
    
    if (visibleCount === 0) {
        if (noResults) noResults.classList.remove('hidden');
        if (entriesContainer) entriesContainer.classList.add('hidden');
        if (tableBody) tableBody.parentElement.parentElement.classList.add('hidden');
    } else {
        if (noResults) noResults.classList.add('hidden');
        if (entriesContainer) entriesContainer.classList.remove('hidden');
        if (tableBody) tableBody.parentElement.parentElement.classList.remove('hidden');
    }
    
    // Réinitialiser la sélection
    selectedEntries.clear();
    updateSelectedUI();
    document.getElementById('select-all-checkbox').checked = false;
}

function resetFilters() {
    document.getElementById('filter-month').value = 'all';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-date').value = '';
    applyFilters();
}

// Fonctions de validation individuelle
function showValidateModal(entryId, action) {
    currentAction = action;
    document.getElementById('validate-entry-id').value = entryId;
    document.getElementById('validate-action').value = action;
    
    const modalTitle = document.getElementById('validate-modal-title');
    const submitBtn = document.getElementById('validate-submit-btn');
    const btnText = document.getElementById('validate-btn-text');
    const form = document.getElementById('validate-form');
    
    if (action === 'validate') {
        modalTitle.textContent = 'Valider ce cahier de texte';
        btnText.textContent = 'Valider';
        submitBtn.className = 'px-6 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl transition-all font-medium shadow-sm hover:shadow-md';
    } else {
        modalTitle.textContent = 'Rejeter ce cahier de texte';
        btnText.textContent = 'Rejeter';
        submitBtn.className = 'px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl transition-all font-medium shadow-sm hover:shadow-md';
    }
    
    form.action = "{{ route('cahier.validate', ['cahier' => ':id']) }}".replace(':id', entryId);
    
    document.getElementById('validate-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeValidateModal() {
    document.getElementById('validate-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fonctions de validation multiple
function validateSelected(action) {
    const selected = Array.from(selectedEntries);
    
    if (selected.length === 0) {
        showToast('Veuillez sélectionner au moins un cahier de texte.', 'warning');
        return;
    }
    
    currentAction = action;
    const modalTitle = document.getElementById('validate-multiple-title');
    const confirmBtn = document.getElementById('validate-multiple-confirm-btn');
    const btnText = document.getElementById('validate-multiple-btn-text');
    const countText = document.getElementById('selected-items-count');
    
    if (action === 'validate') {
        modalTitle.textContent = `Valider ${selected.length} cahier(s)`;
        btnText.textContent = 'Valider';
        confirmBtn.className = 'px-6 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl transition-all font-medium shadow-sm hover:shadow-md';
    } else {
        modalTitle.textContent = `Rejeter ${selected.length} cahier(s)`;
        btnText.textContent = 'Rejeter';
        confirmBtn.className = 'px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl transition-all font-medium shadow-sm hover:shadow-md';
    }
    
    countText.textContent = `${selected.length} cahier(s) sélectionné(s)`;
    
    document.getElementById('validate-multiple-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeValidateMultipleModal() {
    document.getElementById('validate-multiple-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

async function confirmValidateMultiple() {
    const selected = Array.from(selectedEntries);
    const notes = document.getElementById('validate-multiple-notes').value;
    
    if (selected.length === 0) {
        showToast('Aucun cahier sélectionné.', 'warning');
        return;
    }
    
    // Afficher un indicateur de chargement
    const confirmBtn = document.getElementById('validate-multiple-confirm-btn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Traitement...';
    confirmBtn.disabled = true;
    
    try {
        const response = await fetch("{{ route('cahier.validate.multiple') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                entry_ids: selected,
                validation_notes: notes,
                action: currentAction
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showToast(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.error || 'Une erreur est survenue.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Vous ne pouvez pas tout valider ou révoquez. Veuillez réessayer un à un.', 'error');
    } finally {
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    }
}

// Fonction pour afficher les notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    toast.className = `fixed top-5 right-5 ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg z-50 transform translate-x-full opacity-0 transition-all duration-300`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${icons[type]} mr-3"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animation d'entrée
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
        toast.classList.add('translate-x-0', 'opacity-100');
    }, 10);
    
    // Animation de sortie après 4 secondes
    setTimeout(() => {
        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Écouteurs pour les cases à cocher
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('entry-checkbox')) {
            if (e.target.checked) {
                selectedEntries.add(e.target.value);
            } else {
                selectedEntries.delete(e.target.value);
                document.getElementById('select-all-checkbox').checked = false;
            }
            updateSelectedUI();
        }
    });
    
    // Écouteurs pour les filtres
    document.getElementById('filter-month').addEventListener('change', applyFilters);
    document.getElementById('filter-status').addEventListener('change', applyFilters);
    document.getElementById('filter-date').addEventListener('change', applyFilters);
    
    // Écouteurs pour les boutons "Voir plus"
    document.querySelectorAll('.see-more-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const content = this.previousElementSibling.getAttribute('data-full-content');
            openFullContentModal(content);
        });
    });
    
    // Fermer les modals avec Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeFullContentModal();
            closeValidateModal();
            closeValidateMultipleModal();
        }
    });
    
    // Fermer les modals en cliquant à l'extérieur
    document.addEventListener('click', function(event) {
        const modals = ['full-content-modal', 'validate-modal', 'validate-multiple-modal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                if (modalId === 'full-content-modal') closeFullContentModal();
                if (modalId === 'validate-modal') closeValidateModal();
                if (modalId === 'validate-multiple-modal') closeValidateMultipleModal();
            }
        });
    });
    
    // Initialiser les filtres
    applyFilters();
});
</script>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-modalIn {
    animation: modalIn 0.3s ease-out;
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

/* Styles pour les cases à cocher */
.entry-checkbox {
    cursor: pointer;
    transition: all 0.2s ease;
}

.entry-checkbox:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

/* Scrollbar personnalisée */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Transition smooth pour les cartes */
.entry-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.entry-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 20px -10px rgba(0, 0, 0, 0.1);
}

/* Badges avec gradient */
.badge-gradient {
    background: linear-gradient(135deg, var(--tw-gradient-from), var(--tw-gradient-to));
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Responsive design amélioré */
@media (max-width: 768px) {
    .hidden.lg\:block {
        display: none !important;
    }
    
    .lg\:hidden {
        display: block;
    }
    
    .text-3xl {
        font-size: 1.75rem;
        line-height: 2.25rem;
    }
    
    .text-xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
    
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}

@media (max-width: 640px) {
    .grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    
    .gap-3 {
        gap: 0.75rem;
    }
    
    .p-5 {
        padding: 1.25rem;
    }
    
    .space-y-4 > * + * {
        margin-top: 1rem;
    }
}

/* Effets de hover améliorés */
button:not(:disabled):hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease;
}

button:active:not(:disabled) {
    transform: translateY(1px);
}

/* Focus styles pour l'accessibilité */
input:focus, button:focus, select:focus, textarea:focus {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Animation pour les lignes de tableau */
.entry-row {
    animation: fadeIn 0.3s ease-out;
    animation-fill-mode: both;
}

.entry-row:nth-child(even) {
    background-color: rgba(249, 250, 251, 0.5);
}

/* Styles pour les select personnalisés */
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* Ombre portée subtile */
.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.shadow-md {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.shadow-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-xl {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Bordures arrondies */
.rounded-xl {
    border-radius: 0.75rem;
}

.rounded-2xl {
    border-radius: 1rem;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
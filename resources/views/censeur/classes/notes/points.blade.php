@extends('layouts.app')

@php
    $pageTitle = "Points des Notes Disponibles";
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- En-tête avec gradient -->
        <div class="mb-10 bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Points de Notes Disponibles</h1>
                    <div class="flex flex-wrap items-center gap-4 mt-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            Classe : {{ $classe->name }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Trimestre {{ $trimestre }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                            <i class="fas fa-calendar mr-2"></i>
                            {{ $activeYear->name ?? 'Année Académique' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('censeur.classes.trimestres', $classe->id) }}" 
                   class="inline-flex items-center px-5 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:shadow transition-all duration-200 font-medium">
                    <i class="fas fa-arrow-left mr-3"></i> Retour aux trimestres
                </a>
            </div>
        </div>

        <!-- Tableau avec design moderne -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-800 to-gray-900">
                        <tr>
                            <th rowspan="2" class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Matière
                            </th>
                            <th rowspan="2" class="px-6 py-4 text-center text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Coef.
                            </th>
                            <th colspan="5" class="px-6 py-4 text-center text-xs font-semibold text-blue-300 uppercase tracking-wider border-l border-gray-700">
                                Interrogations
                            </th>
                            <th colspan="2" class="px-6 py-4 text-center text-xs font-semibold text-purple-300 uppercase tracking-wider border-l border-gray-700">
                                Devoirs
                            </th>
                            <th rowspan="2" class="px-6 py-4 text-center text-xs font-semibold text-emerald-300 uppercase tracking-wider border-l border-gray-700">
                                Total
                            </th>
                        </tr>
                        <tr class="bg-gradient-to-r from-gray-700 to-gray-800">
                            <!-- Interrogations headers -->
                            @for($i = 1; $i <= 5; $i++)
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-300">
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg font-bold text-blue-200">I{{ $i }}</span>
                                        <span class="text-xs text-gray-400 mt-1">Interro {{ $i }}</span>
                                    </div>
                                </th>
                            @endfor
                            
                            <!-- Devoirs headers -->
                            @for($d = 1; $d <= 2; $d++)
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 border-l border-gray-600">
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg font-bold text-purple-200">D{{ $d }}</span>
                                        <span class="text-xs text-gray-400 mt-1">Devoir {{ $d }}</span>
                                    </div>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($matieres as $index => $m)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 @if($index % 2 == 0) bg-gray-50/50 @endif">
                            <!-- Matière -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mr-4">
                                        <span class="text-white font-bold">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $m->subject->name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Enseignant : {{ $m->teacher->user->name ?? 'Non assigné' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Coefficient -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-800 font-bold text-lg">
                                    {{ $m->coefficient ?? '?' }}
                                </span>
                            </td>

                            <!-- Interrogations -->
                            @for($seq = 1; $seq <= 5; $seq++)
                                <td class="px-4 py-4 text-center border-l border-gray-100">
                                    @php
                                        $iKey = "I$seq";
                                        $hasNote = $notesDisponibles[$m->subject->name][$iKey] ?? false;
                                    @endphp
                                    <div class="flex flex-col items-center">
                                        @if($hasNote)
                                            <div class="mb-2">
                                                <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check text-sm"></i>
                                                </div>
                                            </div>
                                            <form action="{{ route('censeur.notes.autoriserModification') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="teacher_id" value="{{ $m->teacher_id ?? 0 }}">
                                                <input type="hidden" name="class_id" value="{{ $classe->id }}">
                                                <input type="hidden" name="subject_id" value="{{ $m->subject->id }}">
                                                <input type="hidden" name="trimestre" value="{{ $trimestre }}">
                                                <input type="hidden" name="type" value="interrogation">
                                                <input type="hidden" name="sequence" value="{{ $seq }}">
                                                
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-gradient-to-r from-green-50 to-green-100 text-green-700 border border-green-200 hover:from-green-100 hover:to-green-200 hover:shadow transition-all duration-200">
                                                    <i class="fas fa-lock-open mr-1.5"></i>
                                                    <span>Autoriser</span>
                                                </button>
                                            </form>
                                        @else
                                            <div class="mb-2">
                                                <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-400">
                                                    <i class="fas fa-times text-sm"></i>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-400 font-medium">Vide</span>
                                        @endif
                                    </div>
                                </td>
                            @endfor

                            <!-- Devoirs -->
                            @for($seq = 1; $seq <= 2; $seq++)
                                <td class="px-4 py-4 text-center border-l border-gray-100">
                                    @php
                                        $dKey = "D$seq";
                                        $hasNote = $notesDisponibles[$m->subject->name][$dKey] ?? false;
                                    @endphp
                                    <div class="flex flex-col items-center">
                                        @if($hasNote)
                                            <div class="mb-2">
                                                <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-indigo-100 text-indigo-800">
                                                    <i class="fas fa-check text-sm"></i>
                                                </div>
                                            </div>
                                            <form action="{{ route('censeur.notes.autoriserModification') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="teacher_id" value="{{ $m->teacher_id ?? 0 }}">
                                                <input type="hidden" name="class_id" value="{{ $classe->id }}">
                                                <input type="hidden" name="subject_id" value="{{ $m->subject->id }}">
                                                <input type="hidden" name="trimestre" value="{{ $trimestre }}">
                                                <input type="hidden" name="type" value="devoir">
                                                <input type="hidden" name="sequence" value="{{ $seq }}">
                                                
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg bg-gradient-to-r from-indigo-50 to-indigo-100 text-indigo-700 border border-indigo-200 hover:from-indigo-100 hover:to-indigo-200 hover:shadow transition-all duration-200">
                                                    <i class="fas fa-lock-open mr-1.5"></i>
                                                    <span>Autoriser</span>
                                                </button>
                                            </form>
                                        @else
                                            <div class="mb-2">
                                                <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-400">
                                                    <i class="fas fa-times text-sm"></i>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-400 font-medium">Vide</span>
                                        @endif
                                    </div>
                                </td>
                            @endfor

                            <!-- Total -->
                            <td class="px-6 py-4 text-center border-l border-gray-100">
                                <div class="flex flex-col items-center">
                                    <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white font-bold text-xl shadow-lg">
                                        {{ $notesDisponibles[$m->subject->name]['total'] ?? 0 }}
                                    </span>
                                    <span class="text-xs text-gray-500 mt-2 font-medium">sur 7</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <i class="fas fa-book text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Aucune matière trouvée</h3>
                                    <p class="text-gray-500 max-w-md">
                                        Aucune matière n'est enregistrée pour cette classe dans l'année académique active.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Légende et informations -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl p-5 shadow border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center mr-4">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Note disponible</h3>
                        <p class="text-sm text-gray-600 mt-1">L'enseignant a saisi cette note</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-5 shadow border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center mr-4">
                        <i class="fas fa-times text-gray-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Note manquante</h3>
                        <p class="text-sm text-gray-600 mt-1">L'enseignant n'a pas encore saisi cette note</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-5 shadow border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center mr-4">
                        <i class="fas fa-chart-bar text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Résumé</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ count($matieres) }} matières, 7 évaluations possibles par matière</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats en bas -->
        <div class="mt-8 bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl p-6 text-white">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold mb-1">{{ count($matieres) }}</div>
                    <div class="text-sm text-gray-300">Matières</div>
                </div>
                <div class="text-center">
                    @php
                        $totalEvaluations = 0;
                        foreach($notesDisponibles as $matiere) {
                            $totalEvaluations += $matiere['total'] ?? 0;
                        }
                    @endphp
                    <div class="text-3xl font-bold mb-1">{{ $totalEvaluations }}</div>
                    <div class="text-sm text-gray-300">Notes saisies</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold mb-1">{{ count($matieres) * 7 }}</div>
                    <div class="text-sm text-gray-300">Évaluations attendues</div>
                </div>
                <div class="text-center">
                    @php
                        $pourcentage = count($matieres) > 0 ? round(($totalEvaluations / (count($matieres) * 7)) * 100, 1) : 0;
                    @endphp
                    <div class="text-3xl font-bold mb-1">{{ $pourcentage }}%</div>
                    <div class="text-sm text-gray-300">Taux de saisie</div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .hover-scale {
        transition: transform 0.2s ease;
    }
    .hover-scale:hover {
        transform: translateY(-2px);
    }
</style>
@endsection
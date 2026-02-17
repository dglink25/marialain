@extends('layouts.app')

@php
    $pageTitle = "Points des Notes Disponibles";
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- En-tête simplifié -->
        <div class="mb-6 bg-white rounded-xl shadow-sm p-4 border border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Points des Notes Disponibles</h1>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-graduation-cap mr-1"></i>
                            {{ $classe->name }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Trimestre {{ $trimestre }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $activeYear->name ?? 'Année en cours' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('censeur.classes.trimestres', $classe->id) }}" 
                   class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>

        <!-- Tableau avec défilement horizontal -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
            <div class="overflow-x-auto overflow-y-auto" style="max-height: 500px;">
                <table class="min-w-full text-sm" style="border-collapse: separate; border-spacing: 0;">
                    <thead class="bg-gray-50" style="position: sticky; top: 0; z-index: 20;">
                        <tr>
                            <th rowspan="2" class="px-3 py-3 text-left text-xs font-semibold text-blue-600 uppercase tracking-wider border-b border-gray-200 sticky left-0 bg-gray-50 z-30" style="top: 0;">
                                Matière
                            </th>
                            <th colspan="5" class="px-3 py-3 text-center text-xs font-semibold text-blue-600 uppercase tracking-wider border-b border-gray-200">
                                Interrogations
                            </th>
                            <th colspan="2" class="px-3 py-3 text-center text-xs font-semibold text-purple-600 uppercase tracking-wider border-b border-gray-200">
                                Devoirs
                            </th>
                            <th rowspan="2" class="px-3 py-3 text-center text-xs font-semibold text-emerald-600 uppercase tracking-wider border-b border-gray-200">
                                Total
                            </th>
                        </tr>
                        <tr class="bg-gray-50">
                            <!-- Interrogations -->
                            @for($i = 1; $i <= 5; $i++)
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 border-l border-gray-200">
                                    <span class="font-bold text-blue-600">I{{ $i }}</span>
                                </th>
                            @endfor
                            
                            <!-- Devoirs -->
                            @for($d = 1; $d <= 2; $d++)
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 border-l border-gray-200">
                                    <span class="font-bold text-purple-600">D{{ $d }}</span>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($matieres as $index => $m)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 py-3 sticky left-0 bg-white z-10 border-r border-gray-200" style="top: auto;">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-7 w-7 rounded-md bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mr-2">
                                        <span class="text-white text-xs font-bold">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 text-sm">{{ $m->subject->name }}</div>
                                        <div class="text-xs text-gray-400">
                                            Coef. {{ $m->coefficient ?? '?' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Interrogations -->
                            @for($seq = 1; $seq <= 5; $seq++)
                                <td class="px-3 py-3 text-center border-l border-gray-100">
                                    @php
                                        $iKey = "I$seq";
                                        $hasNote = $notesDisponibles[$m->subject->name][$iKey] ?? false;
                                    @endphp
                                    @if($hasNote)
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-100">
                                                <i class="fas fa-check text-xs text-green-600"></i>
                                            </div>
                                            <form action="{{ route('censeur.notes.autoriserModification') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="teacher_id" value="{{ $m->teacher_id ?? 0 }}">
                                                <input type="hidden" name="class_id" value="{{ $classe->id }}">
                                                <input type="hidden" name="subject_id" value="{{ $m->subject->id }}">
                                                <input type="hidden" name="trimestre" value="{{ $trimestre }}">
                                                <input type="hidden" name="type" value="interrogation">
                                                <input type="hidden" name="sequence" value="{{ $seq }}">
                                                
                                                <button type="submit" 
                                                        class="text-xs px-2 py-0.5 rounded bg-green-50 text-green-600 hover:bg-green-100 border border-green-200 transition-colors">
                                                    Autoriser
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-100">
                                                <i class="fas fa-times text-xs text-gray-400"></i>
                                            </div>
                                            <span class="text-xs text-gray-400">-</span>
                                        </div>
                                    @endif
                                </td>
                            @endfor

                            <!-- Devoirs -->
                            @for($seq = 1; $seq <= 2; $seq++)
                                <td class="px-3 py-3 text-center border-l border-gray-100">
                                    @php
                                        $dKey = "D$seq";
                                        $hasNote = $notesDisponibles[$m->subject->name][$dKey] ?? false;
                                    @endphp
                                    @if($hasNote)
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-100">
                                                <i class="fas fa-check text-xs text-indigo-600"></i>
                                            </div>
                                            <form action="{{ route('censeur.notes.autoriserModification') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="teacher_id" value="{{ $m->teacher_id ?? 0 }}">
                                                <input type="hidden" name="class_id" value="{{ $classe->id }}">
                                                <input type="hidden" name="subject_id" value="{{ $m->subject->id }}">
                                                <input type="hidden" name="trimestre" value="{{ $trimestre }}">
                                                <input type="hidden" name="type" value="devoir">
                                                <input type="hidden" name="sequence" value="{{ $seq }}">
                                                
                                                <button type="submit" 
                                                        class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 hover:bg-indigo-100 border border-indigo-200 transition-colors">
                                                    Autoriser
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-100">
                                                <i class="fas fa-times text-xs text-gray-400"></i>
                                            </div>
                                            <span class="text-xs text-gray-400">-</span>
                                        </div>
                                    @endif
                                </td>
                            @endfor

                            <td class="px-4 py-3 text-center border-l border-gray-100">
                                <div class="flex flex-col items-center">
                                    <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm">
                                        {{ $notesDisponibles[$m->subject->name]['total'] ?? 0 }}
                                    </span>
                                    <span class="text-xs text-gray-400">/7</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-book text-gray-300 text-2xl mb-2"></i>
                                <p>Aucune matière trouvée pour cette classe</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mini statistiques -->
        <div class="mt-4 grid grid-cols-4 gap-2">
            <div class="bg-white rounded-lg p-3 text-center border border-gray-200">
                <div class="text-lg font-bold text-gray-700">{{ count($matieres) }}</div>
                <div class="text-xs text-gray-500">Matières</div>
            </div>
            <div class="bg-white rounded-lg p-3 text-center border border-gray-200">
                @php
                    $totalEvaluations = 0;
                    foreach($notesDisponibles as $matiere) {
                        $totalEvaluations += $matiere['total'] ?? 0;
                    }
                @endphp
                <div class="text-lg font-bold text-blue-600">{{ $totalEvaluations }}</div>
                <div class="text-xs text-gray-500">Notes saisies</div>
            </div>
            <div class="bg-white rounded-lg p-3 text-center border border-gray-200">
                <div class="text-lg font-bold text-gray-700">{{ count($matieres) * 7 }}</div>
                <div class="text-xs text-gray-500">Attendues</div>
            </div>
            <div class="bg-white rounded-lg p-3 text-center border border-gray-200">
                @php
                    $pourcentage = count($matieres) > 0 ? round(($totalEvaluations / (count($matieres) * 7)) * 100, 1) : 0;
                @endphp
                <div class="text-lg font-bold text-emerald-600">{{ $pourcentage }}%</div>
                <div class="text-xs text-gray-500">Taux</div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Barre de défilement fine */
    .overflow-x-auto {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f1f5f9;
    }
    
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
        width: 6px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }
    
    .sticky {
        box-shadow: 1px 0 3px -2px rgba(0,0,0,0.1);
    }
    
    thead th.sticky,
    tbody td.sticky {
        z-index: 15;
    }
    
    thead th.sticky {
        z-index: 30;
    }
</style>
@endsection
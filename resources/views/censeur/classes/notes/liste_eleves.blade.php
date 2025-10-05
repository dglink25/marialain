@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Message d'erreur -->
        @if(isset($error))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
            <span class="text-red-700">{{ $error }}</span>
        </div>
        @endif

        <!-- En-tête de page -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="mb-4 lg:mb-0">
                    <h1 class="text-3xl font-bold text-gray-900">Récapitulatif des Notes</h1>
                    <div class="flex items-center mt-2 text-gray-600">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                        <span class="mr-4">{{ $classe->name }}</span>
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span class="mr-4">{{ $trimestre }}ᵉ Trimestre</span>
                        <i class="fas fa-graduation-cap mr-2"></i>
                        <span>{{ $activeYear->name }}</span>
                    </div>
                </div>
                
                <!-- Boutons d'export -->
                <div class="flex space-x-3">
                    <a href="{{ route('censeur.classes.notes.pdf', [$classe->id, $trimestre]) }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i>
                        PDF
                    </a>
                    <a href="{{ route('censeur.classes.notes.excel', [$classe->id, $trimestre]) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-file-excel mr-2"></i>
                        Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            <!-- En-tête de la carte -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-chart-bar text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-white">Tableau des Performances</h2>
                            <p class="text-blue-100 text-sm">Notes et classements des élèves</p>
                        </div>
                    </div>
                    <div class="text-white text-sm bg-blue-500 px-3 py-1 rounded-full">
                        {{ count($classe->students) }} élèves
                    </div>
                </div>
            </div>

            <!-- Tableau responsive -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th rowspan="2" class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-200">
                                Élève
                            </th>
                            
                            @foreach($subjects as $subject)
                            <th colspan="6" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                {{ $subject->name }}
                            </th>
                            @endforeach
                            
                            <th rowspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                Conduite
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                Moy. Gén.
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                Rang
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                Bulletin
                            </th>
                        </tr>
                        <tr>
                            @foreach($subjects as $subject)
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Int.</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">D1</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">D2</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Moy/20</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Coef</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Moy.Coef</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @foreach($classe->students as $index => $student)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Informations élève -->
                            <td class="px-4 py-3 border-r border-gray-200">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-blue-600 text-sm font-semibold">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ strtoupper($student->last_name) }} {{ ucfirst($student->first_name) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $student->num_educ ?? 'N/A' }} • {{ strtoupper($student->gender ?? '-') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Notes par matière -->
                            @foreach($subjects as $subject)
                                @php
                                    $s = $gradesData[$student->id][$subject->id] ?? [];
                                @endphp
                                <td class="px-2 py-2 text-center text-sm border-r border-gray-200 {{ isset($s['moyenneInterro']) ? 'text-gray-700' : 'text-gray-400' }}">
                                    {{ $s['moyenneInterro'] ?? '-' }}
                                </td>
                                <td class="px-2 py-2 text-center text-sm border-r border-gray-200 {{ isset($s['devoirs'][1]) ? 'text-gray-700' : 'text-gray-400' }}">
                                    {{ $s['devoirs'][1] ?? '-' }}
                                </td>
                                <td class="px-2 py-2 text-center text-sm border-r border-gray-200 {{ isset($s['devoirs'][2]) ? 'text-gray-700' : 'text-gray-400' }}">
                                    {{ $s['devoirs'][2] ?? '-' }}
                                </td>
                                <td class="px-2 py-2 text-center text-sm font-medium border-r border-gray-200 
                                    {{ isset($s['moyenne']) ? ($s['moyenne'] >= 10 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' }}">
                                    {{ $s['moyenne'] ?? '-' }}
                                </td>
                                <td class="px-2 py-2 text-center text-sm text-gray-500 border-r border-gray-200">
                                    {{ $s['coef'] ?? 1 }}
                                </td>
                                <td class="px-2 py-2 text-center text-sm font-semibold border-r border-gray-200 
                                    {{ isset($s['moyenneMat']) ? ($s['moyenneMat'] >= 10 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' }}">
                                    {{ $s['moyenneMat'] ?? '-' }}
                                </td>
                            @endforeach

                            <!-- Résultats généraux -->
                            <td class="px-3 py-2 text-center text-sm font-medium border-r border-gray-200
                                {{ isset($conductData[$student->id]) ? 'text-blue-600' : 'text-gray-400' }}">
                                {{ $conductData[$student->id] ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-center text-lg font-bold border-r border-gray-200
                                {{ isset($gradesData[$student->id]['moyenne_generale']) ? 
                                   ($gradesData[$student->id]['moyenne_generale'] >= 10 ? 'text-green-600' : 'text-red-600') : 
                                   'text-gray-400' }}">
                                {{ $gradesData[$student->id]['moyenne_generale'] ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-center text-sm font-semibold border-r border-gray-200
                                {{ isset($gradesData[$student->id]['rang_general']) ? 'text-purple-600' : 'text-gray-400' }}">
                                {{ $gradesData[$student->id]['rang_general'] ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                <a href="{{ route('teacher.classes.students.bulletin', [$classe->id, $student->id, $trimestre]) }}"
                                   class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-colors duration-200 text-sm">
                                    <i class="fas fa-eye mr-1"></i>
                                    Voir
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Message si aucun élève -->
            @if(count($classe->students) === 0)
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun élève dans cette classe</h3>
                <p class="text-gray-500">Les élèves apparaîtront ici une fois inscrits.</p>
            </div>
            @endif
        </div>

        <!-- Légende -->
        <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-gray-600">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                    <span>Moyenne ≥ 10</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                    <span>Moyenne < 10</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-minus text-gray-400 mr-2"></i>
                    <span>Note non saisie</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles pour le tableau responsive -->
<style>
    @media (max-width: 1024px) {
        table {
            font-size: 0.8rem;
        }
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
    }
</style>

<!-- Script pour améliorer l'interaction -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight des lignes au survol
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f9fafb';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>
@endsection
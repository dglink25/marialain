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

        @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
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
                    <!--
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
                    -->
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
                            <th colspan="7" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                {{ $subject->subject->name }}
                            </th>
                            @endforeach
                            
                            <th rowspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                                Conduite
                                <span class="text-xs font-normal text-gray-500">(Coef: 1)</span>
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
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Int. Moy</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">D. Moy</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Moy/20</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Coef</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">MoyCoef</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Dev 1</th>
                                <th class="px-2 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">Dev 2</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @foreach($classe->students as $index => $student)
                        @php
                            $studentData = $gradesData[$student->id] ?? [];
                            $moyenneGenerale = $studentData['moyenne_generale'] ?? 0;
                            $rang = $studentData['rang_general'] ?? '-';
                            $conduiteFinale = $studentData['conduite_finale'] ?? 0;
                        @endphp
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
                                            {{ $student->num_educ ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Notes par matière -->
                            @foreach($subjects as $subject)
                                @php
                                    $matiere = $studentData[$subject->id] ?? [];
                                    $moyenneInterro = $matiere['moyenneInterro'] ?? null;
                                    $devoir1 = $matiere['devoir1'] ?? null;
                                    $devoir2 = $matiere['devoir2'] ?? null;
                                    $moyenneMatiere = $matiere['moyenneMatiere'] ?? null;
                                    $moyenneCoef = $matiere['moyenneCoef'] ?? null;
                                    $coef = $matiere['coef'] ?? 1;
                                    
                                    // Calculer la moyenne des devoirs dans la vue
                                    $moyenneDevoir = null;
                                    if ($devoir1 !== null && $devoir2 !== null) {
                                        $moyenneDevoir = round(($devoir1 + $devoir2) / 2, 2);
                                    } elseif ($devoir1 !== null) {
                                        $moyenneDevoir = $devoir1;
                                    } elseif ($devoir2 !== null) {
                                        $moyenneDevoir = $devoir2;
                                    }
                                @endphp
                                
                                <!-- Moyenne Interro -->
                                <td class="px-2 py-2 text-center text-sm border-r border-gray-200 {{ $moyenneInterro !== null ? 'text-gray-700' : 'text-gray-400' }}">
                                    {{ $moyenneInterro !== null ? number_format($moyenneInterro, 2) : '-' }}
                                </td>
                                
                                <!-- Moyenne Devoir -->
                                <td class="px-2 py-2 text-center text-sm border-r border-gray-200 {{ $moyenneDevoir !== null ? 'text-gray-700' : 'text-gray-400' }}">
                                    {{ $moyenneDevoir !== null ? number_format($moyenneDevoir, 2) : '-' }}
                                </td>
                                
                                <!-- Moyenne Matière -->
                                <td class="px-2 py-2 text-center text-sm font-medium border-r border-gray-200 
                                    {{ $moyenneMatiere !== null ? ($moyenneMatiere >= 10 ? 'text-green-600' : 'text-red-600') : 'text-gray-400' }}">
                                    {{ $moyenneMatiere !== null ? number_format($moyenneMatiere, 2) : '-' }}
                                </td>
                                
                                <!-- Coefficient -->
                                <td class="px-2 py-2 text-center text-sm text-gray-500 border-r border-gray-200">
                                    {{ $coef }}
                                </td>
                                
                                <!-- Moyenne Coefficientée -->
                                <td class="px-2 py-2 text-center text-sm font-semibold border-r border-gray-200 
                                    {{ $moyenneCoef !== null ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ $moyenneCoef !== null ? number_format($moyenneCoef, 2) : '-' }}
                                </td>
                                
                                <!-- Devoir 1 -->
                                <td class="px-2 py-2 text-center text-xs border-r border-gray-200 {{ $devoir1 !== null ? 'text-gray-600' : 'text-gray-400' }}">
                                    {{ $devoir1 !== null ? number_format($devoir1, 2) : '-' }}
                                </td>
                                
                                <!-- Devoir 2 -->
                                <td class="px-2 py-2 text-center text-xs border-r border-gray-200 {{ $devoir2 !== null ? 'text-gray-600' : 'text-gray-400' }}">
                                    {{ $devoir2 !== null ? number_format($devoir2, 2) : '-' }}
                                </td>
                            @endforeach

                            <!-- Conduite -->
                            <td class="px-3 py-2 text-center text-sm font-medium border-r border-gray-200
                                {{ $conduiteFinale > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                                {{ $conduiteFinale > 0 ? number_format($conduiteFinale, 2) : '-' }}
                            </td>
                            
                            <!-- Moyenne Générale -->
                            <td class="px-3 py-2 text-center text-lg font-bold border-r border-gray-200
                                {{ $moyenneGenerale >= 10 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $moyenneGenerale > 0 ? number_format($moyenneGenerale, 2) : '-' }}
                            </td>
                            
                            <!-- Rang -->
                            <td class="px-3 py-2 text-center text-sm font-semibold border-r border-gray-200
                                {{ $rang !== '-' ? 'text-purple-600' : 'text-gray-400' }}">
                                {{ $rang }}
                            </td>
                            
                            <!-- Bulletin -->
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

        <!-- Légende et statistiques -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Légende -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="font-medium text-gray-700 mb-3">Légende</h4>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                        <span>Moyenne ≥ 10/20</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded mr-2"></div>
                        <span>Moyenne < 10/20</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                        <span>Moyenne coefficientée</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-minus text-gray-400 mr-2 w-4 text-center"></i>
                        <span>Note non saisie</span>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 md:col-span-2">
                <h4 class="font-medium text-gray-700 mb-3">Statistiques de la classe</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $moyennes = array_filter(array_column($gradesData, 'moyenne_generale'));
                        $moyenneClasse = count($moyennes) > 0 ? round(array_sum($moyennes) / count($moyennes), 2) : 0;
                        $nombreAdmis = count(array_filter($moyennes, fn($m) => $m >= 10));
                        $tauxReussite = count($moyennes) > 0 ? round(($nombreAdmis / count($moyennes)) * 100, 1) : 0;
                        $meilleureMoyenne = count($moyennes) > 0 ? max($moyennes) : 0;
                    @endphp
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $moyenneClasse }}</div>
                        <div class="text-xs text-gray-500">Moy. classe</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $nombreAdmis }}</div>
                        <div class="text-xs text-gray-500">Admis</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $tauxReussite }}%</div>
                        <div class="text-xs text-gray-500">Taux réussite</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $meilleureMoyenne }}</div>
                        <div class="text-xs text-gray-500">Meilleure moy.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles pour le tableau responsive -->
<style>
    @media (max-width: 768px) {
        table {
            font-size: 0.75rem;
        }
        th, td {
            padding: 0.25rem 0.5rem;
        }
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
            overflow-x: scroll;
        }
    }
    
    table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    th {
        position: sticky;
        top: 0;
        background-color: #f9fafb;
        z-index: 10;
    }
    
    tr:nth-child(even) {
        background-color: #fafafa;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight des lignes au survol
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f0f9ff';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Tri optionnel des colonnes (si nécessaire)
    const headers = document.querySelectorAll('thead th[data-sortable]');
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-column');
            if (column) {
                sortTable(column);
            }
        });
    });
});
</script>
@endsection
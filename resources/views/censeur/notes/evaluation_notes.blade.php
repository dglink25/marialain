@extends('layouts.app')

@section('content')
@php
    $pageTitle = $titre_page;
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Messages flash -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700">{{ session('error') }}</span>
            </div>
        @endif

        <!-- En-tête principal -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $titre_page }}</h1>
                
                <!-- Informations détaillées -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="text-sm text-blue-700 mb-1">Classe</div>
                        <div class="text-lg font-semibold text-blue-900">{{ $classe->name }}</div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <div class="text-sm text-green-700 mb-1">Enseignant</div>
                        <div class="text-lg font-semibold text-green-900">
                            {{ $teacher ? $teacher->name . ' ' . $teacher->last_name : 'Non assigné' }}
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                        <div class="text-sm text-purple-700 mb-1">Coefficient</div>
                        <div class="text-lg font-semibold text-purple-900">
                            {{ $subjectPivot->coefficient ?? $subject->coefficient ?? 1 }}
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <div class="text-sm text-yellow-700 mb-1">Trimestre</div>
                        <div class="text-lg font-semibold text-yellow-900">{{ $trimestre }}</div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="border-t border-gray-200 pt-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total_etudiants'] }}</div>
                        <div class="text-sm text-gray-600">Étudiants</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['etudiants_notes'] }}</div>
                        <div class="text-sm text-gray-600">Avec notes</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $stats['moyenne_generale'] ? 'text-blue-600' : 'text-gray-400' }}">
                            {{ $stats['moyenne_generale'] ?? '-' }}
                        </div>
                        <div class="text-sm text-gray-600">Moyenne</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $stats['note_max'] ? 'text-purple-600' : 'text-gray-400' }}">
                            {{ $stats['note_max'] ?? '-' }}
                        </div>
                        <div class="text-sm text-gray-600">Note max</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des notes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-200">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">N°</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Prénom</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Genre</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Note</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">État</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($classe->students as $student)
                            @php
                                $grade = $gradesData[$student->id] ?? null;
                                $note = $grade['note'] ?? null;
                                $hasNote = $grade['has_note'] ?? false;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ strtoupper($student->last_name) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ ucfirst($student->first_name) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $student->gender == 'M' ? 'M' : 'F' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($hasNote && $note !== null)
                                        <div class="flex items-center">
                                            <span class="text-lg font-bold text-blue-600">{{ $note }}</span>
                                            <span class="ml-1 text-sm text-gray-500">/20</span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($hasNote && $note !== null)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Noté
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> En attente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($hasNote && $grade['updated_at'])
                                        {{ $grade['updated_at']->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Message si aucun élève -->
            @if($classe->students->count() === 0)
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun élève dans cette classe</h3>
                    <p class="text-gray-500">Les élèves apparaîtront ici une fois inscrits.</p>
                </div>
            @endif
        </div>

        <!-- Boutons d'action -->
        <div class="mt-8 flex flex-wrap gap-4 justify-between">
            <div>
                <a href="{{ route('censeur.classes.trimestre.matiere', [$classe->id, $trimestre]) }}" 
                   class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour aux matières
                </a>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('censeur.notes.export.pdf', [$classe->id, $trimestre, $subject->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-sm">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Toutes les notes
                </a>
                
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 shadow-sm">
                    <i class="fas fa-print mr-2"></i>
                    Imprimer
                </button>
            </div>
        </div>

        <!-- Légende -->
        <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-600">
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>Noté</span>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">
                        <i class="fas fa-clock"></i>
                    </span>
                    <span>En attente de notation</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Styles pour l'impression -->
<style>
    @media print {
        .bg-gray-50 { background-color: white !important; }
        .shadow-sm, .shadow-md { box-shadow: none !important; }
        .border { border-color: #ddd !important; }
        .rounded-xl { border-radius: 0 !important; }
        .hidden-print { display: none !important; }
        
        table { 
            width: 100% !important; 
            border-collapse: collapse !important;
        }
        th, td { 
            border: 1px solid #000 !important; 
            padding: 8px !important;
        }
    }
    
    @media (max-width: 768px) {
        table {
            font-size: 0.875rem;
        }
        .px-6 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    }
</style>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation d'apparition
        const elements = document.querySelectorAll('.bg-white');
        elements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.4s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Tri par note
        let sortDirection = 1;
        const sortByNote = () => {
            const table = document.querySelector('tbody');
            const rows = Array.from(table.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                const noteA = parseFloat(a.querySelector('td:nth-child(6)').textContent) || 0;
                const noteB = parseFloat(b.querySelector('td:nth-child(6)').textContent) || 0;
                return (noteA - noteB) * sortDirection;
            });
            
            rows.forEach(row => table.appendChild(row));
            sortDirection *= -1;
        };
        
        // Optionnel: ajouter un bouton de tri
        const noteHeader = document.querySelector('th:nth-child(6)');
        if (noteHeader) {
            noteHeader.style.cursor = 'pointer';
            noteHeader.innerHTML = 'Note <i class="fas fa-sort ml-1"></i>';
            noteHeader.addEventListener('click', sortByNote);
        }
    });
</script>
@endsection
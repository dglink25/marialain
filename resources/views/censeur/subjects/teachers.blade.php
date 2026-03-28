@extends('layouts.app')

@section('title', 'Enseignants de '.$subject->name)

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Liste des enseignants assignés à : <span class="font-semibold text-green-600">{{ $subject->name }}</span></h1>
                <p class="mt-1 text-sm text-gray-600">Matière enseignée</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-4">
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                    <p class="text-sm text-gray-600">Enseignants: <span class="font-medium">{{ $subject->teachers->unique('id')->count() }}</span></p>
                </div>
                <a href="{{ url()->previous() }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Messages d'alerte -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-lg p-3 mr-4">
                    <i class="fas fa-chalkboard-teacher text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-blue-600">Total enseignants</p>
                    <p class="text-2xl font-bold text-blue-800">{{ $subject->teachers->unique('id')->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-green-500 rounded-lg p-3 mr-4">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-green-600">Classes concernées</p>
                    @php
                        $totalClasses = 0;
                        foreach($subject->teachers->unique('id') as $teacher) {
                            $totalClasses += $teacher->classes ? $teacher->classes->count() : 0;
                        }
                    @endphp
                    <p class="text-2xl font-bold text-green-800">{{ $totalClasses }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-purple-500 rounded-lg p-3 mr-4">
                    <i class="fas fa-book text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-purple-600">Matière</p>
                    <p class="text-lg font-bold text-purple-800 truncate">{{ $subject->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="bg-white shadow rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                    Liste des enseignants assignés
                </h3>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Enseignants uniques : {{ $subject->teachers->unique('id')->count() }}
                </div>
            </div>
        </div>
        
        @if($subject->teachers->unique('id')->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enseignant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Informations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classes assignées</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($subject->teachers->unique('id') as $teacher)
                    @php
                        $teacherClasses = $teacher->classes ?? collect();
                        $hasSingleClass = $teacherClasses->count() === 1;
                    @endphp
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <!-- Numéro -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $loop->iteration }}</td>
                        
                        <!-- Enseignant -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white font-semibold text-sm">
                                        {{ substr($teacher->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $teacher->name }}</div>
                                    <div class="flex items-center text-xs text-gray-500">
                                        @if($teacher->gendre)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-100 text-blue-800 mr-2">
                                                <i class="fas fa-{{ $teacher->gendre == 'Homme' ? 'mars' : 'venus' }} text-xs mr-1"></i>
                                                {{ $teacher->gendre }}
                                            </span>
                                        @endif
                                        <span class="text-gray-400">
                                            <i class="fas fa-id-badge mr-1"></i>
                                            ID: {{ $teacher->id }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Informations de contact -->
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-envelope text-gray-400 mr-2 w-4"></i>
                                    <span class="truncate max-w-[180px]">{{ $teacher->email ?? '--' }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-phone text-gray-400 mr-2 w-4"></i>
                                    <span>{{ $teacher->phone ?? '--' }}</span>
                                </div>
                                @if($teacher->specialite)
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-graduation-cap text-gray-400 mr-2 w-4"></i>
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 rounded">{{ $teacher->specialite }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        
                        <!-- Classes assignées -->
                        <td class="px-6 py-4">
                            @if($teacherClasses->count() > 0)
                                <div class="space-y-1">
                                    <div class="flex flex-wrap gap-1 mb-1">
                                        @foreach($teacherClasses as $classe)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-green-100 to-green-50 text-green-800 border border-green-200">
                                                <i class="fas fa-door-open mr-1 text-xs"></i>
                                                {{ $classe->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-chart-bar mr-1"></i>
                                        {{ $teacherClasses->count() }} classe(s) au total
                                    </div>
                                </div>
                            @else
                                <div class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Aucune classe
                                    </span>
                                </div>
                            @endif
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <div class="flex flex-col space-y-3 min-w-[200px]">
                                <!-- Section Cahiers de texte -->
                                @if($teacherClasses->count() > 0)
                                    <div>
                                        <div class="text-xs font-semibold text-gray-700 mb-1 flex items-center">
                                            <i class="fas fa-book-open mr-1.5 text-green-600"></i>
                                            Cahiers de texte :
                                        </div>
                                        
                                        @if($hasSingleClass)
                                            <!-- Un seul bouton si une seule classe -->
                                            @php $classe = $teacherClasses->first(); @endphp
                                            <a href="{{ route('enseignants.cahier.matiere', ['teacher' => $teacher->id, 'classe' => $classe->id, 'subject' => $subject->id]) }}" 
                                               class="inline-flex items-center w-full justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 transition duration-200 mb-1">
                                                <i class="fas fa-book mr-1.5"></i>
                                                Voir cahier - {{ $classe->name }}
                                            </a>
                                        @else
                                            <!-- Boutons séparés pour chaque classe -->
                                            <div class="space-y-2">
                                                @foreach($teacherClasses as $classe)
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2 hover:bg-gray-100 transition duration-150">
                                                        <div class="flex items-center">
                                                            <i class="fas fa-door-open text-gray-400 mr-2 text-xs"></i>
                                                            <span class="text-xs text-gray-700">{{ $classe->name }}</span>
                                                        </div>
                                                        <a href="{{ route('enseignants.cahier.matiere', ['teacher' => $teacher->id, 'classe' => $classe->id, 'subject' => $subject->id]) }}" 
                                                           class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded transition duration-200">
                                                            <i class="fas fa-eye mr-1"></i>
                                                            Voir
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                
                                <!-- Bouton profil -->
                                <a href="{{ route('enseignants.show', $teacher->id) }}" 
                                   class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 transition duration-200">
                                    <i class="fas fa-user-tie mr-1.5"></i>
                                    Voir profil complet
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <div class="mx-auto w-16 h-16 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-user-slash text-gray-400 text-2xl"></i>
            </div>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Aucun enseignant assigné</h4>
            <p class="text-gray-600 mb-6">Aucun enseignant n'est actuellement assigné à cette matière.</p>
            <a href="{{ route('censeur.subjects.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour aux matières
            </a>
        </div>
        @endif
        
        <!-- Pied de table -->
        @if($subject->teachers->unique('id')->count() > 0)
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Affichage de <span class="font-medium">{{ $subject->teachers->unique('id')->count() }}</span> enseignant(s)
                </div>
                <div class="text-sm text-gray-500">
                    Dernière mise à jour : {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Styles additionnels -->
<style>
    tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .bg-gradient-to-r {
        background-size: 200% 200%;
    }
    
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
    
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out forwards;
        opacity: 0;
    }
    
    /* Animation pour les boutons */
    @keyframes buttonPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    a:hover, button:hover {
        animation: buttonPulse 0.3s ease;
    }
    
    /* Style pour les cartes de classes */
    .class-card {
        transition: all 0.2s ease;
    }
    
    .class-card:hover {
        transform: translateX(3px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<!-- Script pour les interactions -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation pour les lignes du tableau
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.05}s`;
            row.classList.add('animate-fadeIn');
        });
        
        // Effet de survol sur les boutons d'action
        const actionButtons = document.querySelectorAll('a, button');
        actionButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Animation pour les cartes de classes
        const classCards = document.querySelectorAll('.class-card');
        classCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
</script>

<!-- Animation CSS -->
<style>
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
    
    /* Styliser les séparateurs */
    tr:not(:last-child) td {
        border-bottom: 1px solid #e5e7eb;
    }
    
    /* Améliorer la visibilité des actions */
    .actions-container a, .actions-container button {
        transition: all 0.2s ease;
    }
    
    .actions-container a:hover, .actions-container button:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endsection
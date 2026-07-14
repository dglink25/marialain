@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Tableau de bord Directeur Maternelle et Primaire';
@endphp

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête du Dashboard -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Directeur Primaire</h1>
                <p class="mt-1 text-sm text-gray-600">Bienvenue, {{ $user->name }}. Gestion du cycle primaire.</p>
            </div>
            <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600">Année scolaire: <span class="font-medium">{{ $annee_academique->name }}</span></p>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Élèves du primaire -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-child text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Élèves primaire</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $primaryStudentsCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium"></span>
                    <span class="text-gray-500"></span>
                </div>
            </div>
        </div>

        <!-- Enseignants -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Enseignants</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $primaryTeacherCount }}</dd>
                        </dl>
                    </div>

                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium"> </span>
                    <span class="text-gray-500"></span>
                </div>
            </div>
        </div>

        <!-- Classes -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-school text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Classes</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $primaryClassCount }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium"></span>
                    <span class="text-gray-500"></span>
                </div>
            </div>
        </div>

    </div>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne de gauche -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Aperçu des classes -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="">Répartition des classes</h3>
                    <a href="{{ route('primaire.classe.classes') }}" class="text-sm text-blue-600 hover:text-blue-800">Voir tout</a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @forelse($classesRepartition as $classe)
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $classe->students_count }}</div>
                            <div class="text-sm font-medium text-gray-700">{{ $classe->name }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $classe->students_count }} élève{{ $classe->students_count > 1 ? 's' : '' }}
                            </div>
                        </div>
                        @empty
                        <div class="col-span-3 text-center text-gray-400 py-6">
                            <i class="fas fa-school text-2xl mb-2"></i>
                            <p class="text-sm">Aucune classe pour cette année</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Colonne de droite -->
        <div class="space-y-6">
            <!-- Actions Rapides -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="">Actions Rapides</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <a href="{{ route('primaire.enseignants.enseignants') }}" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition duration-200">
                            <i class="fas fa-chalkboard-teacher mr-3"></i>
                            <span>Gérer les enseignants</span>
                        </a>
                        <a href="{{ route('primaire.classe.classes') }}" class="flex items-center p-3 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition duration-200">
                            <i class="fas fa-school mr-3"></i>
                            <span>Gérer les classes</span>
                        </a>
                        <a href="{{ route('primaire.ecoliers.liste') }}" class="flex items-center p-3 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition duration-200">
                            <i class="fas fa-users mr-3"></i>
                            <span>Voir les écoliers</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition duration-200">
                            <i class="fas fa-envelope mr-3"></i>
                            <span>Inviter enseignants</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Animation pour les cartes */
    .bg-white {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .bg-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Animation pour les éléments de la liste */
    .flex.items-start {
        animation: fadeInUp 0.5s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Délais d'animation pour les éléments de liste */
    .flex.items-start:nth-child(1) { animation-delay: 0.1s; }
    .flex.items-start:nth-child(2) { animation-delay: 0.2s; }
    .flex.items-start:nth-child(3) { animation-delay: 0.3s; }
</style>

<script>
    // Animation des statistiques au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Effet de comptage pour les statistiques
        const stats = document.querySelectorAll('.text-lg.font-medium');
        stats.forEach(stat => {
            const target = parseInt(stat.textContent.replace(/,/g, ''));
            const duration = 2000;
            const steps = 60;
            const increment = target / steps;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current).toLocaleString();
                }
            }, duration / steps);
        });
    });
</script>
@endsection
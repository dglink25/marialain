@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Tableau de bord Secrétaire';
@endphp

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête du Dashboard -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Secrétaire </h1>
                <p class="mt-1 text-sm text-gray-600">Bienvenue, {{ $user->name }}. Voici un aperçu de vos activités.</p>
            </div>
            <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600">Aujourd'hui: <span class="font-medium">{{ now()->format('d/m/Y') }}</span></p>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Élèves inscrits -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-user-graduate text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Élèves inscrits</dt>
                            <dd class="text-lg font-medium text-gray-900">
                            @if(isset($studentsCount)) {{ $studentsCount }} 
                            @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Inscriptions en attente -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Inscriptions en attente</dt>
                            <dd class="text-lg font-medium text-gray-900">
                            @if(isset($pendingRegistrations))
                            {{ $pendingRegistrations }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reçus générés -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-receipt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Inscriptions validées</dt>
                            <dd class="text-lg font-medium text-gray-900">
                            @if(isset($validatedRegistrations))
                             {{ $validatedRegistrations }} </dd>
                            @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-coins text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total scolarité : </dt>
                            <dd class="text-lg font-medium text-gray-900">
                            @if(isset($totalFees))
                            {{ number_format($totalFees, 0, ',', ' ') }} FCFA</dd>
                            @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


        <!-- Colonne de droite -->
        <div class="space-y-6">

            <!-- Actions Rapides -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Actions Rapides</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <a href="{{ route('students.create') }}" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition duration-200">
                            <i class="fas fa-user-plus mr-3"></i>
                            <span>Inscrire un nouvel élève</span>
                        </a>
                        <a href="{{ route('students.unpaid') }}" class="flex items-center p-3 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition duration-200">
                            <i class="fas fa-bell mr-3"></i>
                            <span>Liste des impayés</span>
                        </a>
                        <a href="{{ route('admin.classes.index') }}" class="flex items-center p-3 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition duration-200">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            <span>Gérer les classes</span>
                        </a>
                        <a href="{{ route('admin.students.pending') }}" class="flex items-center p-3 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition duration-200">
                            <i class="fas fa-bell mr-3"></i>
                            <span>Inscrption en attente</span>
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
    // Animation des barres de progression au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Simuler l'animation des barres de progression
        setTimeout(() => {
            const progressBars = document.querySelectorAll('.bg-green-600, .bg-blue-600');
            progressBars.forEach(bar => {
                const currentWidth = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = currentWidth;
                }, 100);
            });
        }, 500);
    });
</script>
@endsection
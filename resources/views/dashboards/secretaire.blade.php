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
                            <dd class="text-lg font-medium text-gray-900">1,248</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium">+12%</span>
                    <span class="text-gray-500">depuis le mois dernier</span>
                </div>
            </div>
        </div>

        <!-- Paiements en attente -->
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Paiements en attente</dt>
                            <dd class="text-lg font-medium text-gray-900">42</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-red-600 font-medium">-5%</span>
                    <span class="text-gray-500">depuis hier</span>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Reçus générés</dt>
                            <dd class="text-lg font-medium text-gray-900">856</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium">+8%</span>
                    <span class="text-gray-500">ce mois-ci</span>
                </div>
            </div>
        </div>

        <!-- Solde total -->
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Solde total</dt>
                            <dd class="text-lg font-medium text-gray-900">12.5M FCFA</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium">+15%</span>
                    <span class="text-gray-500">cette année</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne de gauche -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Aperçu Financier -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Aperçu Financier</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Recettes du mois</h4>
                            <p class="text-2xl font-bold text-gray-900">4.2M FCFA</p>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-green-600 h-2.5 rounded-full" style="width: 75%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">75% de l'objectif mensuel</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Dépenses du mois</h4>
                            <p class="text-2xl font-bold text-gray-900">1.8M FCFA</p>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: 45%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">45% du budget alloué</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activités Récentes -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Activités Récentes</h3>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Voir tout</a>
                </div>
                <div class="p-4">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Paiement enregistré</p>
                                <p class="text-sm text-gray-500">Koffi Mensah a payé les frais de scolarité</p>
                                <p class="text-xs text-gray-400">Il y a 2 heures</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-user-plus text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Nouvelle inscription</p>
                                <p class="text-sm text-gray-500">Aïcha Bello inscrite en classe de 6ème</p>
                                <p class="text-xs text-gray-400">Il y a 5 heures</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                <i class="fas fa-bell text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Notification envoyée</p>
                                <p class="text-sm text-gray-500">Rappel de paiement envoyé à 15 parents</p>
                                <p class="text-xs text-gray-400">Aujourd'hui, 09:30</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <a href="#" class="flex items-center p-3 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition duration-200">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            <span>Enregistrer un paiement</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 transition duration-200">
                            <i class="fas fa-receipt mr-3"></i>
                            <span>Générer un reçu</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition duration-200">
                            <i class="fas fa-bell mr-3"></i>
                            <span>Envoyer une notification</span>
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
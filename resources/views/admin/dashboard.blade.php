@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Tableau de Bord - Fondateur';
@endphp

<!-- En-tête du Dashboard -->
<header class="bg-gradient-to-r from-blue-600 to-purple-700 text-white p-4 md:p-6 lg:p-8 rounded-2xl mb-6 md:mb-8 shadow-2xl" data-aos="fade-down">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2">Bienvenue, Fondateur !</h1>
            <p class="text-base sm:text-lg lg:text-xl opacity-90 mb-4 md:mb-0">Tableau de bord du Fondateur - CPEG MARIE-ALAIN</p>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="bg-white bg-opacity-20 px-3 py-2 rounded-full text-xs sm:text-sm">
                    <i class="fas fa-crown mr-1"></i>Statut: Fondateur
                </span>
                <span class="bg-white bg-opacity-20 px-3 py-2 rounded-full text-xs sm:text-sm">
                    <i class="fas fa-calendar-alt mr-1"></i>24/10/2023
                </span>
            </div>
        </div>
        <div class="bg-white bg-opacity-10 p-3 md:p-4 rounded-xl w-full md:w-auto">
            <p class="text-xs sm:text-sm">Année académique active</p>
            <p class="text-base sm:text-lg lg:text-lg font-semibold">2023-2024</p>
        </div>
    </div>
</header>

<!-- Statistiques Principales -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8" data-aos="fade-up">
    <!-- Élèves -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Élèves inscrits</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">1,248</p>
                <p class="text-xs text-gray-500 mt-1">
                    De la maternelle à la terminale
                </p>
            </div>
            <div class="bg-blue-100 p-2 md:p-3 rounded-full group-hover:bg-blue-200 transition">
                <i class="fas fa-user-graduate text-blue-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Enseignants -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Enseignants</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">86</p>
                <p class="text-xs text-gray-500 mt-1">Équipe pédagogique</p>
            </div>
            <div class="bg-green-100 p-2 md:p-3 rounded-full group-hover:bg-green-200 transition">
                <i class="fas fa-chalkboard-teacher text-green-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Classes -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Classes actives</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">42</p>
                <p class="text-xs text-gray-500 mt-1">Tous niveaux confondus</p>
            </div>
            <div class="bg-purple-100 p-2 md:p-3 rounded-full group-hover:bg-purple-200 transition">
                <i class="fas fa-school text-purple-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Années académiques -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-orange-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Années académiques</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">8</p>
                <p class="text-xs text-gray-500 mt-1">Historique complet</p>
            </div>
            <div class="bg-orange-100 p-2 md:p-3 rounded-full group-hover:bg-orange-200 transition">
                <i class="fas fa-calendar-alt text-orange-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Contenu Principal -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
    
    <!-- Gestion des Années Scolaires -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover-lift" data-aos="fade-right">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 md:px-6 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <h2 class="text-lg md:text-xl font-bold text-white">
                        <i class="fas fa-calendar-plus mr-2"></i>Gestion des Années Scolaires
                    </h2>
                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm text-white">
                        8 années
                    </span>
                </div>
            </div>
            <div class="p-4 md:p-6">
                <!-- Année Active -->
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                Active
                            </span>
                            <h3 class="text-base md:text-lg font-semibold text-gray-800 mt-2">2023-2024</h3>
                            <p class="text-xs md:text-sm text-gray-600">
                                Du 01/09/2023 au 30/06/2024
                            </p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-xl md:text-2xl font-bold text-green-600">1,248</p>
                            <p class="text-xs md:text-sm text-gray-600">Élèves inscrits</p>
                        </div>
                    </div>
                </div>

                <!-- Bouton Création -->
                <button class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-300 flex items-center justify-center group shine-effect text-sm md:text-base">
                    <i class="fas fa-plus-circle mr-2 group-hover:scale-110 transition"></i>Créer une Nouvelle Année Scolaire
                </button>

                <!-- Liste des Années -->
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-4 text-sm md:text-base">Historique des Années</h4>
                    <div class="space-y-3 max-h-48 md:max-h-60 overflow-y-auto">
                        <!-- Année 2022-2023 -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition gap-2">
                            <div>
                                <span class="font-medium text-gray-600 text-sm md:text-base">2022-2023</span>
                                <p class="text-xs text-gray-500">2022 - 2023</p>
                            </div>
                            <div class="flex space-x-2 self-end sm:self-auto">
                                <button class="text-blue-600 hover:text-blue-800 text-sm" title="Activer">
                                    <i class="fas fa-play-circle"></i>
                                </button>
                                <button class="text-gray-400 hover:text-gray-600 text-sm" title="Archiver">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Année 2021-2022 -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition gap-2">
                            <div>
                                <span class="font-medium text-gray-600 text-sm md:text-base">2021-2022</span>
                                <p class="text-xs text-gray-500">2021 - 2022</p>
                            </div>
                            <div class="flex space-x-2 self-end sm:self-auto">
                                <button class="text-blue-600 hover:text-blue-800 text-sm" title="Activer">
                                    <i class="fas fa-play-circle"></i>
                                </button>
                                <button class="text-gray-400 hover:text-gray-600 text-sm" title="Archiver">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Année 2020-2021 -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition gap-2">
                            <div>
                                <span class="font-medium text-gray-600 text-sm md:text-base">2020-2021</span>
                                <p class="text-xs text-gray-500">2020 - 2021</p>
                            </div>
                            <div class="flex space-x-2 self-end sm:self-auto">
                                <button class="text-blue-600 hover:text-blue-800 text-sm" title="Activer">
                                    <i class="fas fa-play-circle"></i>
                                </button>
                                <button class="text-gray-400 hover:text-gray-600 text-sm" title="Archiver">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-lg hover-lift h-full" data-aos="fade-left">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-4 md:px-6 py-4">
                <h2 class="text-lg md:text-xl font-bold text-white">
                    <i class="fas fa-bolt mr-2"></i>Actions Rapides
                </h2>
            </div>
            <div class="p-4 md:p-6">
                <div class="space-y-2 md:space-y-3">
                    <div class="flex items-center space-x-3 p-2 md:p-3 text-blue-600 hover:bg-blue-50 rounded-xl transition cursor-pointer text-sm md:text-base">
                        <i class="fas fa-cog text-base md:text-lg"></i>
                        <span>Paramètres Administration</span>
                    </div>
                    <div class="flex items-center space-x-3 p-2 md:p-3 text-green-600 hover:bg-green-50 rounded-xl transition cursor-pointer text-sm md:text-base">
                        <i class="fas fa-archive text-base md:text-lg"></i>
                        <span>Consulter les Archives</span>
                    </div>
                    <div class="flex items-center space-x-3 p-2 md:p-3 text-purple-600 hover:bg-purple-50 rounded-xl transition cursor-pointer text-sm md:text-base">
                        <i class="fas fa-chart-bar text-base md:text-lg"></i>
                        <span>Générer un Rapport</span>
                    </div>
                    <div class="flex items-center space-x-3 p-2 md:p-3 text-orange-600 hover:bg-orange-50 rounded-xl transition cursor-pointer text-sm md:text-base">
                        <i class="fas fa-users-cog text-base md:text-lg"></i>
                        <span>Gestion des Utilisateurs</span>
                    </div>
                    <div class="flex items-center space-x-3 p-2 md:p-3 text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer text-sm md:text-base">
                        <i class="fas fa-database text-base md:text-lg"></i>
                        <span>Sauvegarde Données</span>
                    </div>
                    <div class="flex items-center space-x-3 p-2 md:p-3 text-indigo-600 hover:bg-indigo-50 rounded-xl transition cursor-pointer text-sm md:text-base">
                        <i class="fas fa-file-alt text-base md:text-lg"></i>
                        <span>Rapports Annuels</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
    }
    
    /* Animation pour les icônes */
    .group:hover .group-hover\:scale-110 {
        transform: scale(1.1);
    }
    
    /* Effet de brillance sur les boutons */
    .shine-effect {
        position: relative;
        overflow: hidden;
    }
    
    .shine-effect::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(
            to bottom right,
            rgba(255,255,255,0) 0%,
            rgba(255,255,255,0.8) 50%,
            rgba(255,255,255,0) 100%
        );
        transform: rotate(30deg);
        transition: all 0.6s;
        opacity: 0;
    }
    
    .shine-effect:hover::before {
        opacity: 1;
        left: 100%;
    }

    /* Ajustements pour mobile */
    @media (max-width: 640px) {
        .max-h-48 {
            max-height: 12rem;
        }
    }

    @media (min-width: 641px) {
        .max-h-60 {
            max-height: 15rem;
        }
    }

    /* Éviter les espaces vides */
    .lg\:col-span-2,
    .lg\:col-span-1 {
        min-height: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 50,
            disable: window.innerWidth < 768
        });
    });

    window.addEventListener('resize', function() {
        AOS.refresh();
    });
</script>
@endpush
@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Tableau de bord Surveillant';
@endphp

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- En-tête du Dashboard -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Surveillant</h1>
                <p class="mt-1 text-sm text-gray-600">Bienvenue, {{ $user->name }}. Surveillance et discipline des élèves.</p>
            </div>
           <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600">Aujourd'hui: <span class="font-medium">{{ now()->format('d/m/Y') }}</span></p>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Élèves surveillés -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user-friends text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Élèves surveillés</dt>
                            <dd class="text-lg font-medium text-gray-900">1,248</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium">100%</span>
                    <span class="text-gray-500">présents aujourd'hui</span>
                </div>
            </div>
        </div>

        <!-- Incidents ce mois -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Incidents ce mois</dt>
                            <dd class="text-lg font-medium text-gray-900">12</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-red-600 font-medium">-3</span>
                    <span class="text-gray-500">vs mois dernier</span>
                </div>
            </div>
        </div>

        <!-- Retards aujourd'hui -->
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Retards aujourd'hui</dt>
                            <dd class="text-lg font-medium text-gray-900">8</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium">-60%</span>
                    <span class="text-gray-500">vs hier</span>
                </div>
            </div>
        </div>

        <!-- Absences -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-user-slash text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Absences aujourd'hui</dt>
                            <dd class="text-lg font-medium text-gray-900">5</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-green-600 font-medium">Justifiées: 4</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne de gauche -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Incidents récents -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="">Incidents Récents</h3>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Voir tout</a>
                </div>
                <div class="p-4">
                    <div class="space-y-4">
                        <div class="flex items-start p-3 bg-red-50 rounded-lg">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                <i class="fas fa-exclamation-circle text-red-600"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Bagarre dans la cour</p>
                                        <p class="text-sm text-gray-500">Élèves de 4ème A et 4ème B</p>
                                        <p class="text-xs text-gray-400">Aujourd'hui, 10:15</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Grave
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start p-3 bg-yellow-50 rounded-lg">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Retards répétés</p>
                                        <p class="text-sm text-gray-500">Kévin Adjobo - 3ème B</p>
                                        <p class="text-xs text-gray-400">Hier, 08:05</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Moyen
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start p-3 bg-blue-50 rounded-lg">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-ban text-blue-600"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Matériel interdit</p>
                                        <p class="text-sm text-gray-500">Téléphone confisqué en 5ème C</p>
                                        <p class="text-xs text-gray-400">22 Oct, 14:30</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Léger
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Points de surveillance -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="">Points de Surveillance</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-door-open text-green-600 mr-2"></i>
                                <span class="font-medium">Entrée principale</span>
                            </div>
                            <p class="text-sm text-gray-600">Contrôle des entrées/sorties</p>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                <span>Surveillant: Vous</span>
                            </div>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-utensils text-blue-600 mr-2"></i>
                                <span class="font-medium">Réfectoire</span>
                            </div>
                            <p class="text-sm text-gray-600">Heure du déjeuner</p>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                <span>Surveillant: M. Gbedjissi</span>
                            </div>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-basketball-ball text-purple-600 mr-2"></i>
                                <span class="font-medium">Cour de récréation</span>
                            </div>
                            <p class="text-sm text-gray-600">Récréations du matin</p>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                <span>Surveillant: Mme Adjo</span>
                            </div>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-book text-yellow-600 mr-2"></i>
                                <span class="font-medium">CDI/Bibliothèque</span>
                            </div>
                            <p class="text-sm text-gray-600">Étude et recherche</p>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                <span>Surveillant: M. Agossou</span>
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
                    <h3 class="">Actions Rapides</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <a href="#" class="flex items-center p-3 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition duration-200">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            <span>Signaler un incident</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition duration-200">
                            <i class="fas fa-clock mr-3"></i>
                            <span>Enregistrer un retard</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition duration-200">
                            <i class="fas fa-user-slash mr-3"></i>
                            <span>Déclarer une absence</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition duration-200">
                            <i class="fas fa-file-alt mr-3"></i>
                            <span>Rapport quotidien</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tâches en Attente -->
          
            <!-- Horaires de surveillance -->
            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="">Vos Horaires Aujourd'hui</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-2">
                            <div class="flex items-center">
                                <i class="fas fa-sun text-yellow-500 mr-3"></i>
                                <span>Matin</span>
                            </div>
                            <span class="text-sm font-medium">07:30 - 12:00</span>
                        </div>
                        <div class="flex justify-between items-center p-2">
                            <div class="flex items-center">
                                <i class="fas fa-utensils text-orange-500 mr-3"></i>
                                <span>Pause déjeuner</span>
                            </div>
                            <span class="text-sm font-medium">12:00 - 13:30</span>
                        </div>
                        <div class="flex justify-between items-center p-2">
                            <div class="flex items-center">
                                <i class="fas fa-cloud-sun text-blue-500 mr-3"></i>
                                <span>Après-midi</span>
                            </div>
                            <span class="text-sm font-medium">13:30 - 17:00</span>
                        </div>
                        <div class="flex justify-between items-center p-2 bg-green-50 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-door-closed text-green-500 mr-3"></i>
                                <span class="font-medium">Fermeture</span>
                            </div>
                            <span class="text-sm font-medium">17:00 - 17:30</span>
                        </div>
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
    
    /* Animation pour les incidents */
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
    
    /* Délais d'animation */
    .flex.items-start:nth-child(1) { animation-delay: 0.1s; }
    .flex.items-start:nth-child(2) { animation-delay: 0.2s; }
    .flex.items-start:nth-child(3) { animation-delay: 0.3s; }
    
    /* Badges de sévérité */
    .bg-red-100 { background-color: #fed7d7; }
    .bg-yellow-100 { background-color: #feebc8; }
    .bg-blue-100 { background-color: #bee3f8; }
</style>

<script>
    // Animation des statistiques au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Effet de comptage pour les statistiques
        const stats = document.querySelectorAll('.text-lg.font-medium');
        stats.forEach(stat => {
            const target = parseInt(stat.textContent.replace(/,/g, ''));
            const duration = 1500;
            const steps = 30;
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
        
        // Mise à jour de l'heure actuelle
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();
    });
</script>
@endsection
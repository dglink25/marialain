@extends('layouts.app')

@php
    $pageTitle = "Trimestres";
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gestion des Trimestres</h1>
                    <p class="text-lg text-gray-600 mt-2">Classe : {{ $classe->name }}</p>
                </div>
                <a href="{{ url()->previous() }}" 
                   class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour
                </a>
            </div>
        </div>
        
        <!-- Messages flash -->
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

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500 text-lg mt-1 mr-3"></i>
                </div>
                <div>
                    <h3 class="text-green-800 font-medium">Succès</h3>
                    <p class="text-green-700 text-sm mt-1">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Modal de notification -->
        <div id="loadingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay avec animation -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 backdrop-blur-sm" id="modalOverlay"></div>

                <!-- Centre le modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal content avec animation -->
                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
                    <div class="relative">
                        <!-- En-tête avec gradient -->
                        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-500 to-purple-600"></div>
                        
                        <div class="px-6 pt-8 pb-6">
                            <!-- Icon et titre -->
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 mb-6">
                                    <i class="fas fa-info-circle text-4xl text-blue-600"></i>
                                </div>
                                
                                <h3 class="text-2xl font-bold text-gray-900 mb-3" id="modal-title">
                                    Information importante
                                </h3>
                                
                                <div class="mt-4">
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-6 text-left">
                                        <div class="flex items-start">
                                            <i class="fas fa-clock text-yellow-600 text-xl mt-0.5 mr-3"></i>
                                            <div>
                                                <p class="text-sm text-yellow-800 leading-relaxed">
                                                    La page que vous tentez d'accéder pourrait prendre jusqu'à 
                                                    <strong class="font-semibold">5 minutes</strong> selon les performances de votre appareil.
                                                </p>
                                                <p class="text-sm text-yellow-800 mt-2">
                                                    Veuillez patienter pendant le chargement, s'il vous plaît.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Animation de chargement -->
                                    <div id="loadingAnimation" class="hidden mt-6">
                                        <div class="flex flex-col items-center space-y-4">
                                            <div class="relative">
                                                <div class="w-16 h-16 border-4 border-gray-200 rounded-full"></div>
                                                <div class="absolute top-0 left-0 w-16 h-16 border-4 border-blue-600 rounded-full animate-spin border-t-transparent"></div>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-sm font-medium text-gray-700">Chargement en cours...</p>
                                                <p class="text-xs text-gray-500 mt-1">Veuillez patienter</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons -->
                            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                                <button id="confirmButton" 
                                        class="w-full inline-flex justify-center items-center px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-md hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    D'accord
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <a href="#" 
               id="pointAnneeLink"
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl shadow-md hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 hover:shadow-lg">
                <i class="fas fa-calendar-check mr-3 text-lg"></i>
                Point de l'Année Académique
            </a>
        </div>

        <!-- Grille des trimestres -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($trimestres as $t)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300">
                <!-- En-tête de la carte -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white font-bold text-xl">{{ $t }}</span>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">Trimestre {{ $t }}</h2>
                    <p class="text-gray-500 text-sm mt-1">Période d'évaluation</p>
                </div>

                <!-- Boutons d'action -->
                <div class="space-y-3">
                    <a href="{{ route('teacher.classes.trimestres.eleves', [$classe->id, $t]) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200 hover:bg-yellow-100 hover:border-yellow-300 transition-colors duration-200">
                        <i class="fas fa-list-alt mr-3"></i>
                        Récapitulatif
                    </a>
                    
                    <a href="{{ route('censeur.classes.trimestre.matiere', [$classe->id, $t]) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-red-50 text-red-700 rounded-lg border border-red-200 hover:bg-red-100 hover:border-red-300 transition-colors duration-200">
                        <i class="fas fa-book mr-3"></i>
                        Matières
                    </a>

                    <a href="{{ route('censeur.classes.trimestre.points', [$classe->id, $t]) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 hover:border-green-300 transition-colors duration-200">
                        <i class="fas fa-chart-line mr-3"></i>
                        Voir le point des notes disponibles
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Message si aucun trimestre -->
        @if(count($trimestres) === 0)
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun trimestre disponible</h3>
            <p class="text-gray-500 mb-6">Les trimestres apparaîtront ici une fois configurés.</p>
        </div>
        @endif

        <!-- Informations supplémentaires -->
        <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-200">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 text-xl mt-1 mr-4"></i>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-2">Gestion des trimestres</h3>
                    <p class="text-blue-700 text-sm">
                        Consultez le récapitulatif des élèves pour avoir une vue d'ensemble des performances,
                        gérez les matières spécifiques à chaque trimestre, ou utilisez le 
                        <strong>Point de l'Année Académique</strong> pour un bilan complet avec moyennes et statuts de passage.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift:hover { transform: translateY(-2px); }
    
    /* Animation du modal */
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    .modal-content {
        animation: modalFadeIn 0.3s ease-out;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    
    .animate-pulse-slow {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.bg-white.rounded-xl');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(10px)';
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        const flashMessages = document.querySelectorAll('.bg-red-50, .bg-green-50');
        flashMessages.forEach(message => {
            message.style.opacity = '0';
            message.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                message.style.transition = 'all 0.5s ease';
                message.style.opacity = '1';
                message.style.transform = 'translateX(0)';
            }, 300);
        });

        // Gestion du modal pour le point de l'année académique
        const pointAnneeLink = document.getElementById('pointAnneeLink');
        const modal = document.getElementById('loadingModal');
        const confirmButton = document.getElementById('confirmButton');
        const loadingAnimation = document.getElementById('loadingAnimation');
        let isProcessing = false;
        let navigationTimeout = null;

        // URL cible
        const targetUrl = "{{ route('censeur.classes.point-annee', $classe->id) }}";

        pointAnneeLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Afficher le modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Réinitialiser l'état
            confirmButton.disabled = false;
            confirmButton.classList.remove('opacity-50', 'cursor-not-allowed');
            confirmButton.innerHTML = '<i class="fas fa-check-circle mr-2"></i> D\'accord';
            loadingAnimation.classList.add('hidden');
            isProcessing = false;
        });

        confirmButton.addEventListener('click', async function() {
            if (isProcessing) return;
            
            isProcessing = true;
            
            // Désactiver le bouton
            confirmButton.disabled = true;
            confirmButton.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Afficher l'animation de chargement
            loadingAnimation.classList.remove('hidden');
            
            // Changer le texte du bouton
            confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement...';
            
            // Simuler un délai minimum pour montrer l'animation
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Rediriger vers la page cible
            window.location.href = targetUrl;
        });

        // Fermer le modal si on clique sur l'overlay (optionnel, mais pas recommandé pour ce cas)
        document.getElementById('modalOverlay').addEventListener('click', function(e) {
            if (!isProcessing) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    });
</script>
@endsection
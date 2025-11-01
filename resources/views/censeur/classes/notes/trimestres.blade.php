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
        
        <!-- Messages flash améliorés -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg mt-1 mr-3"></i>
                </div>
                <div>
                    <h3 class="text-red-800 font-medium">Erreur</h3>
                    <p class="text-red-700 text-sm mt-1">{{ session('error') }}</p>
                </div>
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
            <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-cog mr-2"></i>
                Configurer les trimestres
            </button>
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
                        ou gérez les matières spécifiques à chaque trimestre pour un contrôle détaillé.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles additionnels -->
<style>
    .hover-lift:hover {
        transform: translateY(-2px);
    }
</style>

<!-- Script pour les animations -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation d'apparition progressive des cartes
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

        // Animation des messages flash
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
    });
</script>
@endsection
@extends('layouts.app')

@section('title', 'Erreur 500 - Problème interne')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <!-- En-tête -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-50 rounded-full mb-4">
                <i class="fas fa-server text-red-500 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Erreur 500</h1>
            <p class="text-gray-600 text-sm">Problème interne du serveur</p>
        </div>

        <!-- Message -->
        <div class="mb-6">
            <p class="text-gray-600 text-center text-sm">
                Une erreur technique est survenue. Notre équipe a été automatiquement informée.
                <br>Veuillez réessayer dans quelques instants.
            </p>
        </div>

        <!-- Détails techniques -->
        <div class="bg-gray-50 rounded p-4 mb-6 border border-gray-200">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tools text-orange-500 text-sm"></i>
                    </div>
                </div>
                <div class="text-left">
                    <h3 class="font-semibold text-gray-800 text-sm mb-1">Que faire ?</h3>
                    <p class="text-gray-600 text-xs leading-relaxed">
                        1. Revenez en arrière puis réessayez<br>
                        2. Rafraîchissez la page<br>
                        4. Réessayez dans 5 minutes<br>
                        5. Contactez le support si le problème persiste
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-2 mb-6">
            <button onclick="window.location.reload()" 
               class="w-full block text-center px-4 py-2.5 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700 transition-colors">
                <i class="fas fa-redo mr-2"></i>
                Rafraîchir la page
            </button>
            
            <a href="{{ url()->previous() }}" 
               class="w-full block text-center px-4 py-2.5 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Page précédente
            </a>
            
            <a href="{{ url('/') }}" 
               class="w-full block text-center px-4 py-2.5 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Accueil
            </a>
        </div>

        <!-- Support -->
        <div class="pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center mb-3">Support technique</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="mailto:cpegmariealain@gmail.com" 
                   class="inline-flex items-center justify-center gap-2 text-xs text-gray-600 hover:text-gray-800">
                    <i class="fas fa-envelope"></i>
                    <span>cpegmariealain@gmail.com</span>
                </a>
                <span class="hidden sm:block text-gray-300">|</span>
                <a href="https://wa.me/22994119476" target="_blank" 
                   class="inline-flex items-center justify-center gap-2 text-xs text-green-600 hover:text-green-700">
                    <i class="fab fa-whatsapp"></i>
                    <span>+229 94 11 94 76</span>
                </a>
            </div>
        </div>

        <!-- ID d'erreur et horodatage -->
        <div class="mt-4 text-center">
            <div class="text-xs text-gray-400">
                <span>ID: ERR-{{ now()->format('His') }}</span>
                <span class="mx-2">•</span>
                <span>{{ now()->format('d/m/Y H:i') }}</span>
            </div>
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-400">
                    <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                    Équipe technique notifiée
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
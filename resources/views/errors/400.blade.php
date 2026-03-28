@extends('layouts.app')

@section('title', 'Erreur 400 - Requête incorrecte')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <!-- En-tête -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-50 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-amber-500 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Erreur 400</h1>
            <p class="text-gray-600 text-sm">Requête incorrecte</p>
        </div>

        <!-- Message -->
        <div class="mb-6">
            <p class="text-gray-600 text-center text-sm">
                La requête envoyée est malformée ou contient des données invalides.
                <br>Veuillez vérifier les informations saisies.
            </p>
        </div>

        <!-- Causes possibles -->
        <div class="bg-gray-50 rounded p-4 mb-6 border border-gray-200">
            <h3 class="font-semibold text-gray-800 text-sm mb-2">Causes possibles :</h3>
            <ul class="space-y-1.5">
                <li class="flex items-start gap-2 text-xs text-gray-600">
                    <i class="fas fa-times text-red-400 mt-0.5"></i>
                    <span>Données de formulaire invalides</span>
                </li>
                <li class="flex items-start gap-2 text-xs text-gray-600">
                    <i class="fas fa-times text-red-400 mt-0.5"></i>
                    <span>URL mal formée</span>
                </li>
                <li class="flex items-start gap-2 text-xs text-gray-600">
                    <i class="fas fa-times text-red-400 mt-0.5"></i>
                    <span>Session expirée (cookies invalides)</span>
                </li>
                <li class="flex items-start gap-2 text-xs text-gray-600">
                    <i class="fas fa-times text-red-400 mt-0.5"></i>
                    <span>Validation de sécurité échouée</span>
                </li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="space-y-2 mb-6">
            <button onclick="window.location.reload()" 
               class="w-full block text-center px-4 py-2.5 bg-amber-500 text-white text-sm font-medium rounded hover:bg-amber-600 transition-colors">
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

        <!-- Horodatage -->
        <div class="mt-4 text-center">
            <span class="text-xs text-gray-400">
                <i class="far fa-clock mr-1"></i>
                {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
</div>
@endsection
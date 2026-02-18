@extends('layouts.app')

@section('title', 'Page non trouvée')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <!-- En-tête -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-50 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Erreur 404</h1>
            <p class="text-gray-600 text-sm">Page non trouvée</p>
        </div>

        <!-- Message -->
        <div class="mb-6">
            <p class="text-gray-600 text-center text-sm">
                La page que vous recherchez n'existe pas ou a été déplacée.
                <br>Veuillez vérifier l'URL que vous tentez d'accéder est correcte ou contacter le support de la plateforme.
            </p>
        </div>

        <!-- URL recherchée -->
        <div class="bg-gray-50 rounded p-3 mb-6 border border-gray-200">
            <div class="flex items-center gap-2">
                <i class="fas fa-link text-gray-400 text-xs"></i>
                <span class="text-xs text-gray-500 truncate">{{ url()->current() }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-2">
            <a href="{{ url('/') }}" 
               class="w-full block text-center px-4 py-2.5 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Retour à l'accueil
            </a>
            
            <a href="{{ url('/home') }}" 
               class="w-full block text-center px-4 py-2.5 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Page précédente
            </a>
        </div>

        <!-- Support -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center mb-3">Support technique</p>
            <div class="flex flex-col sm:flex-row gap-2 justify-center">
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
                {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
</div>
@endsection
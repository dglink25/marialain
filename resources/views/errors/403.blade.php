@extends('layouts.app')

@section('title', 'Accès refusé')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <!-- En-tête -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-50 rounded-full mb-4">
                <i class="fas fa-lock text-red-500 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Accès refusé</h1>
            <p class="text-gray-600 text-sm">Vous n'avez pas les autorisations nécessaires</p>
        </div>

        <!-- Message -->
        <div class="mb-6">
            <p class="text-gray-600 text-center text-sm">
                Cette page est réservée aux utilisateurs disposant des droits spécifiques.
                <br>Veuillez contacter l'administrateur si vous devez y accéder.
            </p>
        </div>

        <!-- Avertissement -->
        <div class="bg-red-50 border border-red-200 rounded p-3 mb-6">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-circle text-red-500 text-sm mt-0.5"></i>
                <p class="text-xs text-gray-600">
                    Les tentatives d'accès non autorisées sont enregistrées à des fins de sécurité.
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-2 mb-6">
            <a href="{{ url()->previous() }}" 
               class="w-full block text-center px-4 py-2.5 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour en arrière
            </a>
            
            <a href="{{ url('/') }}" 
               class="w-full block text-center px-4 py-2.5 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Accueil
            </a>
            
            @guest
            <a href="{{ route('login') }}" 
               class="w-full block text-center px-4 py-2.5 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Se connecter
            </a>
            @endguest
        </div>

        <!-- Support -->
        <div class="pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center mb-3">Demander l'accès</p>
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

        <!-- ID de sécurité -->
        <div class="mt-4 text-center">
            <span class="text-xs text-gray-400">
                <i class="fas fa-shield-alt mr-1"></i>
                SEC-{{ now()->format('Y m d H i s') }}
            </span>
        </div>
    </div>
</div>
@endsection
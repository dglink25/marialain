@extends('layouts.app')
@php
    $pageTitle = "Classe";
@endphp
@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête simplifié -->
        <div class="mb-8 text">
            <h1 class="text-2xl font-bold text-gray-700 mb-2">Gérez les autorisations et accédez aux informations des classes</h1>
            <p class="text-gray-600"></p>
        </div>

        <!-- Grille des classes élégante -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($classes as $classe)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300">
                <!-- En-tête de la classe -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $classe->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $classe->level ?? 'Secondaire' }}</p>
                </div>

                <!-- Boutons d'action -->
                <div class="space-y-3">
                    <a href="{{ route('censeur.permissions.index', $classe->id) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 hover:border-green-300 transition-colors duration-200">
                        <i class="fas fa-user-shield mr-3"></i>
                        Autorisations
                    </a>
                    <a href="{{ route('censeur.classes.trimestres', $classe->id) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-colors duration-200">
                        <i class="fas fa-folder-open mr-3"></i>
                        Accéder
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Message si aucune classe -->
        @if(count($classes) === 0)
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chalkboard text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune classe disponible</h3>
            <p class="text-gray-500 mb-6">Les classes apparaîtront ici une fois créées.</p>
            <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Créer une classe
            </button>
        </div>
        @endif
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
        const cards = document.querySelectorAll('.bg-white');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endsection
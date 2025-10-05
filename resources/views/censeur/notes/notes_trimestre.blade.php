@extends('layouts.app')

@section('content')
@php
    $pageTitle = "Notes";
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Messages flash améliorés -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700">{{ session('error') }}</span>
            </div>
        @endif
        
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span class="text-green-700">{{ session('success') }}</span>
            </div>
        @endif

        <!-- En-tête avec bouton retour intelligent -->
        <div class="mb-8">
            <div class="flex items-center justify-end mb-4">

                <!-- Bouton voir toutes les notes -->
                <a href="{{ route('censeur.classes.notes.list', [$classe->id, $trimestre, $subject]) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 shadow-sm">
                    <i class="fas fa-list mr-2"></i>
                    Voir toutes les notes
                </a>
            </div>

            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Fiche de Notes</h1>
                <div class="flex flex-wrap items-center justify-center gap-4 mt-2 text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-book mr-2"></i>
                        <span>{{ $subject->name }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span>{{ $classe->name }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span>Trimestre {{ $trimestre }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grille des évaluations -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            
            <!-- Interrogations -->
            @for($i = 1; $i <= 5; $i++)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-300">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-pencil-alt text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Interrogation {{ $i }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Évaluation écrite</p>
                    </div>
                    
                    <a href="{{ route('teacher.classes.notes.read', [$classe->id, 'interrogation', $i, $trimestre]) }}"
                       class="w-full flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-colors duration-200 font-medium">
                        <i class="fas fa-eye mr-2"></i>
                        Consulter
                    </a>
                </div>
            @endfor

            <!-- Devoirs -->
            @for($i = 1; $i <= 2; $i++)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-300">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-file-alt text-green-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Devoir {{ $i }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Travail noté</p>
                    </div>
                    
                    <a href="{{ route('teacher.classes.notes.read', [$classe->id, 'devoir', $i, $trimestre]) }}"
                       class="w-full flex items-center justify-center px-4 py-2 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 hover:border-green-300 transition-colors duration-200 font-medium">
                        <i class="fas fa-eye mr-2"></i>
                        Consulter
                    </a>
                </div>
            @endfor

        </div>

        <!-- Section informations -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mt-1 mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-2">Interrogations</h3>
                        <p class="text-blue-700 text-sm">
                            Les interrogations sont des évaluations courtes permettant de vérifier 
                            l'acquisition des connaissances sur des points précis du programme.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-green-600 text-xl mt-1 mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-green-900 mb-2">Devoirs</h3>
                        <p class="text-green-700 text-sm">
                            Les devoirs sont des travaux plus conséquents évaluant la capacité 
                            à mobiliser les connaissances sur des sujets plus complexes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
       <br>
         <!-- Bouton retour qui respecte l'historique -->
                <button onclick="history.back()" 
                        class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour
                </button>
    </div>
</div>

<!-- Styles additionnels -->
<style>
    .hover-lift:hover {
        transform: translateY(-2px);
    }
</style>

<!-- Script pour les animations et gestion du retour -->
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

        // Gestion avancée du bouton retour
        const backButton = document.querySelector('button[onclick="history.back()"]');
        
        // Alternative si l'historique est vide
        backButton.addEventListener('click', function(e) {
            // Si pas d'historique, on va vers une page par défaut
            if (history.length <= 1) {
                e.preventDefault();
                // Redirection vers la page précédente logique (liste des matières par exemple)
                window.location.href = "{{ route('censeur.classes.trimestre.matiere', [$classe->id, $trimestre]) }}";
            }
        });

        // Raccourci clavier Alt + ← pour le retour
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key === 'ArrowLeft') {
                history.back();
            }
        });
    });
</script>
@endsection
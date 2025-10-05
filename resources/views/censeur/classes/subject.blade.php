@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Liste des Matières';
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
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

        <!-- En-tête avec bouton retour -->
        <div class="mb-8">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold text-gray-900">Gestion des Matières</h1>
                    <p class="text-gray-600 mt-2">Classe : {{ $classe->name }} • Trimestre {{ $trimestre }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-3">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">{{ $subjects->count() }}</span> matière(s)
                    </p>
                </div>
            </div>
        </div>

        <!-- Grille des matières -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($subjects as $subject)
                @php
                    $coef = $subject->pivot->coefficient ?? null;
                    $initial = preg_match('/\d+/', $subject->name, $m) ? $m[0] : strtoupper(substr($subject->name, 0, 2));
                @endphp

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-300">
                    <!-- En-tête de la matière -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-white font-bold text-xl">{{ $initial }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $subject->name }}</h3>
                        <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                            <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                                Coef: {{ $subject->coefficient }}
                            </span>
                            @if($coef)
                                <span class="bg-green-50 text-green-700 px-2 py-1 rounded-full">
                                    Actuel: {{ $coef }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="space-y-3">
                        <!-- Bouton coefficient -->
                        <button 
                            onclick="toggleForm({{ $subject->id }})"
                            class="w-full flex items-center justify-center px-4 py-2 rounded-lg border transition-colors duration-200 font-medium
                                {{ $coef 
                                    ? 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 hover:border-blue-300' 
                                    : 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100 hover:border-orange-300' }}">
                            <i class="fas {{ $coef ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $coef ? 'Modifier coefficient' : 'Définir coefficient' }}
                        </button>

                        <!-- Formulaire coefficient -->
                        <div id="form-{{ $subject->id }}" class="hidden bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <form method="POST" action="{{ route('censeur.subjects.coefficient', [$classe->id, $subject->id]) }}" class="space-y-3">
                                @csrf
                                <div class="flex items-center space-x-2">
                                    <input type="number" 
                                           name="coefficient" 
                                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           value="{{ $coef ?? '' }}" 
                                           placeholder="Nouveau coefficient"
                                           required 
                                           min="1" 
                                           max="20">
                                    <button type="submit" 
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" 
                                            onclick="toggleForm({{ $subject->id }})"
                                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Bouton consulter notes -->
                        <a href="{{ route('censeur.classes.notes', [$classe->id, $trimestre, $subject->id]) }}" 
                           class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Consulter les notes
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Message si aucune matière -->
        @if($subjects->count() === 0)
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-book-open text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune matière disponible</h3>
            <p class="text-gray-500 mb-6">Les matières apparaîtront ici une fois configurées.</p>
            <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Ajouter une matière
            </button>
        </div>
        @endif
    </div>
    <br>
    <br><!-- Bouton retour intelligent -->
            <div class="mb-4">
                <button onclick="smartBack()" 
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

@endsection

@section('scripts')
<script>
    function toggleForm(id) {
        const form = document.getElementById('form-' + id);
        if (form) {
            form.classList.toggle('hidden');
        }
    }

    // Fonction de retour intelligente
    function smartBack() {
        // Vérifie si on peut revenir en arrière dans l'historique
        if (document.referrer && document.referrer !== window.location.href) {
            history.back();
        } else {
            // Redirection vers une page par défaut (liste des trimestres)
            window.location.href = "{{ route('censeur.classes.trimestres', $classe->id) }}";
        }
    }

    // Animation d'apparition des cartes
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

        // Raccourci clavier Alt + ← pour le retour
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key === 'ArrowLeft') {
                smartBack();
            }
        });
    });
</script>
@endsection
@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Tableau de Bord - Fondateur';
@endphp

<!-- En-tête du Dashboard -->
<header class="bg-gradient-to-r from-blue-600 to-purple-700 text-white p-4 md:p-6 lg:p-8 rounded-2xl mb-6 md:mb-8 shadow-2xl" data-aos="fade-down">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2">Bienvenue, Fondateur !</h1>
            <p class="text-base sm:text-lg lg:text-xl opacity-90 mb-4 md:mb-0">Tableau de bord du Fondateur - CPEG MARIE-ALAIN</p>
            
        </div>
        <div class="bg-white bg-opacity-10 p-3 md:p-4 rounded-xl w-full md:w-auto">
            <p class="text-xs sm:text-sm">Année académique active</p>
            <p class="text-base sm:text-lg lg:text-lg font-semibold">{{ $activeYear ? $activeYear->name : 'Aucune active' }}</p>
        </div>
    </div>
</header>

<!-- Statistiques Principales -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8" data-aos="fade-up">
    <!-- Élèves -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Élèves inscrits</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">{{ $studentsCount }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    De la maternelle à la terminale
                </p>
            </div>
            <div class="bg-blue-100 p-2 md:p-3 rounded-full group-hover:bg-blue-200 transition">
                <i class="fas fa-user-graduate text-blue-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Enseignants -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Enseignants</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">{{ $teachersCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Équipe pédagogique</p>
            </div>
            <div class="bg-green-100 p-2 md:p-3 rounded-full group-hover:bg-green-200 transition">
                <i class="fas fa-chalkboard-teacher text-green-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Classes -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Classes actives</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">{{ $classesCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Tous niveaux confondus</p>
            </div>
            <div class="bg-purple-100 p-2 md:p-3 rounded-full group-hover:bg-purple-200 transition">
                <i class="fas fa-school text-purple-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Années académiques -->
    <div class="bg-white p-4 md:p-6 rounded-xl shadow-lg border-l-4 border-orange-500 hover:shadow-xl transition-all duration-300 group hover-lift">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Années académiques</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900 mt-1 md:mt-2">{{ $academicYearsCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Historique complet</p>
            </div>
            <div class="bg-orange-100 p-2 md:p-3 rounded-full group-hover:bg-orange-200 transition">
                <i class="fas fa-calendar-alt text-orange-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>



@endsection

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
    }
    
    /* Animation pour les icônes */
    .group:hover .group-hover\:scale-110 {
        transform: scale(1.1);
    }
    
    /* Effet de brillance sur les boutons */
    .shine-effect {
        position: relative;
        overflow: hidden;
    }
    
    .shine-effect::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(
            to bottom right,
            rgba(255,255,255,0) 0%,
            rgba(255,255,255,0.8) 50%,
            rgba(255,255,255,0) 100%
        );
        transform: rotate(30deg);
        transition: all 0.6s;
        opacity: 0;
    }
    
    .shine-effect:hover::before {
        opacity: 1;
        left: 100%;
    }

    /* Ajustements pour mobile */
    @media (max-width: 640px) {
        .max-h-48 {
            max-height: 12rem;
        }
    }

    @media (min-width: 641px) {
        .max-h-60 {
            max-height: 15rem;
        }
    }

    /* Éviter les espaces vides */
    .lg\:col-span-2,
    .lg\:col-span-1 {
        min-height: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 50,
            disable: window.innerWidth < 768
        });
    });

    window.addEventListener('resize', function() {
        AOS.refresh();
    });
</script>
@endpush
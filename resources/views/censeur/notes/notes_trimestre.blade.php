@extends('layouts.app')

@section('content')
@php
    $pageTitle = "Notes";

    // ID matière sécurisé (relation ou direct)
    $subjectId = $subject->subject_id ?? $subject->id;

    // Coefficient par défaut
    $coefficient = $subject->coefficient ?? 1;
@endphp

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Messages flash --}}
        @if(session('error'))
            <div class="mb-6 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-700 px-5 py-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg mr-3"></i>
                    <span class="text-red-700">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-100 border-l-4 border-green-500 text-green-700 px-5 py-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-lg mr-3"></i>
                    <span class="text-green-700">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- En-tête --}}
        <div class="mb-8">
            <!-- Barre d'actions principale - TOUS LES BOUTONS EN HAUT -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Groupe de gauche : navigation -->
                    <div class="flex flex-wrap gap-3">

                        <a href="{{ route('censeur.classes.trimestre.matiere', [$classe->id, $trimestre]) }}"
                           class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all duration-200 font-medium shadow-sm hover:shadow transform hover:scale-105">
                            <i class="fas fa-list mr-2"></i>
                            Retour aux matières
                        </a>

                        <a href="{{ route('censeur.classes.notes.list', [$classe->id, $trimestre, $subjectId]) }}"
                           class="inline-flex items-center px-4 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all duration-200 font-medium shadow-sm hover:shadow transform hover:scale-105">
                            <i class="fas fa-table mr-2"></i>
                            Voir toutes les notes
                        </a>
                    </div>

                    <!-- Groupe de droite : exports -->
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('censeur.notes.export.excel', [
                                'classId'   => $classe->id,
                                'trimestre' => $trimestre,
                                'subjectId' => $subjectId
                            ]) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl hover:from-emerald-700 hover:to-green-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg transform hover:scale-105">
                            <i class="fas fa-file-excel mr-2 text-lg"></i>
                            Export Excel
                        </a>

                        <a href="{{ route('censeur.notes.export.pdf', [
                                'classId'   => $classe->id,
                                'trimestre' => $trimestre,
                                'subjectId' => $subjectId
                            ]) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl hover:from-red-700 hover:to-rose-700 transition-all duration-200 font-medium shadow-md hover:shadow-lg transform hover:scale-105">
                            <i class="fas fa-file-pdf mr-2 text-lg"></i>
                            Export PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations de la page -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg mb-4">
                    <i class="fas fa-file-alt text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">Fiche de Notes</h1>

                <div class="flex flex-wrap justify-center gap-4 mt-2">
                    <div class="flex items-center px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-200">
                        <i class="fas fa-book text-purple-500 mr-2"></i>
                        <span class="text-gray-700">{{ $subject->name }}</span>
                        <span class="ml-2 px-2 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg">
                            Coef : {{ $coefficient }}
                        </span>
                    </div>

                    <div class="flex items-center px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-200">
                        <i class="fas fa-users text-blue-500 mr-2"></i>
                        <span class="text-gray-700">{{ $classe->name }}</span>
                    </div>

                    <div class="flex items-center px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-200">
                        <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                        <span class="text-gray-700">Trimestre {{ $trimestre }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grille des évaluations --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-8">
            
            {{-- Interrogations --}}
            @for($i = 1; $i <= 5; $i++)
                <div class="group bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1" style="animation: fadeInUp 0.5s ease-out both; animation-delay: {{ ($i - 1) * 0.05 }}s;">
                    <div class="h-1.5 bg-gradient-to-r from-blue-500 to-cyan-500"></div>
                    <div class="p-6">
                        <div class="text-center mb-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                                <span class="text-blue-600 font-black text-2xl">{{ $i }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Interrogation {{ $i }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-pen-alt mr-1 text-gray-400"></i>
                                Évaluation écrite
                            </p>
                        </div>

                        <a href="{{ route('censeur.evaluation.notes.view', [
                                'classId'   => $classe->id,
                                'subjectId' => $subjectId,
                                'type'      => 'interrogation',
                                'sequence'  => $i,
                                'trimestre' => $trimestre
                            ]) }}"
                           class="action-btn w-full flex items-center justify-center px-4 py-2.5 bg-blue-50 text-blue-700 rounded-xl border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-all duration-200 font-medium group-hover:shadow-sm">
                            <i class="fas fa-eye mr-2"></i>
                            Consulter les notes
                            <i class="fas fa-chevron-right ml-2 text-xs opacity-0 group-hover:opacity-100 transform translate-x-0 group-hover:translate-x-1 transition-all"></i>
                        </a>
                    </div>
                </div>
            @endfor

            {{-- Devoirs --}}
            @for($i = 1; $i <= 2; $i++)
                <div class="group bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1" style="animation: fadeInUp 0.5s ease-out both; animation-delay: {{ (4 + $i) * 0.05 }}s;">
                    <div class="h-1.5 bg-gradient-to-r from-green-500 to-emerald-500"></div>
                    <div class="p-6">
                        <div class="text-center mb-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                                <span class="text-green-600 font-black text-2xl">{{ $i }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Devoir {{ $i }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-tasks mr-1 text-gray-400"></i>
                                Travail noté
                            </p>
                        </div>

                        <a href="{{ route('censeur.evaluation.notes.view', [
                                'classId'   => $classe->id,
                                'subjectId' => $subjectId,
                                'type'      => 'devoir',
                                'sequence'  => $i,
                                'trimestre' => $trimestre
                            ]) }}"
                           class="action-btn w-full flex items-center justify-center px-4 py-2.5 bg-green-50 text-green-700 rounded-xl border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-200 font-medium group-hover:shadow-sm">
                            <i class="fas fa-eye mr-2"></i>
                            Consulter les notes
                            <i class="fas fa-chevron-right ml-2 text-xs opacity-0 group-hover:opacity-100 transform translate-x-0 group-hover:translate-x-1 transition-all"></i>
                        </a>
                    </div>
                </div>
            @endfor

        </div>

        <!-- Information supplémentaire en bas -->
        <div class="mt-10 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100">
            <div class="flex items-start">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-1">📊 Gestion des notes</h3>
                    <p class="text-blue-800 text-sm">
                        Cliquez sur "Consulter les notes" pour saisir ou modifier les notes des élèves.
                        Utilisez les boutons d'export pour télécharger toutes les notes au format Excel ou PDF.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .hover-lift:hover {
        transform: translateY(-2px);
    }
    
    .action-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .action-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .action-btn:active::before {
        width: 300px;
        height: 300px;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-slide-down {
        animation: slideDown 0.4s ease-out;
    }
    
    /* Animation d'apparition des cartes */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    // Animation d'apparition progressive des cartes
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.group');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.05}s`;
        });
    });
</script>
@endsection
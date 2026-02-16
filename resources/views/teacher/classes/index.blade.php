@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Classes';
@endphp

<div class="container mx-auto px-4 py-8 max-w-7xl">
    {{-- En-tête --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-600 p-3 rounded-xl shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900">Mes classes</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $classes->count() }} classe(s) • {{ auth()->user()->name }}
                    </p>
                </div>
            </div>
            
            {{-- Bouton épreuves --}}
            <a href="{{ route('teacher.exams.index') }}" 
               class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Mes épreuves
            </a>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Classes</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $classes->count() }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Matières</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $classes->sum(function($c) { return $c->subjects->count(); }) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @if ($classes->count() > 0)
        {{-- Grille de classes --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($classes as $class)
                <div class="bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all">
                    {{-- En-tête de carte --}}
                    <div class="px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-600 p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $class->name }}</h2>
                        </div>
                    </div>

                    {{-- Matières enseignées --}}
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Matières enseignées</h3>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                {{ $class->subjects->count() }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach($class->subjects as $subject)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="font-medium text-gray-900">{{ $subject->name }}</p>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex flex-wrap gap-2">
                                        <strong>
                                            <a style="font-size: 15px;" href="{{ route('teacher.cahier.history.subject', [$class->id, $subject->id]) }}" 
                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                    Cahier de texte
                                            </a>
                                        </strong>

                                        <strong>
                                            <a style="font-size: 15px;" href="{{ route('teacher.classes.notes.trimestres.subject', [$class->id, $subject->id]) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                    </svg>
                                                    Notes
                                            </a>
                                        </strong>

                                        <strong>
                                            <a style="font-size: 15px;" href="{{ route('teacher.exams.create', ['class_id' => $class->id, 'subject_id' => $subject->id]) }}"
                                                class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white border border-gray-300 rounded text-xs font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Nouvelle Épreuve
                                            </a>
                                        </strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Actions générales --}}
                        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('teacher.classes.students', $class->id) }}"
                               class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Élèves
                            </a>

                            <a href="{{ route('teacher.classes.timetable', $class->id) }}"
                               class="inline-flex items-center justify-center px-3 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Emploi du temps
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- État vide --}}
        <div class="text-center py-16">
            <div class="max-w-md mx-auto">
                <div class="bg-gray-50 w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune classe assignée</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Vous n'intervenez dans aucune classe pour le moment. Contactez l'administration pour obtenir des affectations.
                </p>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 inline-flex items-start text-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm">Vous serez notifié dès qu'une classe vous sera assignée</span>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    /* Transitions douces */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 200ms;
    }
    
    /* Bordures cohérentes */
    .border-gray-200 {
        border-color: #e5e7eb;
    }
    
    .border-gray-300 {
        border-color: #d1d5db;
    }
    
    /* Ombres subtiles */
    .shadow-sm {
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }
    
    .hover\:shadow-md:hover {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    
    /* Espacement */
    .container {
        max-width: 80rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation simple des cartes au scroll
    const cards = document.querySelectorAll('.grid > div');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        observer.observe(card);
    });
});
</script>

@endsection
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- En-tête avec animation --}}
    <div class="relative mb-10">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-between items-center">
            <div class="flex items-center space-x-4 bg-white pr-8">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-3 rounded-2xl shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Mes Classes
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $classes->count() }} classe(s) assignée(s) • {{ auth()->user()->name }}
                    </p>
                </div>
            </div>
            
            {{-- Bouton vers la liste des épreuves --}}
            <div class="hidden lg:flex items-center space-x-4 bg-white pl-8">
                <a href="{{ route('teacher.exams.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Mes épreuves
                </a>
            </div>
        </div>
    </div>

    {{-- Statistiques rapides améliorées --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Classes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $classes->count() }}</p>
                </div>
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Matières</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $classes->sum(function($c) { return $c->subjects->count(); }) }}</p>
                </div>
                <div class="bg-green-100 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @if ($classes->count() > 0)
        {{-- Grille de classes --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 auto-rows-fr">
            @foreach($classes as $class)
                <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden border border-gray-100">
                    {{-- En-tête de carte avec dégradé --}}
                    <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-5 border-b border-gray-100">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-2.5 rounded-xl shadow-md group-hover:shadow-lg transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">
                                            {{ $class->name }}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Matières enseignées --}}
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Mes matières
                            </h3>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                                {{ $class->subjects->count() }} matière(s)
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach($class->subjects as $subject)
                                <div class="relative bg-gray-50 rounded-xl p-4 hover:bg-white border border-transparent hover:border-indigo-200 transition-all duration-200">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-indigo-400 to-purple-500 rounded-l-xl"></div>
                                    
                                    <div class="ml-3">
                                        <div class="flex items-center justify-between mb-3">
                                            <p class="font-semibold text-gray-800 flex items-center">
                                                <span class="text-indigo-600 mr-2">📘</span>
                                                {{ $subject->name }}
                                            </p>
                                        </div>

                                        {{-- Actions matières --}}
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('teacher.cahier.history.subject', [$class->id, $subject->id]) }}" 
                                               class="inline-flex items-center px-3 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg text-amber-700 text-xs font-medium transition-all hover:shadow-md group">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                                Cahier
                                            </a>

                                            <a href="{{ route('teacher.classes.notes.trimestres.subject', [$class->id, $subject->id]) }}"
                                               class="inline-flex items-center px-3 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg text-blue-700 text-xs font-medium transition-all hover:shadow-md group">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                Notes
                                            </a>

                                            {{-- LIEN VERS LA PAGE DE CRÉATION D'ÉPREUVE (remplace le modal) --}}
                                            <a href="{{ route('teacher.exams.create', ['class_id' => $class->id, 'subject_id' => $subject->id]) }}"
                                               class="inline-flex items-center px-3 py-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg text-purple-700 text-xs font-medium transition-all hover:shadow-md group">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Nouvelle épreuve
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Actions générales de la classe --}}
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <a href="{{ route('teacher.classes.students', $class->id) }}"
                               class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl text-sm font-medium transition-all shadow-md hover:shadow-lg group">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Élèves
                            </a>

                            <a href="{{ route('teacher.classes.timetable', $class->id) }}"
                               class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-xl text-sm font-medium transition-all shadow-md hover:shadow-lg group">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        <div class="text-center py-16 px-4">
            <div class="bg-gradient-to-br from-gray-50 to-white rounded-3xl p-12 max-w-2xl mx-auto border border-gray-200 shadow-xl">
                <div class="bg-gradient-to-br from-indigo-100 to-purple-100 w-24 h-24 rounded-3xl mx-auto flex items-center justify-center mb-6 animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Aucune classe assignée</h3>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">
                    Vous n'intervenez dans aucune classe pour le moment. Contactez l'administration pour obtenir des affectations.
                </p>
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 inline-flex items-center text-yellow-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm">Vous serez notifié dès qu'une classe vous sera assignée</span>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- SUPPRESSION COMPLÈTE DU MODAL --}}
{{-- TOUT LE CODE DU MODAL A ÉTÉ SUPPRIMÉ --}}

{{-- Styles simplifiés --}}
<style>
    .auto-rows-fr > * {
        height: fit-content;
    }

    /* Animation des cartes */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .group {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    /* Responsive design */
    @media (max-width: 640px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        h1 {
            font-size: 1.875rem;
        }
        
        .grid {
            gap: 1rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des cartes au scroll
    const cards = document.querySelectorAll('.group');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});

// Fonction de toast (gardée pour les notifications éventuelles)
function showToast(message, type = 'info') {
    let toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        `;
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: white;
        border-left: 4px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.3s ease-out;
        max-width: 24rem;
    `;

    let icon = '';
    if (type === 'success') icon = '✅';
    else if (type === 'error') icon = '❌';
    else icon = 'ℹ️';

    toast.innerHTML = `
        <span style="font-size: 1.25rem;">${icon}</span>
        <span style="color: #1f2937; font-size: 0.875rem; font-weight: 500;">${message}</span>
        <button onclick="this.parentElement.remove()" style="margin-left: auto; color: #6b7280;">✕</button>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 300);
        }
    }, 5000);
}

// Styles pour les toasts
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
`;
document.head.appendChild(style);
</script>
@endsection
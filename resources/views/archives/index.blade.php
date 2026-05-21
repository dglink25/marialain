@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Archives';
@endphp

<div class="bg-white p-6 rounded-xl shadow-sm">
    {{-- En-tête avec animation ----------------------------------------------------}}
    <div class="mb-8 animate-fadeIn">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-archive text-blue-600 text-lg"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Archives des années académiques</h1>
        </div>
        <p class="text-sm text-gray-500 ml-14 flex items-center gap-2">
            <span class="inline-block w-1 h-1 bg-blue-500 rounded-full"></span>
            Consultez les données historiques par année académique
            <span class="inline-block w-1 h-1 bg-blue-500 rounded-full"></span>
        </p>
    </div>

    @if($archives->isEmpty())
        <div class="text-center py-16 animate-fadeIn">
            <div class="inline-block p-6 bg-gray-50 rounded-full mb-4">
                <i class="fas fa-box-open text-5xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 text-lg">Aucune archive disponible</p>
            <p class="text-gray-400 text-sm mt-2">Les années académiques terminées apparaîtront ici</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($archives as $index => $year)
                <a href="{{ route('archives.show', $year->id) }}" 
                   class="group block bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2 border border-gray-100 overflow-hidden animate-cardReveal"
                   style="animation-delay: {{ $index * 0.05 }}s">
                    
                    <div class="p-5">
                        {{-- Icône et badge --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors duration-300">
                                <i class="fas fa-calendar-alt text-blue-500 text-xl group-hover:scale-110 transition-transform duration-300"></i>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded-full font-medium">
                                    <i class="fas fa-check-circle text-xs mr-1"></i> Archivé
                                </span>
                            </div>
                        </div>

                        {{-- Titre de l'année --}}
                        <h2 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors duration-300 mb-2">
                            {{ $year->name }}
                        </h2>

                        {{-- Description et statistiques --}}
                        <div class="space-y-2 mb-4">
                            <p class="text-sm text-gray-500 flex items-center gap-2">
                                <i class="fas fa-info-circle text-blue-400 text-xs"></i>
                                Année académique terminée
                            </p>
                            
                            @php
                                // Compter le nombre de classes UNIQUES associées à cette archive
                                $user = auth()->user();
                                $query = \App\Models\StudentAcademicRecord::where('academic_year_id', $year->id);
                                
                                // Filtrer par rôle de l'utilisateur en utilisant les relations existantes
                                if($user->role === 'teacher' || $user->teacher) {
                                    $query->whereHas('schoolClass.teacherAssignments', function($q) use ($user) {
                                        $q->where('teacher_id', $user->teacher->id ?? 0);
                                    });
                                } elseif($user->role === 'student' || $user->student) {
                                    $query->where('student_id', $user->student->id ?? 0);
                                }
                                
                                $userClassesCount = $query->distinct('class_id')->count('class_id');
                            @endphp
                            
                            <div class="flex items-center gap-3 pt-2">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-graduation-cap text-blue-400 text-xs"></i>
                                    <span class="text-xs text-gray-600">{{ $userClassesCount }} classe(s)</span>
                                </div>
                                <div class="w-px h-3 bg-gray-200"></div>
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-arrow-right text-blue-400 text-xs"></i>
                                    <span class="text-xs text-gray-600">Détails</span>
                                </div>
                            </div>
                        </div>

                        {{-- Bouton d'action --}}
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between text-blue-600 group-hover:text-blue-700 transition-colors duration-300">
                                <span class="text-sm font-medium">Consulter l'archive</span>
                                <i class="fas fa-chevron-right text-xs group-hover:translate-x-1 transition-transform duration-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        
        {{-- Compteur d'archives --}}
        <div class="mt-8 pt-6 border-t border-gray-100 text-center animate-fadeIn">
            <p class="text-sm text-gray-500">
                <i class="fas fa-database mr-1"></i> 
                {{ $archives->count() }} année(s) académique(s) archivée(s)
            </p>
        </div>
    @endif
</div>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes cardReveal {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.6s ease-out;
}

.animate-cardReveal {
    animation: cardReveal 0.4s ease-out forwards;
    opacity: 0;
}

/* Animation hover smooth pour les cartes */
.group:hover .group-hover\:translate-x-1 {
    transform: translateX(4px);
}

.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

/* Transition globale */
* {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Scrollbar personnalisée */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

@push('scripts')
<script>
// Animation supplémentaire pour les cartes au scroll
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.animate-cardReveal');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    cards.forEach(card => {
        observer.observe(card);
    });
});
</script>
@endpush
@endsection
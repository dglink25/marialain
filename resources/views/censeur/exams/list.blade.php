{{-- resources/views/censeur/exams/list.blade.php --}}
@extends('layouts.app')
@php
    $pageTitle = ucfirst($type) . "n°" . $numero . " T" . $trimestre . " - " . $classe->name;
@endphp
@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Navigation --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('censeur.exams.trimestre', ['classe' => $classe->id, 'trimestre' => $trimestre]) }}" 
                   class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Retour au trimestre {{ $trimestre }}
                </a>
                <span class="mx-2 text-gray-300">|</span>
                <a href="{{ route('censeur.exams.types', $classe->id) }}" 
                   class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left mr-1 text-xs"></i>
                    Tous les trimestres
                </a>
            </div>
            
            <div class="text-sm text-gray-500">
                Année: <span class="font-medium">{{ $activeYear->name ?? 'Non définie' }}</span>
            </div>
        </div>

        {{-- En-tête --}}
        <div class="mb-8">
            <div class="flex items-center gap-4">
                <div class="{{ $type === 'devoir' ? 'bg-orange-600' : 'bg-blue-600' }} rounded-lg p-3">
                    <i class="fas {{ $type === 'devoir' ? 'fa-pencil-alt' : 'fa-question-circle' }} text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        {{ ucfirst($type) }} n°{{ $numero }} - Trimestre {{ $trimestre }}
                    </h1>
                    <p class="text-gray-600 mt-1">
                        {{ $classe->name }} • {{ $activeYear->name }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Filtres rapides --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-sm font-medium text-gray-700">Filtrer par matière:</span>
                <select id="subjectFilter" class="text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Toutes les matières</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
                
                <span class="text-sm font-medium text-gray-700 ml-4">Trier par:</span>
                <select id="sortFilter" class="text-sm border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                    <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Plus récent</option>
                    <option value="ancien" {{ request('sort') == 'ancien' ? 'selected' : '' }}>Plus ancien</option>
                    <option value="matiere" {{ request('sort') == 'matiere' ? 'selected' : '' }}>Matière (A-Z)</option>
                </select>
            </div>
        </div>

        {{-- Liste des épreuves --}}
        @if($exams->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($exams as $exam)
                    <div class="bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="{{ $exam->type === 'devoir' ? 'bg-orange-100' : 'bg-blue-100' }} rounded-lg p-2">
                                        <i class="fas {{ $exam->type === 'devoir' ? 'fa-pencil-alt' : 'fa-question-circle' }} {{ $exam->type === 'devoir' ? 'text-orange-600' : 'text-blue-600' }}"></i>
                                    </div>
                                    <span class="text-xs font-medium px-2 py-1 bg-gray-100 text-gray-600 rounded">
                                        {{ $exam->subject->name }}
                                    </span>
                                </div>
                                <span class="text-xs bg-purple-50 text-purple-600 px-2 py-1 rounded">
                                    {{ $exam->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                            
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $exam->titre }}</h3>
                            
                            @if($exam->description)
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ $exam->description }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    {{ $exam->teacher->name ?? 'Enseignant' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-file-pdf text-red-500"></i>
                                    {{ $exam->file_name ? 'PDF' : 'Document' }}
                                </span>
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="{{ $exam->file_url }}" target="_blank" 
                                   class="flex-1 text-center px-3 py-2 bg-red-50 text-red-600 text-sm rounded-lg hover:bg-red-100 transition-colors">
                                    <i class="fas fa-download mr-1"></i>
                                    PDF
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $exams->withQueryString()->links() }}
            </div>
        @else
            {{-- État vide --}}
            <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas {{ $type === 'devoir' ? 'fa-pencil-alt' : 'fa-question-circle' }} text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune {{ $type }}</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Aucune {{ $type === 'devoir' ? 'devoir' : 'interrogation' }} n°{{ $numero }} n'a été trouvée pour le trimestre {{ $trimestre }}.
                </p>
                <a href="{{ route('censeur.exams.trimestre', ['classe' => $classe->id, 'trimestre' => $trimestre]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour au trimestre {{ $trimestre }}
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const subjectFilter = document.getElementById('subjectFilter');
    const sortFilter = document.getElementById('sortFilter');
    
    function applyFilters() {
        const url = new URL(window.location.href);
        
        if (subjectFilter.value) {
            url.searchParams.set('subject_id', subjectFilter.value);
        } else {
            url.searchParams.delete('subject_id');
        }
        
        if (sortFilter.value) {
            url.searchParams.set('sort', sortFilter.value);
        } else {
            url.searchParams.delete('sort');
        }
        
        window.location.href = url.toString();
    }
    
    subjectFilter.addEventListener('change', applyFilters);
    sortFilter.addEventListener('change', applyFilters);
});
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
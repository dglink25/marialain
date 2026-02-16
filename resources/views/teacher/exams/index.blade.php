{{-- resources/views/teacher/exams/index.blade.php --}}
@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Liste Epreuves';
@endphp

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header avec statistiques --}}
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur-xl opacity-70"></div>
                        <div class="relative bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                            Mes Épreuves
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Gérez toutes vos évaluations et sujets d'examen
                        </p>
                    </div>
                </div>
                
                <a href="{{ route('teacher.exams.create') }}" 
                   class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle épreuve
                </a>
            </div>

            {{-- Statistiques rapides --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-8">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total épreuves</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-indigo-100 rounded-lg p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Trimestre 1</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['trimestre1'] }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-lg p-3">
                            <span class="text-blue-600 font-bold">T1</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Trimestre 2</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['trimestre2'] }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-lg p-3">
                            <span class="text-yellow-600 font-bold">T2</span>
                        </div>
                    </div>
                </div>

                 <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Trimestre 3</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['trimestre3'] }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-lg p-3">
                            <span class="text-yellow-600 font-bold">T3</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Filtrer les épreuves</h2>
            </div>
            
            <form action="{{ route('teacher.exams.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trimestre</label>
                    <select name="trimestre" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 px-4 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                        <option value="">Tous les trimestres</option>
                        <option value="1" {{ request('trimestre') == 1 ? 'selected' : '' }}>Trimestre 1</option>
                        <option value="2" {{ request('trimestre') == 2 ? 'selected' : '' }}>Trimestre 2</option>
                        <option value="3" {{ request('trimestre') == 3 ? 'selected' : '' }}>Trimestre 3</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 px-4 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                        <option value="">Tous les types</option>
                        <option value="devoir" {{ request('type') == 'devoir' ? 'selected' : '' }}>Devoirs</option>
                        <option value="interrogation" {{ request('type') == 'interrogation' ? 'selected' : '' }}>Interrogations</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Classe</label>
                    <select name="class_id" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 px-4 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Matière</label>
                    <select name="subject_id" class="w-full rounded-xl border-gray-300 bg-gray-50 py-3 px-4 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                        <option value="">Toutes les matières</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-4 flex justify-end gap-3 mt-2">
                    <a href="{{ route('teacher.exams.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Réinitialiser
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Appliquer les filtres
                    </button>
                </div>
            </form>
        </div>

        {{-- Liste des épreuves --}}
        @if($exams->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($exams as $exam)
                    <div class="group bg-white rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden border border-gray-100">
                        {{-- En-tête avec badge de trimestre --}}
                        <div class="relative h-32 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-5">
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                    @if($exam->trimestre == 1)
                                        Trimestre 1
                                    @elseif($exam->trimestre == 2)
                                        Trimestre 2
                                    @else
                                        Trimestre 3
                                    @endif
                                </span>
                            </div>
                            
                            <div class="absolute bottom-3 left-5">
                                <span class="inline-flex items-center gap-1.5 text-white">
                                    <span class="bg-white/20 rounded-lg p-1.5 backdrop-blur-sm">
                                        @if($exam->type == 'devoir')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="text-sm font-medium">
                                        {{ $exam->type == 'devoir' ? 'Devoir' : 'Interrogation' }} n°{{ $exam->numero_evaluation }}
                                    </span>
                                </span>
                            </div>
                        </div>
                        
                        {{-- Contenu --}}
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                                        {{ $exam->titre }}
                                    </h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-700">
                                            {{ $exam->class->name }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-purple-100 text-xs font-medium text-purple-700">
                                            {{ $exam->subject->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($exam->description)
                                <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                                    {{ $exam->description }}
                                </p>
                            @endif
                            
                            {{-- Informations complémentaires --}}
                            <div class="flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-4">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $exam->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="flex border-t border-gray-100 divide-x divide-gray-100">
                            <a href="{{ route('teacher.exams.show', $exam->id) }}" 
                               class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Voir
                            </a>
                            
                            <a href="{{ $exam->file_url }}" target="_blank" 
                               class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium text-green-600 hover:bg-green-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                PDF
                            </a>
                            
                            <button onclick="deleteExam({{ $exam->id }})" 
                                    class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Supprimer
                            </button>
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
            <div class="text-center py-16 px-4">
                <div class="bg-gradient-to-br from-gray-50 to-white rounded-3xl p-12 max-w-2xl mx-auto border border-gray-200 shadow-xl">
                    <div class="bg-gradient-to-br from-indigo-100 to-purple-100 w-24 h-24 rounded-3xl mx-auto flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Aucune épreuve pour le moment</h3>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">
                        Commencez par créer votre première épreuve. Vous pourrez ensuite la partager avec vos élèves.
                    </p>
                    <a href="{{ route('teacher.exams.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Créer une épreuve
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Modal de confirmation de suppression --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"></div>
    
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-6 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                Confirmer la suppression
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Êtes-vous sûr de vouloir supprimer cette épreuve ? Cette action est irréversible.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                    <button type="button" id="confirmDelete" 
                            class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 sm:w-auto transition-colors">
                        Supprimer
                    </button>
                    <button type="button" onclick="closeDeleteModal()" 
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let examToDelete = null;

function deleteExam(examId) {
    examToDelete = examId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    examToDelete = null;
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (examToDelete) {
        fetch(`/teacher/exams/${examToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Épreuve supprimée avec succès', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast('Suppression en cours...');
            }
        })
        .catch(() => {
            showToast('Erreur de connexion', 'error');
        })
        .finally(() => {
            closeDeleteModal();
        });
    }
});

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
    if (type === 'success') icon = '';
    else if (type === 'error') icon = '';
    else icon = 'ℹ';

    toast.innerHTML = `
        <span style="font-size: 1.25rem;">${icon}</span>
        <span style="color: #1f2937; font-size: 0.875rem; font-weight: 500;">${message}</span>
        <button onclick="this.parentElement.remove()" style="margin-left: auto; color: #6b7280; hover:color: #374151;">
            ✕
        </button>
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

// Styles pour les animations
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
    
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
`;
document.head.appendChild(style);
</script>
@endsection
{{-- resources/views/censeur/exams/types.blade.php --}}
@extends('layouts.app')
@php
    $pageTitle = "Épreuves " . $classe->name;
@endphp
@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Messages d'alerte -->
            <div class="px-8 pt-6">
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-red-800 font-semibold">Veuillez corriger les erreurs suivantes :</h3>
                    </div>
                    <ul class="mt-2 text-red-700 list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-red-800">{{ session('error') }}</span>
                </div>
                @endif

                @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
                @endif
            </div>
        
        {{-- Navigation --}}
        <div class="mb-6">
            <a href="{{ route('censeur.notes.index') }}" 
               class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour aux classes
            </a>
        </div>

        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-600 rounded-lg p-3">
                        <i class="fas fa-pen-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Épreuves {{ $classe->name }}</h1>
                        <p class="text-gray-600 mt-1">{{ $activeYear->name ?? 'Année académique en cours' }}</p>
                    </div>
                </div>
                
                {{-- Nouveau bouton de téléchargement automatique --}}
                <div class="flex gap-3">
                    <button onclick="openDownloadModal()" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Générer copies
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal de sélection pour téléchargement --}}
        <div id="downloadModal" class="fixed inset-0 z-50 hidden">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Générer les copies</h3>
                            <button onclick="closeDownloadModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <form id="downloadForm" action="{{ route('censeur.exams.download-copies') }}" method="POST">
                            @csrf
                            <input type="hidden" name="classe_id" value="{{ $classe->id }}">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Trimestre</label>
                                    <select name="trimestre" required class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Sélectionner</option>
                                        <option value="1">Trimestre 1</option>
                                        <option value="2">Trimestre 2</option>
                                        <option value="3">Trimestre 3</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type d'épreuve</label>
                                    <select name="type" id="modal_type" required class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Sélectionner</option>
                                        <option value="devoir">Devoir</option>
                                        <option value="interrogation">Interrogation</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Numéro</label>
                                    <select name="numero" id="modal_numero" required class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Sélectionner d'abord le type</option>
                                    </select>
                                </div>
                                
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center gap-2 text-blue-800 mb-2">
                                        <i class="fas fa-info-circle"></i>
                                        <span class="text-sm font-medium">Informations</span>
                                    </div>
                                    <p class="text-sm text-blue-700">
                                        Effectif de la classe: <strong>{{ $studentCount }} élève(s)</strong>
                                    </p>
                                    <p class="text-xs text-blue-600 mt-1">
                                        Le PDF généré contiendra l'ensemble des Épreuves de toutes les professeurs ayant soumis une épreuve pour ces critères
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" onclick="closeDownloadModal()" 
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Annuler
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-download mr-2"></i>
                                    Générer PDF
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function openDownloadModal() {
            document.getElementById('downloadModal').classList.remove('hidden');
        }

        function closeDownloadModal() {
            document.getElementById('downloadModal').classList.add('hidden');
        }

        // Gestion des numéros selon le type
        document.getElementById('modal_type').addEventListener('change', function() {
            const type = this.value;
            const numeroSelect = document.getElementById('modal_numero');
            
            if (type === 'devoir') {
                numeroSelect.innerHTML = '<option value="">Choisir un numéro</option>' +
                    '<option value="1">Devoir n°1</option>' +
                    '<option value="2">Devoir n°2</option>';
            } else if (type === 'interrogation') {
                let options = '<option value="">Choisir un numéro</option>';
                for (let i = 1; i <= 5; i++) {
                    options += `<option value="${i}">Interrogation n°${i}</option>`;
                }
                numeroSelect.innerHTML = options;
            } else {
                numeroSelect.innerHTML = '<option value="">Sélectionner d\'abord le type</option>';
            }
        });
        </script>

        {{-- Sélection du trimestre --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach([1, 2, 3] as $trimestre)
                <a href="{{ route('censeur.exams.trimestre', ['classe' => $classe->id, 'trimestre' => $trimestre]) }}" 
                   class="group bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all hover:border-purple-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                            <span class="text-purple-700 font-bold text-lg">T{{ $trimestre }}</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 group-hover:text-purple-600 transition-colors"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 text-lg mb-1">Trimestre {{ $trimestre }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ $stats['par_trimestre'][$trimestre]['total'] ?? 0 }} épreuve(s)
                    </p>
                </a>
            @endforeach
        </div>

        {{-- Résumé des épreuves par trimestre --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach([1, 2, 3] as $trimestre)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <i class="fas fa-calendar-alt"></i>
                            Trimestre {{ $trimestre }}
                        </h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        {{-- Devoirs --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2 flex items-center gap-2">
                                <i class="fas fa-pencil-alt text-orange-500"></i>
                                Devoirs
                            </h4>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach([1, 2] as $numero)
                                    <a href="{{ route('censeur.exams.list', [
                                        'classe' => $classe->id, 
                                        'trimestre' => $trimestre,
                                        'type' => 'devoir', 
                                        'numero' => $numero
                                    ]) }}"
                                       class="flex items-center justify-between p-2 bg-gray-50 hover:bg-orange-50 rounded-lg border border-gray-200 hover:border-orange-200 transition-all group">
                                        <span class="text-sm text-gray-700">Devoir n°{{ $numero }}</span>
                                        <span class="text-xs bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded-full">
                                            {{ $stats['par_trimestre'][$trimestre]['devoirs'][$numero] ?? 0 }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Interrogations --}}
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2 flex items-center gap-2">
                                <i class="fas fa-question-circle text-blue-500"></i>
                                Interrogations
                            </h4>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach([1, 2, 3, 4, 5] as $numero)
                                    <a href="{{ route('censeur.exams.list', [
                                        'classe' => $classe->id, 
                                        'trimestre' => $trimestre,
                                        'type' => 'interrogation', 
                                        'numero' => $numero
                                    ]) }}"
                                       class="flex items-center justify-between p-2 bg-gray-50 hover:bg-blue-50 rounded-lg border border-gray-200 hover:border-blue-200 transition-all group">
                                        <span class="text-sm text-gray-700">Int. n°{{ $numero }}</span>
                                        <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">
                                            {{ $stats['par_trimestre'][$trimestre]['interrogations'][$numero] ?? 0 }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
@php
    $pageTitle = "Lecteur Notes";
@endphp

<div class="container mx-auto py-6">

    @auth
    @if (Auth::id() == 6)
        <!-- Vue Censeur -->
        <h1 class="text-2xl font-bold mb-6">
            Notes - {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
        </h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 animate-fade-in">
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 animate-fade-in">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $classe->students->count() }}</div>
                <div class="text-sm text-gray-600">Élèves total</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ $classe->students->filter(function($student) { 
                        $grade = $student->grades->first();
                        return $grade && $grade->value >= 10; 
                    })->count() }}
                </div>
                <div class="text-sm text-gray-600">Admis (≥10)</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-red-600">
                    {{ $classe->students->filter(function($student) { 
                        $grade = $student->grades->first();
                        return $grade && $grade->value < 10; 
                    })->count() }}
                </div>
                <div class="text-sm text-gray-600">Échec (<10)</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-600">
                    {{ $classe->students->filter(function($student) { 
                        return !$student->grades->first(); 
                    })->count() }}
                </div>
                <div class="text-sm text-gray-600">Sans note</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6 animate-fade-in">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">N°</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Nom</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Prénom</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Sexe</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Note</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Enrégistré</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Modifié</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classe->students as $student)
                        @php
                            $grade = $student->grades->first();
                            $noteValue = $grade->value ?? null;
                            $hasNote = !is_null($noteValue);
                        @endphp
                        <tr class="border-b hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 border text-gray-600">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 border font-medium">{{ $student->last_name }}</td>
                            <td class="px-4 py-3 border">{{ $student->first_name }}</td>
                            <td class="px-4 py-3 border">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                    {{ $student->gender === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                    {{ $student->gender }}
                                </span>
                            </td>
                            <td class="px-4 py-3 border">
                                @if($hasNote)
                                    <span class="inline-flex items-center justify-center w-12 px-2 py-1 rounded text-sm font-semibold
                                        {{ $noteValue >= 10 ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                        {{ $noteValue }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-12 px-2 py-1 rounded text-sm font-medium bg-gray-100 text-gray-600 border border-gray-300">
                                        --
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border text-sm text-gray-600">
                                @if($hasNote && $grade->created_at)
                                    {{ $grade->created_at->format('d/m/Y H:i') }}
                                @else
                                    --
                                @endif
                            </td>
                            <td class="px-4 py-3 border text-sm text-gray-600">
                                @if($hasNote && $grade->updated_at)
                                    {{ $grade->updated_at->format('d/m/Y H:i') }}
                                @else
                                    --
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bouton retour pour censeur -->
        <div class="mt-6 flex justify-center animate-fade-in">
            <button onclick="smartBack()" 
                    class="bg-gray-600 text-white px-5 py-2.5 rounded hover:bg-gray-700 transition-colors duration-200 inline-flex items-center font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </button>
        </div>

    @else
        <!-- Vue Enseignant -->
        <h1 class="text-2xl font-bold mb-6">
            Notes - {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
        </h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 animate-fade-in">
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 animate-fade-in">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $classe->students->count() }}</div>
                <div class="text-sm text-gray-600">Élèves total</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ $classe->students->filter(function($student) { 
                        $grade = $student->grades->first();
                        return $grade && $grade->value >= 10; 
                    })->count() }}
                </div>
                <div class="text-sm text-gray-600">Admis (≥10)</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-red-600">
                    {{ $classe->students->filter(function($student) { 
                        $grade = $student->grades->first();
                        return $grade && $grade->value < 10; 
                    })->count() }}
                </div>
                <div class="text-sm text-gray-600">Échec (<10)</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
                <div class="text-2xl font-bold text-gray-600">
                    {{ $classe->students->filter(function($student) { 
                        return !$student->grades->first(); 
                    })->count() }}
                </div>
                <div class="text-sm text-gray-600">Sans note</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6 animate-fade-in">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">N°</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Nom</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Prénom</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Sexe</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Note</th>
                            <th class="px-4 py-3 border text-left text-sm font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classe->students as $student)
                        @php
                            $grade = $student->grades->first();
                            $noteValue = $grade->value ?? null;
                            $hasNote = !is_null($noteValue);
                        @endphp
                        <tr class="border-b hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 border text-gray-600">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 border font-medium">{{ $student->last_name }}</td>
                            <td class="px-4 py-3 border">{{ $student->first_name }}</td>
                            <td class="px-4 py-3 border">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                    {{ $student->gender === 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                    {{ $student->gender }}
                                </span>
                            </td>
                            <td class="px-4 py-3 border">
                                @if($hasNote)
                                    <span class="inline-flex items-center justify-center w-12 px-2 py-1 rounded text-sm font-semibold
                                        {{ $noteValue >= 10 ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                        {{ $noteValue }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-12 px-2 py-1 rounded text-sm font-medium bg-gray-100 text-gray-600 border border-gray-300">
                                        --
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 border">
                                <a href="{{ route('teacher.classes.notes.edit', [$classe->id, $type, $num, $trimestre]) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm inline-flex items-center transition-colors duration-200">
                                   <i class="fas fa-edit mr-1 text-xs"></i>
                                   Modifier
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bouton retour en bas -->
        <div class="mt-6 flex  animate-fade-in">
            <button onclick="smartBack()" 
                    class="bg-gray-600 text-white px-5 py-2.5 rounded hover:bg-gray-700 transition-colors duration-200 inline-flex items-center font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour
            </button>
        </div>

    @endif
    @endauth

</div>

<!-- Script pour le bouton retour intelligent et animations -->
<script>
    function smartBack() {
        // Vérifie si on peut revenir en arrière dans l'historique
        if (document.referrer && document.referrer !== window.location.href) {
            history.back();
        } else {
            // Redirection vers une page par défaut
            window.location.href = "{{ route('teacher.classes.notes', [$classe->id, $trimestre]) }}";
        }
    }

    // Raccourci clavier Alt + ← pour le retour
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 'ArrowLeft') {
            smartBack();
        }
    });

    // Animation d'apparition progressive
    document.addEventListener('DOMContentLoaded', function() {
        // Animation pour les éléments avec la classe animate-fade-in
        const animatedElements = document.querySelectorAll('.animate-fade-in');
        
        animatedElements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.5s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animation spécifique pour les lignes du tableau
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-10px)';
            
            setTimeout(() => {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, 300 + (index * 50));
        });
    });
</script>

<style>
    .container {
        max-width: 1200px;
    }
    
    table {
        min-width: 800px;
    }
    
    /* Animation CSS */
    .animate-fade-in {
        animation: fadeInUp 0.6s ease-out;
    }
    
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
    
    /* Transition pour les hover */
    .transition-colors {
        transition: all 0.2s ease-in-out;
    }
    
    @media (max-width: 768px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        table {
            font-size: 0.875rem;
            min-width: 700px;
        }
        
        .text-2xl {
            font-size: 1.5rem;
        }
        
        .grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 640px) {
        table {
            min-width: 600px;
        }
        
        .grid-cols-4 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
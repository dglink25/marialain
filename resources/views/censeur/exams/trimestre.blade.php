{{-- resources/views/censeur/exams/trimestre.blade.php --}}
@extends('layouts.app')
@php
    $pageTitle = "Trimestre" . $trimestre . " - " . $classe->name;
@endphp
@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Navigation --}}
        <div class="mb-6">
            <a href="{{ route('censeur.exams.types', $classe->id) }}" 
               class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour aux trimestres
            </a>
        </div>

        {{-- En-tête --}}
        <div class="mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-purple-600 rounded-lg p-3">
                    <i class="fas fa-calendar-alt text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Trimestre {{ $trimestre }}</h1>
                    <p class="text-gray-600 mt-1">{{ $classe->name }} • {{ $activeYear->name }}</p>
                </div>
            </div>
        </div>

        {{-- Grille des évaluations --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- Devoirs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-orange-500 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-pencil-alt"></i>
                        Devoirs - Trimestre {{ $trimestre }}
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        @foreach([1, 2] as $numero)
                            <a href="{{ route('censeur.exams.list', [
                                'classe' => $classe->id, 
                                'trimestre' => $trimestre,
                                'type' => 'devoir', 
                                'numero' => $numero
                            ]) }}"
                               class="flex flex-col items-center p-4 bg-gray-50 hover:bg-orange-50 rounded-lg border border-gray-200 hover:border-orange-200 transition-all group">
                                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mb-2 group-hover:bg-orange-200">
                                    <span class="text-orange-700 font-bold">{{ $numero }}</span>
                                </div>
                                <span class="font-medium text-gray-700">Devoir n°{{ $numero }}</span>
                                <span class="text-xs text-gray-500 mt-1">Voir toutes les épreuves</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Interrogations --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-500 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <i class="fas fa-question-circle"></i>
                        Interrogations - Trimestre {{ $trimestre }}
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4">
                        @foreach([1, 2, 3, 4, 5] as $numero)
                            <a href="{{ route('censeur.exams.list', [
                                'classe' => $classe->id, 
                                'trimestre' => $trimestre,
                                'type' => 'interrogation', 
                                'numero' => $numero
                            ]) }}"
                               class="flex flex-col items-center p-3 bg-gray-50 hover:bg-blue-50 rounded-lg border border-gray-200 hover:border-blue-200 transition-all group">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-1 group-hover:bg-blue-200">
                                    <span class="text-blue-700 font-bold">{{ $numero }}</span>
                                </div>
                                <span class="text-xs text-center font-medium text-gray-700">Int. n°{{ $numero }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
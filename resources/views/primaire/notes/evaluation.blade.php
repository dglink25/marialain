@extends('layouts.app')

@section('content')
@php
    $isFormative = $type === 'formative';
    $couleur     = $isFormative ? 'emerald' : 'orange';
    $titre       = $isFormative ? 'Évaluation formative' : 'Évaluation sommative';
    $icon        = $isFormative ? 'fas fa-pencil-alt' : 'fas fa-file-alt';
@endphp

<div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">

    {{-- En-tête --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-{{ $couleur }}-600 rounded-xl flex items-center justify-center shadow-md">
                <i class="{{ $icon }} text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $titre }} — {{ $classe->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $annee_academique->name }}</p>
            </div>
        </div>
        <a href="{{ route('primaire.notes.index') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-medium text-sm px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            Retour
        </a>
    </div>

    {{-- Contenu --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <div class="w-20 h-20 bg-{{ $couleur }}-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="{{ $icon }} text-{{ $couleur }}-400 text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-700 mb-2">{{ $titre }}</h2>
        <p class="text-gray-500 text-sm mb-6">Classe : <strong>{{ $classe->name }}</strong> — {{ $classe->students->count() }} élève(s)</p>
        <div class="inline-flex items-center gap-2 bg-{{ $couleur }}-50 text-{{ $couleur }}-700 border border-{{ $couleur }}-200 rounded-xl px-5 py-3 text-sm font-medium">
            <i class="fas fa-tools text-xs"></i>
            Cette fonctionnalité est en cours de développement.
        </div>
    </div>

</div>
@endsection

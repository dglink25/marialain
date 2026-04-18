@extends('layouts.app')

@section('content')
@php
    $pageTitle = "Notes";

    // ID matière sécurisé (relation ou direct)
    $subjectId = $subject->subject_id ?? $subject->id;

    // Coefficient par défaut
    $coefficient = $subject->coefficient ?? 1;
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Messages flash --}}
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span class="text-green-700">{{ session('success') }}</span>
            </div>
        @endif

        {{-- En-tête --}}
        <div class="mb-8">
            <div class="flex items-center justify-end mb-4">
                <a href="{{ route('censeur.classes.notes.list', [
                        $classe->id,
                        $trimestre,
                        $subjectId
                    ]) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <i class="fas fa-list mr-2"></i>
                    Voir toutes les notes
                </a>
            </div>

            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">Fiche de Notes</h1>

                <div class="flex flex-wrap justify-center gap-4 mt-3 text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-book mr-2"></i>
                        {{ $subject->name }}
                        <span class="ml-2 px-2 py-1 bg-purple-100 text-purple-800 text-xs font-semibold rounded">
                            Coef : {{ $coefficient }}
                        </span>
                    </div>

                    <div class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        {{ $classe->name }}
                    </div>

                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Trimestre {{ $trimestre }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Grille des évaluations --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

            {{-- Interrogations --}}
            @for($i = 1; $i <= 5; $i++)
                <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition hover-lift">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-blue-600 font-bold text-xl">{{ $i }}</span>
                        </div>
                        <h3 class="text-lg font-semibold">Interrogation {{ $i }}</h3>
                        <p class="text-sm text-gray-500">Évaluation écrite</p>
                    </div>

                    <a href="{{ route('censeur.evaluation.notes.view', [
                            'classId'   => $classe->id,
                            'subjectId' => $subjectId,
                            'type'      => 'interrogation',
                            'sequence'  => $i,
                            'trimestre' => $trimestre
                        ]) }}"
                       class="w-full flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg border hover:bg-blue-100 transition font-medium">
                        <i class="fas fa-eye mr-2"></i>
                        Consulter les notes
                    </a>
                </div>
            @endfor

            {{-- Devoirs --}}
            @for($i = 1; $i <= 2; $i++)
                <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition hover-lift">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-green-600 font-bold text-xl">{{ $i }}</span>
                        </div>
                        <h3 class="text-lg font-semibold">Devoir {{ $i }}</h3>
                        <p class="text-sm text-gray-500">Travail noté</p>
                    </div>

                    <a href="{{ route('censeur.evaluation.notes.view', [
                            'classId'   => $classe->id,
                            'subjectId' => $subjectId,
                            'type'      => 'devoir',
                            'sequence'  => $i,
                            'trimestre' => $trimestre
                        ]) }}"
                       class="w-full flex items-center justify-center px-4 py-2 bg-green-50 text-green-700 rounded-lg border hover:bg-green-100 transition font-medium">
                        <i class="fas fa-eye mr-2"></i>
                        Consulter les notes
                    </a>
                </div>
            @endfor

        </div>

        {{-- Boutons bas --}}
        <div class="mt-10 flex flex-wrap justify-between gap-4">
            <button onclick="history.back()"
                    class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Retour
            </button>

            <div class="flex flex-wrap gap-3">

                {{-- ✅ NOUVEAU : Export Excel --}}
                <a href="{{ route('censeur.notes.export.excel', [
                        'classId'   => $classe->id,
                        'trimestre' => $trimestre,
                        'subjectId' => $subjectId
                    ]) }}"
                   class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-sm inline-flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Télécharger Excel
                </a>

                <a href="{{ route('censeur.notes.export.pdf', [
                        'classId'   => $classe->id,
                        'trimestre' => $trimestre,
                        'subjectId' => $subjectId
                    ]) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-sm inline-flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Télécharger le PDF
                </a>

                <a href="{{ route('censeur.classes.trimestre.matiere', [
                        $classe->id,
                        $trimestre
                    ]) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm inline-flex items-center">
                    <i class="fas fa-list mr-2"></i> Retour aux matières
                </a>

            </div>
        </div>

    </div>
</div>

<style>
    .hover-lift:hover {
        transform: translateY(-2px);
    }
</style>
@endsection
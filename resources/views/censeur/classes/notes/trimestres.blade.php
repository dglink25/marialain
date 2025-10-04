@extends('layouts.app')

@php
    $pageTitle = "Trimestre";
@endphp

@section('content')
<div class="container py-6">
{{-- Messages flash --}}
    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    <h1 class="text-2xl font-bold mb-6">Trimestres - Classe {{ $classe->name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($trimestres as $t)
            <div class="p-4 bg-white rounded shadow text-center">
                <h2 class="font-semibold text-lg">Trimestre {{ $t }}</h2>
                <a href="{{ route('teacher.classes.trimestres.eleves', [$classe->id, $t]) }}"
                   class="mt-3 inline-block bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                    Consulter le récapitulatif
                </a>
                
               <a href="{{ route('censeur.classes.trimestre.matiere', [$classe->id, $t]) }}"
                class="mt-3 inline-block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Matières
                </a>

            </div>
        @endforeach
    </div>
</div>
@endsection

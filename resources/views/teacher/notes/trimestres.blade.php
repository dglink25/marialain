@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Trimestres";
@endphp

<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Notes - Classe {{ $classe->name }}</h1>

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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([1, 2, 3] as $tri)
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <h2 class="text-lg font-semibold mb-4">Trimestre {{ $tri }}</h2>
                <a href="{{ route('teacher.classes.notes', [$classe->id, $tri]) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
                    Détails
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        <a href="{{ url()->previous() }}"
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           Retour
        </a>
    </div>
</div>
@endsection

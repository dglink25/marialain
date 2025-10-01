@extends('layouts.app')
@php
    $pageTitle = "Classes";
@endphp
@section('content')
<div class="container py-6">
    <h1 class="text-2xl font-bold mb-6">Classes du secondaire</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($classes as $classe)
            <div class="p-4 bg-white rounded shadow">
                <h2 class="font-semibold text-lg">{{ $classe->name }}</h2>
                <a href="{{ route('censeur.permissions.index', $classe->id) }}"
                   class="mt-3 inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Autorisations
                </a>
                <a href="{{ route('censeur.classes.trimestres', $classe->id) }}"
                   class="mt-3 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Accéder
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection

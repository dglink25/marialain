@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Notes';
@endphp
<div class="container mx-auto py-6">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold mb-6">Notes - Classe {{ $classe->name }}</h1>
        <a href="{{ route('teacher.classes.notes.list', $classe->id) }}"
        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-center">
        Voir toutes les notes
        </a>

    </div>

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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- Interrogations --}}
        @for($i = 1; $i <= 5; $i++)
            <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">
                    Interrogation {{ $i }}
                </h2>

                <div class="flex flex-col space-y-2">
                    <a href="{{ route('teacher.classes.notes.create', [$classe->id, 'interrogation', $i]) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-center font-medium transition">
                        Ajouter
                    </a>

                    <a href="{{ route('teacher.classes.notes.read', [$classe->id, 'interrogation', $i]) }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                        Lire
                    </a>
                </div>
            </div>
        @endfor

        {{-- Devoirs --}}
        @for($i = 1; $i <= 2; $i++)
            <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">
                    Devoir {{ $i }}
                </h2>

                <div class="flex flex-col space-y-2">
                    <a href="{{ route('teacher.classes.notes.create', [$classe->id, 'devoir', $i]) }}"
                       class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-center font-medium transition">
                        Ajouter
                    </a>

                    <a href="{{ route('teacher.classes.notes.read', [$classe->id, 'devoir', $i]) }}"
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                        Lire
                    </a>
                </div>
            </div>
        @endfor

        {{-- Calcul moyenne interro --}}
        <form method="POST" action="{{ route('teacher.classes.notes.calc.interro', $classe->id) }}"
              class="bg-white rounded-lg shadow p-4 flex flex-col">
            @csrf
            <h2 class="text-lg font-semibold text-gray-800 mb-3">
                Moyenne Interrogations
            </h2>
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-center font-medium transition">
                Calculer
            </button>
        </form>

        {{-- Calcul moyenne trimestrielle --}}
        <form method="POST" action="{{ route('teacher.classes.notes.calc.trimestre', $classe->id) }}"
              class="bg-white rounded-lg shadow p-4 flex flex-col">
            @csrf
            <h2 class="text-lg font-semibold text-gray-800 mb-3">
                Moyenne Trimestrielle
            </h2>
            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-center font-medium transition">
                Calculer
            </button>
        </form>
    </div>
</div>
@endsection

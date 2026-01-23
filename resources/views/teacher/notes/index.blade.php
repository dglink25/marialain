@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Notes";

use Illuminate\Support\Facades\Auth;
@endphp

@auth
    @if (Auth::id() == 4)
        <div class="container mx-auto py-6">

            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold mb-6">
                    Fiche de Notes @if(isset($subject)) {{ $subject->name }} @endif - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
                </h1>

                <a href="{{ route('teacher.classes.notes.list', [$classe->id, $subject->id, $trimestre]) }}"
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
                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'interrogation', $i, $trimestre]) }}"
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

                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'devoir', $i, $trimestre]) }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Lire
                            </a>
                        </div>
                    </div>
                @endfor

                
            </div>
        </div>
    @else
        <div class="container mx-auto py-6">

            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold mb-6">
                    Fiche de Notes @if(isset($subject)) {{ $subject->name }} @endif - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
                </h1>

                <a href="{{ route('teacher.classes.notes.list', [$classe->id, $subject->id, $trimestre]) }}"
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Interrogations --}}
                @for($i = 1; $i <= 5; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">
                            Interrogation {{ $i }}
                        </h2>

                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('teacher.classes.notes.create', [$classe->id, $subject->id, 'interrogation', $i, $trimestre]) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Ajouter
                            </a>


                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'interrogation', $i, $trimestre]) }}"
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
                            <a href="{{ route('teacher.classes.notes.create', [$classe->id, $subject->id, 'devoir', $i, $trimestre]) }}"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Ajouter
                            </a>

                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'devoir', $i, $trimestre]) }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Lire
                            </a>
                        </div>
                    </div>
                @endfor

                
            </div>
        </div>
    @endif    
@endauth

@endsection

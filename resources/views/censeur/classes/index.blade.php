@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Classes ';
@endphp


    @if(isset($error))
        <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg">
            {{ $error }}
        </div>
    @endif

    <h2 class="text-3xl font-extrabold text-between text-black-700 mb-8">
        Liste des Classes
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($classes as $class)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition duration-300 p-6 flex flex-col justify-between">
                <!-- En-tête avec icône -->
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg">
                        {{ strtoupper(substr($class->name, 0, 2)) }}
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">{{ $class->name }}</h3>
                </div>

                <!-- Actions -->
                <div class="space-y-3 mt-4">
                    <div class="flex items-center justify-between bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 transition">
                        <span>👥 Liste des élèves</span>
                        <a href="{{ route('censeur.classes.students', $class->id) }}" class="text-blue-600 font-medium hover:underline">Voir</a>
                    </div>

                    <div class="flex items-center justify-between bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 transition">
                        <span>🕒 Emploi du temps</span>
                        <a href="{{ route('censeur.classes.timetable', $class->id) }}" class="text-green-600 font-medium hover:underline">Consulter</a>
                    </div>

                    <div class="flex items-center justify-between bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 transition">
                        <span>🎓 Liste des enseignants</span>
                        <a href="{{ route('censeur.classes.teachers', $class->id) }}" class="text-purple-600 font-medium hover:underline">Accéder</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <br>
    <br>
    <br>
    <div class="d-flex justify-content-between">
        <button onclick="window.history.back()" 
            class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
            Retour
        </button>
    </div>

</body>
@endsection

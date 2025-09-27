@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Archives - {{ $year->name }}';
@endphp
<div class="bg-white p-6 rounded shadow">

    <h1 class="text-2xl font-bold mb-6">Archives - {{ $year->name }}</h1>

    @if($classes->isEmpty())
        <p class="text-gray-600">Aucune classe disponible pour cet utilisateur.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classes as $class)
                <div class="bg-white border rounded-2xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
                    <div class="p-6 flex flex-col h-full">
                        <!-- Nom de la classe -->
                        <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $class->name }}</h2>

                        <!-- Infos -->
                        <p class="text-sm text-gray-600 mb-1">
                            Effectif : <span class="font-medium">{{ $class->studentsCount }}</span>
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            Niveau : <span class="font-medium">{{ $class->entity->name ?? '-' }}</span>
                        </p>

                        <!-- Boutons -->
                        <div class="mt-auto flex flex-wrap gap-3">
                            <a href="{{ route('archives.classes.students', [$year->id, $class->id]) }}"
                            class="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Voir élèves
                            </a>

                            @if(auth()->id() == 6) 
                                <a href="{{ route('archives.class_timetables', [$year->id, $class->id]) }}" 
                                class="flex-1 text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    Emploi du temps
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    @endif

    <div class="mt-6">
        <a href="{{ route('archives.index') }}"
           class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
          Retour aux archives
        </a>
    </div>

</div>
@endsection

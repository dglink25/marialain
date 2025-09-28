@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Mes Classes';
@endphp
<div class="container mx-auto py-6">

    @if($classes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classes as $class)
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:shadow-md transition duration-300">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $class->name }}</h2>
                    <p class="text-sm text-gray-500">Classe attribuée à l’enseignant</p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('teacher.classes.notes', $class->id) }}"
                           class="inline-flex items-center bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition">
                            <i class="fas fa-clipboard-list mr-2"></i> Notes
                        </a>
                        <a href="{{ route('teacher.classes.students', $class->id) }}"
                           class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition">
                            <i class="fas fa-user-graduate mr-2"></i> Voir élèves
                        </a>
        
                        <a href="{{ route('teacher.classes.timetable', $class->id) }}"
                           class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                            <i class="fas fa-calendar-alt mr-2"></i> Emploi du temps
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500 shadow-sm">
            <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
            <p>Vous n’intervenez dans aucune classe.</p>
        </div>
    @endif
</div>
@endsection

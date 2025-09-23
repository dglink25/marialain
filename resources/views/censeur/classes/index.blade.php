@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">

    <h1 class="text-2xl font-bold mb-6">Liste des Classes</h1>

    @if(isset($error))
        <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
            {{ $error }}
        </div>
    @endif


    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($classes as $class)
            <div class="flex flex-col items-center bg-white shadow rounded-lg p-6">
                <!-- Cercle avec nom -->
                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                    <span class="text-gray-700 font-semibold">{{ $class->name }}</span>
                </div>

                <!-- Boutons -->
                <div class="flex flex-col space-y-2 w-full">
                    <a href="{{ route('censeur.classes.students', $class->id) }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded text-center hover:bg-blue-700">
                        Liste des élèves
                    </a>

                    <a href="{{ route('censeur.classes.timetable', $class->id) }}"
                       class="bg-green-600 text-white px-4 py-2 rounded text-center hover:bg-green-700">
                        Voir emploi du temps
                    </a>

                    <a href="{{ route('censeur.classes.teachers', $class->id) }}"
                       class="bg-purple-600 text-white px-4 py-2 rounded text-center hover:bg-purple-700">
                        Liste des enseignants
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

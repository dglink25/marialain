@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Mes Classes</h1>

    @if($classes->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
            Vous n'êtes encore assigné à aucune classe.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($classes as $class)
                <div class="flex flex-col items-center bg-white shadow rounded-lg p-6">
                    <!-- Cercle avec nom de la classe -->
                    <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                        <span class="text-gray-700 font-semibold">{{ $class->name }}</span>
                    </div>

                    <!-- Boutons -->
                    <div class="flex flex-col space-y-2 w-full">
                        <a href="{{ route('teacher.classes.students', $class->id) }}"
                           class="bg-blue-600 text-white px-4 py-2 rounded text-center hover:bg-blue-700">
                            Liste des élèves
                        </a>

                        <a href="{{ route('teacher.classes.timetable', $class->id) }}"
                           class="bg-green-600 text-white px-4 py-2 rounded text-center hover:bg-green-700">
                            Voir emploi du temps
                        </a>
                        <a href="{{ route('teacher.classes.notes', $class->id) }}"
                           class="bg-green-600 text-white px-4 py-2 rounded text-center hover:bg-green-700">
                            Notes
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

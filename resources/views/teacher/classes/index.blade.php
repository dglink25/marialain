@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Mes Classes</h1>

    @if($classes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classes as $class)
                <div class="bg-white shadow-md rounded-xl p-6 hover:shadow-lg transition">
                    <h2 class="text-lg font-semibold text-gray-800">{{ $class->name }}</h2>
                    <p class="text-sm text-gray-600"></p>

                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('teacher.classes.students', $class->id) }}"
                           class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                            Voir élèves
                        </a>
                        <a href="{{ route('teacher.classes.timetable', $class->id) }}"
                           class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                            Emploi du temps
                        </a>
                        <a href="{{ route('teacher.classes.notes', $class->id) }}"
                           class="bg-green-600 text-white px-4 py-2 rounded text-center hover:bg-green-700">
                            Notes
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">Vous n’intervenez dans aucune classe.</p>
    @endif
</div>
@endsection

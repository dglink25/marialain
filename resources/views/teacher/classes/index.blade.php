@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">

    <h1 class="text-2xl font-bold mb-6">Mes Classes</h1>

    @if ($classes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($classes as $class)
                <div class="bg-white shadow-md rounded-xl p-6 hover:shadow-lg transition">

                    <h2 class="text-lg font-semibold text-gray-800">
                        {{ $class->name }}
                    </h2>

                    {{-- Si l’enseignant a plusieurs matières dans la classe --}}
                    <div class="mt-4 space-y-3">

                        @foreach($class->subjects as $subject)

                            <div class="border rounded-lg p-3 bg-gray-50">
                                <p class="font-semibold text-gray-700 mb-2">
                                    Matière : {{ $subject->name }}
                                </p>

                                <div class="flex flex-wrap gap-2">
                                    {{-- Cahier de texte --}}
                                    <a href="{{ route('teacher.cahier.history.subject', [$class->id, $subject->id]) }}"
                                        class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm hover:bg-yellow-600">
                                        Cahier de texte
                                    </a>

                                    {{-- Notes --}}
                                    <a href="{{ route('teacher.classes.notes.trimestres.subject', [$class->id, $subject->id]) }}"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                                        Notes
                                    </a>
                                </div>
                            </div>

                        @endforeach
                    </div>
                    <!-- Boutons -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('teacher.classes.students', $class->id) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                            Élèves
                        </a>

                        <a href="{{ route('teacher.classes.timetable', $class->id) }}"
                        class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                            Emploi du temps
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

@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">

  @if ($errors->any())
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (session('error'))
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      {{ session('error') }}
    </div>
  @endif

    <h1 class="text-2xl font-bold mb-6">Mes Classes</h1>

    @if($classes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($classes as $class)
                <div class="bg-white shadow-md rounded-xl p-6 hover:shadow-lg transition">
                    <h2 class="text-lg font-semibold text-gray-800">{{ $class->name }}</h2>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('teacher.classes.students', $class->id) }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                            Elèves
                        </a>
                        <a href="{{ route('teacher.classes.timetable', $class->id) }}"
                        class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                            Emploi du temps
                        </a>
                        <a href="{{ route('teacher.classes.notes.trimestres', $class->id) }}"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Notes
                        </a>
                        <a href="{{ route('teacher.cahier.history', $class->id) }}"
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">
                            Cahier de texte
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

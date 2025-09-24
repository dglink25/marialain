@extends('layouts.app')

@section('content')
<body class="bg-gradient-to-br from-blue-50 to-blue-100 text-gray-800 min-h-screen">

<div class="container mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-800 border-b pb-2">📚 Liste des Classes</h1>
    </div>

    @if(isset($error))
        <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg">
            {{ $error }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
</div>

</body>
@endsection

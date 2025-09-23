@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Archives - {{ $year->name }}</h1>

    <h2 class="font-semibold">Classes :</h2>
    <ul class="list-disc pl-6 mb-4">
        @forelse($classes as $class)
            <li>{{ $class->name }}</li>
        @empty
            <li>Aucune classe enregistrée</li>
        @endforelse
    </ul>

    <h2 class="font-semibold">Élèves :</h2>
    <ul class="list-disc pl-6">
        @forelse($students as $student)
            <li>{{ $student->name }}</li>
        @empty
            <li>Aucun élève enregistré</li>
        @endforelse
    </ul>
</div>
@endsection

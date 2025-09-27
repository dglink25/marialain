@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Modifier un créneau';
@endphp
<div class="p-6 max-w-3xl mx-auto bg-white shadow rounded">
    <h1 class="text-xl font-bold mb-4">Modifier un créneau - {{ $class->name }}</h1>

    <form action="{{ route('censeur.timetables.update', [$class->id, $timetable->id]) }}" method="POST" class="grid gap-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block mb-1 font-medium">Enseignant</label>
            <select name="teacher_id" class="border rounded p-2 w-full" required>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" @selected($t->id == $timetable->teacher_id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-1 font-medium">Matière</label>
            <select name="subject_id" class="border rounded p-2 w-full" required>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" @selected($s->id == $timetable->subject_id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-1 font-medium">Jour</label>
            <select name="day" class="border rounded p-2 w-full" required>
                @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                    <option value="{{ $d }}" @selected($d == $timetable->day)>{{ $d }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-medium">Heure de début</label>
                <input type="time" name="start_time" class="border rounded p-2 w-full" value="{{ $timetable->start_time }}" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Heure de fin</label>
                <input type="time" name="end_time" class="border rounded p-2 w-full" value="{{ $timetable->end_time }}" required>
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4">Modifier</button>
        <button onclick="window.history.back()" 
            class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
            Retour
        </button>
    </form>
</div>
@endsection

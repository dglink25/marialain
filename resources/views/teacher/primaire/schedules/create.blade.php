@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Ajouter un cours à l’emploi du temps</h1>

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('schedules.store') }}" method="POST" class="space-y-4 bg-white shadow-md p-6 rounded-lg">
        @csrf

        <!-- Classe -->
        <div>
            <label class="block text-sm font-medium">Classe</label>
            <input type="text" value="{{ $classe->name }}" disabled
                   class="w-full border rounded px-3 py-2 bg-gray-100">
        </div>

        <!-- Matière -->
        <div>
            <label for="subject_id" class="block text-sm font-medium">Matière</label>
            <select name="subject_id" id="subject_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Sélectionner une matière --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
            @error('subject_id')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jour -->
        <div>
            <label for="day_of_week" class="block text-sm font-medium">Jour</label>
            <select name="day_of_week" id="day_of_week" class="w-full border rounded px-3 py-2">
                <option value="">-- Choisir un jour --</option>
                <option value="Lundi">Lundi</option>
                <option value="Mardi">Mardi</option>
                <option value="Mercredi">Mercredi</option>
                <option value="Jeudi">Jeudi</option>
                <option value="Vendredi">Vendredi</option>
                <option value="Samedi">Samedi</option>
            </select>
            @error('day_of_week')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Heure début -->
        <div>
            <label for="start_time" class="block text-sm font-medium">Heure de début</label>
            <input type="time" name="start_time" id="start_time" class="w-full border rounded px-3 py-2">
            @error('start_time')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Heure fin -->
        <div>
            <label for="end_time" class="block text-sm font-medium">Heure de fin</label>
            <input type="time" name="end_time" id="end_time" class="w-full border rounded px-3 py-2">
            @error('end_time')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <!-- Boutons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('schedules.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Annuler</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enregistrer</button>
        </div>
    </form>
</div>
@endsection

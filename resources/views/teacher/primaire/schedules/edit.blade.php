@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <h1 class="text-xl font-bold mb-4">✏ Modifier un cours</h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('schedules.update', $schedule) }}" method="POST" class="space-y-4 bg-white p-6 rounded-lg shadow">
        @csrf
        @method('PUT')

        {{-- Classe (readonly) --}}
        <div>
            <label class="block font-medium mb-1">Classe</label>
            <input type="text" value="{{ $classe->name }}" readonly 
                   class="w-full border rounded px-3 py-2 bg-gray-100">
        </div>

        {{-- Matière --}}
        <div>
            <label for="subject_id" class="block font-medium mb-1">Matière</label>
            <select name="subject_id" id="subject_id" required
                    class="w-full border rounded px-3 py-2">
               @forelse($subjects as $subject)
                    <option value="{{ $subject->id }}" 
                        {{ $schedule->subject_id == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @empty
                    <option disabled>Aucune matière disponible</option>
                @endforelse

            </select>
        </div>

        {{-- Jour --}}
        <div>
            <label for="day_of_week" class="block font-medium mb-1">Jour</label>
            <select name="day_of_week" id="day_of_week" required
                    class="w-full border rounded px-3 py-2">
                @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                    <option value="{{ $day }}" 
                        {{ $schedule->day_of_week == $day ? 'selected' : '' }}>
                        {{ $day }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Heure début --}}
        <div>
            <label for="start_time" class="block font-medium mb-1">Heure de début</label>
            <input type="time" name="start_time" id="start_time" required 
                   value="{{ $schedule->start_time }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- Heure fin --}}
        <div>
            <label for="end_time" class="block font-medium mb-1">Heure de fin</label>
            <input type="time" name="end_time" id="end_time" required 
                   value="{{ $schedule->end_time }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('schedules.index') }}" 
               class="px-4 py-2 border rounded">⬅ Annuler</a>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700">
                Mettre à jour
            </button>
        </div>
    </form>
</div>
@endsection

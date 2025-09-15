@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Emploi du temps - {{ $class->name }}</h1>

    <!-- Formulaire d'ajout -->
    <form action="{{ route('censeur.timetables.store', $class->id) }}" method="POST"
          class="grid md:grid-cols-6 gap-3 mb-6 bg-white shadow p-4 rounded">
        @csrf
        <select name="teacher_id" class="border p-2 rounded" required>
            <option value="">-- Enseignant --</option>
            @foreach($teachers as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>

        <select name="subject_id" class="border p-2 rounded" required>
            <option value="">-- Matière --</option>
            @foreach($subjects as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>

        <select name="day" class="border p-2 rounded" required>
            <option value="">-- Jour --</option>
            @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                <option value="{{ $d }}">{{ $d }}</option>
            @endforeach
        </select>

        <input type="time" name="start_time" class="border p-2 rounded" required>
        <input type="time" name="end_time" class="border p-2 rounded" required>

        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Ajouter</button>
    </form>

    <!-- Tableau emploi du temps -->

    <div class="flex justify-end mb-4">
        <a href="{{ route('censeur.timetables.download', $class->id) }}"
        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Télécharger PDF
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border text-sm md:text-base">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2 w-24">Heure</th>
                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $d)
                        <th class="border p-2 text-center">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($hours as $hourSlot)
                    @php
                        // Décomposer l'heure pour comparer avec start_time et end_time
                        [$startHour, $endHour] = explode('-', $hourSlot);
                        $startHourFormatted = str_replace('h', ':00', $startHour);
                        $endHourFormatted = str_replace('h', ':00', $endHour);
                    @endphp

                    <tr>
                        <td class="border p-2 text-center font-semibold bg-gray-50">{{ $hourSlot }}</td>

                        @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                            @php
                                // Vérifier si un cours commence à cette heure
                                $course = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                    return $t->day === $day && date('H:i', strtotime($t->start_time)) == $startHourFormatted;
                                });

                                // Vérifier si l'heure est déjà couverte par un rowspan
                                $overlap = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                    return $t->day === $day && strtotime($t->start_time) < strtotime($startHourFormatted) && strtotime($t->end_time) > strtotime($startHourFormatted);
                                });
                            @endphp

                            @if($course)
                                @php
                                    $duration = (strtotime($course->end_time) - strtotime($course->start_time)) / 3600;
                                @endphp
                                <td class="border p-2 align-middle bg-blue-100 text-blue-800 rounded shadow-sm" rowspan="{{ $duration }}">
                                    <div class="flex flex-col items-center justify-center h-full text-center">
                                        <div class="font-bold">{{ $course->subject->name }}</div>
                                        <div class="text-xs">{{ $course->teacher->name }}</div>
                                        <div class="text-xs">{{ date('H:i', strtotime($course->start_time)) }} - {{ date('H:i', strtotime($course->end_time)) }}</div>
                                    
                                        <div class="flex justify-center gap-2 mt-2">
                                            <!-- Modifier -->
                                            <a href="{{ route('censeur.timetables.edit', [$class->id, $course->id]) }}"
                                            class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 text-xs">
                                            Modifier
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            @elseif($overlap)
                                {{-- cellule déjà couverte --}}
                            @else
                                <td class="border p-2"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ route('censeur.classes.index') }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           Retour
        </a>
    </div>
</div>
@endsection

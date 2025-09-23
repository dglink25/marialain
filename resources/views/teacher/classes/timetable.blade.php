

@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Emploi du temps - {{ $class->name }}</h1>

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
        <a href="{{ route('teacher.classes') }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           ← Retour
        </a>
    </div>
</div>
@endsection

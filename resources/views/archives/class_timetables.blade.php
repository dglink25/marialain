@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Emploi du temps';
@endphp
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">
        Emploi du temps - {{ $class->name }} ({{ $year->name }})
    </h1>

    <!-- Tableau d'emploi du temps -->
    <div class="overflow-x-auto border rounded-lg shadow-sm">
        <table class="min-w-full border-collapse border">
            <thead class="bg-gray-100 text-sm">
                <tr>
                    <th class="border px-3 py-2 text-center w-24">Heure</th>
                    @foreach($days as $d)
                        <th class="border px-3 py-2 text-center">{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($hours as $hourSlot)
                    @php
                        [$startHour, $endHour] = explode('-', $hourSlot);
                        $startHourFormatted = str_replace('h', ':00', $startHour);
                    @endphp

                    <tr>
                        <td class="border px-2 py-2 text-center font-semibold bg-gray-50">
                            {{ $hourSlot }}
                        </td>

                        @foreach($days as $day)
                            @php
                                $course = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                    return $t->day === $day && date('H:i', strtotime($t->start_time)) == $startHourFormatted;
                                });

                                $overlap = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                    return $t->day === $day 
                                           && strtotime($t->start_time) < strtotime($startHourFormatted) 
                                           && strtotime($t->end_time) > strtotime($startHourFormatted);
                                });
                            @endphp

                            @if($course)
                                @php
                                    $duration = max(1, round((strtotime($course->end_time) - strtotime($course->start_time)) / 3600));
                                @endphp
                                <td class="border px-2 py-2 text-center bg-blue-50" rowspan="{{ $duration }}">
                                    <div class="font-bold">{{ $course->subject->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $course->teacher->name }}</div>
                                    <div class="text-xs mt-1 bg-blue-200 text-blue-800 px-2 py-1 rounded">
                                        {{ date('H:i', strtotime($course->start_time)) }} - {{ date('H:i', strtotime($course->end_time)) }}
                                    </div>
                                </td>
                            @elseif($overlap)
                                {{-- Cellule fusionnée -> rien à afficher --}}
                            @else
                                <td class="border"></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <a href="{{ route('archives.show', $year->id) }}" 
           class="inline-block px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
           Retour aux classes
        </a>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Emploi du temps';
@endphp
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Emploi du temps - {{ $class->name }}</h1>

    @if($timetables->isEmpty())
        <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
            Aucun emploi du temps défini pour cette classe.
        </div>
    @else
        <table class="w-full border border-gray-300 bg-white shadow rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Jour</th>
                    <th class="p-2 text-left">Horaire</th>
                    <th class="p-2 text-left">Matière</th>
                    <th class="p-2 text-left">Enseignant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($timetables as $t)
                    <tr class="border-t">
                        <td class="p-2">{{ $t->day }}</td>
                        <td class="p-2">{{ $t->start_time }} - {{ $t->end_time }}</td>
                        <td class="p-2">{{ $t->subject->name }}</td>
                        <td class="p-2">{{ $t->teacher->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

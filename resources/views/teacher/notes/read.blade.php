@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">

@php
    $pageTitle = "Lecteur Notes";
@endphp

    <h1 class="text-2xl font-bold mb-6">
        Notes - {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
    </h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-3 py-2 border">N°</th>
                <th class="px-3 py-2 border">Nom</th>
                <th class="px-3 py-2 border">Prénom</th>
                <th class="px-3 py-2 border">Sexe</th>
                <th class="px-3 py-2 border">Note</th>
                <th class="px-3 py-2 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classe->students as $student)
            <tr class="border-b">
                <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                <td class="px-3 py-2 border">{{ $student->gender }}</td>
                <td class="px-3 py-2 border">
                    {{ $student->grades->first()->value ?? '00' }}
                </td>
                <td class="px-3 py-2 border flex space-x-2">
                    <a href="{{ route('teacher.classes.notes.edit', [$classe->id, $type, $num, $trimestre]) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                       Modifier
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        <a href="{{ route('teacher.classes.notes', [$classe->id, $trimestre]) }}"
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           Retour
        </a>
    </div>
</div>
@endsection

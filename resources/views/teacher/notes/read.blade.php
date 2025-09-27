@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">
        Lecture Notes - {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }}
    </h1>

    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-100">
                <th class="px-3 py-2 border">N°</th>
                <th class="px-3 py-2 border">Nom</th>
                <th class="px-3 py-2 border">Prénom</th>
                <th class="px-3 py-2 border">Sexe</th>
                <th class="px-3 py-2 border">Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classe->students as $student)
            <tr class="border-b">
                <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                <td class="px-3 py-2 border">{{ $student->first_gender }}</td>
                <td class="px-3 py-2 border">
                    {{ $student->grades->first()->value ?? '00' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        <a href="{{ route('teacher.classes.notes', $classe->id) }}"
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           Retour
        </a>
    </div>
</div>
@endsection

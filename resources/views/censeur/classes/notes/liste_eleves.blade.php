@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6 text-center">
        Liste des élèves - {{ $classe->name }} (Trimestre {{ $trimestre }})
    </h1>

    <table class="w-full border-collapse border text-sm md:text-base">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">N°</th>
                <th class="border px-2 py-1">Nom</th>
                <th class="border px-2 py-1">Prénom</th>
                <th class="border px-2 py-1">Sexe</th>
                <th class="border px-2 py-1">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($classe->students as $index => $student)
                <tr>
                    <td class="border px-2 py-1 text-center">{{ $index + 1 }}</td>
                    <td class="border px-2 py-1">{{ $student->last_name }}</td>
                    <td class="border px-2 py-1">{{ $student->first_name }}</td>
                    <td class="border px-2 py-1">{{ $student->gender }}</td>
                    <td class="border px-2 py-1 text-center">
                        <a href="{{ route('teacher.classes.students.bulletin', [$classe->id, $student->id, $trimestre]) }}"
                           class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                            Afficher bulletin
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    
</div>
@endsection

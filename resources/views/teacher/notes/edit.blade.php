@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Edite Notes";
@endphp

<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">
        Modifier Notes {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }} /  Trimestre {{ $trimestre }}
    </h1>

    <form action="{{ route('teacher.classes.notes.update', [$classe->id, $type, $num, $trimestre]) }}" method="POST">
        @csrf
        <table class="w-full border-collapse border mb-4">
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
                    <td class="px-3 py-2 border">{{ $student->gender }}</td>
                    <td class="px-3 py-2 border">
                        <input type="number" name="notes[{{ $student->id }}]" step="0.01" min="0" max="20"
                               value="{{ $student->grades->first()->value ?? '' }}"
                               class="w-20 px-2 py-1 border rounded">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex space-x-2">
            <!-- Formulaire pour soumettre les modifications -->
            <form action="{{ route('teacher.classes.notes.update', [$classe->id, $type, $num, $trimestre]) }}" method="POST">
                @csrf
                @method('PUT') <!-- ou PATCH selon ta route -->
            
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Soumettre les modifications
                </button>
            </form>

            <!-- Formulaire pour supprimer toutes les notes -->
            <form action="{{ route('teacher.classes.notes.destroy', [$classe->id, $type, $num, $trimestre]) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer toutes les notes ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 mt-2">
                    Supprimer toutes les notes
                </button>
            </form>

            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $type, $num, $trimestre]) }}"
               class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
               Retour
            </a>
        </div>
    </form>
</div>
@endsection

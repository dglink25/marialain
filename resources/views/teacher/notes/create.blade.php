@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Saisie des notes';
@endphp
<div class="container mx-auto py-6">
    <h1 class="text-xl font-bold mb-4">
        Saisie des notes - {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }}
    </h1>

    <form method="POST" action="{{ route('teacher.classes.notes.store', [$classe->id, $type, $num]) }}">
        @csrf
        <input type="hidden" name="subject_id" value="1"> {{--à remplacer dynamiquement --}}
        <input type="hidden" name="trimestre" value="1"> {{-- à gérer dynamiquement --}}

        <table class="w-full border mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">N°</th>
                    <th class="px-3 py-2 border">Nom</th>
                    <th class="px-3 py-2 border">Prénoms</th>
                    <th class="px-3 py-2 border">Sexe</th>
                    <th class="px-3 py-2 border">Note (/20)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classe->students as $student)
                    <tr>
                        <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                        <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                        <td class="px-3 py-2 border">{{ $student->gender }}</td>
                        <td class="px-3 py-2 border">
                            <input type="number" step="0.01" min="0" max="20"
                                name="notes[{{ $student->id }}]"
                                class="border p-2 w-24">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Enregistrer
        </button>
        <a href="{{ route('teacher.classes.notes', $classe->id) }}"
            class="ml-2 px-4 py-2 border rounded">
            Annuler
        </a>
    </form>
</div>
@endsection

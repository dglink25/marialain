@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-6">Élèves de la classe : {{ $class->name }}</h1>

    <!-- Bouton pour télécharger en PDF -->
    <div class="mb-4 flex justify-end">
        <a href="{{ route('censeur.classes.students.pdf', $class->id) }}" 
           class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
           Télécharger PDF
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        @if($class->students->count() > 0)
            <div class="overflow-y-auto max-h-[500px]">
                <table class="min-w-full border border-gray-300 text-sm table-fixed">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr>
                            <th>N°</th>
                            <th class="border px-4 py-2 text-left w-1/6">N° Éduc Master</th>
                            <th class="border px-4 py-2 text-left w-1/6">Nom</th>
                            <th class="border px-4 py-2 text-left w-1/6">Prénoms</th>
                            <th class="border px-4 py-2 text-left w-1/6">Date de naissance</th>
                            <th class="border px-4 py-2 text-left">Lieu de naissance</th>
                            <th class="border px-4 py-2 text-left w-1/6">Sexe</th>
                            <th class="border px-4 py-2 text-left">Nom parent</th>
                            <th class="border px-4 py-2 text-left w-1/6">Email parent</th>
                            <th class="border px-4 py-2 text-left">Téléphone parent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($class->students->sortBy([['last_name', 'asc'], ['first_name', 'asc']]) as $student)
                            <tr class="hover:bg-gray-50">
                                <td>{{ $loop->iteration }}</td> {{-- Numéro automatique --}}
                                <td class="border px-4 py-2">{{ $student->num_educ ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $student->last_name }}</td>
                                <td class="border px-4 py-2">{{ $student->first_name }}</td>
                                <td class="border px-4 py-2">{{ $student->birth_date }}</td>
                                <td class="border px-4 py-2">{{ $student->birth_place }}</td>
                                <td class="border px-4 py-2">{{ $student->gender ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $student->parent_full_name }}</td>
                                <td class="border px-4 py-2">{{ $student->parent_email }}</td>
                                <td class="border px-4 py-2">{{ $student->parent_phone }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 p-4">Aucun élève dans cette classe.</p>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('censeur.classes.index') }}" 
           class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
           Retour
        </a>
    </div>
</div>
@endsection
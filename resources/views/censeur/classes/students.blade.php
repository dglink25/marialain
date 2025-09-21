@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <!-- Titre -->
    <h1 class="text-3xl font-extrabold text-center text-blue-700 mb-8">
        Élèves de la classe : {{ $class->name }}
    </h1>

    <!-- Bouton PDF -->
    <div class="mb-6 flex justify-end">
        <a href="{{ route('censeur.classes.students.pdf', $class->id) }}" 
           class="bg-red-600 text-white px-5 py-2 rounded-lg shadow hover:bg-red-700 transition">
           Télécharger PDF
        </a>
    </div>

    <!-- Tableau -->
    <div class="bg-white shadow-lg rounded-xl overflow-x-auto">
        @if($class->students->count() > 0)
            <div class="overflow-y-auto max-h-[500px]">
                <table class="min-w-full border border-gray-300 text-sm table-fixed">
                    <thead class="bg-gray-100 sticky top-0 z-10 text-gray-700">
                        <tr>
                            <th class="border px-4 py-3">N°</th>
                            <th class="border px-4 py-3 w-1/6"><center>N° Éduc Master</center></th>
                            <th class="border px-4 py-3 w-1/6"><center>Nom</center></th>
                            <th class="border px-4 py-3 w-1/6">Prénoms</th>
                            <th class="border px-4 py-3 w-1/6">Date de naissance</th>
                            <th class="border px-4 py-3">Lieu de naissance</th>
                            <th class="border px-4 py-3 w-1/12">Sexe</th>
                            <th class="border px-4 py-3">Nom parent</th>
                            <th class="border px-4 py-3 w-1/6"><center>Email parent</center></th>
                            <th class="border px-4 py-3">Téléphone parent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($class->students->sortBy([['last_name', 'asc'], ['first_name', 'asc']]) as $student)
                            <tr class="hover:bg-gray-50 text-center">
                                <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="border px-4 py-2">{{ $student->num_educ ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $student->last_name }}</td>
                                <td class="border px-4 py-2">{{ $student->first_name }}</td>
                                <td class="border px-4 py-2">{{ $student->birth_date }}</td>
                                <td class="border px-4 py-2">{{ $student->birth_place }}</td>
                                <td class="border px-4 py-2">{{ $student->gender ?? '-' }}</td>
                                <td class="border px-4 py-2">{{ $student->parent_full_name }}</td>
                                <td class="border px-4 py-2 break-words">{{ $student->parent_email }}</td>
                                <td class="border px-4 py-2">{{ $student->parent_phone }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 p-6 text-center">Aucun élève dans cette classe.</p>
        @endif
    </div>

    <!-- Bouton retour -->
    <div class="mt-6">
        <a href="{{ route('censeur.classes.index') }}" 
           class="bg-gray-600 text-white px-5 py-2 rounded-lg shadow hover:bg-gray-700 transition">
           Retour
        </a>
    </div>
</div>
@endsection

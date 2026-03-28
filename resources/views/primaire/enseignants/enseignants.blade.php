@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
            Année académique : {{ $annee_academique->name }}
        </h1>
        <div class="flex space-x-3">
            <a href="{{ route('primaire.enseignants.inviter') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow transition">
               Inviter
            </a>
            <a href="{{ route('primaire.enseignants.pdf') }}"
               class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow transition">
               Télécharger
            </a>
        </div>
    </div>

    <!-- Liste des enseignants -->
    <div class="bg-white shadow-lg rounded-lg p-6 overflow-x-auto">
        <h2 class="text-xl font-bold text-gray-700 mb-4">Liste des enseignants du primaire</h2>

        @if($teachers->count() > 0)
        <table class="min-w-full border border-gray-200 text-sm divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-center font-medium text-gray-700">N°</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">   Nom</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Sexe</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Classe assignée</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Contact</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Email</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($teachers as $teacher)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2 text-center">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2 font-semibold text-gray-800"><a href="{{ route('primaire.enseignants.show', $teacher->id) }} " class="text-blue-600 hover:underline">{{ $teacher->name }}</a></td>
                    <td class="px-4 py-2 text-gray-700">{{ $teacher->gender ?? "-" }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $teacher->classePrimaire?->name ?? 'Non assignée' }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $teacher->phone ?? "-" }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $teacher->email ?? 'Non disponible' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-500 mt-4">Aucun enseignant trouvé pour le primaire cette année académique.</p>
        @endif
    </div>

</div>
@endsection

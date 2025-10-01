@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Carte Année académique -->
    <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Année académique : {{ $annee_academique->name }}</h1>
    </div>

    <!-- En-tête et actions -->
    <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
        <h2 class="text-xl font-semibold text-gray-700">Liste des élèves - Primaire</h2>
        <a href="{{ route('primaire.ecoliers.liste.pdf') }}" 
           class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-green-700 transition">
           Télécharger la liste
        </a>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white shadow rounded-lg p-4 mb-6 border border-gray-200 space-y-4">
        <form action="" method="GET" class="flex flex-wrap items-center gap-4">
            @csrf
            <!-- Trier -->
            <div class="flex items-center gap-2">
                <span class="text-gray-700">Trier par :</span>
                <select name="sort" class="border border-gray-300 rounded-lg px-2 py-1">
                    <option value="">-- Sélectionner --</option>
                    <option value="classe" {{ request('sort') == 'classe' ? 'selected' : '' }}>Classe</option>
                    <option value="last_name" {{ request('sort') == 'last_name' ? 'selected' : '' }}>Nom</option>
                    <option value="first_name" {{ request('sort') == 'first_name' ? 'selected' : '' }}>Prénom</option>
                    <option value="age" {{ request('sort') == 'age' ? 'selected' : '' }}>Âge</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded shadow hover:bg-blue-700 transition">Trier</button>
            </div>

            <!-- Filtrer -->
            <div class="flex items-center gap-2">
                <span class="text-gray-700">Filtrer par :</span>
                <select name="classe" class="border border-gray-300 rounded-lg px-2 py-1">
                    <option value="">--Classe--</option>
                    @foreach ($classes as $classe)
                        <option value="{{ $classe->name }}" {{ request('classe') == $classe->name ? 'selected' : '' }}>
                            {{ $classe->name }}
                        </option>
                    @endforeach
                </select>
                <select name="gender" class="border border-gray-300 rounded-lg px-2 py-1">
                    <option value="">--Sexe--</option>
                    <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Féminin</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded shadow hover:bg-blue-700 transition">Filtrer</button>
            </div>

            <!-- Rechercher -->
            <div class="flex items-center gap-2 flex-1">
                <input type="text" name="search" placeholder="Rechercher un élève"
                       value="{{ request('search') }}"
                       class="border border-gray-300 rounded-lg px-2 py-1 w-full">
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded shadow hover:bg-blue-700 transition">Rechercher</button>
            </div>

            <a href="{{ route('primaire.ecoliers.liste') }}" class="text-gray-600 hover:text-gray-800 px-3 py-1 rounded border border-gray-200">Voir tout</a>
        </form>
    </div>

    <!-- Tableau des élèves -->
    <div class="bg-white shadow rounded-lg border border-gray-200 overflow-x-auto">
        @if($students->count() > 0)
        <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-center">N°</th>
                    <th class="px-4 py-3 text-left">N° Educ Master</th>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Prénom</th>
                    <th class="px-4 py-3 text-left">Classe</th>
                    <th class="px-4 py-3 text-center">Sexe</th>
                    <th class="px-4 py-3 text-center">Date de naissance</th>
                    <th class="px-4 py-3 text-left">Lieu de naissance</th>
                    <th class="px-4 py-3 text-center">Tuteur</th>
                    <th class="px-4 py-3 text-left">Email Parent</th>
                    <th class="px-4 py-3 text-left">Contact</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($students as $student)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 text-center">{{ $student->num_educ }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        <a href="{{ route('primaire.ecoliers.show', $student->id) }}" class="hover:underline">
                            {{ $student->last_name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-800">{{ $student->first_name }}</td>
                    <td class="px-4 py-3 text-gray-800">{{ $student->classe?->name ?? 'Non assignée' }}</td>
                    <td class="px-4 py-3 text-center text-gray-800">{{ $student->gender }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $student->birth_date ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $student->birth_place ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $student->parent_full_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $student->parent_email ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $student->parent_phone ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-gray-500 italic p-4">Aucun élève inscrit</p>
        @endif
    </div>
</div>
@endsection

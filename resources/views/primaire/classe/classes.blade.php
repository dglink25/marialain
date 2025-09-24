@extends('layouts.app')
@section('content')
<div>
    <!-- When there is no desire, all things are at peace. - Laozi -->
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-bold mb-6">Annéee académique : {{ $annee_academique -> name}} </h1> 
        <div class="haut" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="text-1xl font-bold mb-6">Liste des classes du Primaire</h1>
        </div>
        
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden" style="margin-top: 30px ;">
    <thead class="bg-orange-100 " >
        <tr>
            <th class="px-4 py-2 text-left">N°</th>
            <th class="px-4 py-2 text-left">Classe</th>
            <th class="px-4 py-2 text-left">Enseignant</th>
            <th class="px-4 py-2 text-left">N° salle</th>
            <th class="px-4 py-2 text-left">Visiter</th>
        </tr>
    </thead>
    <tbody>
        @foreach($classes as $index => $class)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $index + 1 }}</td>
                <td class="px-4 py-2 font-semibold text-gray-800">{{ $class->name }}</td>
                <td class="px-4 py-2">{{ $class->teacher?->name ?? 'Non assigné' }}</td>
                <td class="px-4 py-2"> ...</td>
                <td class="px-4 py-2 flex space-x-2">
                    <!-- Voir -->
                    <a href="{{ route('primaire.classe.showclass', $class->id) }}" title="Voir" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-eye"></i> Voir
                    </a>
                
                </td>
                
            </tr>
        @endforeach
    </tbody>
</table>

        
</div>

@endsection

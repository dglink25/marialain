@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="haut" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="text-2xl font-bold mb-6">
        Liste des enseignants

    </h1>
    <div class="" style="display: flex;">
         <a href=" {{  route('primaire.enseignants.inviter') }}"
                           class="bg-blue-600 text-white px-4 py-1 rounded text-center hover:bg-blue-700" style="margin: 10px">
                            Inviter
                        </a>
         <a href="#"
                           class="bg-green-600 text-white px-4 py-1 rounded text-center hover:bg-blue-700" style="margin: 10px">
                            Télécharger
                        </a>
                       
    </div>
   
    </div>
    </div>
    
    
    <div class="bg-white shadow-md rounded-lg p-6 overflow-x-auto">
        @if($teachers->count() > 0)
            <table class="min-w-full border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2">N°</th>
                        <th class="border px-4 py-2 text-left">Nom</th>
                        <th class="border px-4 py-2 text-left">Email</th>
                        <th class="border px-4 py-2 text-left">Classe assignée</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2 text-center">
                                {{ $loop->iteration }}
                            </td>
                            <td class="border px-4 py-2 font-semibold text-gray-700">
                                {{ $teacher->name }}
                            </td>
                            <td class="border px-4 py-2 text-gray-600">
                                {{ $teacher->email ?? 'Non disponible' }}
                            </td>
                            <td class="border px-4 py-2 text-gray-600">
                                {{ $teacher->classePrimaire?->name ?? 'Non assignée' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500">Aucun enseignant trouvé.</p>
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

@extends('layouts.app')
@section('content')
<div>
    <!-- When there is no desire, all things are at peace. - Laozi -->
    <div class="container mx-auto py-6">
        <div class="haut" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 class="text-2xl font-bold mb-6">Liste des classes du Primaire</h1>
        <a href="#ajouter" 
                          class="bg-blue-600 text-white px-4 py-2 rounded text-center hover:bg-blue-700 ">
                            Ajouter une classe
            </a>

        </div>
        
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden" style="margin-top: 30px ;">
    <thead class="bg-orange-100 " >
        <tr>
            <th class="px-4 py-2 text-left">N</th>
            <th class="px-4 py-2 text-left">Classe</th>
            <th class="px-4 py-2 text-left">Enseignant</th>
            <th class="px-4 py-2 text-left">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($classes as $index => $class)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $index + 1 }}</td>
                <td class="px-4 py-2 font-semibold text-gray-800">{{ $class->name }}</td>
                <td class="px-4 py-2">{{ $class->teacher?->name ?? 'Non assigné' }}</td>
                <td class="px-4 py-2 flex space-x-2">
                    <!-- Voir -->
                    <a href="{{ route('primaire.classe.showclass', $class->id) }}" title="Voir" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-eye"></i>
                    </a>
                    <!-- Éditer -->
                    <a href="#" title="Éditer" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-edit"></i>
                    </a>
                    <!-- Supprimer -->
                    <form action="#" method="POST" onsubmit="return confirm('Confirmer la suppression ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Supprimer" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

        
</div>
<div id="ajouter">
     <h1 class="text-2xl font-bold mb-6">Ajouter une classe</h1>
    <form action="{{ route('primaire.classe.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nom de la classe</label>
            <input type="text" name="name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
            
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Ajouter</button>
    </form>

</div>

@endsection

@extends('layouts.app')

@section('content')
@if(auth()->check())
<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Détails sur l'élève {{ $student->last_name }} {{ $student->first_name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm text-gray-500">Nom</p>
            <p class="text-lg font-semibold">{{ $student->last_name }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Prénoms</p>
            <p class="text-lg font-semibold">{{ $student->first_name }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Date de naissance</p>
            <p class="text-lg font-semibold">{{ $student->birth_date }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Entité</p>
            <p class="text-lg font-semibold">{{ $student->entity->name ?? 'N/A' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Classe</p>
            <p class="text-lg font-semibold">{{ $student->classe->name ?? 'N/A' }}</p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Frais de scolarité payés</p>
            <p class="text-lg font-semibold">{{ $student->school_fees }} FCFA</p>
        </div>
    </div>

    <div class="flex justify-between mt-8">
        <a href="{{ route('admin.students.index') }}" 
           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            Retour
        </a>

        <div class="flex space-x-2">
            <a href="{{ route('admin.students.edit', $student->id) }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Modifier
            </a>

            <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" 
                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet étudiant ?');">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Supprimer
                </button>
            </form>
        </div>
    </div>
</div>
@else
<p style="color:red"> Une erreur s'est produite lors de l'affichage de cette section <br> Veuillez vous connectez à nouveau pour continuer <a href="{{ route('login') }}" class="block px-3 py-2 rounded bg-blue-600 text-white">Se connecter</a></p>
@endif
@endsection

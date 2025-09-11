@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Gestion des années académiques</h1>

    <form method="POST" action="{{ route('admin.academic_years.store') }}" class="bg-white shadow-md rounded p-4 mb-6">
        @csrf
        <div class="flex gap-2">
            <input type="text" name="name" placeholder="2025-2026"
                   class="border rounded p-2 w-full" required>
            <select name="active" id="" class="border rounded p-2 w-full" required>
                <option value="">Selectionner une option</option>
                <option value="0">Désactivé</option>
                <option value="1">Activé</option>
            </select>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Créer</button>
            
        </div>
    </form>

    <div class="bg-white shadow-md rounded p-4">
        <h2 class="font-semibold mb-3">Liste des années</h2>
        <ul class="divide-y">
            @foreach($years as $year)
                <li class="py-2 flex justify-between items-center">
                    <span>{{ $year->name }}</span>
                    <span class="text-sm {{ $year->active ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $year->active ? 'Active' : 'Inactive' }}
                    </span>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.academic_years.edit', $year->id) }}"
                        class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">
                            Modifier
                        </a>

                        <form method="POST" action="{{ route('admin.academic_years.destroy', $year->id) }}"
                            onsubmit="return confirm('Attention ! En supprimant cette année, toutes les données associées seront perdues. Êtes-vous sûr ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

</div>
@endsection

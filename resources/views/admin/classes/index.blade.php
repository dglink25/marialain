@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Gestion des classes</h1>

    <form method="POST" action="{{ route('admin.classes.store') }}" class="bg-white shadow-md rounded p-4 mb-6">
        @csrf
        <div class="mb-3">
            <label for="name" class="block text-sm font-medium">Nom de la classe</label>
            <input type="text" name="name" id="name" class="border rounded p-2 w-full" required>
        </div>

        <div class="mb-3">
            <label for="academic_year_id" class="block text-sm font-medium">Année académique</label>
            <select name="academic_year_id" id="academic_year_id" class="border rounded p-2 w-full" required>
                @foreach($years as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="entity_id">Entité</label>
            <select name="entity_id" class="border rounded p-2 w-full" required>
                @foreach($entities as $entity)
                    <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="school_fees" class="block text-sm font-medium">Frais de scolarité</label>
            <input type="number" step="0.01" name="school_fees" id="school_fees" class="border rounded p-2 w-full" required>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Créer</button>
    </form>

    <div class="bg-white shadow-md rounded p-4">
        <h2 class="text-xl font-semibold mb-4">Liste des classes</h2>

        <!-- Filtrage -->
        <form method="GET" class="mb-4 flex flex-col md:flex-row md:items-center gap-2">
            <select name="entity_id" class="border px-3 py-2 rounded">
                <option value="">-- Toutes les entités --</option>
                @foreach($entities as $entity)
                    <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                        {{ $entity->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filtrer</button>
        </form>

        <!-- Table responsive -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Classe</th>
                        <th class="px-4 py-2 text-left">Entité</th>
                        <th class="px-4 py-2 text-left">Année Académique</th>
                        <th class="px-4 py-2 text-left">Frais</th>
                        <th class="px-4 py-2 text-left">Actions</th> <!-- Nouvelle colonne -->
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $classe)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $classe->name }}</td>
                        <td class="px-4 py-2">{{ ucfirst($classe->entity->name) }}</td>
                        <td class="px-4 py-2">{{ $classe->academicYear->name }}</td>
                        <td class="px-4 py-2">{{ number_format($classe->school_fees, 0, ',', ' ') }} FCFA</td>
                        <td class="px-4 py-2 flex gap-2">
                            <!-- Bouton Modifier -->
                            <a href="{{ route('admin.classes.edit', $classe->id) }}"
                            class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">
                                Modifier
                            </a>

                            <!-- Bouton Supprimer -->
                            <form method="POST" action="{{ route('admin.classes.destroy', $classe->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $classes->links('pagination::tailwind') }}
        </div>

    </div>
</div>
@endsection

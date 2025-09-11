@extends('layouts.app')

@section('title', 'Liste des étudiants')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Liste des élèves</h1>
        <a href="{{ route('admin.students.create') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Nouvelle inscription
        </a>
        <a href="{{ route('admin.students.list') }}" class="hover:text-gray-200">
            Liste Alphabétique
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Nom complet</th>
                    <th class="px-4 py-2 text-left">Entité</th>
                    <th class="px-4 py-2 text-left">Classe</th>
                    <th class="px-4 py-2 text-left">Âge</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.students.show', $student->id) }}" 
                            class="text-blue-600 hover:underline">
                            {{ $student->last_name }} {{ $student->first_name }}
                            </a>
                        </td>
                        <td class="px-4 py-2">{{ $student->entity->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $student->classe->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $student->age }} ans </td>
                        <td class="px-4 py-2 flex gap-2">
                            <a href="{{ route('admin.students.edit', $student->id) }}" 
                               class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                Modifier
                            </a>
                            <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" 
                                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet étudiant ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Aucun étudiant inscrit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
</div>
@endsection

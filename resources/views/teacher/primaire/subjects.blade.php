@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-green-700">Gestion des matières</h1>

    {{-- Messages d’erreur / succès --}}
    @if(session('error'))
        <div class="p-4 mb-4 text-red-700 bg-red-100 rounded">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">{{ session('success') }}</div>
    @endif

    @isset($error)
        <div class="p-4 mb-4 text-red-700 bg-red-100 rounded">{{ $error }}</div>
    @endisset

    @if(!$classe)
        <p class="text-gray-600">Aucune classe primaire n’est assignée.</p>
    @else
        {{-- Infos classe --}}
        <div class="p-4 border rounded bg-gray-50 mb-6">
            <h2 class="font-semibold text-lg mb-2">Classe : {{ $classe->name }}</h2>
            <p><strong>Année académique :</strong> {{ $classe->academicYear->name ?? '---' }}</p>
        </div>

        {{-- Formulaire ajout matière --}}
        <form method="POST" action="{{ route('teacher.subjects.store') }}" class="mb-6 flex gap-4 flex-wrap">
            @csrf
            <input type="text" name="name" placeholder="Nom de la matière"
                   class="border rounded p-2 flex-1" required>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Ajouter
            </button>
        </form>

        {{-- Liste matières --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border px-4 py-2">Nom</th>
                        <th class="border px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2">{{ $subject->name }}</td>
                            <td class="border px-4 py-2 flex gap-2">
                                {{-- Modifier --}}
                                <form method="POST" action="{{ route('teacher.subjects.update', $subject) }}" class="flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $subject->name }}" class="border rounded p-1 text-sm">
                                    
                                    <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600">Modifier</button>
                                </form>

                                {{-- Supprimer --}}
                                <form method="POST" action="{{ route('teacher.subjects.destroy', $subject) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Supprimer cette matière ?')" 
                                            class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center p-4 text-gray-500">Aucune matière disponible.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

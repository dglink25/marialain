@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Modifier l'année académique</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-2 rounded mb-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form method="POST" action="{{ route('admin.academic_years.update', $academicYear->id) }}" class="bg-white shadow-md rounded p-4">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium mb-1">Nom de l'année</label>
            <input type="text" name="name" id="name" value="{{ old('name', $academicYear->name) }}"
                   class="border rounded p-2 w-full" required>
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="active" class="block text-sm font-medium mb-1">Statut</label>
            <select name="active" id="active" class="border rounded p-2 w-full" required>
                <option value="1" {{ $academicYear->active ? 'selected' : '' }}>Activé</option>
                <option value="0" {{ !$academicYear->active ? 'selected' : '' }}>Désactivé</option>
            </select>
            @error('active')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Enregistrer
            </button>
            <a href="{{ route('admin.academic_years.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

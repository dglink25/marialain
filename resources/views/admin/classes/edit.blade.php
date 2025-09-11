@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Modifier la classe</h1>

    <form method="POST" action="{{ route('admin.classes.update', $classe->id) }}" class="bg-white shadow-md rounded p-4">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="block text-sm font-medium">Nom de la classe</label>
            <input type="text" name="name" id="name" value="{{ $classe->name }}" class="border rounded p-2 w-full" required>
        </div>

        <div class="mb-3">
            <label for="academic_year_id" class="block text-sm font-medium">Année académique</label>
            <select name="academic_year_id" id="academic_year_id" class="border rounded p-2 w-full" required>
                @foreach($years as $year)
                    <option value="{{ $year->id }}" @if($classe->academic_year_id == $year->id) selected @endif>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="entity_id" class="block text-sm font-medium">Entité</label>
            <select name="entity_id" id="entity_id" class="border rounded p-2 w-full" required>
                @foreach($entities as $entity)
                    <option value="{{ $entity->id }}" @if($classe->entity_id == $entity->id) selected @endif>{{ $entity->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="school_fees" class="block text-sm font-medium">Frais de scolarité</label>
            <input type="number" step="0.01" name="school_fees" id="school_fees" 
                value="{{ old('school_fees', $classe->school_fees) }}" 
                class="border rounded p-2 w-full" required>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Mettre à jour</button>
    </form>
</div>
@endsection

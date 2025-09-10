@extends('layouts.app')
@section('content')

<h2>Ajouter une année scolaire</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.years.store') }}">
    @csrf
    <label>Nom de l'année scolaire</label>
    <input type="text" name="name" placeholder="Ex: 2025-2026" required>

    <label>Date de début</label>
    <input type="date" name="start_date" required>

    <label>Date de fin</label>
    <input type="date" name="end_date" required>

    <button type="submit">Enregistrer</button>
</form>

<a href="{{ route('admin.years.index') }}">Retour aux années scolaires</a>

@endsection

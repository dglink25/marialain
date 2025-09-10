@extends('layouts.app')

@section('content')
<h1>Nouvelle année académique</h1>
<form action="{{ route('academic_years.store') }}" method="POST">
    @csrf
    <label>Nom</label>
    <input type="text" name="name" required>

    <label>Date de début</label>
    <input type="date" name="start_date" required>

    <label>Date de fin</label>
    <input type="date" name="end_date" required>

    <label>Active ?</label>
    <input type="checkbox" name="is_active" value="1">

    <button type="submit">Enregistrer</button>
</form>
@endsection

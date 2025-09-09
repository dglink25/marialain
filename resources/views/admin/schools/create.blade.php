@extends('layouts.app')
@section('content')
    <h2>Créer école</h2>
    <form method="POST" action="{{ route('schools.store') }}">@csrf
        <label>Nom <input name="name" /></label>
        <label>Description <textarea name="description"></textarea></label>
        <button>Créer</button>
    </form>
@endsection
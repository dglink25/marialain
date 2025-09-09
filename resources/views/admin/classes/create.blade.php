@extends('layouts.app')
@section('content')
    <h2>Créer classe</h2>
    <form method="POST" action="{{ route('classes.store') }}">@csrf
        <label>École
            <select name="school_id">
                @foreach($schools as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </label>
        <label>Nom <input name="name" /></label>
        <label>Niveau <input name="level" placeholder="primary|secondary|highschool" /></label>
        <label>Série <input name="series" placeholder="A/B/C" /></label>
        <label>Année scolaire
            <select name="academic_year_id">
                <option value="">--</option>
                @foreach($years as $y)
                    <option value="{{ $y->id }}">{{ $y->name }}</option>
                @endforeach
            </select>
        </label>
        <button>Créer</button>
    </form>
@endsection
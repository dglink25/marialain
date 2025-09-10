@extends('layouts.app')
@section('content')
<h2>Années scolaires</h2>
<ul>
    @foreach($years as $year)
        <li>
            <a href="{{ route('admin.years.show', $year->id) }}">
                {{ $year->name }} ({{ $year->start_date }} - {{ $year->end_date }})
            </a>
        </li>
    @endforeach
</ul>
<a href="{{ route('admin.years.create') }}">Ajouter une nouvelle année scolaire</a>
@endsection

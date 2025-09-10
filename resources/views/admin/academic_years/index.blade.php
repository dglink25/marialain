@extends('layouts.app')

@section('content')
<h1>Années académiques</h1>
<a href="{{ route('academic_years.create') }}">Ajouter</a>
<ul>
    @foreach($years as $year)
        <li>{{ $year->name }} ({{ $year->start_date }} → {{ $year->end_date }}) 
            @if($year->is_active) Active @endif
        </li>
    @endforeach
</ul>
@endsection

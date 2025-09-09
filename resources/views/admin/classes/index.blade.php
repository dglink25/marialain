@extends('layouts.app')
@section('content')
    <h2>Classes</h2>
    <a href="{{ route('classes.create') }}">Créer une classe</a>
    <ul>
    @foreach($classes as $c)
        <li>{{ $c->name }} ({{ $c->school?->name }}) - {{ $c->series ?? '' }} <form method="POST" action="{{ route('classes.destroy',$c) }}" style="display:inline">@csrf @method('DELETE')<button>Suppr</button></form></li>
    @endforeach
    </ul>
    {{ $classes->links() }}
@endsection
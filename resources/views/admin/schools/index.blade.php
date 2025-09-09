@extends('layouts.app')
@section('content')
    <h2>Écoles</h2>
    <a href="{{ route('schools.create') }}">Créer une école</a>
    <ul>
    @foreach($schools as $s)
        <li>{{ $s->name }} - <form method="POST" action="{{ route('schools.destroy',$s) }}" style="display:inline">@csrf @method('DELETE')<button>Suppr</button></form></li>
    @endforeach
    </ul>
@endsection
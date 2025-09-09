@extends('layouts.app')
@section('content')
    <h2>Accepter invitation</h2>
    <p>Invitation pour: <strong>{{ $inv->email ?? $inv->phone }}</strong> (role: {{ $inv->role }})</p>
    <form method="POST" action="{{ route('invitation.accept.submit', $inv->token) }}">@csrf
        <label>Mot de passe <input type="password" name="password" required></label>
        <label>Confirmer <input type="password" name="password_confirmation" required></label>
        <button>Activer compte</button>
    </form>
@endsection
@extends('layouts.app')

@section('content')
<h2>Inviter un acteur</h2>

@if(session('success'))
    <div style="color: green">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div style="color: red">
        <ul>
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.invitations.store') }}">
    @csrf

    <label>Année scolaire</label>
    <select name="year_id" required>
        <option value="">Sélectionnez</option>
        @foreach($years as $year)
            <option value="{{ $year->id }}">{{ $year->name }}</option>
        @endforeach
    </select>

    <label>Email</label>
    <input type="email" name="email" value="{{ old('email') }}" required>

    <label>Rôle</label>
    <select name="role" required>
        <option value="">Sélectionnez</option>
        <option value="Censeur">Censeur</option>
        <option value="Secrétaire">Secrétaire</option>
        <option value="Directeur Primaire">Directeur Primaire</option>
        <option value="Surveillant">Surveillant</option>
    </select>

    <label>Téléphone (optionnel)</label>
    <input type="text" name="phone" value="{{ old('phone') }}">

    <button type="submit">Envoyer l'invitation</button>
</form>
@endsection

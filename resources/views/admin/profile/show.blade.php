@extends('layouts.app')
@section('content')
<h2>Profil Administrateur</h2>
<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
    @csrf
    <label>Nom</label>
    <input type="text" name="name" value="{{ $user->name }}">
    <label>Prénom</label>
    <input type="text" name="first_name" value="{{ $user->first_name }}">
    <label>Email</label>
    <input type="email" name="email" value="{{ $user->email }}">
    <label>Tel</label>
    <input type="text" name="phone" value="{{ $user->phone }}">
    <label>Photo de profil</label>
    <input type="file" name="photo">
    @if($user->photo)
        <img src="{{ asset('storage/'.$user->photo) }}" width="100">
    @endif
    <button type="submit">Enregistrer</button>
</form>
@endsection

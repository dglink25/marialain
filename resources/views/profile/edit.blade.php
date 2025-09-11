@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Mon Profil</h1>

    <div class="flex items-center mb-6">
        @if($user->profile_photo)
            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Photo profil" class="rounded-full w-32 h-32 object-cover mb-4">
        @else
            <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                <span class="text-gray-500">Pas de photo</span>
            </div>
        @endif
        <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="flex gap-2">
            @csrf
            <input type="file" name="profile_photo" class="border p-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Changer</button>
        </form>
        <form method="POST" action="{{ route('profile.photo') }}">
            @csrf
            <input type="hidden" name="remove_photo" value="1">
            <button class="ml-2 text-red-600 hover:underline">Supprimer</button>
        </form>
    </div>

    <!-- Infos -->
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        <div>
            <label>Nom</label>
            <input type="text" name="name" value="{{ $user->name }}" class="w-full border rounded p-2">
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ $user->email }}" class="w-full border rounded p-2">
        </div>
        <div>
            <label>Téléphone</label>
            <input type="text" name="phone" value="{{ $user->phone }}" class="w-full border rounded p-2">
        </div>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Enregistrer</button>
    </form>

    <!-- Mot de passe -->
    <h2 class="text-xl font-semibold mt-8">Modifier le mot de passe</h2>
    <form method="POST" action="{{ route('profile.password') }}" class="space-y-4 mt-4">
        @csrf
        <div>
            <label>Nouveau mot de passe</label>
            <input type="password" name="password" class="w-full border rounded p-2">
        </div>
        <div>
            <label>Confirmer mot de passe</label>
            <input type="password" name="password_confirmation" class="w-full border rounded p-2">
        </div>
        <button class="bg-yellow-600 text-white px-4 py-2 rounded">Changer</button>
    </form>
</div>
@endsection
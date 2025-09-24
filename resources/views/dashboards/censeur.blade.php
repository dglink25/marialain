@extends('layouts.app')

@section('content')

<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 text-green-600">Dashboard - Censeur</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-4 border rounded">
            <p><strong>Nom :</strong> {{ $user->name }}</p>
            <p><strong>Email :</strong> {{ $user->email }}</p>
            <p><strong>Téléphone :</strong> {{ $user->phone }}</p>
            <p><strong>Rôle :</strong> {{ ucfirst($user->role->name) }}</p>
        </div>
        <div class="p-4 border rounded">
            <h2 class="font-semibold mb-2">Actions</h2>
            <ul class="list-disc pl-5 text-gray-700">
                <li>Inviter les professeurs (secondaire)</li>
                <li>Assigner matières et classes</li>
                <li>Superviser l’insertion des notes</li>
            </ul>
        </div>
    </div>
</div>
@endsection

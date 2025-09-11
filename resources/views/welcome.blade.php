@extends('layouts.app')


@section('content')
    <div class="grid md:grid-cols-2 gap-6 items-center">
        <div>
            <h2 class="text-3xl font-bold mb-4">Bienvenue à l'École MARI ALAIN</h2>
            <p class="text-gray-600 mb-6">De la maternelle à la terminale, une plateforme unique pour la gestion des inscriptions, enseignants et suivi académique.</p>
            <ul class="space-y-2">
                <li>Gestion des recrutements</li>
                <li>Inscription & suivi des élèves</li>
                <li>Gestion des classes et entités</li>
            </ul>
        </div>
        <div class="bg-white p-6 rounded shadow text-center">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="mx-auto mb-4 h-32" />
            <a href="{{ route('login') }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded">Se connecter</a>
        </div>
    </div>
@endsection
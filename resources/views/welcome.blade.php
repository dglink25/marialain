@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-6 py-12">

    <!-- Titre principal -->
    <h2 class="text-4xl text-center font-extrabold text-gray-800 mb-10">
        Bienvenue à l'École MARIE ALAIN
    </h2>

    <div class="grid md:grid-cols-2 gap-12 items-center max-w-6xl w-full">
        <!-- Section texte -->
        <div class="space-y-6">
            <p class="text-gray-600 text-lg">
                De la maternelle à la terminale, une plateforme unique pour la gestion des inscriptions, enseignants et suivi académique.
            </p>
            <ul class="space-y-4 text-gray-700 text-lg">
                <li class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    Gestion des recrutements
                </li>
                <li class="flex items-center gap-3">
                    <i class="fas fa-user-graduate text-green-500 text-xl"></i>
                    Inscription & suivi des élèves
                </li>
                <li class="flex items-center gap-3">
                    <i class="fas fa-chalkboard-teacher text-green-500 text-xl"></i>
                    Gestion des classes et entités
                </li>
            </ul>
        </div>

        <!-- Carte logo et bouton -->
        <div class="bg-white p-10 rounded-3xl shadow-xl flex flex-col items-center">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-40 w-auto mb-6">
            <p class="text-gray-600 mb-6 text-center text-lg">
                Accédez à votre espace sécurisé pour gérer vos classes, enseignants et élèves.
            </p>
            <a href="{{ route('login') }}" 
               class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition">
               Se connecter
            </a>
        </div>
    </div>
</div>
@endsection

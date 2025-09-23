@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 px-4">
    <!-- Titre -->
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Tableau de bord Enseignant</h1>

    <!-- Carte d'accueil -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl shadow-lg p-6 md:p-8 mb-8 transform transition duration-500 hover:scale-105">
        <p class="text-lg mb-4">
            Bonjour <span class="font-semibold">{{ Auth::user()->name }}</span>, 
            ravi de vous revoir sur votre espace enseignant.
        </p>
        <p class="opacity-90">Gérez vos classes, vos élèves et consultez rapidement vos emplois du temps.</p>
    </div>

    <!-- Boutons d'accès rapide -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Voir mes classes -->
        <a href="{{ route('teacher.classes') }}"
           class="flex items-center justify-center bg-white shadow-md rounded-xl p-6 hover:bg-blue-50 transform transition hover:-translate-y-1 hover:shadow-xl">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-3 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <strong>1</strong>
                </div>
                <h2 class="text-xl font-semibold text-gray-800">Mes Classes</h2>
                <p class="text-sm text-gray-600 mt-1">Voir et gérer vos classes et élèves</p>
            </div>
        </a>

    
    </div>
</div>
@endsection

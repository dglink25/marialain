@extends('layouts.app')

@section('content')

<!-- Page Header -->
<header class="bg-white shadow p-6 mb-6 rounded-lg" data-aos="fade-down">
    <h1 class="text-3xl font-bold text-gray-800">Bienvenue {{ auth()->user()->name ?? 'Utilisateur' }} !</h1>
    <p class="text-gray-600 mt-2">
        Votre tableau de bord vous permet de gérer vos classes, enseignants et élèves facilement.
    </p>
</header>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" data-aos="fade-up">
    <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 flex flex-col items-start">
        <div class="flex items-center justify-between w-full">
            <h3 class="font-semibold text-blue-800">Élèves inscrits</h3>
            <i class="fas fa-users text-blue-500 text-2xl"></i>
        </div>
        <p class="text-3xl font-bold mt-4 text-blue-700">{{ \App\Models\Student::count() }}</p>
        <p class="text-sm text-blue-600 mt-1">
            {{ \App\Models\Student::whereDate('created_at', now()->subDay())->count() }} nouveaux hier
        </p>
    </div>

    <div class="bg-green-50 p-6 rounded-lg border border-green-100 flex flex-col items-start">
        <div class="flex items-center justify-between w-full">
            <h3 class="font-semibold text-green-800">Enseignants</h3>
            <i class="fas fa-chalkboard-teacher text-green-500 text-2xl"></i>
        </div>
        <p class="text-3xl font-bold mt-4 text-green-700">{{ \App\Models\User::count() }}</p>
        <p class="text-sm text-green-600 mt-1">+{{ \App\Models\User::whereDate('created_at', now()->subDay())->count() }} nouveaux hier</p>
    </div>

    <div class="bg-purple-50 p-6 rounded-lg border border-purple-100 flex flex-col items-start">
        <div class="flex items-center justify-between w-full">
            <h3 class="font-semibold text-purple-800">Classes</h3>
            <i class="fas fa-school text-purple-500 text-2xl"></i>
        </div>
        <p class="text-3xl font-bold mt-4 text-purple-700">{{ \App\Models\Classe::count() }}</p>
        <p class="text-sm text-purple-600 mt-1">{{ \App\Models\Classe::whereDate('created_at', now()->subDay())->count() }} nouvelles hier</p>
    </div>
</div>

<!-- Vos actions -->
<section class="mb-8" data-aos="fade-up">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Vos actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">👥 Gérer sa Communauté</h3>
            <p class="text-sm text-gray-500 mt-2">Invitez, attribuer et dissimuler une matière dans une classe</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">📊 Gestion Scolarité</h3>
            <p class="text-sm text-gray-500 mt-2">Suivre l'inscription, la validation de inscription d'élèves et paiement scolarité </p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">💳 Inscriptions, adhésions, dons</h3>
            <p class="text-sm text-gray-500 mt-2">Gérez les contributions et les soutiens.</p>
        </div>
    </div>
</section>

<!-- Présentation École -->
<div class="grid md:grid-cols-2 gap-12 items-center mb-12" data-aos="fade-up">
    <div class="space-y-6">
        <h2 class="text-3xl font-bold text-gray-800">Bienvenue à l'École MARIE ALAIN</h2>
        <p class="text-gray-600 text-lg">
            De la maternelle à la terminale, une plateforme unique pour la gestion des inscriptions, enseignants et suivi académique.
        </p>
        <ul class="space-y-4 text-gray-700 text-lg">
            <li class="flex items-center gap-3"><i class="fas fa-check-circle text-green-500 text-xl"></i> Gestion des recrutements</li>
            <li class="flex items-center gap-3"><i class="fas fa-user-graduate text-green-500 text-xl"></i> Inscription & suivi des élèves</li>
            <li class="flex items-center gap-3"><i class="fas fa-chalkboard-teacher text-green-500 text-xl"></i> Gestion des classes et entités</li>
        </ul>
    </div>
    <div class="bg-white p-10 rounded-3xl shadow-xl flex flex-col items-center">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-40 w-auto mb-6">
        <p class="text-gray-600 mb-6 text-center text-lg">
            Accédez à votre espace sécurisé pour gérer vos classes, enseignants et élèves.
        </p>
        <a href="{{ route('login') }}" class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition">
            Se connecter
        </a>
    </div>
</div>

@endsection
@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Acceuil';
@endphp
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



@endsection
@extends('layouts.app')

@section('content')
@auth
@php
$user = auth()->user();
$role = optional($user->role)->name;
@endphp

@if($user)

<!-- Page Header -->
<header class="bg-white shadow p-6 mb-6 rounded-lg" data-aos="fade-down">
    <h1 class="text-3xl font-bold text-gray-800">Bienvenue {{ $user->name }} 👋</h1>
    <p class="text-gray-600 mt-2">
        Votre tableau de bord vous permet de gérer vos classes, enseignants et élèves facilement.
    </p>
</header>

<!-- Contenu spécifique selon rôle -->
@switch($role)
@case('directeur_primaire')

<!-- 🔹 Statistiques en haut -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" data-aos="fade-up">
    <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 flex flex-col items-start">
        <div class="flex items-center justify-between w-full">
            <h3 class="font-semibold text-blue-800">Élèves inscrits</h3>
            <i class="fas fa-users text-blue-500 text-2xl"></i>
        </div>
        <p class="text-3xl font-bold mt-4 text-blue-700">{{ $primaryStudentsCount }}</p>
    </div>

    <div class="bg-green-50 p-6 rounded-lg border border-green-100 flex flex-col items-start">
        <div class="flex items-center justify-between w-full">
            <h3 class="font-semibold text-green-800">Enseignants</h3>
            <i class="fas fa-chalkboard-teacher text-green-500 text-2xl"></i>
        </div>
        <p class="text-3xl font-bold mt-4 text-green-700">{{ $primaryTeacherCount }}</p>
    </div>

    <div class="bg-purple-50 p-6 rounded-lg border border-purple-100 flex flex-col items-start">
        <div class="flex items-center justify-between w-full">
            <h3 class="font-semibold text-purple-800">Classes</h3>
            <i class="fas fa-school text-purple-500 text-2xl"></i>
        </div>
        <p class="text-3xl font-bold mt-4 text-purple-700">{{ $primaryClassCount }}</p>
    </div>
</div>

<!-- 🔹 Vos actions (directeur primaire) -->
<section class="mb-8" data-aos="fade-up">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Vos actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">👩‍🏫 Gestion des enseignants</h3>
            <p class="text-sm text-gray-500 mt-2">Attribuez les classes aux enseignants et suivez leurs affectations.</p>
            <a href="{{ url('/primaire/enseignants/liste') }}" class="text-sm text-blue-600 mt-3 block">
                Gérer les enseignants →
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">📚 Gestion des classes</h3>
            <p class="text-sm text-gray-500 mt-2">Consultez les classes du primaire et leur répartition.</p>
            <a href="{{ url('/primaire/classes/liste') }}" class="text-sm text-blue-600 mt-3 block">
                Voir les classes →
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">👨‍👩‍👧 Suivi des élèves</h3>
            <p class="text-sm text-gray-500 mt-2">Consultez les inscriptions, résultats et suivis académiques.</p>
            <a href="{{ url('/primaire/ecoliers/liste') }}" class="text-sm text-blue-600 mt-3 block">
                Gérer les élèves →
            </a>
        </div>

    </div>
</section>

@break

@case('teacher')
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Vos classes</h2>
    <a href="{{ route('teacher.classes') }}" class="text-blue-600">Voir mes classes</a>
</div>
@break

@case('censeur')
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Gestion académique</h2>
    <a href="{{ route('censeur.classes.index') }}" class="text-blue-600">Liste des classes</a>
</div>
@break

@case('secretaire')
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Inscriptions</h2>
    <a href="{{ route('admin.students.pending') }}" class="text-blue-600">Valider inscriptions</a>
</div>
@break

@case('super_admin')
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Administration Générale</h2>
    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">Accéder au tableau de bord</a>
</div>
@break

@default
<div class="bg-red-50 p-6 rounded-lg shadow text-red-600">
    Votre rôle n’est pas encore défini. Contactez un administrateur.
</div>
@endswitch

@else
<!-- Page d’accueil publique -->
<div class="grid md:grid-cols-2 gap-12 items-center mb-12" data-aos="fade-up">
    <div class="space-y-6">
        <h2 class="text-3xl font-bold text-gray-800">Bienvenue à l'École MARIE ALAIN</h2>
        <p class="text-gray-600 text-lg">
            De la maternelle à la terminale, une plateforme unique pour la gestion des inscriptions, enseignants et suivi académique.
        </p>
    </div>
    <div class="bg-white p-10 rounded-3xl shadow-xl flex flex-col items-center">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="h-40 w-auto mb-6">
        <a href="{{ route('login') }}" class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition">
            Se connecter
        </a>
    </div>
</div>
@endif
@endauth
@endsection
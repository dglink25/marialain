@extends('layouts.app')

@section('content')
    @auth
        @php
            $user = auth()->user();
            $role = optional($user->role)->name;
        @endphp

        <!-- Page Header -->
        <header class="bg-white shadow p-6 mb-6 rounded-lg" data-aos="fade-down">
            <h1 class="text-3xl font-bold text-gray-800">Bienvenue {{ $user->name }} !</h1>
            <p class="text-gray-600 mt-2">
                Votre tableau de bord vous permet de gérer vos classes, enseignants et élèves facilement.
            </p>
        </header>

        <!-- Contenu spécifique selon rôle -->
        @switch($role)
            @case('directeur_primaire')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2">
                            <i class="fas fa-school"></i> Classes du Primaire
                        </h3>
                        <p class="text-3xl font-bold mt-4 text-blue-700">{{ \App\Models\Classe::count() }}</p>
                        <a href="{{ route('primaire.classe.classes') }}" class="text-sm text-blue-600 mt-2 block">Gérer</a>
                    </div>

                    <div class="bg-green-50 p-6 rounded-lg border border-green-100">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2">
                            <i class="fas fa-chalkboard-teacher"></i> Enseignants
                        </h3>
                        <p class="text-3xl font-bold mt-4 text-green-700">{{ \App\Models\User::whereHas('role', fn($q)=>$q->where('name','teacher'))->count() }}</p>
                        <a href="{{ route('primaire.enseignants.enseignants') }}" class="text-sm text-green-600 mt-2 block">Inviter & gérer</a>
                    </div>

                    <div class="bg-purple-50 p-6 rounded-lg border border-purple-100">
                        <h3 class="font-semibold text-purple-800 flex items-center gap-2">
                            <i class="fas fa-users"></i> Élèves
                        </h3>
                        <p class="text-3xl font-bold mt-4 text-purple-700">{{ \App\Models\Student::count() }}</p>
                        <a href="{{ route('primaire.ecoliers.liste') }}" class="text-sm text-purple-600 mt-2 block">Voir la liste</a>
                    </div>
                </div>
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

            @case('surveillant')
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="font-bold text-gray-800 text-xl mb-4">Surveillance</h2>
                    <a href="{{ route('surveillant.dashboard') }}" class="text-blue-600">Aller au dashboard</a>
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

        <!-- Vos actions (section ajoutée) -->
        <section class="mb-8" data-aos="fade-up">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Vos actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <h3 class="font-semibold text-gray-700">👥 Gérer sa communauté</h3>
                    <p class="text-sm text-gray-500 mt-2">Invitez des enseignants, attribuez des classes et gérez les accès.</p>
                    {{-- Exemple de lien (décommenter/swap si tu as une route) --}}
                    {{-- <a href="{{ route('users.index') }}" class="text-sm text-blue-600 mt-3 block">Gérer les utilisateurs →</a> --}}
                </div>

                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <h3 class="font-semibold text-gray-700">📊 Gestion scolarité</h3>
                    <p class="text-sm text-gray-500 mt-2">Suivi des inscriptions, validations et paiements de scolarité.</p>
                    {{-- <a href="{{ route('admin.students.pending') }}" class="text-sm text-blue-600 mt-3 block">Voir les inscriptions →</a> --}}
                </div>

                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <h3 class="font-semibold text-gray-700">💳 Inscriptions & dons</h3>
                    <p class="text-sm text-gray-500 mt-2">Gérez les contributions, les reçus et les historiques de paiement.</p>
                    {{-- <a href="#" class="text-sm text-blue-600 mt-3 block">Gérer les paiements →</a> --}}
                </div>
            </div>
        </section>

     

    @else
        <!-- Page d’accueil publique -->
        <div class="grid md:grid-cols-2 gap-12 items-center mb-12" data-aos="fade-up">
            <div class="space-y-6">
                <h2 class="text-3xl font-bold text-gray-800">Bienvenue à l'École MARIE ALAIN</h2>
                <p class="text-gray-600 text-lg">
                    De la maternelle à la terminale, une plateforme unique pour la gestion des inscriptions, enseignants et suivi académique.
                </p>
                <ul class="space-y-4 text-gray-700 text-lg">
                    <li class="flex items-center gap-3"><i class="fas fa-check-circle text-green-500 text-xl"></i> Gestion des recrutements</li>
                    <li class="flex items-center gap-3"><i class="fas fa-user-graduate text-green-500 text-xl"></i> Inscriptions & suivi des élèves</li>
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
    @endauth
@endsection

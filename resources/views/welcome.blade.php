@extends('layouts.app')

@section('content')

@auth

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
            <h3 class="font-semibold text-gray-700">Gérer sa Communauté</h3>
            <p class="text-sm text-gray-500 mt-2">Invitez, attribuer et dissimuler une matière dans une classe</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">Gestion Scolarité</h3>
            <p class="text-sm text-gray-500 mt-2">Suivre l'inscription, la validation de inscription d'élèves et paiement scolarité </p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">Inscriptions, adhésions, dons</h3>
            <p class="text-sm text-gray-500 mt-2">Gérez les contributions et les soutiens.</p>
        </div>
    </div>
    <!-- Contenu spécifique selon rôle -->
@switch(auth()->user()->id)

@case(3)

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
            <h3 class="font-semibold text-gray-700">Gestion des enseignants</h3>
            <p class="text-sm text-gray-500 mt-2">Attribuez les classes aux enseignants et suivez leurs affectations.</p>
            <a href="{{ url('/primaire/enseignants/liste') }}" class="text-sm text-blue-600 mt-3 block">
                Gérer les enseignants →
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <h3 class="font-semibold text-gray-700">Gestion des classes</h3>
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

@case(4)
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Gestion académique</h2>
    <a href="{{ route('censeur.classes.index') }}" class="text-blue-600">Liste des classes</a>
</div>
@break

@case(6)
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Inscriptions</h2>
    <a href="{{ route('admin.students.pending') }}" class="text-blue-600">Valider inscriptions</a>
</div>
@break

@case(7)
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Administration Générale</h2>
    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">Accéder au tableau de bord</a>
</div>
@break

@default
<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="font-bold text-gray-800 text-xl mb-4">Vos classes</h2>
    <a href="{{ route('teacher.classes') }}" class="text-blue-600">Voir mes classes</a>
</div>
@break

@endswitch

</section>
@else

<!-- Présentation École -->
<div class="relative grid grid-cols-12 gap-5 items-center  mt-10 px-5 lg:px-0 rounded-lg shadow-sm bg-blue-50" data-aos="fade-up">
  <!-- Texte de présentation : 8 colonnes sur 12 -->
  <div class="col-span-12 md:col-span-9 space-y-6">
    <h2 class="text-4xl font-extrabold text-gray-900 leading-tight tracking-tight">
      Bienvenue à l'École MARIE ALAIN
    </h2>
    <p class="text-gray-700 text-lg leading-relaxed">
      De la maternelle à la terminale, une plateforme unique pour la gestion des inscriptions, du personnel enseignant et du suivi académique.
    </p>
    <ul class="space-y-4 text-gray-800 text-base">
      <li class="flex items-center gap-3">
        <i class="fas fa-user-tie text-blue-700 text-xl"></i>
        <span class="font-medium">Gestion des recrutements</span>
      </li>
      <li class="flex items-center gap-3">
        <i class="fas fa-user-graduate text-blue-700 text-xl"></i>
        <span class="font-medium">Inscription & suivi des élèves</span>
      </li>
      <li class="flex items-center gap-3">
        <i class="fas fa-chalkboard-teacher text-blue-700 text-xl"></i>
        <span class="font-medium">Gestion des classes et des entités</span>
      </li>
    </ul>
  </div>

  <!-- Bloc image : 4 colonnes sur 12 + position absolue -->
    <div class="col-span-12 md:col-span-3 flex flex-col items-center mt-6 md:mt-0">
        <img src="{{ asset('logo.png') }}" alt="Logo de l'école" class="h-32.5 w-auto mb-6 max-w-xs ">
  </div>
</div>

<section class=" py-9 px-6 ">
  <div class="max-w-4xl mx-auto text-center space-y-6">
    <h3 class="text-2xl font-bold text-gray-900">
      Veuillez vous connecter
    </h3>
    <p class="text-gray-700 text-lg">
      Pour accéder à votre espace de travail sécurisé, connectez-vous avec vos identifiants.
    </p>
    <a href="{{ route('login') }}"
       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow transition duration-300">
      Se connecter
    </a>
  </div>
</section>




@endauth
@endsection





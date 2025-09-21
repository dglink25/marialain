@extends('layouts.app')


@section('content')
<header class="bg-white shadow p-6">
    <h1 class="text-3xl font-bold text-[#2c3e50]">Bienvenue Valentine !</h1>
    <p class="text-[#7f8c8d] mt-2">
      Votre page d'accueil est votre alliée : elle vous indique les tâches à accomplir pour gérer votre association.
    </p>
  </header>

  <!-- Notifications -->
  <section class="bg-[#fff9db] border-l-4 border-[#f1c40f] p-6 m-6 rounded">
    <h2 class="text-lg font-semibold text-[#f39c12]">🔔 Vérifiez votre compte</h2>
    <p class="text-[#d35400] mt-2">
      Votre compte en ligne est créé. Vous devez le certifier pour débloquer les fonds collectés. Suivez nos conseils.
    </p>
    <button class="mt-4 bg-[#2980b9] text-white px-4 py-2 rounded hover:bg-[#2471a3]">Créer</button>
  </section>

  <!-- Vos actions -->
  <section class="m-6">
    <h2 class="text-xl font-bold text-[#2c3e50] mb-4">🛠️ Vos actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded shadow hover:shadow-md">
        <h3 class="font-semibold text-[#34495e]">👥 Gérer sa Communauté</h3>
        <p class="text-sm text-[#7f8c8d] mt-2">Invitez, modérez et animez votre groupe.</p>
      </div>
      <div class="bg-white p-4 rounded shadow hover:shadow-md">
        <h3 class="font-semibold text-[#34495e]">📊 Gérer sa Comptabilité</h3>
        <p class="text-sm text-[#7f8c8d] mt-2">Suivez les dépenses et recettes de l’association.</p>
      </div>
      <div class="bg-white p-4 rounded shadow hover:shadow-md">
        <h3 class="font-semibold text-[#34495e]">💳 Inscriptions, adhésions, dons</h3>
        <p class="text-sm text-[#7f8c8d] mt-2">Gérez les contributions et les soutiens.</p>
      </div>
    </div>
  </section>

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
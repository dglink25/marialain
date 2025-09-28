@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Tableau de bord';
@endphp
<div class="container mx-auto py-8 px-4">

    <!-- Carte d'accueil -->
<!-- <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 md:p-8 mb-8">
  <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
    <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
    Bienvenue, {{ Auth::user()->name }}
  </h2>
  <p class="text-base text-gray-700 mb-2">
    Ravi de vous revoir sur votre espace enseignant.
  </p>
  <p class="text-sm text-gray-500">
    Gérez vos classes, vos élèves et consultez rapidement vos emplois du temps.
  </p>
</div> -->


    <!-- Statistiques clés -->
    <section class="max-w-4xl mx-auto pb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Nombre de classes -->
      <div class="bg-blue-600 text-white rounded-lg shadow p-6 text-center">
        <i class="fas fa-users text-4xl mb-3"></i>
        <h3 class="text-lg font-medium">Nombre de Classes Assignées</h3>
        <p class="text-3xl font-bold mt-2">5</p>
      </div>

      <!-- Matières enseignées -->
      <div class="bg-yellow-400 text-gray-900 rounded-lg shadow p-6 text-center">
        <i class="fas fa-book-open text-4xl mb-3"></i>
        <h3 class="text-lg font-medium">Matières Enseignées</h3>
        <ul class="mt-2 space-y-1 text-base font-semibold">
          <li>Mathématiques</li>
          <li>Sciences Physiques</li>
          <li>Technologie</li>
        </ul>
      </div>
    </section>

    <!-- Informations personnelles -->
    <section class="w-full px-4 mt-8">
      <div class="bg-white rounded-xl shadow-md w-full">
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-xl">
          <h2 class="text-lg font-semibold"><i class="fas fa-id-card mr-2"></i> Mes Informations</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
            <div class="flex items-center text-gray-700">
              <i class="fas fa-user text-blue-600 mr-2"></i>
              <span class="font-medium">Nom :</span>
            </div>
            <div class="text-gray-800 font-semibold">M. Jean Kossi</div>

            <div class="flex items-center text-gray-700">
              <i class="fas fa-envelope text-gray-500 mr-2"></i>
              <span class="font-medium">Email :</span>
            </div>
            <div class="text-gray-800 font-semibold">jean.kossiau</div>

            <div class="flex items-center text-gray-700">
              <i class="fas fa-school text-green-500 mr-2"></i>
              <span class="font-medium">Établissement :</span>
            </div>
            <div class="text-gray-800 font-semibold">Collège Lokossa</div>

            <div class="flex items-center text-gray-700">
              <i class="fas fa-phone-alt text-yellow-500 mr-2"></i>
              <span class="font-medium">Téléphone :</span>
            </div>
            <div class="text-gray-800 font-semibold">0145583172</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Meilleures performances -->
    <section class="w-full px-4 mt-8">
      <div class="bg-white rounded-xl shadow-md w-full">
        <div class="bg-green-600 text-white px-6 py-4 rounded-t-xl">
          <h2 class="text-lg font-semibold flex items-center gap-2">
            <i class="fas fa-trophy"></i> Meilleures Performances
          </h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Élève 1 -->
            <div class="bg-green-50 hover:bg-green-100 transition duration-300 rounded-lg p-5 shadow text-center">
              <i class="fas fa-medal text-yellow-500 text-3xl mb-3"></i>
              <h3 class="text-lg font-semibold text-gray-800">Ahouanvoédo Mireille</h3>
              <p class="text-sm text-gray-600">Classe : <span class="font-medium text-gray-700">3e A</span></p>
              <p class="text-sm text-gray-600">Moyenne Générale</p>
              <p class="text-2xl font-bold text-green-700 mt-2">18.5 / 20</p>
            </div>

            <!-- Élève 2 -->
            <div class="bg-green-50 hover:bg-green-100 transition duration-300 rounded-lg p-5 shadow text-center">
              <i class="fas fa-medal text-gray-500 text-3xl mb-3"></i>
              <h3 class="text-lg font-semibold text-gray-800">Kossi Rodrigue</h3>
              <p class="text-sm text-gray-600">Classe : <span class="font-medium text-gray-700">4e B</span></p>
              <p class="text-sm text-gray-600">Moyenne Générale</p>
              <p class="text-2xl font-bold text-green-700 mt-2">17.8 / 20</p>
            </div>

            <!-- Élève 3 -->
            <div class="bg-green-50 hover:bg-green-100 transition duration-300 rounded-lg p-5 shadow text-center">
              <i class="fas fa-medal text-orange-400 text-3xl mb-3"></i>
              <h3 class="text-lg font-semibold text-gray-800">Tchibozo Grâce</h3>
              <p class="text-sm text-gray-600">Classe : <span class="font-medium text-gray-700">3e C</span></p>
              <p class="text-sm text-gray-600">Moyenne Générale</p>
              <p class="text-2xl font-bold text-green-700 mt-2">17.2 / 20</p>
            </div>

          </div>
        </div>
      </div>
    </section>

  </div>
@endsection

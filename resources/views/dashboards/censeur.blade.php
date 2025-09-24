@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow">
    <!-- Titre -->
    <h1 class="text-2xl font-bold mb-6 text-green-700">Dashboard - Censeur</h1>

    <!-- Infos personnelles -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="p-4 border rounded bg-gray-50">
            <h2 class="font-semibold text-lg mb-3">Informations personnelles</h2>
            <p><strong>Nom : </strong>Maurel LOGBO</p>
            <p><strong>Email : </strong>logbomaurel@gmail.com </p>
            <p><strong>Téléphone : </strong>0190078988</p>
            <p><strong>Rôle : </strong></p>
        </div>

        <!-- Statistiques rapides -->
        <div class="p-4 border rounded bg-gray-50">
            <h2 class="font-semibold text-lg mb-3">Statistiques</h2>
            <ul class="text-gray-700 space-y-2">
                <li>📚 <strong></strong> matières supervisées</li>
                <li>👩‍🏫 <strong></strong> professeurs</li>
                <li>👨‍🎓 <strong></strong> élèves inscrits</li>
                <li>📝 <strong></strong> notes en attente de validation</li>
            </ul>
        </div>
    </div>

    <!-- Actions principales -->
    <div class="p-4 border rounded mb-6 bg-gray-50">
        <h2 class="font-semibold text-lg mb-3">Actions rapides</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="" class="block bg-green-100 hover:bg-green-200 p-3 rounded text-center shadow">
                ➕ Gérer les professeurs
            </a>
            <a href="" class="block bg-blue-100 hover:bg-blue-200 p-3 rounded text-center shadow">
                📘 Gérer les matières
            </a>
            <a href="" class="block bg-yellow-100 hover:bg-yellow-200 p-3 rounded text-center shadow">
                🏫 Gérer les classes
            </a>
            <a href="" class="block bg-purple-100 hover:bg-purple-200 p-3 rounded text-center shadow">
                📅 Emploi du temps
            </a>
            <a href="" class="block bg-red-100 hover:bg-red-200 p-3 rounded text-center shadow">
                ✅ Gérer les notes
            </a>
            <a href="" class="block bg-gray-100 hover:bg-gray-200 p-3 rounded text-center shadow">
                📊 Rapports et statistiques
            </a>
        </div>
    </div>
    </div>
</div>
@endsection

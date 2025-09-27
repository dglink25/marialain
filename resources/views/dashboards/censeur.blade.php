@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Tableau de bord Censeur';
@endphp

<div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow">
    <!-- Titre -->
    <h1 class="text-2xl font-bold mb-6 text-green-700">Dashboard - Censeur</h1>

    <!-- Infos personnelles -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="p-4 border rounded bg-gray-50">
            <h2 class="font-semibold text-lg mb-3">Informations personnelles</h2>
            <p><strong>Nom : </strong>{{ old('name',$user->name) }}</p>
            <p><strong>Email : </strong>{{ old('name',$user->email) }}</p>
            <p><strong>Téléphone : </strong>{{ old('name',$user->phone) }}</p>
            <p><strong>Titre : </strong>Censeur</p>
        </div>
        @if(isset($studentsCount) && isset($teachersCount) && isset($classesCount))
            <!-- Statistiques rapides -->
            <div class="p-4 border rounded bg-gray-50">
                <h2 class="font-semibold text-lg mb-3">Statistiques</h2>
                <ul class="text-gray-700 space-y-2">
                    <li><strong>Nombre total d'élèves Secondaire : </strong>{{ $studentsCount }}</li>
                    <li><strong>Nombre total d'enseignants Secondaire : </strong>{{ $teachersCount }}</li>
                    <li><strong>Nombre de classes : </strong>{{ $classesCount }}</li>
                </ul>

            </div>
            @else
            <!-- Statistiques rapides -->
            <div class="p-4 border rounded bg-gray-50">
                <h2 class="font-semibold text-lg mb-3">Statistiques</h2>
                <ul class="text-gray-700 space-y-2">
                    <li><strong>Nombre total d'élèves Secondaire : </strong>00</li>
                    <li><strong>Nombre total d'enseignants Secondaire : </strong>00</li>
                    <li><strong>Nombre de classes : </strong>00</li>
                </ul>

            </div>
        @endif
    </div>

    <!-- Actions principales -->
    <div class="p-4 border rounded mb-6 bg-gray-50">
        <h2 class="font-semibold text-lg mb-3">Actions rapides</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('censeur.invitations.index') }}" class="block bg-green-100 hover:bg-green-200 p-3 rounded text-center shadow">
                Gérer les professeurs
            </a>
            <a href="{{ route('censeur.subjects.index') }}" class="block bg-blue-100 hover:bg-blue-200 p-3 rounded text-center shadow">
                Gérer les matières
            </a>
            <a href="{{ route('censeur.classes.index') }}" class="block bg-yellow-100 hover:bg-yellow-200 p-3 rounded text-center shadow">
                Gérer les classes
            </a>
            <a href="{{ route('censeur.classes.index') }}" class="block bg-purple-100 hover:bg-purple-200 p-3 rounded text-center shadow">
                Emploi du temps
            </a>
            <a href="" class="block bg-red-100 hover:bg-red-200 p-3 rounded text-center shadow">
                Gérer les notes
            </a>
            <a href="" class="block bg-gray-100 hover:bg-gray-200 p-3 rounded text-center shadow">
                Rapports et statistiques
            </a>
        </div>
    </div>
    
</div>
@endsection
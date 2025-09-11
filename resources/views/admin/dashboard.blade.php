@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Tableau de bord Fondateur de CPEG MARIE-ALAIN</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Années académiques -->
        <div class="bg-white p-6 rounded-lg shadow flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-700">Années académiques</h2>
                <p class="text-3xl font-bold mt-2 text-blue-600">{{ $academicYearsCount }}</p>
            </div>
            <a href="{{ route('admin.academic_years.index') }}" class="mt-4 inline-block text-blue-600 hover:underline">Voir toutes</a>
        </div>

        <!-- Classes -->
        <div class="bg-white p-6 rounded-lg shadow flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-700">Classes</h2>
                <p class="text-3xl font-bold mt-2 text-green-600">{{ $classesCount }}</p>
            </div>
            <a href="{{ route('admin.classes.index') }}" class="mt-4 inline-block text-green-600 hover:underline">Voir toutes</a>
        </div>

        <!-- Invitations -->
        <div class="bg-white p-6 rounded-lg shadow flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-700">Invitations envoyées</h2>
                <p class="text-3xl font-bold mt-2 text-purple-600">{{ $invitationsCount }}</p>
            </div>
            <a href="{{ route('admin.invitations.index') }}" class="mt-4 inline-block text-purple-600 hover:underline">Voir toutes</a>
        </div>
    </div>

    <!-- Section complémentaire (optionnelle) -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Résumé rapide</h2>
        <p class="text-gray-600">Bienvenue sur la plateforme MARI ALAIN. Vous pouvez gérer les années académiques, les classes, et envoyer des invitations aux enseignants directement depuis ce tableau de bord.</p>
    </div>
</div>
@endsection
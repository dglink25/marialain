@extends('layouts.app')

@section('content')
<div class="container mx-auto ">
    <!--  Message d’erreur -->
    @if(session('error'))
        <div class="bg-red-100 text-red-700 border border-red-300 p-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!--  Titre principal -->
    <div class=" flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Année académique : <span class="text-blue-600">{{ $annee_academique->name }}</span>
        </h1> 
        <h2 class="text-lg font-semibold text-gray-600 mt-3 md:mt-0">
             Liste des classes du Primaire
        </h2>
    </div>

    <!--  Tableau -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-blue-200 to-orange-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">N°</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Classe</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Enseignant</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Emploi du temps</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($classes as $index => $class)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-gray-800">{{ $class->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md text-sm 
                                {{ $class->teacher ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $class->teacher?->name ?? 'Non assigné' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('schedules.ind', $class->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium transition">
                                Consulter
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('primaire.classe.showclass', $class->id) }}" 
                               class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                                <i class="fas fa-eye mr-2"></i> Voir
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

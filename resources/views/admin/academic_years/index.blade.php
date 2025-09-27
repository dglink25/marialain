@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Gestion des années';
@endphp
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Gestion des années académiques</h1>
        <p class="text-gray-600">Administration des périodes académiques - CPEG MARIE-ALAIN</p>
    </div>

    <!-- Formulaire de création -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-plus-circle"></i>
            Créer une nouvelle année académique
        </h2>
        
        <form method="POST" action="{{ route('admin.academic_years.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'année</label>
                    <input type="text" name="name" placeholder="Ex: 2025-2026"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200"
                           required>
                </div>

                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="active" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200" required>
                        <option value="">Sélectionner un statut</option>
                        <option value="1">Activée</option>
                        <option value="0">Désactivée</option>
                    </select>
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 font-medium flex items-center gap-2 justify-center">
                        <i class="fas fa-save"></i>
                        Créer l'année
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des années académiques -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-list"></i>
            Liste des années académiques
        </h2>

        @if($years->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-3xl text-gray-400 mb-3"></i>
                <p class="text-gray-600">Aucune année académique créée</p>
                <p class="text-gray-500 text-sm mt-1">Commencez par créer votre première année académique</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Année académique
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Statut
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($years as $year)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">
                                    {{ $year->name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $year->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <i class="fas fa-circle text-xs mr-1 {{ $year->active ? 'text-green-500' : 'text-gray-500' }}"></i>
                                        {{ $year->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <a href="{{ route('admin.academic_years.edit', $year->id) }}"
                                           class="bg-yellow-500 text-white px-3 py-1 rounded text-xs hover:bg-yellow-600 transition duration-200 flex items-center gap-1 justify-center whitespace-nowrap">
                                            <i class="fas fa-edit"></i>
                                            Modifier
                                        </a>
                                        <form method="POST" action="{{ route('admin.academic_years.destroy', $year->id) }}"
                                              onsubmit="return confirm('Attention ! En supprimant cette année, toutes les données associées seront perdues. Êtes-vous sûr ?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition duration-200 flex items-center gap-1 justify-center w-full whitespace-nowrap">
                                                <i class="fas fa-trash"></i>
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Message d'information -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <div>
                <p class="text-blue-800 font-medium">Important</p>
                <p class="text-blue-700 text-sm mt-1">
                    Une seule année académique peut être active à la fois. L'activation d'une année désactivera automatiquement les autres.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    /* Adaptation mobile */
    @media (max-width: 768px) {
        table {
            min-width: 400px;
        }
        
        th, td {
            padding: 0.75rem 0.5rem;
        }
    }
    
    @media (max-width: 640px) {
        .grid-cols-1 > * {
            width: 100%;
        }
    }
</style>
@endsection
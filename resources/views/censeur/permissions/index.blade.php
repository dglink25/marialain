@extends('layouts.app')

@section('content')
@php
    $pageTitle = "Gestion des autorisations";
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gestion des autorisations</h1>
                    <p class="text-lg text-gray-600 mt-2">Classe : {{ $classe->name }}</p>
                </div>
                <a href="{{ url()->previous() }}" 
                   class="inline-flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour
                </a>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- En-tête de la carte -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Autorisations des trimestres</h2>
                        <p class="text-blue-100 text-sm">Gérez l'accès aux bulletins par trimestre</p>
                    </div>
                </div>
            </div>

            <!-- Tableau amélioré -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Trimestre</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">État</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($permissions as $perm)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Trimestre -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                        <span class="text-blue-600 font-semibold">{{ $perm->trimestre }}</span>
                                    </div>
                                    <span class="text-gray-900 font-medium">Trimestre {{ $perm->trimestre }}</span>
                                </div>
                            </td>
                            
                            <!-- État -->
                            <td class="px-6 py-4">
                                @if($perm->is_open)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Ouvert
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Fermé
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Action -->
                            <td class="px-6 py-4">
                                <form action="{{ route('censeur.permissions.toggle', [$classe->id, $perm->trimestre]) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                                                   {{ $perm->is_open 
                                                      ? 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:border-red-300' 
                                                      : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 hover:border-green-300' }}">
                                        <i class="fas {{ $perm->is_open ? 'fa-lock' : 'fa-unlock' }} mr-2"></i>
                                        {{ $perm->is_open ? 'Révoquer' : 'Autoriser' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pied de tableau -->
            @if($permissions->isEmpty())
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-ban text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune autorisation configurée</h3>
                <p class="text-gray-500">Les autorisations de trimestre apparaîtront ici une fois créées.</p>
            </div>
            @endif
        </div>

        <!-- Informations supplémentaires -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mt-1 mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-2">Information</h3>
                        <p class="text-blue-700 text-sm">
                            Lorsqu'un trimestre est "Ouvert", les enseignants peuvent saisir les notes et appréciations.
                            Lorsqu'il est "Fermé", la saisie est bloquée.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                <div class="flex items-start">
                    <i class="fas fa-clock text-green-600 text-xl mt-1 mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-green-900 mb-2">Conseil</h3>
                        <p class="text-green-700 text-sm">
                            Pensez à ouvrir les trimestres pendant les périodes de saisie 
                            et à les fermer une fois les bulletins édités.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles additionnels -->
<style>
    tr:last-child {
        border-bottom: none;
    }
</style>

<!-- Script pour les interactions -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ajout d'une confirmation pour la révocation
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = this.querySelector('button');
                if (button.textContent.includes('Révoquer')) {
                    if (!confirm('Êtes-vous sûr de vouloir révoquer l\'accès à ce trimestre ?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    });
</script>
@endsection
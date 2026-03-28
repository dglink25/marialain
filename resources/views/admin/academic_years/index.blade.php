@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-8 py-4 sm:py-6">
    <!-- En-tête -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Gestion des années académiques</h1>
        <p class="text-sm sm:text-base text-gray-600">Administration des périodes académiques - CPEG MARIE-ALAIN</p>
    </div>

    <!-- Formulaire de création -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 mb-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-plus-circle text-blue-600"></i>
            Créer une nouvelle année académique
        </h2>
        
        <form method="POST" action="{{ route('admin.academic_years.store') }}" class="space-y-4 sm:space-y-0">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'année</label>
                    <input type="text" name="name" placeholder="Ex: 2025-2026"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm sm:text-base focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200"
                           required>
                </div>

                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="active" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm sm:text-base focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200" required>
                        <option value="">Sélectionner</option>
                        <option value="1">Activée</option>
                        <option value="0">Désactivée</option>
                    </select>
                </div>

                <div class="sm:col-span-1 flex items-end">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 font-medium flex items-center gap-2 justify-center text-sm sm:text-base">
                        <i class="fas fa-save"></i>
                        <span class="whitespace-nowrap">Créer l'année</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des années académiques -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list text-blue-600"></i>
                Liste des années académiques
            </h2>
            <div class="bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                <p class="text-xs sm:text-sm text-gray-600">Total: <span class="font-medium">{{ $years->count() }} année(s)</span></p>
            </div>
        </div>

        @if($years->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-3xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 text-sm sm:text-base">Aucune année académique créée</p>
                <p class="text-gray-500 text-xs sm:text-sm mt-1">Commencez par créer votre première année académique</p>
            </div>
        @else
            <div class="overflow-x-auto -mx-2 sm:mx-0">
                <div class="min-w-full inline-block align-middle">
                    <table class="min-w-full text-sm sm:text-base">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs sm:text-sm whitespace-nowrap">
                                    Année académique
                                </th>
                                <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs sm:text-sm whitespace-nowrap">
                                    Statut
                                </th>
                                <th class="px-3 sm:px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs sm:text-sm whitespace-nowrap">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($years as $year)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-3 sm:px-4 py-3 whitespace-nowrap font-medium text-gray-900 text-sm sm:text-base">
                                        {{ $year->name }}
                                    </td>
                                    <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium 
                                            {{ $year->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            <i class="fas fa-circle text-[10px] mr-1 {{ $year->active ? 'text-green-500' : 'text-gray-500' }}"></i>
                                            {{ $year->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                        <div class="flex flex-col xs:flex-row gap-1 sm:gap-2">
                                            <a href="{{ route('admin.academic_years.edit', $year->id) }}"
                                               class="bg-yellow-500 text-white px-2 sm:px-3 py-1.5 rounded text-xs hover:bg-yellow-600 transition duration-200 flex items-center gap-1 justify-center whitespace-nowrap min-w-[70px]">
                                                <i class="fas fa-edit text-[10px] sm:text-xs"></i>
                                                <span class="hidden xs:inline">Modifier</span>
                                                <span class="xs:hidden">Modif</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.academic_years.destroy', $year->id) }}"
                                                  onsubmit="return confirm('Attention ! En supprimant cette année, toutes les données associées seront perdues. Êtes-vous sûr ?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="bg-red-500 text-white px-2 sm:px-3 py-1.5 rounded text-xs hover:bg-red-600 transition duration-200 flex items-center gap-1 justify-center w-full xs:w-auto whitespace-nowrap min-w-[70px]">
                                                    <i class="fas fa-trash text-[10px] sm:text-xs"></i>
                                                    <span class="hidden xs:inline">Supprimer</span>
                                                    <span class="xs:hidden">Supp</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Message d'information -->
    <div class="mt-4 sm:mt-6 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
        <div class="flex items-start gap-2 sm:gap-3">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 text-sm sm:text-base"></i>
            <div class="flex-1">
                <p class="text-blue-800 font-medium text-sm sm:text-base">Important</p>
                <p class="text-blue-700 text-xs sm:text-sm mt-1 leading-relaxed">
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
    
    /* Adaptation pour très petits écrans */
    @media (max-width: 480px) {
        .min-w-full {
            min-width: 320px;
        }
        
        .px-3 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
    }
    
    @media (max-width: 380px) {
        .grid-cols-1 > * {
            width: 100%;
        }
        
        .flex-col.xs\:flex-row {
            flex-direction: column;
        }
        
        .min-w-\[70px\] {
            min-width: 100%;
        }
    }
    
    /* Pour les écrans entre 480px et 640px */
    @media (min-width: 480px) and (max-width: 640px) {
        .sm\:grid-cols-2 > * {
            width: 100%;
        }
    }
</style>

<script>
    // Amélioration de l'expérience mobile
    document.addEventListener('DOMContentLoaded', function() {
        // Adaptation des formulaires sur mobile
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (window.innerWidth < 640) {
                    // Petit délai pour améliorer le feedback visuel sur mobile
                    setTimeout(() => {
                        const submitBtn = this.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
                            submitBtn.disabled = true;
                        }
                    }, 100);
                }
            });
        });
    });
</script>
@endsection
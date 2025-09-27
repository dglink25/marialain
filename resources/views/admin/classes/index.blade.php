@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- En-tête principal -->
    <div class="mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Gestion des classes</h1>
        <p class="text-gray-600">Administration des classes - CPEG MARIE-ALAIN</p>
    </div>

    @if(!$activeYear)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded-lg mb-6">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                {{ $message }}
            </div>
        </div>
    @else
        <!-- Section Ajout de classe -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chalkboard"></i>
                    Liste des classes
                </h2>
                <button id="toggleFormBtn" 
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i>
                    Ajouter une classe
                </button>
            </div>

            <!-- Formulaire d'ajout -->
            <form method="POST" action="{{ route('admin.classes.store') }}" 
                  id="classForm"
                  class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4 mt-4">
                @csrf
                <h3 class="text-md font-semibold mb-4 text-gray-700">Nouvelle classe</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom de la classe</label>
                        <input type="text" name="name" id="name" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200" 
                               required placeholder="Ex: Terminale A">
                    </div>

                    <div>
                        <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1">Année académique</label>
                        <select name="academic_year_id" id="academic_year_id" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200" 
                                required>
                            @foreach($years as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="entity_id" class="block text-sm font-medium text-gray-700 mb-1">Entité</label>
                        <select name="entity_id" id="entity_id" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200" 
                                required>
                            @foreach($entities as $entity)
                                <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="school_fees" class="block text-sm font-medium text-gray-700 mb-1">Frais de scolarité (FCFA)</label>
                        <input type="number" step="0.01" name="school_fees" id="school_fees" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200" 
                               required placeholder="Ex: 150000">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        Créer la classe
                    </button>
                    <button type="button" id="cancelFormBtn" 
                            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-200 flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Annuler
                    </button>
                </div>
            </form>
        </div>

        <!-- Section Filtrage et Tableau -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <!-- Filtrage -->
            <form method="GET" class="mb-6">
                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filtrer par entité</label>
                        <div class="flex gap-2">
                            <select name="entity_id" 
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-500 transition duration-200">
                                <option value="">Toutes les entités</option>
                                @foreach($entities as $entity)
                                    <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                        {{ $entity->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" 
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center gap-2 whitespace-nowrap">
                                <i class="fas fa-filter"></i>
                                Appliquer
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tableau responsive -->
            <div class="overflow-x-auto">
                <table class="w-full bg-white text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Classe
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Entité
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Année Académique
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Frais de scolarité
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($classes as $classe)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">
                                {{ $classe->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                {{ ucfirst($classe->entity->name) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                {{ $classe->academicYear->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-700 font-semibold">
                                {{ number_format($classe->school_fees, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <a href="{{ route('admin.classes.edit', $classe->id) }}"
                                       class="bg-yellow-500 text-white px-3 py-1 rounded text-xs hover:bg-yellow-600 transition duration-200 flex items-center gap-1 justify-center whitespace-nowrap">
                                        <i class="fas fa-edit text-xs"></i>
                                        Modifier
                                    </a>
                                    <form method="POST" action="{{ route('admin.classes.destroy', $classe->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition duration-200 flex items-center gap-1 justify-center w-full whitespace-nowrap"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')">
                                            <i class="fas fa-trash text-xs"></i>
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

            <!-- Pagination -->
            @if($classes->hasPages())
                <div class="mt-6 border-t border-gray-200 pt-4">
                    {{ $classes->links('pagination::tailwind') }}
                </div>
            @endif
        </div>

        <!-- Message si aucune classe -->
        @if($classes->isEmpty())
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <i class="fas fa-chalkboard-teacher text-3xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 font-medium">Aucune classe trouvée</p>
                <p class="text-gray-500 text-sm mt-1">Commencez par créer votre première classe</p>
            </div>
        @endif
    @endif
</div>

<!-- Script JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById("toggleFormBtn");
        const cancelBtn = document.getElementById("cancelFormBtn");
        const form = document.getElementById("classForm");

        if (toggleBtn && form) {
            toggleBtn.addEventListener("click", () => {
                form.classList.toggle("hidden");
                form.classList.toggle("block");
            });
        }

        if (cancelBtn && form) {
            cancelBtn.addEventListener("click", () => {
                form.classList.add("hidden");
                form.classList.remove("block");
            });
        }

        // Focus sur le premier champ quand le formulaire s'ouvre
        if (form) {
            form.addEventListener('transitionend', function() {
                if (!form.classList.contains('hidden')) {
                    const firstInput = form.querySelector('input, select');
                    if (firstInput) firstInput.focus();
                }
            });
        }
    });
</script>

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
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Animation douce pour l'affichage du formulaire */
    #classForm {
        transition: all 0.3s ease-in-out;
    }
    
    /* Adaptation mobile */
    @media (max-width: 768px) {
        table {
            min-width: 600px;
        }
        
        th, td {
            padding: 0.75rem 0.5rem;
        }
    }
</style>
@endsection
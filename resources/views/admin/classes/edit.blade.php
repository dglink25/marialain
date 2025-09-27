@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <!-- En-tête avec fond solide -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 p-3 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Modifier la classe</h1>
                    <p class="text-blue-100">{{ $classe->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages d'erreur -->
    @if ($errors->any())
    <div class="mx-8 mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-red-800 font-semibold">Veuillez corriger les erreurs suivantes :</h3>
        </div>
        <ul class="mt-2 text-red-700 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Formulaire -->
    <form method="POST" action="{{ route('admin.classes.update', $classe->id) }}" class="p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations de base -->
            <div class="md:col-span-2">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Informations de la classe</h2>
            </div>

            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom de la classe *</label>
                <input type="text" name="name" id="name" 
                       value="{{ old('name', $classe->name) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                       placeholder="Ex: CP1, 6ème A, Terminale S"
                       required>
            </div>

            <div>
                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">Année académique *</label>
                <select name="academic_year_id" id="academic_year_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        required>
                    <option value="">-- Sélectionnez une année --</option>
                    @foreach($years as $year)
                        <option value="{{ $year->id }}" 
                            {{ old('academic_year_id', $classe->academic_year_id) == $year->id ? 'selected' : '' }}>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="entity_id" class="block text-sm font-medium text-gray-700 mb-2">Entité *</label>
                <select name="entity_id" id="entity_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        required>
                    <option value="">-- Sélectionnez une entité --</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}" 
                            {{ old('entity_id', $classe->entity_id) == $entity->id ? 'selected' : '' }}>
                            {{ $entity->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="school_fees" class="block text-sm font-medium text-gray-700 mb-2">Frais de scolarité (FCFA) *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500"></span>
                    </div>
                    <input type="number" step="0.01" name="school_fees" id="school_fees" 
                           value="{{ old('school_fees', $classe->school_fees) }}" 
                           class="w-full px-4 py-3 pl-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           placeholder="0.00"
                           min="0"
                           required>
                </div>
                <p class="text-sm text-gray-500 mt-1">Montant total des frais de scolarité pour l'année</p>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 pt-8 mt-8 border-t border-gray-200">
            <a href="{{ route('admin.classes.index') }}" 
               class="w-full sm:w-auto px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200 flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Annuler
            </a>

            <div class="flex space-x-3">
                <button type="submit" 
                        class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center font-semibold">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mettre à jour
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Animation de focus sur les champs
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('focus', function() {
            this.classList.add('ring-2', 'ring-blue-200');
        });
        
        element.addEventListener('blur', function() {
            this.classList.remove('ring-2', 'ring-blue-200');
        });
    });

    // Formatage automatique des frais de scolarité
    const schoolFeesInput = document.getElementById('school_fees');
    
    schoolFeesInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });

    // Validation en temps réel
    schoolFeesInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
</script>

<style>
    /* Styles personnalisés pour améliorer l'apparence */
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Amélioration de l'apparence des inputs number */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
@endsection
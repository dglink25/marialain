@extends('layouts.app')

@section('content')
@if(auth()->check())
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
                    <h1 class="text-2xl font-bold text-white">Modifier l'étudiant</h1>
                    <p class="text-blue-100">{{ $student->last_name }} {{ $student->first_name }}</p>
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
    <form method="POST" action="{{ route('admin.students.update', $student->id) }}" enctype="multipart/form-data" class="p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations personnelles -->
            <div class="md:col-span-2">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Informations personnelles</h2>
            </div>

            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input type="text" name="last_name" id="last_name" 
                       value="{{ old('last_name', $student->last_name) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                       required>
            </div>

            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Prénoms *</label>
                <input type="text" name="first_name" id="first_name" 
                       value="{{ old('first_name', $student->first_name) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                       required>
            </div>

            <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">Date de naissance *</label>
                <input type="date" name="birth_date" id="birth_date" 
                       min="{{ date('Y-m-d', strtotime('-50 years')) }}" 
                       max="{{ date('Y-m-d') }}" 
                       value="{{ old('birth_date', $student->birth_date) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                       required>
            </div>

            <div>
                <label for="birth_place" class="block text-sm font-medium text-gray-700 mb-2">Lieu de naissance *</label>
                <input type="text" name="birth_place" id="birth_place"
                    value="{{ old('birth_place', $student->birth_place) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    required>
            </div>

            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Sexe *</label>
                <select name="gender" id="gender" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        required>
                    <option value="">Sélectionnez une option</option>
                    <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>Féminin</option>
                </select>
            </div>

            <div>
                <label for="num_educ" class="block text-sm font-medium text-gray-700 mb-2">Numéro Éduc Master *</label>
                <input type="text" name="num_educ" id="num_educ"
                    value="{{ old('num_educ', $student->num_educ) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    required>
            </div>

            <!-- Informations scolaires -->
            <div class="md:col-span-2 mt-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Informations scolaires</h2>
            </div>

            <div>
                <label for="entity_id" class="block text-sm font-medium text-gray-700 mb-2">Entité *</label>
                <select name="entity_id" id="entity_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        required>
                    <option value="">-- Sélectionnez une entité --</option>
                    @foreach ($entities as $entity)
                        <option value="{{ $entity->id }}" 
                            {{ old('entity_id', $student->entity_id) == $entity->id ? 'selected' : '' }}>
                            {{ $entity->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="classe_id" class="block text-sm font-medium text-gray-700 mb-2">Classe *</label>
                <select name="classe_id" id="classe_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                        required>
                    <option value="">-- Sélectionnez une classe --</option>
                    @foreach ($classes as $classe)
                        <option value="{{ $classe->id }}" 
                            {{ old('classe_id', $student->classe_id) == $classe->id ? 'selected' : '' }}>
                            {{ $classe->name }} ({{ $classe->entity->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Informations du parent -->
            <div class="md:col-span-2 mt-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Informations du parent/tuteur</h2>
            </div>

            <div class="md:col-span-2">
                <label for="parent_full_name" class="block text-sm font-medium text-gray-700 mb-2">Nom complet du parent *</label>
                <input type="text" name="parent_full_name" id="parent_full_name" 
                    value="{{ old('parent_full_name', $student->parent_full_name) }}" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    required>
            </div>

            <div>
                <label for="parent_email" class="block text-sm font-medium text-gray-700 mb-2">Email du parent *</label>
                <input type="email" name="parent_email" id="parent_email" 
                    value="{{ old('parent_email', $student->parent_email) }}" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    required>
            </div>

            <div>
                <label for="parent_phone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone du parent *</label>
                <input type="text" name="parent_phone" id="parent_phone"
                    value="{{ old('parent_phone', $student->parent_phone) }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    required>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 pt-8 mt-8 border-t border-gray-200">
            <a href="{{ route('admin.students.index') }}" 
               class="w-full sm:w-auto px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200 flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Annuler
            </a>

            <button type="submit" 
                    class="w-full sm:w-auto px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center font-semibold">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mettre à jour l'étudiant
            </button>
        </div>
    </form>
</div>

<script>
    // Gestion des classes dynamiques
    const entitySelect = document.getElementById('entity_id');
    const classeSelect = document.getElementById('classe_id');

    entitySelect.addEventListener('change', function () {
        const entityId = this.value;

        // Charger les classes dynamiquement
        if (entityId) {
            fetch(`/admin/entities/${entityId}/classes`)
                .then(res => res.json())
                .then(data => {
                    classeSelect.innerHTML = '<option value="">-- Sélectionnez une classe --</option>';
                    data.forEach(cls => {
                        const selected = cls.id == {{ old('classe_id', $student->classe_id) }} ? 'selected' : '';
                        classeSelect.innerHTML += `<option value="${cls.id}" ${selected}>${cls.name}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des classes:', error);
                });
        } else {
            classeSelect.innerHTML = '<option value="">-- Sélectionnez une classe --</option>';
        }
    });

    // Animation de focus sur les champs
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-blue-200');
        });
        
        element.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-blue-200');
        });
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
</style>
@else
<div class="max-w-md mx-auto mt-8 bg-red-50 border border-red-200 rounded-lg p-6 text-center">
    <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <h3 class="text-lg font-semibold text-red-800 mb-2">Session expirée</h3>
    <p class="text-red-600 mb-4">Veuillez vous reconnecter pour continuer</p>
    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
        Se connecter
    </a>
</div>
@endif 
@endsection
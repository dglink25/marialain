@extends('layouts.app')

@section('content')
<!-- Conteneur principal avec padding responsive -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <!-- En-tête responsive -->
    <div class="mb-6 sm:mb-8">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Modifier l'année académique</h1>
        <p class="text-sm text-gray-600">Mettez à jour les informations de l'année académique</p>
    </div>

    <!-- Messages d'alerte -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 sm:mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 sm:mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-red-800 font-medium mb-2">Erreurs de validation :</p>
                    <ul class="list-disc list-inside text-red-700 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulaire avec ombres et espacement responsive -->
    <form method="POST" action="{{ route('admin.academic_years.update', $academicYear->id) }}" 
          class="bg-white shadow-sm sm:shadow-md rounded-lg p-4 sm:p-6 border border-gray-200">
        @csrf
        @method('PUT')

        <!-- Champ nom -->
        <div class="mb-4 sm:mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom de l'année</label>
            <input type="text" name="name" id="name" value="{{ old('name', $academicYear->name) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 sm:px-4 sm:py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm sm:text-base"
                   placeholder="Ex: 2024-2025" required>
            @error('name')
                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Champ statut -->
        <div class="mb-6 sm:mb-8">
            <label for="active" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select name="active" id="active" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 sm:px-4 sm:py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm sm:text-base appearance-none bg-white" required>
                <option value="1" {{ $academicYear->active ? 'selected' : '' }}>Activée</option>
                <option value="0" {{ !$academicYear->active ? 'selected' : '' }}>Désactivée</option>
            </select>
            @error('active')
                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Boutons d'action -->
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-4 border-t border-gray-200">
            <button type="submit" 
                    class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2.5 sm:px-6 sm:py-3 rounded-lg hover:bg-blue-700 transition duration-200 font-medium text-sm sm:text-base flex items-center justify-center gap-2 order-2 sm:order-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.academic_years.index') }}" 
               class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2.5 sm:px-6 sm:py-3 rounded-lg hover:bg-gray-600 transition duration-200 font-medium text-sm sm:text-base flex items-center justify-center gap-2 order-1 sm:order-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Annuler
            </a>
        </div>
    </form>
</div>

<style>
    /* Améliorations supplémentaires pour mobile */
    @media (max-width: 640px) {
        .max-w-3xl {
            max-width: 100%;
        }
        
        /* S'assurer que les inputs prennent toute la largeur */
        input, select {
            font-size: 16px; /* Évite le zoom sur iOS */
        }
    }

    /* Style personnalisé pour le select */
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
</style>
@endsection
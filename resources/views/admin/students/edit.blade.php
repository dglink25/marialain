@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Modifier un étudiant';
@endphp
@if(auth()->check())
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Modifier un étudiant</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.students.update', $student->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Nom -->
        <div class="mb-3">
            <label for="last_name" class="block text-sm font-medium">Nom</label>
            <input type="text" name="last_name" id="last_name" 
                   value="{{ old('last_name', $student->last_name) }}" 
                   class="border rounded p-2 w-full" required>
        </div>

        <!-- Prénoms -->
        <div class="mb-3">
            <label for="first_name" class="block text-sm font-medium">Prénoms</label>
            <input type="text" name="first_name" id="first_name" 
                   value="{{ old('first_name', $student->first_name) }}" 
                   class="border rounded p-2 w-full" required>
        </div>

        <!-- Date de naissance -->
        <div class="mb-3">
            <label for="birth_date" class="block text-sm font-medium">Date de naissance</label>
            <input 
                type="date" 
                name="birth_date" 
                id="birth_date" 
                min="{{ date('Y-m-d', strtotime('-50 years')) }}" 
                max="{{ date('Y-m-d') }}" 
                value="{{ old('birth_date', $student->birth_date) }}" 
                class="border rounded p-2 w-full" 
                required
            >
        </div>

        <!-- Lieu de naissance -->
        <div class="mb-3">
            <label for="birth_place" class="block text-sm font-medium">Lieu de naissance</label>
            <input type="text" name="birth_place" id="birth_place"
                value="{{ old('birth_place', $student->birth_place) }}"
                class="border rounded p-2 w-full" required>
        </div>

        <!-- Sexe -->
            <div class="mb-3">
                <label class="block mb-1" for="gender">Sexe</label>
                <select name="gender" id="gender" class="border rounded p-2 w-full" required>
                    <option value="">Sélectionnez une option</option>
                    <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>Féminin</option>
                </select>
            </div>

            <!-- Numéro éduque Master -->
            <div class="mb-3">
                <label for="num_educ" class="block mb-1">Numéro éduque Master</label>
                <input type="text" name="num_educ" id="num_educ"
                    value="{{ old('num_educ', $student->num_educ) }}"
                    class="border rounded p-2 w-full" required>
            </div>



        <!-- Entité -->
        <div class="mb-3">
            <label for="entity_id" class="block text-sm font-medium">Entité</label>
            <select name="entity_id" id="entity_id" class="border rounded p-2 w-full" required>
                <option value="">-- Sélectionnez une entité --</option>
                @foreach ($entities as $entity)
                    <option value="{{ $entity->id }}" 
                        {{ old('entity_id', $student->entity_id) == $entity->id ? 'selected' : '' }}>
                        {{ $entity->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Classe -->
        <div class="mb-3">
            <label for="classe_id" class="block text-sm font-medium">Classe</label>
            <select name="classe_id" id="classe_id" class="border rounded p-2 w-full" required>
                <option value="">-- Sélectionnez une classe --</option>
                @foreach ($classes as $classe)
                    <option value="{{ $classe->id }}" 
                        {{ old('classe_id', $student->classe_id) == $classe->id ? 'selected' : '' }}>
                        {{ $classe->name }} ({{ $classe->entity->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Nom du parent -->
        <div class="mb-3">
            <label for="parent_full_name" class="block text-sm font-medium">Nom complet du parent</label>
            <input type="text" name="parent_full_name" id="parent_full_name" 
                value="{{ old('parent_full_name', $student->parent_full_name) }}" 
                class="border rounded p-2 w-full" required>
        </div>

        <!-- Email du parent -->
        <div class="mb-3">
            <label for="parent_email" class="block text-sm font-medium">Email du parent</label>
            <input type="email" name="parent_email" id="parent_email" 
                value="{{ old('parent_email', $student->parent_email) }}" 
                class="border rounded p-2 w-full" required>
        </div>

        <!-- Téléphone du parent -->
        <div class="mb-3">
            <label for="parent_phone" class="block text-sm font-medium">Téléphone du parent</label>
            <input type="text" name="parent_phone" id="parent_phone"
                value="{{ old('parent_phone', $student->parent_phone) }}"
                class="border rounded p-2 w-full" required>
        </div>


        <!-- Boutons -->
        <div class="flex justify-between mt-6">
            <a href="{{ route('admin.students.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Annuler
            </a>

            <button type="submit" 
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Mettre à jour
            </button>
        </div>
    </form>
</div>

<script>
    // Calcul automatique de l'âge
    const birthDateInput = document.getElementById('birth_date');
    const ageInput = document.getElementById('age');

    birthDateInput.addEventListener('change', function () {
        const birthDate = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        ageInput.value = age;
    });

    // Gestion affichage vaccination et classes dynamiques
    const entitySelect = document.getElementById('entity_id');
    const classeSelect = document.getElementById('classe_id');
    const vaccinationDiv = document.getElementById('vaccination_card_div');

    entitySelect.addEventListener('change', function () {
        const entityId = this.value;
        const selectedText = entitySelect.options[entitySelect.selectedIndex].text.toLowerCase();

        // Afficher le carnet de vaccination seulement si Maternelle
        if (selectedText === 'maternelle') {
            vaccinationDiv.classList.remove('hidden');
        } else {
            vaccinationDiv.classList.add('hidden');
        }

        // Charger les classes dynamiquement
        if (entityId) {
            fetch(`/admin/entities/${entityId}/classes`)
                .then(res => res.json())
                .then(data => {
                    classeSelect.innerHTML = '<option value="">Sélectionnez une classe</option>';
                    data.forEach(cls => {
                        classeSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                    });
                });
        } else {
            classeSelect.innerHTML = '<option value="">Sélectionnez une classe</option>';
        }
    });
</script>
@else
<p style="color:red"> Une erreur s'est produite lors de l'affichage de cette section <br> Veuillez vous connectez à nouveau pour continuer <a href="{{ route('login') }}" class="block px-3 py-2 rounded bg-blue-600 text-white">Se connecter</a></p>
@endif 
@endsection

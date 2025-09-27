@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Inscription';
@endphp

@if(auth()->check())
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Carte principale -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200">
            <!-- En-tête avec logo et titre -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="{{ asset('logo.png') }}" alt="Logo CPEG MARIE-ALAIN" class="h-16 w-auto bg-white p-2 rounded-lg">
                        <div>
                            <h1 class="text-3xl font-bold text-white">Inscription au CPEG MARIE-ALAIN</h1>
                            <p class="text-blue-100 mt-1">Formulaire d'inscription des élèves</p>
                        </div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-full p-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Messages d'alerte -->
            <div class="px-8 pt-6">
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-red-800 font-semibold">Veuillez corriger les erreurs suivantes :</h3>
                    </div>
                    <ul class="mt-2 text-red-700 list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-red-800">{{ session('error') }}</span>
                </div>
                @endif

                @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
                @endif
            </div>

            <!-- Formulaire -->
            <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="px-8 pb-8">
                @csrf

                <div class="space-y-8">
                    <!-- Section Informations personnelles -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Informations personnelles
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prénoms *</label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Date de naissance *</label>
                                <input type="date" name="birth_date" id="birth_date" 
                                       min="{{ date('Y-m-d', strtotime('-50 years')) }}" 
                                       max="{{ date('Y-m-d') }}" 
                                       value="{{ old('birth_date') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Âge</label>
                                <input type="number" id="age" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-100 text-gray-600" 
                                       readonly>
                            </div>
                            <div class="md:col-span-2">
                                <label for="birth_place" class="block text-sm font-semibold text-gray-700 mb-2">Lieu de naissance *</label>
                                <input type="text" name="birth_place" id="birth_place" value="{{ old('birth_place') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Sexe *</label>
                                <select name="gender" id="gender" 
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                        required>
                                    <option value="">Sélectionnez une option</option>
                                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro éduque Master *</label>
                                <input type="text" name="num_educ" value="{{ old('num_educ') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- Section Scolarité -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" transform="translate(0 6)"/>
                            </svg>
                            Informations scolaires
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Entité *</label>
                                <select name="entity_id" id="entity_id" 
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                        required>
                                    <option value="">Sélectionnez une entité</option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}" {{ old('entity_id') == $entity->id ? 'selected' : '' }}>
                                            {{ $entity->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Classe *</label>
                                <select name="classe_id" id="classe_id" 
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                        required>
                                    <option value="">Sélectionnez une classe</option>
                                </select>
                            </div>
                            <div id="vaccination_card_div" class="hidden md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Carnet de vaccination (PDF)</label>
                                <input type="file" name="vaccination_card" accept="application/pdf" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                                <p class="text-xs text-gray-500 mt-1">Requis pour l'inscription en maternelle</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section Documents -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Documents requis
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Acte de naissance (PDF) *</label>
                                <input type="file" name="birth_certificate" accept="application/pdf" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Bulletin de notes année antérieure (PDF)</label>
                                <input type="file" name="previous_report_card" accept="application/pdf" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Relevé de notes diplôme obtenu (optionnel)</label>
                                <input type="file" name="diploma_certificate" accept="application/pdf" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                                <p class="text-xs text-gray-500 mt-1">Pour les élèves de 6ème ou 2nde uniquement</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section Parents/Tuteurs -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Informations des parents/tuteurs
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet des parents/tuteurs *</label>
                                <input type="text" name="parent_full_name" value="{{ old('parent_full_name') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email parents/tuteurs *</label>
                                <input type="email" name="parent_email" value="{{ old('parent_email') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone du parent *</label>
                                <input type="text" name="parent_phone" id="parent_phone" value="{{ old('parent_phone') }}" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                       required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Scolarité payée à l'inscription (FCFA) *</label>
                                <div class="relative">
                                    <input type="number" name="school_fees" value="{{ old('school_fees') }}" 
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 pl-16 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 pt-8 mt-8 border-t border-gray-200">
                    <a href="{{ route('admin.students.index') }}" 
                       class="w-full sm:w-auto px-8 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200 flex items-center justify-center font-semibold">
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
                        Enregistrer l'inscription
                    </button>
                </div>
            </form>
        </div>
    </div>
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
        if (selectedText.includes('maternelle')) {
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
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des classes:', error);
                });
        } else {
            classeSelect.innerHTML = '<option value="">Sélectionnez une classe</option>';
        }
    });

    // Animation de focus sur les champs
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('focus', function() {
            this.classList.add('ring-2', 'ring-blue-200');
        });
        
        element.addEventListener('blur', function() {
            this.classList.remove('ring-2', 'ring-blue-200');
        });
    });

    // Formatage automatique des numéros de téléphone
    const phoneInput = document.getElementById('parent_phone');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 0) {
            value = value.match(/.{1,2}/g).join(' ');
        }
        e.target.value = value;
    });
</script>
@else
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8 text-center">
        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Session expirée</h3>
        <p class="text-gray-600 mb-6">Veuillez vous reconnecter pour accéder au formulaire d'inscription</p>
        <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
            Se connecter
        </a>
    </div>
</div>
@endif 
@endsection
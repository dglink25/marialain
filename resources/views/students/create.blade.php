@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Inscription à CPEG MARIE-ALAIN</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="bg-white shadow-md rounded p-4 space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nom et Prénoms -->
            <div>
                <label class="block mb-1">Nom</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" class="border rounded p-2 w-full" required>
            </div>
            <div>
                <label class="block mb-1">Prénoms</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" class="border rounded p-2 w-full" required>
            </div>

            <!-- Date de naissance et âge -->
            <div>
                <label class="block mb-1">Date de naissance</label>
                <input 
                    type="date" 
                    name="birth_date" 
                    id="birth_date" 
                    min="{{ date('Y-m-d', strtotime('-50 years')) }}" 
                    max="{{ date('Y-m-d') }}" 
                    value="{{ old('birth_date') }}" 
                    class="border rounded p-2 w-full" 
                    required
                >
                
            </div>
            <div>
                <label class="block mb-1">Âge</label>
                <input type="number" id="age" class="border rounded p-2 w-full" readonly>
            </div>

            <!-- Entité et Classe -->
            <div>
                <label class="block mb-1">Entité</label>
                <select name="entity_id" id="entity_id" class="border rounded p-2 w-full" required>
                    <option value="">Sélectionnez une entité</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1">Classe</label>
                <select name="classe_id" id="classe_id" class="border rounded p-2 w-full" required>
                    <option value="">Sélectionnez une classe</option>
                </select>
            </div>

            <!-- Carnet de vaccination (conditionnel) -->
            <div id="vaccination_card_div" class="hidden col-span-2">
                <label class="block mb-1">Carnet de vaccination (PDF)</label>
                <input type="file" name="vaccination_card" accept="application/pdf" class="border rounded p-2 w-full">
            </div>

            <!-- Documents obligatoires / optionnels -->
            <div class="col-span-2">
                <label class="block mb-1">Acte de naissance (PDF)</label>
                <input type="file" name="birth_certificate" accept="application/pdf" class="border rounded p-2 w-full" required>
            </div>

            <div class="col-span-2">
                <label class="block mb-1">Bulletin de notes année antérieure (PDF)</label>
                <input type="file" name="previous_report_card" accept="application/pdf" class="border rounded p-2 w-full">
            </div>

            <div class="col-span-2">
                <label class="block mb-1">Relevé de notes diplôme obtenu (optionnel)</label>
                <input type="file" name="diploma_certificate" accept="application/pdf" class="border rounded p-2 w-full">
                <small class="text-gray-500">Pour les élèves de 6ème ou 2nde uniquement</small>
            </div>

            <!-- Parents / Tuteurs -->
            <div>
                <label class="block mb-1">Nom complet des parents/tuteurs</label>
                <input type="text" name="parent_full_name" value="{{ old('parent_full_name') }}" class="border rounded p-2 w-full" required>
            </div>
            <div>
                <label class="block mb-1">Email parents/tuteurs</label>
                <input type="email" name="parent_email" value="{{ old('parent_email') }}" class="border rounded p-2 w-full" required>
            </div>

            <!-- Scolarité -->
            <div class="col-span-2">
                <label class="block mb-1">Scolarité payée à l'inscription</label>
                <input type="number" name="school_fees" value="{{ old('school_fees') }}" class="border rounded p-2 w-full" required>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Enregistrer</button>
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
@endsection

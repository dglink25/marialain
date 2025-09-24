<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription à CPEG MARIE-ALAIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class=" text-gray-800 font-sans bg-blue-50 ">


<div class="max-w-4xl mx-auto px-4 py-8 bloc">
    
  @if ($errors->any())
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (session('error'))
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
      {{ session('error') }}
    </div>
  @endif

  @if (session('success'))
    <div class="bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-6">
      <p class="font-semibold">{{ session('success') }}</p>
      <p class="mt-2 text-sm">
        Veuillez vous rapprocher du Secrétariat de CPEG MARIE-ALAIN avec votre dossier physique pour valider votre inscription.
      </p>
    </div>
  @endif

  <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data" class="bg-white shadow-lg border border-gray-200 rounded-xl p-6 space-y-6">
    <div class="flex items-center gap-4 bg-white mb-6">
        <img src="{{ asset('logo.png') }}" alt="Logo CPEG MARIE-ALAIN" class="h-16 w-auto">
        <h1 class="text-3xl font-bold text-gray-800 border-b pb-2 text-blue-400"> Inscription à CPEG MARIE-ALAIN</h1>
    </div>
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Nom et Prénoms -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Nom</label>
        <input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Prénoms</label>
        <input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
      </div>

      <!-- Date de naissance et âge -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Date de naissance</label>
        <input type="date" name="birth_date" id="birth_date" min="{{ date('Y-m-d', strtotime('-50 years')) }}" max="{{ date('Y-m-d') }}" value="{{ old('birth_date') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Âge</label>
        <input type="number" id="age" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-600" readonly>
      </div>

      <!-- Lieu de naissance -->
      <div class="md:col-span-2">
        <label for="birth_place" class="block text-sm font-semibold text-gray-700 mb-1">Lieu de naissance</label>
        <input type="text" name="birth_place" id="birth_place" value="{{ old('birth_place') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
      </div>

      <!-- Sexe -->
      <div>
        <label for="gender" class="block text-sm font-semibold text-gray-700 mb-1">Sexe</label>
        <select name="gender" id="gender" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
          <option value="">Sélectionnez une option</option>
          <option value="M">Masculin</option>
          <option value="F">Féminin</option>
        </select>
      </div>

      <!-- Numéro éduc -->
      <div>
        <label for="num_educ" class="block text-sm font-semibold text-gray-700 mb-1">Numéro éduque Master</label>
        <input type="text" name="num_educ" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
      </div>

      <!-- Entité et Classe -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Entité</label>
        <select name="entity_id" id="entity_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
          <option value="">Sélectionnez une entité</option>
          @foreach($entities as $entity)
            <option value="{{ $entity->id }}">{{ $entity->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Classe</label>
        <select name="classe_id" id="classe_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
          <option value="">Sélectionnez une classe</option>
        </select>
      </div>

      <!-- Carnet de vaccination -->
      <div id="vaccination_card_div" class="hidden md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Carnet de vaccination (PDF)</label>
        <input type="file" name="vaccination_card" accept="application/pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2">
      </div>

      <!-- Documents -->
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Acte de naissance (PDF)</label>
        <input type="file" name="birth_certificate" accept="application/pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Bulletin de notes année antérieure (PDF)</label>
        <input type="file" name="previous_report_card" accept="application/pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2">
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Relevé de notes diplôme obtenu (optionnel)</label>
        <input type="file" name="diploma_certificate" accept="application/pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2">
        <p class="text-xs text-gray-500 mt-1">Pour les élèves de 6ème ou 2nde uniquement</p>
      </div>

      <!-- Parents / Tuteurs -->
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nom complet des parents/tuteurs</label>
            <input type="text" name="parent_full_name" value="{{ old('parent_full_name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email parents / tuteurs</label>
            <input type="email" name="parent_email" value="{{ old('parent_email') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
        </div>
        <div class="md:col-span-2">
            <label for="parent_phone" class="block text-sm font-semibold text-gray-700 mb-1">Téléphone du parent</label>
            <input type="text" name="parent_phone" id="parent_phone" value="{{ old('parent_phone') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
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

</body>
</html>
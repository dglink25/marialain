@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

    <!-- En-tête -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Effectif complet de la classe : {{ $class->name }}</h1>
                <p class="mt-1 text-sm text-gray-600"></p>
            </div>
            <div class="mt-4 md:mt-0 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600">Total: <span class="font-medium">{{ $class->students->count() }} élève(s)</span></p>
            </div>
        </div>
    </div>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tableau -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-200">
        @if($class->students->count() > 0)
        <div class="overflow-x-auto">
            <div class="max-h-[600px] overflow-y-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">N°</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Matricule</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Élève</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Naissance</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Sexe</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Parent</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Téléphone</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($class->students->sortBy([['last_name', 'asc'], ['first_name', 'asc']]) as $student)
                        <tr class="hover:bg-blue-50 transition duration-150 group">

                            <!-- Numéro -->
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-b">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Numéro Éduc Master -->
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 border-b">
                                <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">
                                    {{ $student->num_educ ?? 'N/A' }}
                                </span>
                            </td>

                            <!-- Nom et Prénom -->
                            <td class="px-4 py-3 border-b">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                        <span class="text-white text-xs font-bold">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $student->last_name }}</div>
                                        <div class="text-sm text-gray-600">{{ $student->first_name }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Date et lieu de naissance -->
                            <td class="px-4 py-3 border-b">
                                <div class="text-sm text-gray-900">{{ $student->birth_date }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-[150px]">{{ $student->birth_place }}</div>
                            </td>

                            <!-- Sexe -->
                            <td class="px-4 py-3 whitespace-nowrap border-b">
                                @if($student->gender)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $student->gender == 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                        {{ $student->gender }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">--</span>
                                @endif
                            </td>

                            <!-- Nom du parent -->
                            <td class="px-4 py-3 border-b">
                                <div class="text-sm text-gray-900">{{ $student->parent_full_name }}</div>
                            </td>

                            <!-- Email du parent -->
                            <td class="px-4 py-3 border-b">
                                <div class="text-sm text-gray-600 break-all max-w-[200px]">
                                    {{ $student->parent_email }}
                                </div>
                            </td>

                            <!-- Téléphone du parent + bouton modifier -->
                            <td class="px-4 py-3 whitespace-nowrap border-b">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-900 font-mono phone-display-{{ $student->id }}">
                                        {{ $student->parent_phone ?? '—' }}
                                    </span>
                                    <button
                                        type="button"
                                        onclick="openPhoneModal({{ $student->id }}, '{{ addslashes($student->parent_full_name) }}', '{{ addslashes($student->parent_phone ?? '') }}')"
                                        title="Modifier le téléphone"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-600 hover:bg-amber-200 hover:text-amber-800 transition-colors duration-150 flex-shrink-0"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Aucun élève dans cette classe</h4>
            <p class="text-gray-600">Cette classe ne contient actuellement aucun élève.</p>
        </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="mt-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
        <button onclick="window.history.back()"
                class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200 font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </button>

        <div class="flex space-x-3">
            <a href="{{ route('censeur.classes.students.pdf', $class->id) }}"
               class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Télécharger PDF
            </a>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════
     MODAL — Modifier le téléphone du parent
═══════════════════════════════════════════════════════ --}}
<div id="phoneModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closePhoneModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

            <!-- Barre colorée -->
            <div class="h-1.5 bg-gradient-to-r from-amber-400 to-orange-500"></div>

            <div class="p-6">
                <!-- Titre -->
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Modifier le téléphone</h3>
                            <p id="phoneModalSubtitle" class="text-sm text-gray-500"></p>
                        </div>
                    </div>
                    <button onclick="closePhoneModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Champ -->
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nouveau numéro de téléphone <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="tel"
                        id="phoneInput"
                        placeholder="Ex : +229 97000000"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-gray-800 font-mono text-sm transition"
                    >
                    <p id="phoneError" class="hidden mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span id="phoneErrorText"></span>
                    </p>
                    <p class="mt-1.5 text-xs text-gray-400">
                        Ce numéro sera mis à jour dans le profil de l'élève et du compte parent associé.
                    </p>
                </div>

                <!-- Boutons -->
                <div class="flex gap-3">
                    <button onclick="closePhoneModal()"
                            class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-medium text-sm">
                        Annuler
                    </button>
                    <button id="phoneSaveBtn" onclick="savePhone()"
                            class="flex-1 px-4 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition font-semibold text-sm inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════
     TOAST notification
═══════════════════════════════════════════════ --}}
<div id="toast"
     class="fixed bottom-6 right-6 z-[60] hidden items-center gap-3 px-5 py-3.5 rounded-xl shadow-lg text-sm font-medium transition-all duration-300">
</div>


<style>
    .min-w-full { border-collapse: separate; border-spacing: 0; }
    .min-w-full th { background-color: #f9fafb; position: sticky; top: 0; z-index: 10; border-bottom: 2px solid #e5e7eb; }
    .min-w-full td { border-bottom: 1px solid #f3f4f6; }
    .min-w-full tr:last-child td { border-bottom: none; }
    .hover\:bg-blue-50:hover { background-color: #eff6ff; }
    @media (max-width: 768px) {
        .max-w-7xl { padding-left: 1rem; padding-right: 1rem; }
        .overflow-x-auto { margin-left: -1rem; margin-right: -1rem; }
    }
</style>


<script>
// ── État courant ─────────────────────────────────────────────────────
let currentStudentId = null;

// Route de base pour l'update (on remplace __STUDENT__ dynamiquement)
const updatePhoneUrlBase = "{{ url('/censeur/classes/' . $class->id . '/students/__STUDENT__/update-phone') }}";
const csrfToken          = "{{ csrf_token() }}";

// ── Ouvrir le modal ──────────────────────────────────────────────────
function openPhoneModal(studentId, parentName, currentPhone) {
    currentStudentId = studentId;

    document.getElementById('phoneModalSubtitle').textContent = 'Parent : ' + parentName;
    document.getElementById('phoneInput').value               = currentPhone;
    document.getElementById('phoneError').classList.add('hidden');

    document.getElementById('phoneModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => document.getElementById('phoneInput').focus(), 100);
}

// ── Fermer le modal ──────────────────────────────────────────────────
function closePhoneModal() {
    document.getElementById('phoneModal').classList.add('hidden');
    document.body.style.overflow = '';
    currentStudentId = null;
}

// ── Enregistrer ─────────────────────────────────────────────────────
async function savePhone() {
    const input    = document.getElementById('phoneInput');
    const errorDiv = document.getElementById('phoneError');
    const errorTxt = document.getElementById('phoneErrorText');
    const saveBtn  = document.getElementById('phoneSaveBtn');
    const phone    = input.value.trim();

    // Validation côté client
    errorDiv.classList.add('hidden');
    if (!phone) {
        errorTxt.textContent = 'Le numéro de téléphone est obligatoire.';
        errorDiv.classList.remove('hidden');
        input.focus();
        return;
    }
    if (!/^[0-9+\s\-]{8,20}$/.test(phone)) {
        errorTxt.textContent = 'Format invalide. Utilisez uniquement des chiffres, +, espaces ou tirets (8 à 20 caractères).';
        errorDiv.classList.remove('hidden');
        input.focus();
        return;
    }

    // Désactiver le bouton pendant la requête
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Enregistrement...';

    const url = updatePhoneUrlBase.replace('__STUDENT__', currentStudentId);

    try {
        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ parent_phone: phone }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Mettre à jour l'affichage dans le tableau sans recharger
            const displayEl = document.querySelector(`.phone-display-${currentStudentId}`);
            if (displayEl) displayEl.textContent = data.new_phone;

            closePhoneModal();
            showToast('✓ Numéro mis à jour avec succès.', 'success');
        } else {
            // Erreurs de validation renvoyées par Laravel
            const msg = data.errors?.parent_phone?.[0] ?? data.message ?? 'Une erreur est survenue.';
            errorTxt.textContent = msg;
            errorDiv.classList.remove('hidden');
        }
    } catch (err) {
        errorTxt.textContent = 'Erreur réseau. Veuillez réessayer.';
        errorDiv.classList.remove('hidden');
        console.error(err);
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Enregistrer';
    }
}

// ── Toast ────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.className = 'fixed bottom-6 right-6 z-[60] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 '
        + (type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white');
    toast.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3500);
}

// ── Fermer avec Échap ────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closePhoneModal();
});

// ── Soumettre avec Entrée dans le champ ──────────────────────────────
document.getElementById('phoneInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') savePhone();
});
</script>
@endsection
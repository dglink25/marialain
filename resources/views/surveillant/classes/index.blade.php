@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Classes du secondaire</h1>
            <div class="w-24 h-1 bg-green-500 mx-auto rounded-full"></div>
        </div>

        <!-- Messages de succès/erreur -->
        @if(session('success'))
        <div class="max-w-4xl mx-auto mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="max-w-4xl mx-auto mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong class="font-medium">Erreurs de validation :</strong>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Classes Grid -->
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($classes as $class)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="p-4">
                        <div class="mb-4">
                            <h2 class="text-lg font-semibold text-gray-800 truncate">{{ $class->name }}</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"></path>
                                    </svg>
                                    {{ $class->students_count ?? '0' }} élève(s)
                                </span>
                            </p>
                        </div>
                        
                        <!-- Buttons Container -->
                        <div class="space-y-2">
                            <button onclick="openConductModal({{ $class->id }})" 
                                    class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium py-2.5 px-3 rounded-lg transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 shadow-sm hover:shadow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Attribuer Conduite</span>
                            </button>
                            
                            <a href="{{ route('surveillant.classes.students', $class->id) }}"
                               class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium py-2.5 px-3 rounded-lg transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2 shadow-sm hover:shadow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"></path>
                                </svg>
                                <span>Voir les élèves</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal Attribuer conduite -->
<div id="conductModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-100 p-6 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Attribuer la conduite</h2>
                    <p class="text-sm text-gray-500 mt-1">Pour la classe sélectionnée</p>
                </div>
                <button onclick="closeConductModal()" 
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <form method="POST" id="conductForm" class="space-y-5">
                @csrf
                
                <!-- Note de conduite -->
                <div>
                    <label for="grade" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Note de conduite *
                        </span>
                    </label>
                    <input type="number" 
                           id="grade" 
                           name="grade" 
                           placeholder="Ex: 18"
                           min="0" 
                           max="20" 
                           step="0.1"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3.5 text-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                           required>
                    <p class="text-xs text-gray-400 mt-2 px-1">
                        ⓘ Note sur 20 (ex: 16, 17, 18)
                    </p>
                </div>
                
                <!-- Trimestre -->
                <div>
                    <label for="trimestre" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            Trimestre *
                        </span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative">
                            <input type="radio" name="trimestre" value="1" class="sr-only peer" required>
                            <div class="p-4 border-2 border-gray-200 rounded-xl text-center cursor-pointer transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300">
                                <span class="font-semibold text-gray-700">1er</span>
                                <p class="text-xs text-gray-500 mt-1">Trimestre</p>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="trimestre" value="2" class="sr-only peer" required>
                            <div class="p-4 border-2 border-gray-200 rounded-xl text-center cursor-pointer transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300">
                                <span class="font-semibold text-gray-700">2ème</span>
                                <p class="text-xs text-gray-500 mt-1">Trimestre</p>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="trimestre" value="3" class="sr-only peer" required>
                            <div class="p-4 border-2 border-gray-200 rounded-xl text-center cursor-pointer transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300">
                                <span class="font-semibold text-gray-700">3ème</span>
                                <p class="text-xs text-gray-500 mt-1">Trimestre</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Commentaire -->
                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            Commentaire (optionnel)
                        </span>
                    </label>
                    <textarea id="comment" 
                              name="comment" 
                              placeholder="Ex: Bonne conduite, respect des règles de l'école..."
                              rows="3"
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>
                
                <!-- Boutons -->
                <div class="flex gap-3 pt-2">
                    <button type="button" 
                            onclick="closeConductModal()" 
                            class="flex-1 border-2 border-gray-300 text-gray-700 font-semibold px-4 py-3.5 rounded-xl transition-all hover:bg-gray-50 hover:border-gray-400 active:scale-[0.98]">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold px-4 py-3.5 rounded-xl transition-all hover:shadow-lg active:scale-[0.98] flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Valider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar */
    #conductModal div::-webkit-scrollbar {
        width: 8px;
    }
    
    #conductModal div::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    #conductModal div::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }
    
    #conductModal div::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    
    /* Smooth transitions */
    * {
        transition: background-color 0.2s, border-color 0.2s, transform 0.2s;
    }
</style>

<script>
let currentClassId = null;

function openConductModal(classId) {
    currentClassId = classId;
    const form = document.getElementById('conductForm');
    form.action = `/surveillant/classes/${classId}/conducts`;
    
    // Réinitialiser le formulaire
    form.reset();
    
    // Afficher le modal
    const modal = document.getElementById('conductModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Focus sur le premier champ
    setTimeout(() => {
        document.getElementById('grade').focus();
    }, 100);
}

function closeConductModal() {
    const modal = document.getElementById('conductModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    currentClassId = null;
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('conductModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConductModal();
    }
});

// Fermer avec la touche Echap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConductModal();
    }
});

// Validation avant soumission
document.getElementById('conductForm').addEventListener('submit', function(e) {
    const gradeInput = document.getElementById('grade');
    const grade = gradeInput.value;
    const trimestre = document.querySelector('input[name="trimestre"]:checked');
    
    // Validation du grade
    if (grade < 0 || grade > 20) {
        e.preventDefault();
        gradeInput.focus();
        showToast('La note de conduite doit être comprise entre 0 et 20.', 'error');
        return false;
    }
    
    // Validation du trimestre
    if (!trimestre) {
        e.preventDefault();
        showToast('Veuillez sélectionner un trimestre.', 'error');
        return false;
    }
    
    // Validation du format numérique
    if (isNaN(parseFloat(grade))) {
        e.preventDefault();
        gradeInput.focus();
        showToast('La note doit être un nombre valide.', 'error');
        return false;
    }
    
    return true;
});

// Fonction pour afficher des toasts (optionnel)
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium ${
        type === 'error' ? 'bg-red-500' : 'bg-green-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 4000);
}

// Empêcher la soumission multiple
document.getElementById('conductForm').addEventListener('submit', function() {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Traitement...</span>';
    }
});
</script>
@endsection
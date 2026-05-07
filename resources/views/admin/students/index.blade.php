@extends('layouts.app')

@section('title', 'Liste des étudiants')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @if(!$activeYear)
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h1 class="text-xl font-bold text-gray-800 mb-4">Liste de tous les élèves de CPEG MARIE-ALAIN</h1>
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded-lg">
                {{ $message }}
            </div>
        </div>
    @else
        <!-- En-tête principal -->
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Liste des élèves</h1>
                    <p class="text-gray-600">CPEG MARIE-ALAIN - Gestion des étudiants</p>
                </div>
                <a href="{{ route('admin.students.create') }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 whitespace-nowrap text-center font-medium flex items-center gap-2">
                    <i class="fas fa-user-plus"></i>
                    Nouvelle inscription
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6">
                <div class="flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- Section Filtres et Export -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    
            <div class="mb-6">
               
                <form id="mailForm" method="POST" action="{{ route('students.mail.sendAll') }}">
                    @csrf
                    <button type="submit" 
                        class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200 font-medium flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer un message de rappel
                    </button>
                    <p>:pour non payement de contribution au parents</p>
                </form>
            </div>

            <!-- Filtres de recherche -->
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-filter"></i>
                    Filtres de recherche
                </h2>
                <form method="GET" action="{{ route('admin.students.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    <input type="text" name="search" placeholder="Rechercher un élève"
                           value="{{ request('search') }}"
                           class="border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-500">

                    <select name="entity_id" class="border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-500">
                        <option value="">Entité</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="class_id" class="border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-500">
                        <option value="">Classe</option>
                        @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" {{ request('class_id') == $classe->id ? 'selected' : '' }}>
                                {{ $classe->name }}
                            </option>
                        @endforeach
                    </select>

                    <input type="date" name="date" value="{{ request('date') }}" 
                           class="border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-500">

                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm font-medium whitespace-nowrap flex items-center gap-2 flex-1 justify-center">
                            <i class="fas fa-search"></i>
                            Filtrer
                        </button>
                        <a href="{{ route('admin.students.index') }}" 
                           class="bg-gray-500 text-white px-3 py-2 rounded-lg hover:bg-gray-600 transition duration-200 text-sm font-medium whitespace-nowrap flex items-center gap-2 flex-1 justify-center">
                            <i class="fas fa-redo"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Export PDF + Émargement -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-file-export"></i>
                    Export des données
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                    <!-- Export liste classique -->
                    <form method="GET" action="{{ route('admin.students.export.pdf') }}" class="flex gap-2">
                        <select name="class_id" class="border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-blue-500 flex-1">
                            <option value="">Toutes les classes</option>
                            @foreach($classes as $classe)
                                <option value="{{ $classe->id }}">{{ $classe->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition duration-200 text-sm font-medium whitespace-nowrap flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                    </form>

                    <!-- Export ZIP toutes les classes -->
                    <a href="{{ route('admin.students.export.all.pdf') }}" 
                       class="bg-red-700 text-white px-3 py-2 rounded-lg hover:bg-red-800 transition duration-200 text-sm font-medium whitespace-nowrap flex items-center gap-2 justify-center">
                       <i class="fas fa-file-archive"></i>
                       Toutes les classes (ZIP)
                    </a>

                    <!-- ✅ NOUVEAU : Bouton Liste d'émargement -->
                    <button type="button" onclick="openEmmagementModal()"
                        class="bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 transition duration-200 text-sm font-medium whitespace-nowrap flex items-center gap-2 justify-center">
                        <i class="fas fa-clipboard-list"></i>
                        Liste d'émargement (Bulletins)
                    </button>
                </div>
            </div>
        </div>

        @if(auth()->check())
            <!-- Tableau des étudiants -->
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full bg-white text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">N°</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">N° Éduc</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Nom</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Prénoms</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Sexe</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Entité</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Classe</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Total Payé</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Reste</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Naissance</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Parent</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Inscription</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($students as $student)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">{{ $student->num_educ ?? '-' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap font-medium text-blue-600">
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="hover:underline flex items-center gap-1">
                                            {{ \Illuminate\Support\Str::limit($student->last_name, 12) }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap font-medium text-blue-600">
                                        <a href="{{ route('admin.students.show', $student->id) }}" class="hover:underline">
                                            {{ \Illuminate\Support\Str::limit($student->first_name, 12) }}
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 text-center">{{ $student->gender ?? '-' }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">{{ \Illuminate\Support\Str::limit($student->entity->name ?? '-', 8) }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">{{ \Illuminate\Support\Str::limit($student->classe->name ?? '-', 8) }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">
                                        <div class="font-medium">{{ number_format($student->total_paid, 2) }} FCFA</div>
                                        <a href="{{ route('students.payments.index', $student->id) }}" 
                                            class="text-blue-600 hover:text-blue-800 text-xs flex items-center gap-1 mt-1">
                                            <i class="fas fa-list"></i>
                                            Détails
                                        </a>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900 font-semibold">
                                        {{ number_format($student->remaining_fees, 2) }} FCFA
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">{{ $student->birth_date }}</td>
                                    <td class="px-3 py-3 text-gray-900">
                                        <div class="whitespace-nowrap">{{ \Illuminate\Support\Str::limit($student->parent_full_name ?? '-', 10) }}</div>
                                        <div class="text-gray-600 text-xs">{{ $student->parent_phone ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-900">{{ $student->created_at->format('d/m/Y') }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="flex flex-col sm:flex-row gap-1">
                                            <a href="{{ route('admin.students.edit', $student->id) }}" 
                                               class="bg-yellow-500 text-white px-2 py-1 rounded text-xs hover:bg-yellow-600 transition duration-200 flex items-center gap-1 justify-center">
                                                <i class="fas fa-edit"></i>
                                                Modif
                                            </a>
                                            <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" 
                                                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet étudiant ?')" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700 transition duration-200 w-full flex items-center gap-1 justify-center">
                                                    <i class="fas fa-trash"></i>
                                                    Supp
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-users-slash text-2xl mb-2 block"></i>
                                        Aucun étudiant inscrit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bouton Retour + Pagination -->
            <div class="mt-6 flex justify-between items-center">
                <button onclick="window.history.back()" 
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200 font-medium flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </button>
                
                <div class="flex-1 flex justify-center">
                    {{ $students->links() }}
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
                <i class="fas fa-exclamation-triangle text-2xl mb-3 block"></i>
                <p class="font-semibold mb-2">Une erreur s'est produite</p>
                <p class="mb-4">Veuillez vous connecter à nouveau pour continuer</p>
                <a href="{{ route('login') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 inline-flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </a>
            </div>
        @endif 
    @endif
</div>

<!-- =============================================
     MODAL : Liste d'émargement des bulletins
     ============================================= -->
<div id="emmagementModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-5">
            <div class="flex items-center gap-2">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <i class="fas fa-clipboard-list text-purple-600 text-lg"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Liste d'émargement – Retrait de Bulletin</h3>
            </div>
            <button onclick="closeEmmagementModal()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <p class="text-sm text-gray-500 mb-5">
            Sélectionnez la classe et le trimestre pour télécharger la liste d'émargement au format PDF.
        </p>

        <form method="GET" action="{{ route('admin.students.emmagement.pdf') }}" id="emmagementForm">
            <!-- Classe -->
            <div class="mb-4">
                <label for="emm_class_id" class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-school text-purple-500 mr-1"></i> Classe
                </label>
                <select name="class_id" id="emm_class_id" required
                    class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                    <option value="">-- Sélectionner une classe --</option>
                    @foreach($classes as $classe)
                        <option value="{{ $classe->id }}">{{ $classe->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Trimestre -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-purple-500 mr-1"></i> Trimestre
                </label>
                <div class="flex gap-3">
                    @foreach([1 => '1er Trimestre', 2 => '2ème Trimestre', 3 => '3ème Trimestre'] as $num => $label)
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="trimestre" value="{{ $num }}" class="sr-only peer" {{ $num === 1 ? 'required' : '' }}>
                        <div class="text-center border-2 border-gray-200 rounded-lg py-2.5 px-2 text-sm font-medium text-gray-600
                                    peer-checked:border-purple-600 peer-checked:bg-purple-50 peer-checked:text-purple-700
                                    hover:border-purple-300 transition duration-150">
                            T{{ $num }}
                            <div class="text-xs font-normal mt-0.5">{{ $label }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeEmmagementModal()"
                    class="flex-1 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-200 transition font-medium text-sm">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 bg-purple-600 text-white px-4 py-2.5 rounded-lg hover:bg-purple-700 transition font-medium text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    Télécharger le PDF
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .overflow-x-auto::-webkit-scrollbar { height: 6px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
    
    @media (max-width: 768px) {
        table { min-width: 1000px; }
        th, td { padding: 0.5rem 0.25rem; font-size: 0.75rem; }
        .flex.justify-between { flex-direction: column; gap: 1rem; }
    }
</style>

<script>
    function openEmmagementModal() {
        document.getElementById('emmagementModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEmmagementModal() {
        document.getElementById('emmagementModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Fermer le modal si on clique dehors
    document.getElementById('emmagementModal').addEventListener('click', function(e) {
        if (e.target === this) closeEmmagementModal();
    });

    // Validation avant soumission
    document.getElementById('emmagementForm').addEventListener('submit', function(e) {
        const classe = document.getElementById('emm_class_id').value;
        const trimestre = document.querySelector('input[name="trimestre"]:checked');
        if (!classe) {
            e.preventDefault();
            alert('Veuillez sélectionner une classe.');
            return;
        }
        if (!trimestre) {
            e.preventDefault();
            alert('Veuillez sélectionner un trimestre.');
            return;
        }
    });
</script>
@endsection
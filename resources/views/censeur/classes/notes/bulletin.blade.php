@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
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

        <!-- Modal d'accès non autorisé -->
        <div id="accessModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div id="modalOverlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Espace pour centrer la modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Contenu de la modal -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <!-- En-tête de la modal -->
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-shield-alt text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-white" id="modal-title">
                                    Accès non autorisé
                                </h3>
                                <p class="text-red-100 text-sm mt-1">
                                    Sécurité - Zone restreinte
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Corps de la modal -->
                    <div class="bg-white px-6 py-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-lock text-red-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-700 font-medium">
                                    Cette fonctionnalité est strictement réservée à :
                                </p>
                                <ul class="mt-3 space-y-2">
                                    <li class="flex items-center text-gray-600">
                                        <i class="fas fa-user-tie text-blue-500 w-6"></i>
                                        <span>La Secrétaire Comptable</span>
                                    </li>
                                    <li class="flex items-center text-gray-600">
                                        <i class="fas fa-crown text-yellow-500 w-6"></i>
                                        <span>Le Directeur Fondateur</span>
                                    </li>
                                </ul>
                                <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                        Pour obtenir l'accès, veuillez contacter l'administrateur ou utiliser un compte disposant des privilèges nécessaires.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pied de la modal -->
                    <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeModal()"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Fermer
                        </button>
                        <a href="{{ route('home') }}" 
                           class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <i class="fas fa-home mr-2"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte principale du bulletin -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            
            <!-- En-tête avec fond coloré -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
                <div class="text-center text-white">
                    <h1 class="text-3xl font-bold mb-2">BULLETIN SCOLAIRE</h1>
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 text-blue-100">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>Trimestre {{ $trimestre }}</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span>{{ $activeYear->name ?? $activeYear->label ?? 'Année en cours' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations élève -->
            <div class="px-8 py-6 border-b border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Élève</p>
                            <p class="font-semibold text-gray-900">{{ strtoupper($student->last_name) }} {{ ucfirst($student->first_name) }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-id-card text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Matricule</p>
                            <p class="font-semibold text-gray-900">{{ $student->num_educ ?? '—' }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-users text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Classe</p>
                            <p class="font-semibold text-gray-900">{{ $classe->name }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-mars-and-venus text-orange-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sexe</p>
                            <p class="font-semibold text-gray-900">{{ strtoupper($student->gender ?? '-') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des notes -->
            <div class="overflow-x-auto">
                @if(count($bulletin) > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="border-b border-gray-200">
                            <th rowspan="2" class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Matière</th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700">Coef</th>
                            <th colspan="5" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">
                                Interrogations
                            </th>
                            <th colspan="2" class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">
                                Devoirs
                            </th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">Moyenne</th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700">Moy x Coef</th>
                            <th rowspan="2" class="px-4 py-4 text-center text-sm font-semibold text-gray-700 border-l border-gray-200">Appréciation</th>
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I1</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I2</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I3</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">I4</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">I5</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500">D1</th>
                            <th class="px-3 py-2 text-xs font-medium text-gray-500 border-r border-gray-200">D2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($bulletin as $ligne)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 text-left font-medium text-gray-900">{{ $ligne['subject'] }}</td>
                            <td class="px-4 py-4 text-center text-gray-600">{{ $ligne['coef'] }}</td>
                            
                            {{-- Interrogations --}}
                            @for ($i = 1; $i <= 5; $i++)
                                <td class="px-3 py-4 text-center {{ $i === 5 ? 'border-r border-gray-200' : '' }}">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                        {{ isset($ligne['interros'][$i]) ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $ligne['interros'][$i] ?? '-' }}
                                    </span>
                                </td>
                            @endfor

                            {{-- Devoirs --}}
                            <td class="px-3 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                    {{ isset($ligne['devoirs'][1]) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $ligne['devoirs'][1] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-center border-r border-gray-200">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                    {{ isset($ligne['devoirs'][2]) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $ligne['devoirs'][2] ?? '-' }}
                                </span>
                            </td>

                            {{-- Moyennes --}}
                            <td class="px-4 py-4 text-center border-r border-gray-200">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-semibold
                                    {{ isset($ligne['moyenne']) ? 
                                       ($ligne['moyenne'] >= 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') : 
                                       'bg-gray-100 text-gray-600' }}">
                                    {{ $ligne['moyenne'] ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center font-semibold text-gray-700">
                                {{ $ligne['moyCoeff'] ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-center border-l border-gray-200 italic text-gray-600">
                                {{ $ligne['appreciation'] ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-book-open text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Aucune matière trouvée</h3>
                    <p class="text-gray-500">Aucune matière n'est enseignée dans cette classe pour l'année en cours.</p>
                </div>
                @endif
            </div>

            <!-- Résultats généraux -->
            @if(count($bulletin) > 0)
            <div class="bg-gray-50 border-t border-gray-200 px-8 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Conduite</div>
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($conduiteFinale, 1) ?? '-' }}/20</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Moyenne Générale</div>
                        <div class="text-2xl font-bold 
                            {{ isset($moyenneGenerale) ? 
                               ($moyenneGenerale >= 10 ? 'text-green-600' : 'text-red-600') : 
                               'text-gray-600' }}">
                            {{ number_format($moyenneGenerale, 2) ?? '-' }}/20
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Rang Général</div>
                        <div class="text-2xl font-bold text-purple-600">
                            {{ $rang ?? '-' }}/{{ $classe->students->count() ?? 0 }}
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-sm text-gray-500 mb-1">Appréciation</div>
                        <div class="text-lg font-semibold text-gray-700">{{ $appreciationGenerale ?? '-' }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Boutons d'action -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-6">
            
            @if(count($bulletin) > 0)
            <button onclick="checkAccessAndShowModal({{ auth()->user()->id }}, '{{ route('censeur.classes.notes.bulletin.pdf', [$classe->id, $student->id, $trimestre]) }}')"
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-sm">
                <i class="fas fa-file-pdf mr-2"></i>
                Télécharger le bulletin PDF
            </button>
            @endif
        </div>
    </div>
</div>

<!-- Styles pour le responsive -->
<style>
    @media (max-width: 768px) {
        table {
            font-size: 0.75rem;
        }
        .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .px-8 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation d'apparition progressive
        const cards = document.querySelectorAll('.bg-white');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Ajout d'infobulles pour les notes
        const noteCells = document.querySelectorAll('td span');
        noteCells.forEach(cell => {
            const note = cell.textContent.trim();
            if (note !== '-') {
                cell.title = 'Note: ' + note;
            }
        });
    });

    // Fonction pour vérifier l'accès et afficher la modal
    function checkAccessAndShowModal(userId, redirectUrl) {
        // IDs autorisés : 7 (Secrétaire Comptable) et 6 (Directeur Fondateur)
        const authorizedIds = [6, 7];
        
        if (!authorizedIds.includes(userId)) {
            showModal();
        } else {
            // Rediriger vers la route si autorisé
            window.location.href = redirectUrl;
        }
    }

    // Fonction pour afficher la modal
    function showModal() {
        const modal = document.getElementById('accessModal');
        modal.classList.remove('hidden');
        
        // Empêcher le scroll du body
        document.body.style.overflow = 'hidden';
        
        // Animation d'apparition
        setTimeout(() => {
            modal.querySelector('.transform').classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    // Fonction pour fermer la modal
    function closeModal() {
        const modal = document.getElementById('accessModal');
        modal.classList.add('hidden');
        
        // Réactiver le scroll
        document.body.style.overflow = 'auto';
    }

    // Fermer la modal avec la touche Echap
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // Fermer la modal en cliquant sur l'overlay
    document.getElementById('modalOverlay')?.addEventListener('click', function() {
        closeModal();
    });
</script>
@endsection
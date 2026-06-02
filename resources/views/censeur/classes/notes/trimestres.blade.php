@extends('layouts.app')

@php
    $pageTitle = "Trimestres";
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- En-tête avec navigation -->
        <div class="mb-8">
            <nav class="flex items-center justify-between flex-wrap sm:flex-nowrap gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-chalkboard text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">Gestion des Trimestres</h1>
                            <p class="text-sm sm:text-base text-gray-600 mt-1">
                                <i class="fas fa-graduation-cap mr-1 text-blue-500"></i>
                                Classe : {{ $classe->name }}
                            </p>
                        </div>
                    </div>
                </div>
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center px-5 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow">
                    <i class="fas fa-arrow-left mr-2 text-sm"></i>
                    Retour
                </a>
            </nav>
        </div>

        <!-- Messages flash -->
        @if ($errors->any())
            <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5 mr-3"></i>
                    <div>
                        <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
                        <ul class="list-disc pl-5 space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg mr-3"></i>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-gradient-to-r from-green-50 to-emerald-100 border-l-4 border-green-500 text-green-700 px-5 py-4 rounded-xl mb-6 shadow-sm animate-slide-down">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-lg mr-3"></i>
                    <div>
                        <p class="font-semibold">Succès</p>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal chargement -->
        <div id="loadingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-60 backdrop-blur-sm" id="modalOverlay"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
                    <div class="relative">
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
                        <div class="px-6 pt-8 pb-6">
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-br from-blue-100 to-purple-100 mb-6">
                                    <i class="fas fa-info-circle text-4xl text-blue-600"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-3" id="modal-title">Information importante</h3>
                                <div class="mt-4">
                                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg mb-6 text-left">
                                        <div class="flex items-start">
                                            <i class="fas fa-hourglass-half text-amber-600 text-xl mt-0.5 mr-3"></i>
                                            <div>
                                                <p class="text-sm text-amber-800 leading-relaxed">
                                                    La page que vous tentez d'accéder pourrait prendre jusqu'à
                                                    <strong class="font-semibold">5 minutes</strong> selon les performances de votre appareil.
                                                </p>
                                                <p class="text-sm text-amber-800 mt-2">
                                                    Veuillez patienter pendant le chargement, s'il vous plaît.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="loadingAnimation" class="hidden mt-6">
                                        <div class="flex flex-col items-center space-y-4">
                                            <div class="relative">
                                                <div class="w-16 h-16 border-4 border-gray-200 rounded-full"></div>
                                                <div class="absolute top-0 left-0 w-16 h-16 border-4 border-blue-600 rounded-full animate-spin border-t-transparent"></div>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-sm font-medium text-gray-700">Chargement en cours...</p>
                                                <p class="text-xs text-gray-500 mt-1">Veuillez patienter</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                                <button id="confirmButton"
                                        class="w-full inline-flex justify-center items-center px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-md hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    D'accord
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal fiche de notes -->
        <div id="listeElevesModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm" id="listeElevesOverlay"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 modal-content">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-sky-500 to-blue-600 rounded-t-2xl"></div>
                    <div class="px-6 pt-8 pb-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-sky-100 to-blue-100 rounded-xl flex items-center justify-center mr-3">
                                    <i class="fas fa-file-pdf text-sky-600 text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">Fiche de notes</h3>
                                    <p class="text-sm text-gray-500" id="listeElevesTrimestreLabel"></p>
                                </div>
                            </div>
                            <button onclick="closeListe()" class="text-gray-400 hover:text-gray-600 transition p-1">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-book mr-1.5 text-sky-500"></i>
                                Sélectionner une matière
                            </label>
                            <select id="listeElevesSubjectSelect"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 text-gray-700 bg-white transition">
                                <option value="">-- Choisissez une matière --</option>
                                @foreach($matieres as $matiere)
                                    <option value="{{ $matiere->subject_id ?? $matiere->id }}">
                                        {{ $matiere->name ?? $matiere->subject->name ?? 'Matière' }}
                                        @if(isset($matiere->coefficient))
                                            (Coefficient {{ $matiere->coefficient }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($matieres->isEmpty())
                                <p class="text-xs text-amber-600 mt-2 flex items-center">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Aucune matière trouvée pour cette classe.
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-3">
                            <button onclick="closeListe()"
                                    class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-medium">
                                Annuler
                            </button>
                            <button onclick="telechargerListe()"
                                    class="flex-1 px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-600 text-white rounded-xl hover:from-sky-700 hover:to-blue-700 transition font-medium inline-flex items-center justify-center shadow-md hover:shadow-lg">
                                <i class="fas fa-download mr-2"></i>
                                Télécharger PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions principales -->
        <div class="mb-8 flex flex-col sm:flex-row gap-4 justify-end">
            <a href="{{ route('censeur.classes.point-annee.pdf', $classe->id) }}" 
               class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 text-white font-semibold rounded-xl shadow-md hover:from-red-700 hover:to-rose-700 transition-all duration-200 hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-file-pdf mr-2"></i>
                Point de l'Année (PDF)
            </a>

            <a href="#"
               id="pointAnneeLink"
               class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl shadow-md hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-chart-line mr-2"></i>
                Point de l'Année Académique
            </a>
        </div>

        <!-- Grille des trimestres -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($trimestres as $t)
            <div class="group bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <!-- Bandeau couleur par trimestre -->
                <div class="h-2 bg-gradient-to-r 
                    @if($t == 1) from-blue-500 to-cyan-500
                    @elseif($t == 2) from-green-500 to-emerald-500
                    @else from-purple-500 to-pink-500
                    @endif">
                </div>
                
                <div class="p-6">
                    <!-- En-tête -->
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br 
                            @if($t == 1) from-blue-100 to-cyan-100
                            @elseif($t == 2) from-green-100 to-emerald-100
                            @else from-purple-100 to-pink-100
                            @endif 
                            rounded-2xl flex items-center justify-center shadow-inner">
                            <span class="font-black text-3xl 
                                @if($t == 1) text-blue-600
                                @elseif($t == 2) text-green-600
                                @else text-purple-600
                                @endif">
                                {{ $t }}
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Trimestre {{ $t }}</h2>
                        <p class="text-gray-500 text-sm mt-1">
                            <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>
                            Période d'évaluation
                        </p>
                    </div>

                    <!-- Grille des actions -->
                    <div class="grid grid-cols-1 gap-2">
                        <a href="{{ route('teacher.classes.trimestres.eleves', [$classe->id, $t]) }}"
                           class="action-btn group relative overflow-hidden flex items-center justify-between px-4 py-3 bg-amber-50 rounded-xl border border-amber-200 hover:bg-amber-100 hover:border-amber-300 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-amber-200 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-list-alt text-amber-700 text-sm"></i>
                                </div>
                                <span class="font-medium text-amber-800">Récapitulatif</span>
                            </div>
                            <i class="fas fa-chevron-right text-amber-600 text-sm opacity-0 group-hover:opacity-100 transform translate-x-0 group-hover:translate-x-1 transition-all"></i>
                        </a>

                        <a href="{{ route('censeur.classes.trimestre.matiere', [$classe->id, $t]) }}"
                           class="action-btn group relative overflow-hidden flex items-center justify-between px-4 py-3 bg-red-50 rounded-xl border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-200 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-book text-red-700 text-sm"></i>
                                </div>
                                <span class="font-medium text-red-800">Matières</span>
                            </div>
                            <i class="fas fa-chevron-right text-red-600 text-sm opacity-0 group-hover:opacity-100 transform translate-x-0 group-hover:translate-x-1 transition-all"></i>
                        </a>

                        <a href="{{ route('censeur.classes.trimestre.points', [$classe->id, $t]) }}"
                           class="action-btn group relative overflow-hidden flex items-center justify-between px-4 py-3 bg-green-50 rounded-xl border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-200 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-chart-line text-green-700 text-sm"></i>
                                </div>
                                <span class="font-medium text-green-800">Points disponibles</span>
                            </div>
                            <i class="fas fa-chevron-right text-green-600 text-sm opacity-0 group-hover:opacity-100 transform translate-x-0 group-hover:translate-x-1 transition-all"></i>
                        </a>

                        <button type="button"
                                onclick="openListe({{ $t }})"
                                class="action-btn group relative overflow-hidden flex items-center justify-between px-4 py-3 bg-sky-50 rounded-xl border border-sky-200 hover:bg-sky-100 hover:border-sky-300 transition-all duration-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-sky-200 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-file-pdf text-sky-700 text-sm"></i>
                                </div>
                                <span class="font-medium text-sky-800">Fiche de notes PDF</span>
                            </div>
                            <i class="fas fa-chevron-right text-sky-600 text-sm opacity-0 group-hover:opacity-100 transform translate-x-0 group-hover:translate-x-1 transition-all"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Message si aucun trimestre -->
        @if(count($trimestres) === 0)
        <div class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">Aucun trimestre disponible</h3>
            <p class="text-gray-500">Les trimestres apparaîtront ici une fois configurés.</p>
        </div>
        @endif

        <!-- Informations supplémentaires -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
            <div class="flex items-start">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-2">Gestion des trimestres</h3>
                    <p class="text-blue-800 text-sm leading-relaxed">
                        Consultez le <strong>récapitulatif</strong> des élèves pour une vue d'ensemble des performances,<br>
                        gérez les <strong>matières</strong> spécifiques à chaque trimestre, ou utilisez le
                        <strong>Point de l'Année Académique</strong> pour un bilan complet avec moyennes et statuts de passage.<br>
                        Utilisez <strong>Fiche de notes (PDF)</strong> pour télécharger la fiche de notes d'une matière.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .action-btn {
        position: relative;
        overflow: hidden;
    }
    
    .action-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .action-btn:active::before {
        width: 300px;
        height: 300px;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-slide-down {
        animation: slideDown 0.4s ease-out;
    }
    
    .modal-content {
        animation: modalFadeIn 0.3s ease-out;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animation d'apparition des cartes
    document.querySelectorAll('.group.bg-white').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Modal point de l'année
    const pointAnneeLink = document.getElementById('pointAnneeLink');
    const modal = document.getElementById('loadingModal');
    const confirmButton = document.getElementById('confirmButton');
    const loadingAnimation = document.getElementById('loadingAnimation');
    const targetUrl = "{{ route('censeur.classes.point-annee', $classe->id) }}";
    let isProcessing = false;

    pointAnneeLink.addEventListener('click', function (e) {
        e.preventDefault();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        confirmButton.disabled = false;
        confirmButton.classList.remove('opacity-50', 'cursor-not-allowed');
        confirmButton.innerHTML = '<i class="fas fa-check-circle mr-2"></i> D\'accord';
        loadingAnimation.classList.add('hidden');
        isProcessing = false;
    });

    confirmButton.addEventListener('click', async function () {
        if (isProcessing) return;
        isProcessing = true;
        confirmButton.disabled = true;
        confirmButton.classList.add('opacity-50', 'cursor-not-allowed');
        loadingAnimation.classList.remove('hidden');
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement...';
        await new Promise(resolve => setTimeout(resolve, 1500));
        window.location.href = targetUrl;
    });

    document.getElementById('modalOverlay').addEventListener('click', function () {
        if (!isProcessing) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
});

let selectedTrimestre = null;
const listeElevesRouteTemplate = "{{ url('/censeur/classes/' . $classe->id . '/trimestres/__TRIMESTRE__/subjects/__SUBJECT__/liste-eleves/pdf') }}";

function openListe(trimestre) {
    selectedTrimestre = trimestre;
    document.getElementById('listeElevesTrimestreLabel').textContent = 'Trimestre ' + trimestre;
    document.getElementById('listeElevesSubjectSelect').value = '';
    document.getElementById('listeElevesModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeListe() {
    document.getElementById('listeElevesModal').classList.add('hidden');
    document.body.style.overflow = '';
    selectedTrimestre = null;
}

function telechargerListe() {
    const subjectId = document.getElementById('listeElevesSubjectSelect').value;
    if (!subjectId) {
        alert('Veuillez sélectionner une matière.');
        return;
    }
    const url = listeElevesRouteTemplate
        .replace('__TRIMESTRE__', selectedTrimestre)
        .replace('__SUBJECT__', subjectId);
    closeListe();
    window.location.href = url;
}

document.getElementById('listeElevesOverlay').addEventListener('click', function () {
    closeListe();
});
</script>
@endsection
{{-- resources/views/censeur/classes/notes/index.blade.php --}}
@extends('layouts.app')
@php
    $pageTitle = "Classes";
@endphp
@section('content')

{{-- Iframe invisible pour déclencher le téléchargement sans quitter la page --}}
<iframe id="downloadFrame" name="downloadFrame" style="display:none; width:0; height:0; border:none;" src="about:blank"></iframe>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- En-tête -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Gestion des classes</h1>
                <p class="text-gray-600">Gérez les autorisations et accédez aux informations des classes</p>
            </div>

            <!-- Bouton Liste Récursive -->
            <button
                id="btnListeRecursive"
                onclick="openListeRecursiveModal()"
                class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 text-white rounded-xl shadow-md hover:bg-red-700 active:scale-95 transition-all duration-200 font-semibold text-sm whitespace-nowrap"
            >
                <i class="fas fa-exclamation-triangle"></i>
                Liste récursive
            </button>
        </div>

        <!-- Grille des classes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($classes as $classe)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-300">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chalkboard-teacher text-blue-600 text-xl"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $classe->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $classe->level ?? 'Secondaire' }}</p>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('censeur.permissions.index', $classe->id) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-100 hover:border-green-300 transition-colors duration-200">
                        <i class="fas fa-user-shield mr-3"></i>
                        Autorisations
                    </a>
                    <a href="{{ route('censeur.classes.trimestres', $classe->id) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-blue-50 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-colors duration-200">
                        <i class="fas fa-folder-open mr-3"></i>
                        Accéder
                    </a>
                    <a href="{{ route('censeur.exams.types', $classe->id) }}"
                       class="w-full flex items-center justify-center px-4 py-3 bg-purple-50 text-purple-700 rounded-lg border border-purple-200 hover:bg-purple-100 hover:border-purple-300 transition-colors duration-200">
                        <i class="fas fa-pen-alt mr-3"></i>
                        Épreuves
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        @if(count($classes) === 0)
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-chalkboard text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune classe disponible</h3>
            <p class="text-gray-500">Les classes apparaîtront ici une fois créées.</p>
        </div>
        @endif
    </div>
</div>


<!-- ============================================================ -->
<!-- MODAL : Sélection du trimestre pour Liste Récursive          -->
<!-- ============================================================ -->
<div
    id="modalListeRecursive"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display:none !important;"
    aria-modal="true"
    role="dialog"
    aria-labelledby="modalListeRecursiveTitle"
>
    <!-- Overlay (cliquable uniquement hors chargement) -->
    <div
        id="modalOverlay"
        onclick="overlayClick()"
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        style="opacity:0; transition: opacity 0.3s ease;"
    ></div>

    <!-- Carte du modal -->
    <div
        id="modalCard"
        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
        style="transform: translateY(30px) scale(0.97); opacity:0; transition: transform 0.35s cubic-bezier(.34,1.56,.64,1), opacity 0.3s ease;"
    >
        <!-- Bandeau supérieur -->
        <div class="bg-gradient-to-r from-red-500 to-red-700 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-pdf text-white text-lg"></i>
                </div>
                <div>
                    <h2 id="modalListeRecursiveTitle" class="text-white font-bold text-lg leading-tight">
                        Liste récursive
                    </h2>
                    <p class="text-red-100 text-xs mt-0.5">Enseignants avec notes manquantes</p>
                </div>
            </div>
            <button
                id="btnFermerModal"
                onclick="fermerModal()"
                class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition-colors duration-200"
                aria-label="Fermer"
            >
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Corps du modal -->
        <div class="px-6 py-6">

            <!-- Description (masquée en mode chargement) -->
            <div id="sectionSelection">
                <p class="text-gray-600 text-sm mb-6 leading-relaxed">
                    Sélectionnez le trimestre pour générer la liste PDF de tous les enseignants
                    n'ayant pas encore saisi au moins <strong>2 interrogations</strong> et
                    <strong>2 devoirs</strong> par matière dans leurs classes.
                </p>

                <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                    Choisissez un trimestre
                </p>

                <div class="grid grid-cols-3 gap-3 mb-6" id="trimestreGrid">
                    @foreach([1, 2, 3] as $t)
                    <button
                        type="button"
                        onclick="selectTrimestre({{ $t }})"
                        data-trimestre="{{ $t }}"
                        class="trimestre-btn flex flex-col items-center justify-center gap-2 py-5 rounded-xl border-2 border-gray-200 bg-gray-50 hover:border-red-400 hover:bg-red-50 transition-all duration-200 cursor-pointer group"
                    >
                        <div class="w-9 h-9 rounded-full bg-red-100 group-hover:bg-red-200 flex items-center justify-center transition-colors">
                            <span class="font-bold text-red-600 text-base">{{ $t }}</span>
                        </div>
                        <span class="text-xs font-semibold text-gray-600 group-hover:text-red-700 transition-colors">Trimestre {{ $t }}</span>
                    </button>
                    @endforeach
                </div>

                <!-- Message de confirmation de sélection -->
                <div id="trimestreSelectedMsg" class="hidden mb-4 items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    <i class="fas fa-check-circle text-red-500 text-sm flex-shrink-0"></i>
                    <span class="text-sm text-red-700 font-medium" id="trimestreSelectedText"></span>
                </div>
            </div>

            <!-- =============================================
                 BLOC CHARGEMENT (affiché pendant téléchargement)
            ============================================= -->
            <div id="sectionLoading" class="hidden py-4">
                <!-- Spinner animé centré -->
                <div class="flex flex-col items-center justify-center gap-4 mb-6">
                    <div class="relative w-16 h-16">
                        <svg class="animate-spin w-16 h-16 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <i class="fas fa-file-pdf text-red-400 text-lg"></i>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-gray-800 text-base">Traitement en cours…</p>
                        <p class="text-sm text-gray-500 mt-1">Veuillez patienter, le PDF est en cours de téléchargement.</p>
                    </div>
                </div>

                <!-- Barre de progression indéterminée -->
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden mb-2">
                    <div class="progress-bar h-1.5 bg-red-500 rounded-full"></div>
                </div>
                <p class="text-center text-xs text-gray-400">Le téléchargement démarrera automatiquement</p>
            </div>

            <!-- Boutons d'action -->
            <div class="flex gap-3 mt-4">
                <button
                    type="button"
                    id="btnAnnuler"
                    onclick="annulerTelechargement()"
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-300 text-gray-600 bg-white hover:bg-gray-50 font-semibold text-sm transition-colors duration-200"
                >
                    Annuler
                </button>

                <button
                    type="button"
                    id="btnTelechargerPdf"
                    onclick="lancerTelechargement()"
                    class="flex-1 px-4 py-3 rounded-xl bg-red-600 text-white font-semibold text-sm text-center flex items-center justify-center gap-2 opacity-40 cursor-not-allowed transition-all duration-200"
                    disabled
                >
                    <i class="fas fa-download text-sm"></i>
                    <span>Télécharger PDF</span>
                </button>
            </div>

        </div>

        <!-- Bande décorative bas -->
        <div class="h-1 bg-gradient-to-r from-red-400 via-red-500 to-red-700"></div>
    </div>
</div>


<style>
    .trimestre-btn.selected {
        border-color: #dc2626 !important;
        background-color: #fef2f2 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
    }
    .trimestre-btn.selected .rounded-full {
        background-color: #fecaca !important;
    }
    .trimestre-btn.selected span {
        color: #b91c1c !important;
    }

    /* Barre de progression indéterminée */
    @keyframes progress-slide {
        0%   { transform: translateX(-100%); width: 40%; }
        50%  { width: 60%; }
        100% { transform: translateX(260%); width: 40%; }
    }
    .progress-bar {
        animation: progress-slide 1.6s ease-in-out infinite;
    }
</style>

<script>
    const ROUTE_BASE   = "{{ rtrim(url('/censeur/liste-recursive/trimestre'), '/') }}";

    let selectedTrimestre = null;
    let modalOpen         = false;
    let isDownloading     = false;
    let abortController   = null;   // pour annuler le fetch() en cours

    /* ======================================================
       OUVRIR LE MODAL
    ====================================================== */
    function openListeRecursiveModal() {
        resetModal();

        const modal = document.getElementById('modalListeRecursive');
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
        modalOpen = true;

        requestAnimationFrame(() => {
            document.getElementById('modalOverlay').style.opacity = '1';
            document.getElementById('modalCard').style.transform  = 'translateY(0) scale(1)';
            document.getElementById('modalCard').style.opacity    = '1';
        });

        document.body.style.overflow = 'hidden';
    }

    /* ======================================================
       RESET COMPLET DU MODAL
    ====================================================== */
    function resetModal() {
        selectedTrimestre = null;
        isDownloading     = false;

        // Annuler tout fetch en cours
        if (abortController) {
            abortController.abort();
            abortController = null;
        }

        // Réinitialiser les sections
        document.getElementById('sectionSelection').classList.remove('hidden');
        document.getElementById('sectionLoading').classList.add('hidden');

        // Réinitialiser les boutons trimestre
        document.querySelectorAll('.trimestre-btn').forEach(btn => btn.classList.remove('selected'));
        document.getElementById('trimestreSelectedMsg').classList.add('hidden');
        document.getElementById('trimestreSelectedMsg').classList.remove('flex');

        // Bouton télécharger : désactivé
        const btnPdf = document.getElementById('btnTelechargerPdf');
        btnPdf.disabled = true;
        btnPdf.classList.add('opacity-40', 'cursor-not-allowed');
        btnPdf.classList.remove('hover:bg-red-700', 'cursor-pointer');
        btnPdf.innerHTML = '<i class="fas fa-download text-sm"></i><span>Télécharger PDF</span>';

        // Bouton Annuler : libellé normal
        document.getElementById('btnAnnuler').textContent = 'Annuler';

        // Bouton fermer : réactivé
        document.getElementById('btnFermerModal').style.pointerEvents = '';
        document.getElementById('btnFermerModal').style.opacity = '';

        document.getElementById('modalOverlay').style.opacity = '0';
        document.getElementById('modalCard').style.transform  = 'translateY(30px) scale(0.97)';
        document.getElementById('modalCard').style.opacity    = '0';
    }

    /* ======================================================
       FERMER LE MODAL (croix en-tête uniquement si pas en téléchargement)
    ====================================================== */
    function fermerModal() {
        if (isDownloading) return; // la croix ne fait rien en cours de téléchargement
        closeAnimate();
    }

    /* ======================================================
       CLIC SUR L'OVERLAY
    ====================================================== */
    function overlayClick() {
        if (isDownloading) return;
        closeAnimate();
    }

    /* ======================================================
       ANIMATION DE FERMETURE
    ====================================================== */
    function closeAnimate() {
        const modal   = document.getElementById('modalListeRecursive');
        const overlay = document.getElementById('modalOverlay');
        const card    = document.getElementById('modalCard');

        overlay.style.opacity = '0';
        card.style.transform  = 'translateY(30px) scale(0.97)';
        card.style.opacity    = '0';

        setTimeout(() => {
            modal.style.display       = 'none';
            document.body.style.overflow = '';
            modalOpen     = false;
            isDownloading = false;
        }, 320);
    }

    /* ======================================================
       SÉLECTIONNER UN TRIMESTRE
    ====================================================== */
    function selectTrimestre(num) {
        if (isDownloading) return;

        selectedTrimestre = num;

        document.querySelectorAll('.trimestre-btn').forEach(btn => btn.classList.remove('selected'));
        document.querySelector(`.trimestre-btn[data-trimestre="${num}"]`).classList.add('selected');

        const msg = document.getElementById('trimestreSelectedMsg');
        document.getElementById('trimestreSelectedText').textContent =
            `Trimestre ${num} sélectionné — cliquez sur « Télécharger PDF » pour continuer.`;
        msg.classList.remove('hidden');
        msg.classList.add('flex');

        // Activer le bouton PDF
        const btnPdf = document.getElementById('btnTelechargerPdf');
        btnPdf.disabled = false;
        btnPdf.classList.remove('opacity-40', 'cursor-not-allowed');
        btnPdf.classList.add('hover:bg-red-700', 'cursor-pointer');
    }

    /* ======================================================
       LANCER LE TÉLÉCHARGEMENT VIA FETCH (Blob)
       — Remplace le mécanisme iframe qui ne déclenchait
         pas toujours l'événement "load" sur les navigateurs
         modernes pour les réponses de type attachment.
    ====================================================== */
    async function lancerTelechargement() {
        if (isDownloading || !selectedTrimestre) return;

        isDownloading = true;

        // ---- Basculer vers le bloc chargement ----
        document.getElementById('sectionSelection').classList.add('hidden');
        document.getElementById('sectionLoading').classList.remove('hidden');

        // ---- Désactiver le bouton télécharger ----
        const btnPdf = document.getElementById('btnTelechargerPdf');
        btnPdf.disabled = true;
        btnPdf.classList.add('opacity-40', 'cursor-not-allowed');
        btnPdf.classList.remove('hover:bg-red-700');
        btnPdf.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i><span>Traitement…</span>';

        // ---- Changer le libellé Annuler ----
        document.getElementById('btnAnnuler').textContent = 'Annuler';

        // ---- Désactiver la croix ----
        document.getElementById('btnFermerModal').style.pointerEvents = 'none';
        document.getElementById('btnFermerModal').style.opacity = '0.4';

        // ---- Fetch avec AbortController (permet l'annulation) ----
        abortController = new AbortController();
        const url = `${ROUTE_BASE}/${selectedTrimestre}/pdf`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                signal: abortController.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/pdf',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                }
            });

            if (!response.ok) {
                throw new Error(`Erreur serveur : ${response.status}`);
            }

            // Récupérer le nom du fichier depuis Content-Disposition si disponible
            let fileName = `Liste_Recursive_Notes_Manquantes_T${selectedTrimestre}.pdf`;
            const disposition = response.headers.get('Content-Disposition');
            if (disposition) {
                const match = disposition.match(/filename[^;=\n]*=(['"]?)([^'";\n]+)\1/);
                if (match && match[2]) {
                    fileName = match[2].trim();
                }
            }

            // Convertir la réponse en Blob et déclencher le téléchargement
            const blob = await response.blob();
            const blobUrl = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href     = blobUrl;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Libérer l'URL objet après un court délai
            setTimeout(() => URL.revokeObjectURL(blobUrl), 2000);

            // ---- Succès : revenir à l'état sélection ----
            resetApresTelechargement();

        } catch (err) {
            if (err.name === 'AbortError') {
                // Annulation volontaire — on ne fait rien (closeAnimate gère l'UI)
                return;
            }
            // Erreur réseau ou serveur
            isDownloading = false;
            document.getElementById('sectionLoading').classList.add('hidden');
            document.getElementById('sectionSelection').classList.remove('hidden');

            // Réactiver le bouton pour qu'il puisse réessayer
            btnPdf.disabled = false;
            btnPdf.classList.remove('opacity-40', 'cursor-not-allowed');
            btnPdf.classList.add('hover:bg-red-700', 'cursor-pointer');
            btnPdf.innerHTML = '<i class="fas fa-download text-sm"></i><span>Réessayer</span>';

            document.getElementById('btnFermerModal').style.pointerEvents = '';
            document.getElementById('btnFermerModal').style.opacity = '';

            // Afficher un message d'erreur dans la zone de confirmation
            const msg = document.getElementById('trimestreSelectedMsg');
            document.getElementById('trimestreSelectedText').textContent =
                `Erreur lors de la génération du PDF. Veuillez réessayer.`;
            msg.classList.remove('hidden');
            msg.classList.add('flex');

            console.error('Erreur téléchargement PDF :', err);
        }
    }

    function annulerTelechargement() {
        // Annuler le fetch en cours s'il existe
        if (abortController) {
            abortController.abort();
            abortController = null;
        }

        isDownloading = false;

        // Fermer le modal directement
        closeAnimate();
    }

    function resetApresTelechargement() {
        isDownloading = false;
        abortController = null;

        // Revenir à la section de sélection
        document.getElementById('sectionLoading').classList.add('hidden');
        document.getElementById('sectionSelection').classList.remove('hidden');

        // Remettre le message de trimestre sélectionné
        const msg = document.getElementById('trimestreSelectedMsg');
        msg.classList.remove('hidden');
        msg.classList.add('flex');
        document.getElementById('trimestreSelectedText').textContent =
            `Trimestre ${selectedTrimestre} — téléchargement terminé ✓`;

        // Réactiver le bouton PDF
        const btnPdf = document.getElementById('btnTelechargerPdf');
        btnPdf.disabled = false;
        btnPdf.classList.remove('opacity-40', 'cursor-not-allowed');
        btnPdf.classList.add('hover:bg-red-700', 'cursor-pointer');
        btnPdf.innerHTML = '<i class="fas fa-download text-sm"></i><span>Télécharger à nouveau</span>';

        // Réactiver la croix
        document.getElementById('btnFermerModal').style.pointerEvents = '';
        document.getElementById('btnFermerModal').style.opacity = '';

        // Libellé bouton gauche → Fermer
        document.getElementById('btnAnnuler').textContent = 'Fermer';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOpen && !isDownloading) {
            closeAnimate();
        }
    });

 
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.grid > div');
        cards.forEach((card, index) => {
            card.style.opacity   = '0';
            card.style.transform = 'translateY(10px)';
            setTimeout(() => {
                card.style.transition = 'all 0.4s ease';
                card.style.opacity    = '1';
                card.style.transform  = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endsection
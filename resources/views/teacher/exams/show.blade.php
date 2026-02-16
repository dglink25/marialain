@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Détails Épreuve';
@endphp

<style>
.pdf-viewer {
    background: #f8fafc;
    border-radius: 0.5rem;
    overflow: hidden;
}

.pdf-pages-container {
    height: 600px;
    overflow-y: auto;
    padding: 1.5rem;
    background: #f1f5f9;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.pdf-page {
    max-width: 100%;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
    animation: spin 1s linear infinite;
    width: 40px;
    height: 40px;
    border: 3px solid #e2e8f0;
    border-top-color: #3b82f6;
    border-radius: 50%;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.pdf-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.pdf-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Styles pour l'iframe PDF */
.pdf-iframe {
    width: 100%;
    height: 700px;
    border: none;
    background: #f1f5f9;
}

/* Message d'erreur */
.pdf-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 400px;
    background: white;
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
}

.pdf-error p {
    color: #6b7280;
    margin: 1rem 0;
}

/* Suppression des styles inutilisés */
.thumbnail-grid, .thumbnail-item, .thumbnail-image {
    display: none;
}
</style>

<div class="bg-gray-50 py-8 min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
        
        {{-- Navigation --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('teacher.exams.index') }}" 
               class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour à la liste
            </a>
            
            <div class="flex gap-2">
                <a href="{{ $exam->file_url }}" download 
                   class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Télécharger
                </a>
            </div>
        </div>

        {{-- Carte principale --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            {{-- En-tête --}}
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
                <div class="flex items-start gap-4">
                    <div class="bg-blue-600 rounded-lg p-3 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-xl font-semibold text-gray-900">{{ $exam->titre }}</h1>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                {{ $exam->class->name }}
                            </span>
                            <span class="text-xs bg-purple-50 text-purple-600 px-2 py-1 rounded">
                                {{ $exam->subject->name }}
                            </span>
                            <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded">
                                T{{ $exam->trimestre }} • {{ ucfirst($exam->type) }} n°{{ $exam->numero_evaluation }}
                            </span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $exam->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            {{-- Corps --}}
            <div class="p-6">
                {{-- Grille d'informations --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Trimestre</p>
                        <p class="font-medium text-gray-900">Trimestre {{ $exam->trimestre }}</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Type</p>
                        <p class="font-medium text-gray-900 capitalize">{{ $exam->type }} n°{{ $exam->numero_evaluation }}</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Fichier</p>
                        <p class="font-medium text-gray-900 text-sm truncate" title="{{ $exam->file_name }}">
                            {{ $exam->file_name }}
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Enseignant</p>
                        <p class="font-medium text-gray-900">{{ $exam->teacher->name ?? 'Vous' }}</p>
                    </div>
                </div>

                {{-- Description --}}
                @if($exam->description)
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <p class="text-xs text-gray-500 mb-2">Description</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $exam->description }}</p>
                    </div>
                @endif

                {{-- Visionneuse PDF --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="bg-red-100 rounded p-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Aperçu du document</span>
                            </div>
                            
                            <a href="{{ $exam->file_url }}?flags=attachment" 
                               class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Télécharger
                            </a>
                        </div>
                    </div>
                    
                    <div id="pdfViewer" class="bg-gray-100 min-h-[600px]">
                        {{-- Le PDF sera chargé ici --}}
                        <div id="pdfLoading" class="flex items-center justify-center h-[600px]">
                            <div class="text-center">
                                <div class="loading-spinner mx-auto mb-4"></div>
                                <p class="text-gray-600">Chargement du document...</p>
                            </div>
                        </div>
                        
                        <iframe id="pdfFrame" class="pdf-iframe hidden" sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-downloads"></iframe>
                        
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pdfUrl = "{{ $exam->file_url }}";
    const pdfFrame = document.getElementById('pdfFrame');
    const pdfLoading = document.getElementById('pdfLoading');
    const pdfError = document.getElementById('pdfError');
    
    // Fonction pour charger le PDF
    function loadPDF() {
        // Pour les PDF sur Cloudinary, on peut utiliser le viewer Google Docs comme fallback
        const googleViewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(pdfUrl)}&embedded=true`;
        
        // Essayer d'abord avec l'iframe direct
        pdfFrame.src = pdfUrl;
        
        // Gestionnaire d'erreur pour l'iframe
        pdfFrame.onerror = function() {
            showFallbackViewer();
        };
        
        // Timeout pour détecter si le chargement échoue
        setTimeout(function() {
            try {
                // Vérifier si l'iframe a pu charger
                const iframeDoc = pdfFrame.contentDocument || pdfFrame.contentWindow?.document;
                if (!iframeDoc || iframeDoc.body.innerHTML === '' || iframeDoc.body.innerText.includes('error')) {
                    showFallbackViewer();
                } else {
                    // Succès
                    pdfLoading.classList.add('hidden');
                    pdfFrame.classList.remove('hidden');
                }
            } catch (e) {
                // Erreur de cross-origin, on considère que c'est un succès
                pdfLoading.classList.add('hidden');
                pdfFrame.classList.remove('hidden');
            }
        }, 3000);
    }
    
    function showFallbackViewer() {
        // Essayer avec le viewer Google
        pdfFrame.src = `https://docs.google.com/viewer?url=${encodeURIComponent(pdfUrl)}&embedded=true`;
        
        // Nouveau timeout pour le viewer Google
        setTimeout(function() {
            try {
                const iframeDoc = pdfFrame.contentDocument || pdfFrame.contentWindow?.document;
                if (!iframeDoc || iframeDoc.body.innerHTML === '' || iframeDoc.body.innerText.includes('error')) {
                    // Échec aussi avec Google, afficher le message d'erreur
                    pdfLoading.classList.add('hidden');
                    pdfError.classList.remove('hidden');
                } else {
                    pdfLoading.classList.add('hidden');
                    pdfFrame.classList.remove('hidden');
                }
            } catch (e) {
                pdfLoading.classList.add('hidden');
                pdfFrame.classList.remove('hidden');
            }
        }, 3000);
    }
    
    // Lancer le chargement
    loadPDF();
    
    // Option de téléchargement direct
    window.downloadPDF = function() {
        window.open(pdfUrl + '?flags=attachment', '_blank');
    };
});
</script>
@endsection
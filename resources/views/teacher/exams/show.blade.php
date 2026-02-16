@extends('layouts.app')

@section('content')
@php
    $pageTitle = 'Détails Épreuve';
@endphp

<style>
    /* Styles pour la visionneuse PDF */
    .pdf-viewer {
        display: flex;
        flex-direction: column;
        height: 700px;
        background: #f8fafc;
        border-radius: 1rem;
        overflow: hidden;
    }
    
    .pdf-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: white;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .pdf-pages {
        flex: 1;
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
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }
    
    .pdf-page:hover {
        transform: scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }
    
    .loading-pdf {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 500px;
        background: white;
        border-radius: 0.75rem;
    }
    
    .spinner {
        animation: spin 1s linear infinite;
        width: 48px;
        height: 48px;
        border: 4px solid #e2e8f0;
        border-top-color: #6366f1;
        border-radius: 50%;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .page-indicator {
        background: #1e293b;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    /* Mode grille pour les miniatures */
    .thumbnail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .thumbnail-item {
        cursor: pointer;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .thumbnail-item:hover {
        border-color: #6366f1;
        transform: translateY(-2px);
    }
    
    .thumbnail-item.active {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
    }
    
    .thumbnail-image {
        width: 100%;
        height: 100px;
        object-fit: cover;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header avec navigation --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('teacher.exams.index') }}" 
               class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition-colors group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour à la liste
            </a>
            
            <div class="flex gap-2">
                <button onclick="toggleViewMode()" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span id="viewModeText">Vue normale</span>
                </button>
                
                <a href="{{ $exam->file_url }}" download 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Télécharger PDF
                </a>
            </div>
        </div>

        <div class="max-w-6xl mx-auto">
            {{-- Carte principale --}}
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden mb-6">
                {{-- En-tête avec dégradé --}}
                <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-6 py-8 md:px-8 md:py-10">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    
                    <div class="relative z-10">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-3 md:p-4 flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-10 md:w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-2xl md:text-3xl font-bold text-white">{{ $exam->titre }}</h1>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" />
                                            </svg>
                                            {{ $exam->class->name }}
                                        </span>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            {{ $exam->subject->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 md:flex-shrink-0">
                                <span class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl text-white border border-white/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $exam->total_pages ?? 1 }} page(s)
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Formes décoratives --}}
                    <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4">
                        <div class="w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
                    </div>
                    <div class="absolute bottom-0 left-0 -translate-x-1/4 translate-y-1/4">
                        <div class="w-64 h-64 rounded-full bg-purple-500/20 blur-3xl"></div>
                    </div>
                </div>

                {{-- Corps --}}
                <div class="p-6 md:p-8">
                    {{-- Grille d'informations --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 border border-gray-100">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-blue-100 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Trimestre</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">
                                @if($exam->trimestre == 1)
                                    <span class="text-blue-600">Trimestre 1</span>
                                @elseif($exam->trimestre == 2)
                                    <span class="text-green-600">Trimestre 2</span>
                                @else
                                    <span class="text-orange-600">Trimestre 3</span>
                                @endif
                            </p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 border border-gray-100">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-purple-100 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Type</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">
                                <span class="capitalize">{{ $exam->type }}</span>
                                <span class="text-sm font-normal text-gray-500 ml-1">n°{{ $exam->numero_evaluation }}</span>
                            </p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 border border-gray-100">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-green-100 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Publié le</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900">
                                {{ $exam->created_at->format('d/m/Y') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                à {{ $exam->created_at->format('H:i') }}
                            </p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 border border-gray-100">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-amber-100 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Enseignant</span>
                            </div>
                            <p class="text-xl font-bold text-gray-900 truncate" title="{{ $exam->teacher->name ?? 'Vous' }}">
                                {{ $exam->teacher->name ?? 'Vous' }}
                            </p>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($exam->description)
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-5 border border-gray-100 mb-8">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="bg-orange-100 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Description</span>
                            </div>
                            <p class="text-gray-700 leading-relaxed whitespace-pre-line">
                                {{ $exam->description }}
                            </p>
                        </div>
                    @endif

                    {{-- Visionneuse PDF améliorée --}}
                    <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-200 overflow-hidden">
                        <div class="border-b border-gray-200 bg-white px-5 py-4">
                            <div class="flex items-center justify-between flex-wrap gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="bg-red-100 rounded-lg p-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Document original</h3>
                                        <p class="text-xs text-gray-500">{{ $exam->file_name }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2" id="pageNavigation">
                                    <button onclick="previousPage()" 
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            id="prevPageBtn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-700" id="pageIndicator">
                                        Page <span id="currentPage">1</span> / <span id="totalPages">{{ $exam->total_pages ?? 1 }}</span>
                                    </span>
                                    <button onclick="nextPage()" 
                                            class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            id="nextPageBtn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div id="pdfViewer" class="relative bg-gray-900/5 min-h-[600px] flex items-center justify-center p-4">
                            {{-- Vue normale (défilement) --}}
                            <div id="normalView" class="w-full">
                                <div id="pdfPages" class="flex flex-col items-center gap-4">
                                    <div class="loading-pdf">
                                        <div class="spinner mb-4"></div>
                                        <p class="text-gray-600">Chargement du document...</p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Vue grille (miniatures) --}}
                            <div id="gridView" class="w-full hidden">
                                <div id="thumbnailGrid" class="thumbnail-grid">
                                    <div class="col-span-full text-center py-8">
                                        <div class="spinner mx-auto mb-4"></div>
                                        <p class="text-gray-600">Chargement des miniatures...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 bg-white px-5 py-3">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">
                                    <span class="font-medium">Fichier:</span> {{ $exam->file_name }}
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ $exam->file_url }}" target="_blank" 
                                       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Ouvrir dans Cloudinary
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données des prévisualisations
    const previews = @json($previews ?? []);
    const totalPages = {{ $exam->total_pages ?? 1 }};
    const pdfUrl = "{{ $exam->file_url }}";
    const previewUrl = "{{ $exam->preview_url }}";
    
    let currentPage = 1;
    let viewMode = 'normal'; // normal ou grid
    
    // Initialiser la visionneuse
    initViewer();
    
    function initViewer() {
        if (previews && previews.length > 0) {
            // Utiliser les previews Cloudinary
            renderPagesFromPreviews();
        } else if (previewUrl) {
            // Utiliser la preview unique
            renderSinglePreview();
        } else {
            // Fallback: iframe PDF
            renderIframeViewer();
        }
    }
    
    function renderPagesFromPreviews() {
        const container = document.getElementById('pdfPages');
        container.innerHTML = '';
        
        previews.sort((a, b) => a.page - b.page).forEach(preview => {
            const pageDiv = document.createElement('div');
            pageDiv.className = 'relative group';
            pageDiv.id = `page-${preview.page}`;
            
            const img = document.createElement('img');
            img.src = preview.url;
            img.alt = `Page ${preview.page}`;
            img.className = 'pdf-page max-w-full h-auto shadow-lg rounded-lg border border-gray-200';
            img.loading = 'lazy';
            
            const pageNumber = document.createElement('div');
            pageNumber.className = 'absolute bottom-3 right-3 bg-black/70 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm';
            pageNumber.textContent = `Page ${preview.page}`;
            
            pageDiv.appendChild(img);
            pageDiv.appendChild(pageNumber);
            container.appendChild(pageDiv);
        });
        
        // Initialiser la navigation
        initNavigation();
        
        // Générer les miniatures pour la vue grille
        renderThumbnails();
    }
    
    function renderSinglePreview() {
        const container = document.getElementById('pdfPages');
        container.innerHTML = '';
        
        const pageDiv = document.createElement('div');
        pageDiv.className = 'relative group';
        
        const img = document.createElement('img');
        img.src = previewUrl;
        img.alt = 'Aperçu du document';
        img.className = 'pdf-page max-w-full h-auto shadow-lg rounded-lg border border-gray-200';
        
        const pageNumber = document.createElement('div');
        pageNumber.className = 'absolute bottom-3 right-3 bg-black/70 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm';
        pageNumber.textContent = 'Page 1';
        
        pageDiv.appendChild(img);
        pageDiv.appendChild(pageNumber);
        container.appendChild(pageDiv);
        
        // Désactiver la navigation
        document.getElementById('pageNavigation').classList.add('hidden');
    }
    
    function renderIframeViewer() {
        const container = document.getElementById('pdfPages');
        container.innerHTML = '';
        
        const iframe = document.createElement('iframe');
        iframe.src = pdfUrl + '#view=FitH';
        iframe.className = 'w-full h-[700px] rounded-lg border border-gray-200';
        iframe.style.border = 'none';
        
        container.appendChild(iframe);
        
        // Désactiver la navigation
        document.getElementById('pageNavigation').classList.add('hidden');
    }
    
    function renderThumbnails() {
        const gridContainer = document.getElementById('thumbnailGrid');
        gridContainer.innerHTML = '';
        
        previews.sort((a, b) => a.page - b.page).forEach(preview => {
            const thumbDiv = document.createElement('div');
            thumbDiv.className = 'thumbnail-item';
            thumbDiv.onclick = () => goToPage(preview.page);
            
            const img = document.createElement('img');
            img.src = preview.url;
            img.alt = `Page ${preview.page}`;
            img.className = 'thumbnail-image';
            img.loading = 'lazy';
            
            const pageLabel = document.createElement('div');
            pageLabel.className = 'bg-gray-100 text-center py-1 text-xs font-medium text-gray-700';
            pageLabel.textContent = `Page ${preview.page}`;
            
            thumbDiv.appendChild(img);
            thumbDiv.appendChild(pageLabel);
            gridContainer.appendChild(thumbDiv);
        });
    }
    
    function initNavigation() {
        if (totalPages <= 1) {
            document.getElementById('prevPageBtn').disabled = true;
            document.getElementById('nextPageBtn').disabled = true;
            return;
        }
        
        updateNavigation();
        
        // Scroll pour changer de page
        const pagesContainer = document.getElementById('pdfPages');
        pagesContainer.addEventListener('scroll', debounce(function() {
            const pages = document.querySelectorAll('#pdfPages .relative');
            let activePage = 1;
            let minDiff = Infinity;
            
            pages.forEach((page, index) => {
                const rect = page.getBoundingClientRect();
                const diff = Math.abs(rect.top - 100);
                if (diff < minDiff) {
                    minDiff = diff;
                    activePage = index + 1;
                }
            });
            
            if (activePage !== currentPage) {
                currentPage = activePage;
                updateNavigation();
            }
        }, 100));
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function updateNavigation() {
        document.getElementById('currentPage').textContent = currentPage;
        document.getElementById('prevPageBtn').disabled = currentPage === 1;
        document.getElementById('nextPageBtn').disabled = currentPage === totalPages;
        
        // Mettre à jour la classe active dans les miniatures
        document.querySelectorAll('.thumbnail-item').forEach((item, index) => {
            if (index + 1 === currentPage) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    // Fonctions globales
    window.previousPage = function() {
        if (currentPage > 1) {
            currentPage--;
            const targetPage = document.getElementById(`page-${currentPage}`);
            if (targetPage) {
                targetPage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            updateNavigation();
        }
    };
    
    window.nextPage = function() {
        if (currentPage < totalPages) {
            currentPage++;
            const targetPage = document.getElementById(`page-${currentPage}`);
            if (targetPage) {
                targetPage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            updateNavigation();
        }
    };
    
    window.goToPage = function(page) {
        currentPage = page;
        const targetPage = document.getElementById(`page-${page}`);
        if (targetPage) {
            targetPage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        updateNavigation();
        
        // Basculer en vue normale si on est en vue grille
        if (viewMode === 'grid') {
            toggleViewMode();
        }
    };
    
    window.toggleViewMode = function() {
        const normalView = document.getElementById('normalView');
        const gridView = document.getElementById('gridView');
        const viewModeText = document.getElementById('viewModeText');
        
        if (viewMode === 'normal') {
            normalView.classList.add('hidden');
            gridView.classList.remove('hidden');
            viewModeText.textContent = 'Vue miniatures';
            viewMode = 'grid';
        } else {
            normalView.classList.remove('hidden');
            gridView.classList.add('hidden');
            viewModeText.textContent = 'Vue normale';
            viewMode = 'normal';
        }
    };
});

// Gestionnaire d'erreur pour les images
window.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG') {
        e.target.src = '{{ asset("images/pdf-placeholder.png") }}';
        e.target.alt = 'Image non disponible';
    }
}, true);
</script>

@endsection
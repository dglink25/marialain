@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Ajouter une epreuve';
@endphp

<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
        
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('teacher.exams.index') }}" 
               class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour à la liste
            </a>
        </div>

        {{-- Formulaire --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- En-tête --}}
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 rounded-lg p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Nouvelle épreuve</h1>
                        <p class="text-sm text-gray-500">Créez et publiez un sujet d'examen</p>
                    </div>
                </div>
            </div>

            {{-- Corps du formulaire --}}
            <div class="p-6">
                <form id="examForm" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Sélection de la classe --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Classe <span class="text-red-500">*</span>
                        </label>
                        <select name="class_id" id="class_id" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Sélectionner une classe</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sélection de la matière --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Matière <span class="text-red-500">*</span>
                        </label>
                        <select name="subject_id" id="subject_id" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Sélectionnez d'abord une classe</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Trimestre --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Trimestre <span class="text-red-500">*</span>
                            </label>
                            <select name="trimestre" id="trimestre" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Sélectionner</option>
                                <option value="1">Trimestre 1</option>
                                <option value="2">Trimestre 2</option>
                                <option value="3">Trimestre 3</option>
                            </select>
                        </div>

                        {{-- Type d'évaluation --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select name="type" id="exam_type" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Sélectionner</option>
                                <option value="interrogation">Interrogation</option>
                                <option value="devoir">Devoir</option>
                            </select>
                        </div>
                    </div>

                    {{-- Numéro d'évaluation --}}
                    <div id="numero_evaluation_container" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Numéro d'évaluation <span class="text-red-500">*</span>
                        </label>
                        <select name="numero_evaluation" id="numero_evaluation" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Choisir un numéro</option>
                        </select>
                        <p id="numero_evaluation_hint" class="mt-1 text-xs text-gray-500"></p>
                    </div>

                    {{-- Titre --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Titre de l'épreuve <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="titre" id="titre" required
                               placeholder="Ex: Devoir sur les équations du second degré"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description (optionnelle)
                        </label>
                        <textarea name="description" id="description" rows="2"
                                  placeholder="Coefficient, durée, consignes particulières..."
                                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>

                    {{-- Zone de dépôt de fichier PDF avec conversion en images --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Sujet (PDF) <span class="text-red-500">*</span>
                        </label>
                        
                        <div id="dropzone" 
                             class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer bg-gray-50">
                            
                            <input type="file" name="file" id="file" accept=".pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                            
                            <div id="uploadPlaceholder">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-blue-600">Cliquez pour parcourir</span> ou glissez-déposez
                                </p>
                                <p class="text-xs text-gray-400 mt-1">PDF uniquement • Max 10 Mo</p>
                            </div>

                            <div id="fileInfo" class="hidden">
                                <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <div class="text-left">
                                            <p class="text-sm font-medium text-gray-700" id="fileName"></p>
                                            <p class="text-xs text-gray-500" id="filePages"></p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="removeFile()" class="text-gray-400 hover:text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Barre de progression --}}
                            <div id="progressContainer" class="hidden mt-3">
                                <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                    <span>Conversion en cours...</span>
                                    <span id="progressPercent">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div id="progressBar" class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <p id="progressStatus" class="text-xs text-gray-500 mt-1"></p>
                            </div>
                        </div>

                        {{-- Prévisualisation des pages converties --}}
                        <div id="previewContainer" class="hidden mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Aperçu des pages</p>
                            <div id="pagePreviews" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 max-h-48 overflow-y-auto p-2 bg-gray-50 rounded-lg border border-gray-200">
                                <!-- Les aperçus seront injectés ici -->
                            </div>
                        </div>
                    </div>

                    {{-- Messages d'erreur --}}
                    <div id="errorMessages" class="hidden rounded-lg border border-red-200 bg-red-50 p-4">
                        <div class="flex gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800 mb-1">Veuillez corriger :</p>
                                <ul id="errorList" class="list-disc pl-5 text-sm text-red-700 space-y-1"></ul>
                            </div>
                        </div>
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('teacher.exams.index') }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" id="submitBtn" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Publier l'épreuve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
// Configuration de PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

document.addEventListener('DOMContentLoaded', function() {
    // Chargement des matières
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        
        if (classId) {
            fetch(`/teacher/classes/${classId}/subjects`)
                .then(response => response.json())
                .then(data => {
                    subjectSelect.innerHTML = '<option value="">Sélectionner une matière</option>';
                    data.forEach(subject => {
                        subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                });
        } else {
            subjectSelect.innerHTML = '<option value="">Sélectionnez d\'abord une classe</option>';
        }
    });

    // Gestion du type d'évaluation
    const typeSelect = document.getElementById('exam_type');
    const numeroContainer = document.getElementById('numero_evaluation_container');
    const numeroSelect = document.getElementById('numero_evaluation');
    const numeroHint = document.getElementById('numero_evaluation_hint');

    typeSelect.addEventListener('change', function() {
        const type = this.value;
        
        if (type) {
            numeroContainer.classList.remove('hidden');
            
            fetch(`/teacher/exams/evaluation-numbers?type=${type}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        numeroSelect.innerHTML = '<option value="">Choisir un numéro</option>';
                        
                        data.numbers.forEach(num => {
                            const option = document.createElement('option');
                            option.value = num;
                            
                            if (type === 'devoir') {
                                option.textContent = `Devoir n°${num}`;
                                numeroHint.textContent = '2 devoirs maximum par trimestre';
                            } else {
                                option.textContent = `Interrogation n°${num}`;
                                numeroHint.textContent = '5 interrogations maximum par trimestre';
                            }
                            
                            numeroSelect.appendChild(option);
                        });
                    }
                });
        } else {
            numeroContainer.classList.add('hidden');
        }
    });

    // Drag & drop et conversion PDF
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file');
    let pdfPages = [];

    // Prévention des comportements par défaut
    ['dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Style au survol
    ['dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    // Gestion du drop
    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handlePDFConversion(files[0]);
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handlePDFConversion(this.files[0]);
        }
    });

    // Fonction de conversion PDF en images
    async function handlePDFConversion(file) {
        if (file.type !== 'application/pdf') {
            showToast('Veuillez sélectionner un fichier PDF', 'error');
            return;
        }

        // Afficher la progression
        document.getElementById('uploadPlaceholder').classList.add('hidden');
        document.getElementById('progressContainer').classList.remove('hidden');
        document.getElementById('fileName').textContent = file.name;
        
        try {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            const numPages = pdf.numPages;
            
            document.getElementById('filePages').textContent = `${numPages} page${numPages > 1 ? 's' : ''}`;
            
            pdfPages = [];
            const previewContainer = document.getElementById('pagePreviews');
            previewContainer.innerHTML = '';
            
            for (let i = 1; i <= numPages; i++) {
                // Mise à jour de la progression
                const progress = Math.round((i / numPages) * 100);
                document.getElementById('progressBar').style.width = `${progress}%`;
                document.getElementById('progressPercent').textContent = `${progress}%`;
                document.getElementById('progressStatus').textContent = `Conversion page ${i}/${numPages}`;
                
                const page = await pdf.getPage(i);
                const viewport = page.getViewport({ scale: 1.5 });
                
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                
                await page.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise;
                
                // Convertir en PNG base64
                const imgData = canvas.toDataURL('image/png');
                pdfPages.push(imgData);
                
                // Ajouter l'aperçu
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group';
                previewDiv.innerHTML = `
                    <img src="${imgData}" alt="Page ${i}" class="w-full h-auto rounded border border-gray-200 cursor-pointer hover:border-blue-500 transition-colors">
                    <span class="absolute bottom-1 right-1 bg-black bg-opacity-50 text-white text-xs px-1 rounded">${i}</span>
                `;
                
                previewDiv.addEventListener('click', () => {
                    // Ouvrir l'image en grand
                    window.open(imgData, '_blank');
                });
                
                previewContainer.appendChild(previewDiv);
            }
            
            // Masquer la progression et afficher l'aperçu
            document.getElementById('progressContainer').classList.add('hidden');
            document.getElementById('previewContainer').classList.remove('hidden');
            document.getElementById('fileInfo').classList.remove('hidden');
            
            // Ajouter les données des pages au formulaire
            const pagesInput = document.createElement('input');
            pagesInput.type = 'hidden';
            pagesInput.name = 'pdf_pages';
            pagesInput.value = JSON.stringify(pdfPages);
            pagesInput.id = 'pdfPagesInput';
            
            const existingInput = document.getElementById('pdfPagesInput');
            if (existingInput) existingInput.remove();
            
            document.getElementById('examForm').appendChild(pagesInput);
            
            showToast('PDF converti avec succès', 'success');
            
        } catch (error) {
            console.error('Erreur de conversion:', error);
            showToast('Erreur lors de la conversion du PDF', 'error');
            resetFileUpload();
        }
    }

    // Soumission du formulaire
    const examForm = document.getElementById('examForm');
    const submitBtn = document.getElementById('submitBtn');

    examForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideErrors();

        // Vérifier que le PDF a été converti
        if (!document.getElementById('pdfPagesInput')) {
            showToast('Veuillez d\'abord convertir un fichier PDF', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Publication en cours...';

        try {
            const formData = new FormData(this);
            
            // Ajouter les pages converties
            formData.append('pdf_pages', JSON.stringify(pdfPages));
            
            const response = await fetch('{{ route("teacher.exams.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('Épreuve publiée avec succès !', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("teacher.exams.index") }}';
                }, 1500);
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showToast(data.message || 'Une erreur est survenue', 'error');
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Publier l\'épreuve';
            }
        } catch (error) {
            console.error('Erreur:', error);
            showToast('Erreur de connexion', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Publier l\'épreuve';
        }
    });
});

// Fonctions utilitaires
function removeFile() {
    document.getElementById('file').value = '';
    document.getElementById('fileInfo').classList.add('hidden');
    document.getElementById('previewContainer').classList.add('hidden');
    document.getElementById('uploadPlaceholder').classList.remove('hidden');
    document.getElementById('progressContainer').classList.add('hidden');
    
    const pagesInput = document.getElementById('pdfPagesInput');
    if (pagesInput) pagesInput.remove();
    
    pdfPages = [];
}

function resetFileUpload() {
    document.getElementById('file').value = '';
    document.getElementById('uploadPlaceholder').classList.remove('hidden');
    document.getElementById('progressContainer').classList.add('hidden');
    document.getElementById('fileInfo').classList.add('hidden');
    document.getElementById('previewContainer').classList.add('hidden');
}

function showErrors(errors) {
    const errorDiv = document.getElementById('errorMessages');
    const errorList = document.getElementById('errorList');
    
    errorList.innerHTML = '';
    
    Object.values(errors).forEach(errorArray => {
        errorArray.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
    });
    
    errorDiv.classList.remove('hidden');
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideErrors() {
    document.getElementById('errorMessages').classList.add('hidden');
    document.getElementById('errorList').innerHTML = '';
}

function showToast(message, type = 'info') {
    let toastContainer = document.getElementById('toastContainer');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        `;
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: white;
        border-left: 4px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.3s ease-out;
        min-width: 300px;
    `;

    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
    const iconColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';

    toast.innerHTML = `
        <span style="color: ${iconColor}; font-weight: bold;">${icon}</span>
        <span style="color: #1f2937; font-size: 0.875rem;">${message}</span>
        <button onclick="this.parentElement.remove()" style="margin-left: auto; color: #9ca3af; hover:color: #4b5563;">✕</button>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 300);
        }
    }, 5000);
}

// Styles pour les animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
`;
document.head.appendChild(style);
</script>
@endsection

Call to undefined method Spatie\PdfToImage\Pdf::setOutputFormat()

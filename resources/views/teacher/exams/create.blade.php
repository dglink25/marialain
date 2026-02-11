{{-- resources/views/teacher/exams/create.blade.php --}}
@extends('layouts.app')

@section('content')

@php
    $pageTitle = 'Ajouter une epreuve';
@endphp
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('teacher.exams.index') }}" 
                       class="group flex items-center gap-2 text-gray-600 hover:text-indigo-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Retour à la liste</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Formulaire --}}
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                {{-- En-tête avec dégradé --}}
                <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 px-8 py-10">
                    <div class="absolute inset-0 bg-black opacity-10"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Nouvelle épreuve</h1>
                                <p class="text-indigo-100 mt-1">
                                    Créez et publiez un sujet d'examen pour vos élèves
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Formes décoratives --}}
                    <div class="absolute top-0 right-0 -mt-10 -mr-10">
                        <div class="w-40 h-40 rounded-full bg-white/10 blur-3xl"></div>
                    </div>
                    <div class="absolute bottom-0 left-0 -mb-10 -ml-10">
                        <div class="w-40 h-40 rounded-full bg-purple-500/20 blur-3xl"></div>
                    </div>
                </div>

                {{-- Corps du formulaire --}}
                <div class="p-8">
                    <form id="examForm" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- Sélection de la classe --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-100">
                                        <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </span>
                                    Classe
                                </span>
                            </label>
                            <select name="class_id" id="class_id" required
                                    class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3.5 text-gray-700 transition-all focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                                <option value="">Sélectionner une classe</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sélection de la matière --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-purple-100">
                                        <svg class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </span>
                                    Matière
                                </span>
                            </label>
                            <select name="subject_id" id="subject_id" required
                                    class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3.5 text-gray-700 transition-all focus:border-purple-400 focus:outline-none focus:ring-4 focus:ring-purple-100">
                                <option value="">Sélectionnez d'abord une classe</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Trimestre --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <span class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-100">
                                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </span>
                                        Trimestre
                                    </span>
                                </label>
                                <select name="trimestre" id="trimestre" required
                                        class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3.5 text-gray-700 transition-all focus:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                    <option value="">Sélectionner</option>
                                    <option value="1">🔵 Trimestre 1</option>
                                    <option value="2">🟢 Trimestre 2</option>
                                    <option value="3">🟠 Trimestre 3</option>
                                </select>
                            </div>

                            {{-- Type d'évaluation --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    <span class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-100">
                                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </span>
                                        Type
                                    </span>
                                </label>
                                <select name="type" id="exam_type" required
                                        class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3.5 text-gray-700 transition-all focus:border-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-100">
                                    <option value="">Sélectionner</option>
                                    <option value="interrogation">Interrogation</option>
                                    <option value="devoir">Devoir</option>
                                </select>
                            </div>
                        </div>

                        {{-- Numéro d'évaluation --}}
                        <div id="numero_evaluation_container" class="hidden space-y-2 animate-fadeIn">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                        </svg>
                                    </span>
                                    Numéro d'évaluation
                                </span>
                            </label>
                            <select name="numero_evaluation" id="numero_evaluation" required
                                    class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3.5 text-gray-700 transition-all focus:border-green-400 focus:outline-none focus:ring-4 focus:ring-green-100">
                                <option value="">Choisir un numéro</option>
                            </select>
                            <p id="numero_evaluation_hint" class="flex items-center gap-1 text-xs text-gray-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span></span>
                            </p>
                        </div>

                        {{-- Titre --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-cyan-100">
                                        <svg class="h-4 w-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </span>
                                    Titre de l'épreuve
                                </span>
                            </label>
                            <input type="text" name="titre" id="titre" required
                                   placeholder="Ex: Devoir sur les équations du second degré"
                                   class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3.5 text-gray-700 transition-all placeholder:text-gray-400 focus:border-cyan-400 focus:outline-none focus:ring-4 focus:ring-cyan-100">
                        </div>

                        {{-- Description --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-orange-100">
                                        <svg class="h-4 w-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                        </svg>
                                    </span>
                                    Description (optionnelle)
                                </span>
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      placeholder="Coefficient, durée, consignes particulières..."
                                      class="w-full resize-none rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3.5 text-gray-700 transition-all placeholder:text-gray-400 focus:border-orange-400 focus:outline-none focus:ring-4 focus:ring-orange-100"></textarea>
                        </div>

                        {{-- Zone de dépôt de fichier --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">
                                <span class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-red-100">
                                        <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </span>
                                    Sujet (PDF)
                                </span>
                            </label>
                            
                            <div id="dropzone" 
                                 class="group relative cursor-pointer rounded-2xl border-3 border-dashed border-gray-300 bg-gradient-to-b from-gray-50 to-white p-10 text-center transition-all hover:border-red-400 hover:from-red-50 hover:to-white">
                                
                                <input type="file" name="file" id="file" accept=".pdf" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" required>
                                
                                <div class="space-y-4">
                                    <div class="relative mx-auto w-24 h-24">
                                        <div class="absolute inset-0 rounded-2xl bg-red-100 opacity-0 transition-all group-hover:scale-150 group-hover:opacity-20"></div>
                                        <div class="relative flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-pink-600 shadow-lg transition-all group-hover:scale-110 group-hover:shadow-xl">
                                            <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <p class="text-gray-600">
                                            <span class="font-semibold text-red-600">Cliquez pour parcourir</span>
                                            <span class="mx-2 text-gray-400">ou</span>
                                            <span class="font-medium text-gray-500">glissez-déposez</span>
                                        </p>
                                        <p class="mt-2 text-xs text-gray-400">
                                            PDF uniquement • Taille max : 10 Mo
                                        </p>
                                    </div>
                                </div>

                                <div id="fileInfo" class="mt-6 hidden">
                                    <div class="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 px-5 py-3 border border-green-200">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500">
                                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-medium text-gray-700" id="fileName"></p>
                                            <p class="text-xs text-gray-500">PDF • Prêt à être envoyé</p>
                                        </div>
                                        <button type="button" onclick="removeFile()" 
                                                class="rounded-lg p-1.5 text-gray-400 transition-all hover:bg-red-50 hover:text-red-500">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Messages d'erreur --}}
                        <div id="errorMessages" class="hidden rounded-xl border border-red-200 bg-gradient-to-r from-red-50 to-red-100/80 px-5 py-4 text-red-700">
                            <div class="flex items-start gap-3">
                                <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-lg bg-red-200">
                                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="mb-2 font-semibold">Veuillez corriger :</p>
                                    <ul id="errorList" class="list-disc space-y-1 pl-5 text-sm"></ul>
                                </div>
                            </div>
                        </div>

                        {{-- Boutons d'action --}}
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end pt-6 border-t border-gray-200">
                            <a href="{{ route('teacher.exams.index') }}" 
                               class="inline-flex w-full items-center justify-center rounded-xl border-2 border-gray-300 bg-white px-6 py-3.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:border-gray-400 hover:bg-gray-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 sm:w-auto">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Annuler
                            </a>
                            <button type="submit" id="submitBtn" 
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg transition-all hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 sm:w-auto group relative overflow-hidden">
                                <span class="absolute inset-0 bg-white/10 opacity-0 transition-opacity group-hover:opacity-100"></span>
                                <svg class="mr-2 h-5 w-5 transition-transform group-hover:-translate-y-1 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Publier l'épreuve
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chargement des matières selon la classe
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
                        const option = document.createElement('option');
                        option.value = subject.id;
                        option.textContent = subject.name;
                        subjectSelect.appendChild(option);
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
    const hintSpan = numeroHint.querySelector('span');

    typeSelect.addEventListener('change', function() {
        const type = this.value;
        
        if (type) {
            numeroContainer.classList.remove('hidden');
            numeroContainer.classList.add('animate-fadeIn');
            
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
                                hintSpan.textContent = '2 devoirs maximum par trimestre';
                            } else {
                                option.textContent = `Interrogation n°${num}`;
                                hintSpan.textContent = '5 interrogations maximum par trimestre';
                            }
                            
                            numeroSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showToast('Erreur lors du chargement des numéros', 'error');
                });
        } else {
            numeroContainer.classList.add('hidden');
            numeroContainer.classList.remove('animate-fadeIn');
            numeroSelect.innerHTML = '<option value="">Sélectionnez d\'abord le type</option>';
        }
    });

    // Drag & drop
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file');

    ['dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.add('border-red-500', 'bg-red-100', 'scale-105');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove('border-red-500', 'bg-red-100', 'scale-105');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFileInfo(files[0]);
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            updateFileInfo(this.files[0]);
        }
    });

    // Soumission du formulaire
    const examForm = document.getElementById('examForm');
    const submitBtn = document.getElementById('submitBtn');

    examForm.addEventListener('submit', function(e) {
        e.preventDefault();

        hideErrors();

        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');
        submitBtn.innerHTML = 'Publication en cours...';

        const formData = new FormData(this);

        fetch('{{ route("teacher.exams.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Épreuve publiée avec succès !', 'success');
                
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("teacher.exams.index") }}';
                }, 1500);
            } else {
                if (data.errors) {
                    showErrors(data.errors);
                    showToast('Erreur de validation', 'error');
                } else {
                    showToast('' + (data.message || 'Une erreur est survenue'), 'error');
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur de connexion', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
            submitBtn.innerHTML = `
                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Publier l'épreuve
            `;
        });
    });
});

// Fonctions utilitaires
function updateFileInfo(file) {
    if (file && file.type === 'application/pdf') {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileInfo').classList.remove('hidden');
        document.getElementById('dropzone').classList.add('border-green-500', 'bg-green-50');
        
        const checkIcon = document.createElement('div');
        checkIcon.innerHTML = '✓';
        checkIcon.style.cssText = `
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #10b981;
            color: white;
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: bold;
            animation: scaleIn 0.3s ease-out;
        `;
        document.getElementById('dropzone').appendChild(checkIcon);
        
        setTimeout(() => {
            checkIcon.remove();
        }, 2000);
    } else {
        showToast('Veuillez sélectionner un fichier PDF', 'error');
        document.getElementById('file').value = '';
    }
}

function removeFile() {
    document.getElementById('file').value = '';
    document.getElementById('fileInfo').classList.add('hidden');
    document.getElementById('dropzone').classList.remove('border-green-500', 'bg-green-50');
}

function showErrors(errors) {
    const errorDiv = document.getElementById('errorMessages');
    const errorList = document.getElementById('errorList');
    
    errorList.innerHTML = '';
    
    Object.values(errors).forEach(errorArray => {
        errorArray.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            li.style.cssText = 'margin-left: 1rem;';
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
        padding: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.3s ease-out;
        max-width: 24rem;
    `;

    let icon = '';
    if (type === 'success') icon = '';
    else if (type === 'error') icon = '';
    else icon = 'ℹ';

    toast.innerHTML = `
        <span style="font-size: 1.25rem;">${icon}</span>
        <span style="color: #1f2937; font-size: 0.875rem; font-weight: 500;">${message}</span>
        <button onclick="this.parentElement.remove()" style="margin-left: auto; color: #6b7280; hover:color: #374151;">
            ✕
        </button>
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
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
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
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
    
    .btn-loading {
        position: relative;
        color: transparent !important;
    }
    
    .btn-loading::after {
        content: '';
        position: absolute;
        width: 1rem;
        height: 1rem;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        border: 3px solid transparent;
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: button-loading-spinner 0.6s linear infinite;
    }
    
    @keyframes button-loading-spinner {
        from {
            transform: rotate(0turn);
        }
        to {
            transform: rotate(1turn);
        }
    }
`;
document.head.appendChild(style);
</script>
@endsection
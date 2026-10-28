@extends('layouts.app')

@section('title', 'Téléchargement des Cahiers de Texte')

@section('content')
@php
    $pageTitle = 'Cahier de texte';
@endphp

<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- En-tête de la page --}}
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <i class="fas fa-file-pdf mr-1.5"></i> Édition Officielle &bull; Année {{ $academicYear->name }}
                    </div>
                    @if($isCenseur || (isset($entityId) && $entityId == 3))
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                            <i class="fas fa-graduation-cap mr-1.5"></i> Secondaire (Collège & Lycée)
                        </div>
                    @elseif($isPrimaire || (isset($entityId) && $entityId == 2))
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <i class="fas fa-child mr-1.5"></i> Primaire
                        </div>
                    @endif
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Téléchargement des Cahiers de Texte
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    Générez et téléchargez le cahier de texte officiel au format institutionnel (style noir et blanc pour inspection et archivage).
                </p>
            </div>
            <div>
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>
        </div>

        @if(!$isCenseur && !$isPrimaire)
            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mr-1">Filtrer par entité :</span>
                <a href="{{ route('cahier.export.index') }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ empty($entityId) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Toutes les classes
                </a>
                <a href="{{ route('cahier.export.index', ['entity_id' => 3]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ (isset($entityId) && $entityId == 3) ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-graduation-cap mr-1"></i> Secondaire
                </a>
                <a href="{{ route('cahier.export.index', ['entity_id' => 2]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ (isset($entityId) && $entityId == 2) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    <i class="fas fa-child mr-1"></i> Primaire
                </a>
            </div>
        @endif
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 flex items-center shadow-sm">
            <i class="fas fa-check-circle text-green-500 text-lg mr-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center shadow-sm">
            <i class="fas fa-exclamation-triangle text-red-500 text-lg mr-3"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 shadow-sm">
            <div class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire de génération --}}
    <form id="cahierExportForm" action="{{ route('cahier.export.pdf') }}" method="POST" target="_blank" class="space-y-8">
        @csrf
        <input type="hidden" name="entity_id" id="entity_id" value="{{ $entityId ?? '' }}">

        {{-- ÉTAPE 1 : Choix du type de filtre --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow">
                    1
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Sélectionnez le mode de filtrage</h2>
                    <p class="text-xs text-gray-500">Choisissez la portée du cahier de texte à télécharger</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Option 1 : Matière & Classe --}}
                <label class="filter-card relative flex flex-col p-5 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md border-indigo-600 bg-indigo-50/40" id="card-subject_class">
                    <input type="radio" name="filter_type" value="subject_class" checked class="sr-only" onchange="onFilterTypeChange('subject_class')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="check-indicator w-5 h-5 rounded-full border-2 border-indigo-600 bg-indigo-600 flex items-center justify-center text-white text-xs">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="font-bold text-gray-900 text-base mb-1">Par Matière & Classe</div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Télécharger les textes saisis pour une matière spécifique au sein d'une classe.
                    </p>
                </label>

                {{-- Option 2 : Professeur & Classe --}}
                <label class="filter-card relative flex flex-col p-5 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md border-gray-200 bg-white" id="card-teacher_class">
                    <input type="radio" name="filter_type" value="teacher_class" class="sr-only" onchange="onFilterTypeChange('teacher_class')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="check-indicator w-5 h-5 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center text-white text-xs">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="font-bold text-gray-900 text-base mb-1">Par Professeur & Classe</div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Toutes les matières d'un même enseignant dans la classe (ex: Lecture & Communication Écrite).
                    </p>
                </label>

                {{-- Option 3 : Toute la Classe --}}
                <label class="filter-card relative flex flex-col p-5 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md border-gray-200 bg-white" id="card-all_class">
                    <input type="radio" name="filter_type" value="all_class" class="sr-only" onchange="onFilterTypeChange('all_class')">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-lg">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="check-indicator w-5 h-5 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center text-white text-xs">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="font-bold text-gray-900 text-base mb-1">Toute la Classe</div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        L'ensemble des matières de la classe réunies dans un cahier de texte complet.
                    </p>
                </label>
            </div>
        </div>

        {{-- ÉTAPE 2 : Paramètres et Filtres Détaillés --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow">
                    2
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Paramètres de sélection</h2>
                    <p class="text-xs text-gray-500">Précisez la classe et les critères complémentaires</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Sélection de la Classe --}}
                <div class="md:col-span-2">
                    <label for="class_id" class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="fas fa-school text-indigo-600 mr-1.5"></i> Classe concernée <span class="text-red-500">*</span>
                    </label>
                    <select id="class_id" name="class_id" required onchange="onClassChange(this.value)" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm font-medium">
                        <option value="">-- Sélectionnez une classe --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ (isset($selectedClassId) && $selectedClassId == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }}@if(!$entityId && $c->entity) ({{ $c->entity->name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Champ Dynamique : Matière (Filtre 1) --}}
                <div id="field-subject_id" class="md:col-span-2">
                    <label for="subject_id" class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="fas fa-book text-indigo-600 mr-1.5"></i> Matière <span class="text-red-500">*</span>
                    </label>
                    <select id="subject_id" name="subject_id" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm font-medium">
                        <option value="">-- Sélectionnez d'abord une classe --</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Seules les matières enseignées dans cette classe sont listées.</p>
                </div>

                {{-- Champ Dynamique : Enseignant (Filtre 2) --}}
                <div id="field-teacher_id" class="md:col-span-2 hidden">
                    <label for="teacher_id" class="block text-sm font-semibold text-gray-800 mb-2">
                        <i class="fas fa-user-tie text-emerald-600 mr-1.5"></i> Enseignant <span class="text-red-500">*</span>
                    </label>
                    <select id="teacher_id" name="teacher_id" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-sm font-medium">
                        <option value="">-- Sélectionnez d'abord une classe --</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Toutes les matières de cet enseignant dans la classe sélectionnée seront incluses.</p>
                </div>

                {{-- Message d'information : Toute la Classe (Filtre 3) --}}
                <div id="field-all_class_info" class="md:col-span-2 hidden">
                    <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 flex items-start gap-3">
                        <i class="fas fa-info-circle text-purple-600 text-lg mt-0.5"></i>
                        <div class="text-sm">
                            <strong class="font-semibold">Rapport Global de la Classe :</strong>
                            Le cahier de texte généré regroupera l'intégralité des matières dispensées pour la classe sélectionnée, chacune présentée avec ses séances chronologiques et son volume d'heures.
                        </div>
                    </div>
                </div>

                {{-- Filtres optionnels par période --}}
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="far fa-calendar-alt text-gray-500 mr-1"></i> Date de début <span class="text-xs text-gray-400 font-normal">(Optionnel)</span>
                    </label>
                    <input type="date" id="start_date" name="start_date" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="far fa-calendar-check text-gray-500 mr-1"></i> Date de fin <span class="text-xs text-gray-400 font-normal">(Optionnel)</span>
                    </label>
                    <input type="date" id="end_date" name="end_date" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

            </div>

            {{-- Indicateur de chargement AJAX --}}
            <div id="loadingIndicator" class="hidden mt-4 text-center py-3 text-sm text-indigo-600 font-medium">
                <i class="fas fa-spinner fa-spin mr-2"></i> Chargement des données de la classe en cours...
            </div>

            {{-- Statut & Décompte --}}
            <div id="classStatsBanner" class="hidden mt-6 p-4 rounded-xl bg-gray-50 border border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-gray-700">Séances enregistrées pour cette classe : <strong id="statsTotalEntries" class="text-gray-900 font-bold">0</strong></span>
                    <span id="classEntityBadge" class="hidden px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200"></span>
                </div>
                <div class="text-xs text-gray-500">
                    <i class="fas fa-check-double mr-1 text-emerald-600"></i> Tous les statuts sont inclus (validés ou non)
                </div>
            </div>
        </div>

        {{-- ÉTAPE 3 : Action de Téléchargement --}}
        <div class="bg-gradient-to-r from-gray-900 to-indigo-950 rounded-2xl p-6 md:p-8 text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-bold">Prêt pour la génération du document ?</h3>
                <p class="text-gray-300 text-sm mt-1 max-w-xl">
                    Le document PDF officiel généré respecte strictement la charte institutionnelle : en-tête d'État, format noir et blanc sans couleur, texte justifié et colonne d'observation pour visa.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                {{-- Bouton Aperçu --}}
                <button type="button" onclick="submitFormWithPreview(true)" class="inline-flex items-center justify-center px-5 py-3 border border-gray-400 rounded-xl text-sm font-semibold text-white hover:bg-white/10 transition shadow-sm">
                    <i class="fas fa-eye mr-2"></i> Aperçu
                </button>

                {{-- Bouton Télécharger --}}
                <button type="button" onclick="submitFormWithPreview(false)" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-500 hover:bg-indigo-600 rounded-xl text-sm font-bold text-white transition shadow-lg hover:shadow-indigo-500/30">
                    <i class="fas fa-download mr-2"></i> Télécharger le PDF
                </button>
            </div>
        </div>

    </form>

</div>

<script>
    // État courant
    let currentFilterType = 'subject_class';
    let currentClassData = null;

    // Gestion du changement de type de filtre
    function onFilterTypeChange(type) {
        currentFilterType = type;

        // Mise à jour visuelle des cartes
        ['subject_class', 'teacher_class', 'all_class'].forEach(t => {
            const card = document.getElementById('card-' + t);
            const indicator = card.querySelector('.check-indicator');
            if (t === type) {
                card.classList.remove('border-gray-200', 'bg-white');
                card.classList.add('border-indigo-600', 'bg-indigo-50/40', 'shadow-sm');
                indicator.classList.remove('border-gray-300', 'bg-white');
                indicator.classList.add('border-indigo-600', 'bg-indigo-600');
            } else {
                card.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'shadow-sm');
                card.classList.add('border-gray-200', 'bg-white');
                indicator.classList.remove('border-indigo-600', 'bg-indigo-600');
                indicator.classList.add('border-gray-300', 'bg-white');
            }
        });

        // Afficher / masquer les champs correspondants
        const fieldSubject = document.getElementById('field-subject_id');
        const fieldTeacher = document.getElementById('field-teacher_id');
        const fieldAllInfo = document.getElementById('field-all_class_info');
        const selectSubject = document.getElementById('subject_id');
        const selectTeacher = document.getElementById('teacher_id');

        if (type === 'subject_class') {
            fieldSubject.classList.remove('hidden');
            fieldTeacher.classList.add('hidden');
            fieldAllInfo.classList.add('hidden');
            selectSubject.setAttribute('required', 'required');
            selectTeacher.removeAttribute('required');
        } else if (type === 'teacher_class') {
            fieldSubject.classList.add('hidden');
            fieldTeacher.classList.remove('hidden');
            fieldAllInfo.classList.add('hidden');
            selectSubject.removeAttribute('required');
            selectTeacher.setAttribute('required', 'required');
        } else {
            // all_class
            fieldSubject.classList.add('hidden');
            fieldTeacher.classList.add('hidden');
            fieldAllInfo.classList.remove('hidden');
            selectSubject.removeAttribute('required');
            selectTeacher.removeAttribute('required');
        }
    }

    // Gestion du changement de classe
    function onClassChange(classId) {
        const selectSubject = document.getElementById('subject_id');
        const selectTeacher = document.getElementById('teacher_id');
        const loading = document.getElementById('loadingIndicator');
        const statsBanner = document.getElementById('classStatsBanner');
        const statsEntries = document.getElementById('statsTotalEntries');

        if (!classId) {
            selectSubject.innerHTML = '<option value="">-- Sélectionnez d\'abord une classe --</option>';
            selectTeacher.innerHTML = '<option value="">-- Sélectionnez d\'abord une classe --</option>';
            statsBanner.classList.add('hidden');
            currentClassData = null;
            return;
        }

        loading.classList.remove('hidden');

        fetch(`{{ url('cahier-de-texte/ajax/classe-data') }}/${classId}`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                currentClassData = data;

                // Remplir le select des matières
                selectSubject.innerHTML = '<option value="">-- Choisir une matière --</option>';
                if (data.subjects && data.subjects.length > 0) {
                    data.subjects.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        const countStr = s.count > 0 ? ` (${s.count} séance${s.count > 1 ? 's' : ''})` : ' (0 séance)';
                        opt.textContent = `${s.name}${countStr}`;
                        selectSubject.appendChild(opt);
                    });
                } else {
                    selectSubject.innerHTML = '<option value="">Aucune matière trouvée pour cette classe</option>';
                }

                // Remplir le select des enseignants
                selectTeacher.innerHTML = '<option value="">-- Choisir un enseignant --</option>';
                if (data.teachers && data.teachers.length > 0) {
                    data.teachers.forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t.id;
                        const subjectsLabel = t.subjects && t.subjects.length > 0 ? ` [${t.subjects.join(', ')}]` : '';
                        const countStr = t.count > 0 ? ` (${t.count} séance${t.count > 1 ? 's' : ''})` : ' (0 séance)';
                        opt.textContent = `${t.name}${subjectsLabel}${countStr}`;
                        selectTeacher.appendChild(opt);
                    });
                } else {
                    selectTeacher.innerHTML = '<option value="">Aucun enseignant trouvé pour cette classe</option>';
                }

                // Afficher le décompte et l'entité
                statsEntries.textContent = data.total_entries || 0;
                const entityBadge = document.getElementById('classEntityBadge');
                if (data.classe && data.classe.entity) {
                    entityBadge.textContent = data.classe.entity;
                    entityBadge.classList.remove('hidden');
                } else {
                    entityBadge.classList.add('hidden');
                }
                statsBanner.classList.remove('hidden');
            })
            .catch(err => {
                loading.classList.add('hidden');
                console.error('Erreur chargement données classe:', err);
            });
    }

    // Soumission avec option Aperçu ou Téléchargement
    function submitFormWithPreview(isPreview) {
        const form = document.getElementById('cahierExportForm');
        const classSelect = document.getElementById('class_id');

        if (!classSelect.value) {
            alert('Veuillez d\'abord sélectionner une classe.');
            classSelect.focus();
            return;
        }

        if (currentFilterType === 'subject_class') {
            const subjectSelect = document.getElementById('subject_id');
            if (!subjectSelect.value) {
                alert('Veuillez sélectionner une matière.');
                subjectSelect.focus();
                return;
            }
        }

        if (currentFilterType === 'teacher_class') {
            const teacherSelect = document.getElementById('teacher_id');
            if (!teacherSelect.value) {
                alert('Veuillez sélectionner un enseignant.');
                teacherSelect.focus();
                return;
            }
        }

        // Ajoute ou modifie le champ preview
        let previewInput = form.querySelector('input[name="preview"]');
        if (!previewInput) {
            previewInput = document.createElement('input');
            previewInput.type = 'hidden';
            previewInput.name = 'preview';
            form.appendChild(previewInput);
        }
        previewInput.value = isPreview ? '1' : '0';

        form.submit();
    }

    // Initialisation au chargement si une classe est déjà sélectionnée
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        if (classSelect && classSelect.value) {
            onClassChange(classSelect.value);
        }
    });
</script>

@endsection

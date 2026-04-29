@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Notes";
    use Illuminate\Support\Facades\Auth;

    $uploadResult  = session('upload_result');
    $uploadClassId = session('upload_classId');
    $uploadSubjId  = session('upload_subjId');
    $uploadTrim    = session('upload_trim');

    // Détermine si le résultat appartient à cette page
    $showModal = $uploadResult
        && $uploadClassId == $classe->id
        && $uploadSubjId  == $subject->id
        && $uploadTrim    == $trimestre;
@endphp

@auth
    @if (Auth::id() == 4 || Auth::id() == 6 || Auth::id() == 7)

        {{-- ── Vue censeur simplifiée ── --}}
        <div class="container mx-auto py-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold mb-6">
                    Fiche de Notes @if(isset($subject)) {{ $subject->name }} @endif - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
                </h1>
                <a href="{{ route('teacher.classes.notes.list', [$classe->id, $subject->id, $trimestre]) }}"
                   class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-center">
                    Voir toutes les notes
                </a>
            </div>

            @if(session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @for($i = 1; $i <= 5; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Interrogation {{ $i }}</h2>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'interrogation', $i, $trimestre]) }}"
                               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Lire
                            </a>
                        </div>
                    </div>
                @endfor
                @for($i = 1; $i <= 2; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Devoir {{ $i }}</h2>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'devoir', $i, $trimestre]) }}"
                               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Lire
                            </a>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

    @else

        {{-- ══════════════════════════════════════════════════════════════════
             VUE ENSEIGNANT
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="container mx-auto py-6 px-4">

            {{-- En-tête --}}
            <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Fiche de Notes
                    @if(isset($subject)) <span class="text-indigo-700">{{ $subject->name }}</span> @endif
                    — Classe <span class="text-indigo-700">{{ $classe->name }}</span> / Trimestre {{ $trimestre }}
                </h1>
                <a href="{{ route('teacher.classes.notes.list', [$classe->id, $subject->id, $trimestre]) }}"
                   class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm font-medium">
                    Voir toutes les notes
                </a>
            </div>

            {{-- Flash messages --}}
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-lg">
                    <div class="flex items-center mb-1">
                        <svg class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-red-800 font-semibold">Erreurs :</h3>
                    </div>
                    <ul class="mt-1 text-red-700 list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-5 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-red-800">{{ session('error') }}</span>
                </div>
            @endif
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-5 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-green-800">{{ session('success') }}</span>
                </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════
                 SECTION TÉLÉVERSEMENT PDF — bouton ouvre un modal
            ════════════════════════════════════════════════════════════ --}}
            <div class="bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-200 rounded-2xl p-6 mb-8 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-sky-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                        <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-sky-900">Téléverser une fiche de notes (PDF)</h2>
                        <p class="text-sm text-sky-700">Importez le PDF scanné ou numérique de votre fiche pour saisie automatique des notes.</p>
                    </div>
                </div>

                {{-- Bouton qui ouvre le modal d'upload (reste sur la page) --}}
                <button type="button"
                        onclick="openUploadModal()"
                        class="inline-flex items-center px-5 py-2.5 bg-sky-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-sky-700 active:scale-95 transition focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Téléverser Fiche
                </button>

                {{-- Info structure PDF attendue --}}
                <div class="mt-4 bg-white bg-opacity-70 rounded-xl p-4 border border-sky-100">
                    <p class="text-xs font-semibold text-sky-800 mb-2">Structure PDF attendue :</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['N° Matricule','Nom','Prénoms','Sexe','I1','I2','I3','I4','I5','D1','D2'] as $col)
                            <span class="inline-block px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-xs font-mono">{{ $col }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 GRILLE ÉVALUATIONS (saisie manuelle)
            ════════════════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @for($i = 1; $i <= 5; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Interrogation {{ $i }}</h2>
                        <div class="flex flex-col space-y-2">
                            <button type="button"
                                onclick="openSecurityModal('{{ route('teacher.classes.notes.create', [$classe->id, $subject->id, 'interrogation', $i, $trimestre]) }}')"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-center font-medium transition w-full">
                                Ajouter
                            </button>
                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'interrogation', $i, $trimestre]) }}"
                               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Lire
                            </a>
                        </div>
                    </div>
                @endfor

                @for($i = 1; $i <= 2; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Devoir {{ $i }}</h2>
                        <div class="flex flex-col space-y-2">
                            <button type="button"
                                onclick="openSecurityModal('{{ route('teacher.classes.notes.create', [$classe->id, $subject->id, 'devoir', $i, $trimestre]) }}')"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-center font-medium transition w-full">
                                Ajouter
                            </button>
                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'devoir', $i, $trimestre]) }}"
                               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Lire
                            </a>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════════
             MODAL TÉLÉVERSEMENT PDF (upload + analyse + résultat)
        ════════════════════════════════════════════════════════════════════ --}}
        <div id="uploadModalOverlay"
             style="display:none; position:fixed; inset:0; z-index:9000;
                    background:rgba(15,23,42,0.55); backdrop-filter:blur(6px);
                    -webkit-backdrop-filter:blur(6px);
                    align-items:flex-start; justify-content:center;
                    padding: clamp(0.5rem, 3vw, 1.5rem);
                    overflow-y:auto; -webkit-overflow-scrolling:touch;">

            <div id="uploadModalBox"
                 style="background:#fff; border-radius:16px; width:100%; max-width:900px;
                        box-shadow:0 24px 80px rgba(0,0,0,0.22);
                        overflow:hidden; opacity:0; transform:scale(0.94) translateY(12px);
                        margin:auto; position:relative;
                        max-height:calc(100dvh - clamp(1rem, 6vw, 3rem));
                        display:flex; flex-direction:column;">

                {{-- Barre colorée en haut --}}
                <div style="height:5px; background:linear-gradient(90deg,#0ea5e9,#3b82f6,#6366f1,#3b82f6,#0ea5e9);
                            background-size:200% auto; animation:barFlow 3s linear infinite;"></div>

                {{-- En-tête modal --}}
                <div style="display:flex; align-items:center; justify-content:space-between; gap:.75rem;
                            padding:1rem clamp(.75rem,4vw,1.5rem); border-bottom:1px solid #e2e8f0;
                            flex-shrink:0; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:.75rem; min-width:0; flex:1;">
                        <div style="width:36px;height:36px;min-width:36px;background:#e0f2fe;border-radius:50%;
                                    display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="#0284c7" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div style="min-width:0;">
                            <h2 style="font-size:clamp(.9rem,2.5vw,1.05rem);font-weight:700;color:#0f172a;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                Téléverser une fiche de notes (PDF)
                            </h2>
                            <p style="font-size:.75rem;color:#64748b;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                Matière : <strong>{{ $subject->name }}</strong> — Classe : <strong>{{ $classe->name }}</strong> — Trim. {{ $trimestre }}
                            </p>
                        </div>
                    </div>
                    <button onclick="closeUploadModal()"
                            style="width:34px;height:34px;min-width:34px;border:none;background:#f1f5f9;border-radius:50%;
                                   cursor:pointer;display:flex;align-items:center;justify-content:center;
                                   color:#64748b;font-size:1.1rem;line-height:1;transition:background .2s;"
                            onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                        ✕
                    </button>
                </div>

                {{-- Corps du modal --}}
                <div style="padding:clamp(.75rem,4vw,1.5rem); overflow-y:auto; flex:1; -webkit-overflow-scrolling:touch;">

                    {{-- Zone de sélection de fichier --}}
                    <form method="POST"
                          action="{{ route('teacher.classes.notes.upload-pdf', [$classe->id, $subject->id, $trimestre]) }}"
                          enctype="multipart/form-data"
                          id="uploadForm">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end mb-4">
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-sky-800 mb-1">
                                    Fichier PDF <span class="text-red-500">*</span>
                                </label>
                                <input type="file"
                                       name="pdf_fiche"
                                       id="pdfFicheInput"
                                       accept=".pdf,application/pdf"
                                       required
                                       class="block w-full text-sm text-gray-700
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-lg file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-sky-600 file:text-white
                                              hover:file:bg-sky-700
                                              border border-sky-300 rounded-lg
                                              bg-white px-3 py-2 cursor-pointer
                                              focus:outline-none focus:ring-2 focus:ring-sky-400">
                                <p class="text-xs text-sky-600 mt-1">Format PDF uniquement · Max 10 Mo · Texte sélectionnable requis</p>
                            </div>
                            <button type="button"
                                    onclick="submitUpload()"
                                    id="uploadBtn"
                                    class="inline-flex items-center px-5 py-2.5 bg-sky-600 text-white text-sm font-semibold
                                           rounded-lg shadow hover:bg-sky-700 transition focus:outline-none focus:ring-2
                                           focus:ring-sky-400 whitespace-nowrap active:scale-95">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span id="uploadBtnText">Analyser le PDF</span>
                            </button>
                        </div>
                    </form>

                    {{-- ── Résultat de l'analyse (affiché si session présente) ── --}}
                    @if($showModal)
                        @php
                            $result   = $uploadResult;
                            $conform  = $result['conformite'];
                            $globalOk = $result['global_ok'];
                            $rows     = $result['rows'];

                            // Construire le JSON pour la sauvegarde
                            $notesForSave = [];
                            foreach ($rows as $row) {
                                for ($i = 1; $i <= 5; $i++) {
                                    $cell = $row['interros'][$i];
                                    if ($cell['value'] !== null && $cell['status'] === 'ok') {
                                        $notesForSave[] = [
                                            'student_id' => $row['student']->id,
                                            'type'       => 'interrogation',
                                            'sequence'   => $i,
                                            'value'      => $cell['value'],
                                        ];
                                    }
                                }
                                for ($i = 1; $i <= 2; $i++) {
                                    $cell = $row['devoirs'][$i];
                                    if ($cell['value'] !== null && $cell['status'] === 'ok') {
                                        $notesForSave[] = [
                                            'student_id' => $row['student']->id,
                                            'type'       => 'devoir',
                                            'sequence'   => $i,
                                            'value'      => $cell['value'],
                                        ];
                                    }
                                }
                            }

                            // Compteurs erreurs
                            $conflictCount   = 0;
                            $noPdfDataCount  = 0;
                            $nameMismatch    = 0;
                            foreach ($rows as $row) {
                                if (!$row['found_pdf']) { $noPdfDataCount++; continue; }
                                if (!$row['name_match']) $nameMismatch++;
                                foreach (array_merge($row['interros'], $row['devoirs']) as $cell) {
                                    if ($cell['status'] === 'conflict') $conflictCount++;
                                }
                            }
                            $hasErrors = $conflictCount > 0 || $noPdfDataCount > 0 || $nameMismatch > 0 || !$conform['is_conforming'];
                        @endphp

                        <div id="analysisResult" style="border-top:1px solid #e2e8f0; padding-top:1.25rem; margin-top:.25rem;">

                            {{-- En-tête résultat --}}
                            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Résultat de l'analyse
                                </h3>
                                @if($globalOk)
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full border border-green-300">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Tout est valide
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-full border border-red-300">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Des problèmes détectés
                                    </span>
                                @endif
                            </div>

                            {{-- Conformité liste --}}
                            <div class="bg-white rounded-xl border p-4 mb-4 shadow-sm">
                                <h4 class="font-semibold text-gray-800 mb-3 text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                    </svg>
                                    Conformité de la liste des élèves
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                                        <div class="text-xl font-bold text-blue-700">{{ $conform['total_bdd'] }}</div>
                                        <div class="text-xs text-blue-600 mt-0.5">Élèves en base</div>
                                    </div>
                                    <div class="bg-purple-50 rounded-lg p-3 text-center">
                                        <div class="text-xl font-bold text-purple-700">{{ $conform['total_pdf'] }}</div>
                                        <div class="text-xs text-purple-600 mt-0.5">Élèves dans le PDF</div>
                                    </div>
                                    <div class="{{ empty($conform['missing_in_pdf']) ? 'bg-green-50' : 'bg-red-50' }} rounded-lg p-3 text-center">
                                        <div class="text-xl font-bold {{ empty($conform['missing_in_pdf']) ? 'text-green-700' : 'text-red-700' }}">
                                            {{ count($conform['missing_in_pdf']) }}
                                        </div>
                                        <div class="text-xs {{ empty($conform['missing_in_pdf']) ? 'text-green-600' : 'text-red-600' }} mt-0.5">Absents dans PDF</div>
                                    </div>
                                    <div class="{{ empty($conform['unknown_in_db']) ? 'bg-green-50' : 'bg-orange-50' }} rounded-lg p-3 text-center">
                                        <div class="text-xl font-bold {{ empty($conform['unknown_in_db']) ? 'text-green-700' : 'text-orange-700' }}">
                                            {{ count($conform['unknown_in_db']) }}
                                        </div>
                                        <div class="text-xs {{ empty($conform['unknown_in_db']) ? 'text-green-600' : 'text-orange-600' }} mt-0.5">Inconnus en base</div>
                                    </div>
                                </div>

                                @if(!empty($conform['missing_in_pdf']))
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-2">
                                        <p class="text-xs font-semibold text-red-700 mb-1">
                                            Matricules en base absents du PDF (num_educ) :
                                        </p>
                                        <p class="text-xs text-red-600 font-mono break-all">{{ implode(', ', $conform['missing_in_pdf']) }}</p>
                                    </div>
                                @endif
                                @if(!empty($conform['unknown_in_db']))
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-2">
                                        <p class="text-xs font-semibold text-orange-700 mb-1">
                                            Matricules dans le PDF non trouvés dans cette classe/année :
                                        </p>
                                        <p class="text-xs text-orange-600 font-mono break-all">{{ implode(', ', $conform['unknown_in_db']) }}</p>
                                    </div>
                                @endif
                                @if($conform['is_conforming'])
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-xs text-green-700 font-medium">Liste conforme — tous les élèves correspondent à cette classe et cette année académique.</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Légende --}}
                            <div class="flex flex-wrap gap-3 mb-3 text-xs">
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-4 h-4 rounded bg-green-100 border border-green-400"></span>Note valide
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-4 h-4 rounded bg-gray-100 border border-gray-300"></span>Vide
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-4 h-4 rounded bg-red-200 border border-red-500"></span>Conflit (note existante)
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-4 h-4 rounded bg-yellow-100 border border-yellow-400"></span>Élève absent du PDF
                                </span>
                            </div>

                            {{-- Tableau des notes --}}
                            <div class="overflow-x-auto rounded-xl border shadow-sm">
                                <table class="min-w-full text-xs" style="border-collapse:collapse;">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th rowspan="2" class="px-3 py-2 border text-left font-semibold text-gray-700 whitespace-nowrap">Matricule</th>
                                            <th rowspan="2" class="px-3 py-2 border text-left font-semibold text-gray-700 whitespace-nowrap">Nom (base)</th>
                                            <th rowspan="2" class="px-3 py-2 border text-left font-semibold text-gray-700 whitespace-nowrap">Nom (PDF)</th>
                                            <th rowspan="2" class="px-3 py-2 border text-left font-semibold text-gray-700">Prénoms</th>
                                            <th colspan="5" class="px-2 py-1 border text-center font-semibold text-blue-700 bg-blue-50">Interrogations</th>
                                            <th colspan="2" class="px-2 py-1 border text-center font-semibold text-green-700 bg-green-50">Devoirs</th>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            @for($i=1;$i<=5;$i++)
                                                <th class="px-2 py-1 border text-center font-semibold text-blue-600 bg-blue-50">I{{ $i }}</th>
                                            @endfor
                                            @for($i=1;$i<=2;$i++)
                                                <th class="px-2 py-1 border text-center font-semibold text-green-600 bg-green-50">D{{ $i }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rows as $rowIdx => $row)
                                            @php
                                                $rowBg  = ! $row['found_pdf'] ? 'bg-yellow-50' : ($rowIdx % 2 === 0 ? 'bg-white' : 'bg-gray-50');
                                                $nameBg = (! $row['name_match'] && $row['found_pdf']) ? 'bg-red-50' : '';
                                            @endphp
                                            <tr class="{{ $rowBg }} hover:bg-blue-50 transition">

                                                {{-- Matricule (num_educ) --}}
                                                <td class="px-3 py-2 border font-mono text-gray-700 whitespace-nowrap text-xs">
                                                    {{ $row['student']->num_educ ?? '—' }}
                                                </td>

                                                {{-- Nom BDD --}}
                                                <td class="px-3 py-2 border font-semibold text-gray-800 whitespace-nowrap {{ $nameBg }}">
                                                    {{ strtoupper($row['student']->last_name) }}
                                                </td>

                                                {{-- Nom PDF --}}
                                                <td class="px-3 py-2 border whitespace-nowrap {{ !$row['name_match'] && $row['found_pdf'] ? 'text-red-700 font-bold bg-red-50' : 'text-gray-600' }}">
                                                    @if(!$row['found_pdf'])
                                                        <span class="text-yellow-600 font-semibold">Non trouvé</span>
                                                    @elseif($row['pdf_nom'])
                                                        {{ strtoupper($row['pdf_nom']) }}
                                                        @if(!$row['name_match'])
                                                            <span class="ml-1 text-xs text-red-600">⚠ Divergence</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>

                                                {{-- Prénoms PDF --}}
                                                <td class="px-3 py-2 border text-gray-600">
                                                    {{ $row['pdf_prenom'] ?? ucfirst($row['student']->first_name) }}
                                                </td>

                                                {{-- Interrogations --}}
                                                @for($i = 1; $i <= 5; $i++)
                                                    @php $cell = $row['interros'][$i]; @endphp
                                                    <td class="px-2 py-2 border text-center font-semibold {{ 
                                                        $cell['status'] === 'ok'          ? 'bg-green-50 text-green-800 border-green-300' :
                                                        ($cell['status'] === 'conflict'   ? 'bg-red-100 text-red-800 border-red-400 ring-2 ring-red-400 ring-inset' :
                                                        ($cell['status'] === 'no_pdf_data'? 'bg-yellow-50 text-yellow-600 border-yellow-300' :
                                                        'bg-gray-50 text-gray-400 border-gray-200')) }}">
                                                        @if($cell['status'] === 'conflict')
                                                            <span title="Note existante : {{ $cell['existing'] }}">
                                                                {{ $cell['value'] !== null ? number_format($cell['value'], 2, ',', '') : '—' }}
                                                                <br><span class="text-xs font-normal text-red-600">Existe: {{ $cell['existing'] }}</span>
                                                            </span>
                                                        @elseif($cell['status'] === 'ok')
                                                            {{ number_format($cell['value'], 2, ',', '') }}
                                                        @elseif($cell['status'] === 'no_pdf_data')
                                                            <span class="text-yellow-500">—</span>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                @endfor

                                                {{-- Devoirs --}}
                                                @for($i = 1; $i <= 2; $i++)
                                                    @php $cell = $row['devoirs'][$i]; @endphp
                                                    <td class="px-2 py-2 border text-center font-semibold {{ 
                                                        $cell['status'] === 'ok'          ? 'bg-green-50 text-green-800 border-green-300' :
                                                        ($cell['status'] === 'conflict'   ? 'bg-red-100 text-red-800 border-red-400 ring-2 ring-red-400 ring-inset' :
                                                        ($cell['status'] === 'no_pdf_data'? 'bg-yellow-50 text-yellow-600 border-yellow-300' :
                                                        'bg-gray-50 text-gray-400 border-gray-200')) }}">
                                                        @if($cell['status'] === 'conflict')
                                                            <span title="Note existante : {{ $cell['existing'] }}">
                                                                {{ $cell['value'] !== null ? number_format($cell['value'], 2, ',', '') : '—' }}
                                                                <br><span class="text-xs font-normal text-red-600">Existe: {{ $cell['existing'] }}</span>
                                                            </span>
                                                        @elseif($cell['status'] === 'ok')
                                                            {{ number_format($cell['value'], 2, ',', '') }}
                                                        @elseif($cell['status'] === 'no_pdf_data')
                                                            <span class="text-yellow-500">—</span>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Récapitulatif des erreurs --}}
                            @if($hasErrors)
                                <div class="mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
                                    <h4 class="font-bold text-red-800 mb-2 flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Récapitulatif des erreurs
                                    </h4>
                                    <ul class="space-y-1 text-xs text-red-700 list-disc list-inside">
                                        @if($conflictCount > 0)
                                            <li><strong>{{ $conflictCount }}</strong> case(s) en conflit — une note existe déjà en base.</li>
                                        @endif
                                        @if($noPdfDataCount > 0)
                                            <li><strong>{{ $noPdfDataCount }}</strong> élève(s) de la classe non trouvé(s) dans le PDF.</li>
                                        @endif
                                        @if($nameMismatch > 0)
                                            <li><strong>{{ $nameMismatch }}</strong> nom(s) divergent entre la base et le PDF.</li>
                                        @endif
                                        @if(!$conform['is_conforming'])
                                            <li>La liste du PDF ne correspond pas à la liste officielle de la classe.</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            {{-- Boutons d'action --}}
                            <div class="mt-5 flex flex-wrap gap-3 items-center">
                                {{-- Nouveau fichier --}}
                                <button type="button"
                                        onclick="resetUpload()"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Nouveau fichier
                                </button>

                                {{-- Sauvegarder (uniquement si tout est OK) --}}
                                @if($globalOk && count($notesForSave) > 0)
                                    <form method="POST"
                                          action="{{ route('teacher.classes.notes.save-pdf-notes', [$classe->id, $subject->id, $trimestre]) }}"
                                          id="saveForm">
                                        @csrf
                                        <input type="hidden" name="notes_json" value="{{ json_encode($notesForSave) }}">
                                        <button type="button"
                                                onclick="confirmSave()"
                                                class="inline-flex items-center px-6 py-2.5 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 transition shadow-md active:scale-95">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                            </svg>
                                            Sauvegarder et télécharger le PDF
                                        </button>
                                    </form>
                                @elseif($globalOk && count($notesForSave) === 0)
                                    <span class="text-sm text-gray-500 italic">Aucune note à sauvegarder (toutes les cases sont vides).</span>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Sauvegarder (corrigez les erreurs d'abord)
                                    </span>
                                @endif
                            </div>

                        </div>{{-- /analysisResult --}}
                    @endif

                </div>{{-- /corps modal --}}

            </div>{{-- /uploadModalBox --}}
        </div>{{-- /uploadModalOverlay --}}

        {{-- ════════════════════════════════════════════════════════════════════
             MODAL CONFIRMATION SAUVEGARDE
        ════════════════════════════════════════════════════════════════════ --}}
        <div id="saveConfirmOverlay"
             style="display:none; position:fixed; inset:0; z-index:10000;
                    background:rgba(0,0,0,0.5); backdrop-filter:blur(4px);
                    align-items:center; justify-content:center;
                    padding:clamp(.75rem,4vw,1.5rem); overflow-y:auto;">
            <div id="saveConfirmBox"
                 style="background:#fff; border-radius:16px;
                        padding:clamp(1.25rem,5vw,2rem); max-width:420px; width:100%;
                        box-shadow:0 20px 60px rgba(0,0,0,0.2); margin:auto;">
                <div style="text-align:center; margin-bottom:1.5rem;">
                    <div style="width:64px;height:64px;background:#f0fdf4;border:2px solid #86efac;border-radius:50%;
                                display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <svg width="30" height="30" fill="none" stroke="#16a34a" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                    </div>
                    <h3 style="font-size:1.1rem;font-weight:700;color:#14532d;margin-bottom:.5rem;">Confirmer la sauvegarde</h3>
                    <p style="font-size:.875rem;color:#374151;line-height:1.6;">
                        Vous êtes sur le point de <strong>sauvegarder définitivement</strong> les notes lues dans le PDF.
                        Cette action <strong>ne peut pas être annulée</strong>.
                    </p>
                    <p style="font-size:.8rem;color:#6b7280;margin-top:.6rem;">
                        Toutes les actions sont tracées et enregistrées sous votre identité.
                    </p>
                </div>
                <div style="display:flex;gap:.75rem;">
                    <button onclick="closeSaveConfirm()"
                            style="flex:1;padding:.75rem;background:#f3f4f6;border:1px solid #d1d5db;
                                   border-radius:10px;font-weight:600;color:#374151;cursor:pointer;font-size:.875rem;">
                        Annuler
                    </button>
                    <button onclick="doSave()"
                            style="flex:1;padding:.75rem;background:linear-gradient(135deg,#15803d,#16a34a);
                                   border:none;border-radius:10px;font-weight:700;color:#fff;cursor:pointer;
                                   font-size:.875rem;box-shadow:0 4px 14px rgba(22,163,74,.35);">
                        ✓ Oui, sauvegarder
                    </button>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════════
             MODAL DE SÉCURITÉ — navigation (blanc/bleu)
        ════════════════════════════════════════════════════════════════════ --}}
        <style>
            #smOverlay {
                display:none; position:fixed; inset:0; z-index:9999;
                background:rgba(30,64,120,0.45); backdrop-filter:blur(8px);
                -webkit-backdrop-filter:blur(8px);
                align-items:center; justify-content:center;
                padding:clamp(.5rem,3vw,1rem); overflow-y:auto;
            }
            #smBox {
                position:relative; width:100%; max-width:480px; background:#ffffff;
                border-radius:20px;
                box-shadow:0 0 0 1px rgba(59,130,246,.12),0 8px 16px rgba(59,130,246,.08),0 32px 64px rgba(30,64,120,.18);
                overflow:hidden; opacity:0; transform:scale(0.93) translateY(10px);
                max-height:calc(100dvh - 2rem); display:flex; flex-direction:column;
            }
            .sm-top-bar {
                height:5px;
                background:linear-gradient(90deg,#2563eb,#3b82f6,#60a5fa,#3b82f6,#2563eb);
                background-size:200% auto; animation:smBarFlow 3s linear infinite; flex-shrink:0;
            }
            @keyframes smBarFlow { 0%{background-position:0% center}100%{background-position:200% center} }
            .sm-body { padding:clamp(1.25rem,5vw,2rem) clamp(1rem,5vw,2rem) clamp(1.25rem,4vw,1.75rem); overflow-y:auto; flex:1; -webkit-overflow-scrolling:touch; }
            .sm-icon-wrap {
                width:72px;height:72px;
                background:linear-gradient(135deg,#eff6ff,#dbeafe);
                border:2px solid #93c5fd; border-radius:50%;
                display:flex;align-items:center;justify-content:center;
                margin:0 auto 1.25rem;
                box-shadow:0 0 0 6px #eff6ff;
                animation:smIconPulse 2.8s ease-in-out infinite;
            }
            @keyframes smIconPulse { 0%,100%{box-shadow:0 0 0 6px #eff6ff}50%{box-shadow:0 0 0 10px #dbeafe} }
            .sm-title { font-size:clamp(1rem,3vw,1.2rem);font-weight:700;color:#1e40af;text-align:center;margin:0 0 .25rem;letter-spacing:-.01em; }
            .sm-divider { width:40px;height:3px;background:linear-gradient(90deg,#3b82f6,#60a5fa);border-radius:2px;margin:0 auto 1.1rem; }
            .sm-intro { background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:.9rem 1.1rem;
                        margin-bottom:1.1rem;font-size:.875rem;color:#1e3a8a;line-height:1.7;text-align:center; }
            .sm-intro strong { color:#1d4ed8; }
            .sm-measures { margin-bottom:1.2rem; }
            .sm-row { display:flex;align-items:flex-start;gap:.75rem;padding:.65rem 0;border-bottom:1px solid #f0f4ff; }
            .sm-row:last-child { border-bottom:none; }
            .sm-row-icon { min-width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px; }
            .sm-row-icon.blue   { background:#dbeafe;border:1.5px solid #93c5fd; }
            .sm-row-icon.sky    { background:#e0f2fe;border:1.5px solid #7dd3fc; }
            .sm-row-icon.indigo { background:#e0e7ff;border:1.5px solid #a5b4fc; }
            .sm-row p { font-size:.845rem;color:#374151;line-height:1.65;margin:0; }
            .sm-row p strong { color:#1d4ed8;font-weight:600; }
            .sm-footnote { font-size:.76rem;color:#9ca3af;text-align:center;font-style:italic;line-height:1.6;margin-bottom:1.4rem; }
            .sm-btn-confirm {
                width:100%;padding:.85rem 1.5rem;
                background:linear-gradient(135deg,#1d4ed8,#2563eb,#3b82f6);
                background-size:200% auto;color:#ffffff;font-weight:700;font-size:.92rem;
                letter-spacing:.04em;border:none;border-radius:12px;cursor:pointer;
                box-shadow:0 4px 18px rgba(37,99,235,.35);
                transition:background-position .4s ease,box-shadow .3s ease,transform .15s ease;
                margin-bottom:.6rem;
            }
            .sm-btn-confirm:hover { background-position:right center;box-shadow:0 6px 28px rgba(37,99,235,.5); }
            .sm-btn-confirm:active { transform:scale(.97); }
            .sm-btn-cancel {
                background:none;border:1px solid #dbeafe;color:#3b82f6;
                font-size:.845rem;font-weight:500;width:100%;padding:.7rem;
                border-radius:10px;cursor:pointer;transition:background .2s,color .2s,border-color .2s;
            }
            .sm-btn-cancel:hover { background:#eff6ff;border-color:#93c5fd;color:#1d4ed8; }
            .sm-bottom-bar { height:3px;background:linear-gradient(90deg,#dbeafe,#93c5fd,#dbeafe);flex-shrink:0; }
            @keyframes smIn  { from{opacity:0;transform:scale(.91) translateY(14px)} to{opacity:1;transform:scale(1) translateY(0)} }
            @keyframes smOut { from{opacity:1;transform:scale(1) translateY(0)} to{opacity:0;transform:scale(.91) translateY(14px)} }
            @keyframes modalIn  { from{opacity:0;transform:scale(.94) translateY(12px)} to{opacity:1;transform:scale(1) translateY(0)} }
            @keyframes modalOut { from{opacity:1;transform:scale(1) translateY(0)} to{opacity:0;transform:scale(.94) translateY(12px)} }
            @keyframes barFlow  { 0%{background-position:0% center}100%{background-position:200% center} }
        </style>

        {{-- MODAL SÉCURITÉ --}}
        <div id="smOverlay">
            <div id="smBox">
                <div class="sm-top-bar"></div>
                <div class="sm-body">
                    <div class="sm-icon-wrap">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none"
                             stroke="#2563eb" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M9 12l2 2 4-4" stroke-width="2"/>
                        </svg>
                    </div>
                    <h2 class="sm-title">Zone Sécurisée — Saisie de Notes</h2>
                    <div class="sm-divider"></div>
                    <div class="sm-intro">
                        Vous accédez à une fonctionnalité <strong>hautement confidentielle</strong>.
                        Veuillez lire attentivement les mesures de sécurité ci-dessous.
                    </div>
                    <div class="sm-measures">
                        <div class="sm-row">
                            <div class="sm-row-icon blue">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <p>Toutes vos actions sont <strong>enregistrées en votre nom</strong> et horodatées avec précision.</p>
                        </div>
                        <div class="sm-row">
                            <div class="sm-row-icon sky">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                            </div>
                            <p>L'<strong>identifiant de votre appareil</strong> est collecté à des fins de traçabilité et d'audit.</p>
                        </div>
                        <div class="sm-row">
                            <div class="sm-row-icon indigo">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                </svg>
                            </div>
                            <p>Votre <strong>localisation géographique</strong> est enregistrée pour garantir la sécurité du système.</p>
                        </div>
                    </div>
                    <p class="sm-footnote">Ces mesures s'appliquent conformément à la politique de sécurité et de confidentialité de l'établissement.</p>
                    <button class="sm-btn-confirm" onclick="smConfirmNavigate()">✓ &nbsp; D'accord, j'ai compris</button>
                    <button class="sm-btn-cancel"  onclick="smClose()">Annuler</button>
                </div>
                <div class="sm-bottom-bar"></div>
            </div>
        </div>

        <script>
        // ── Modal sécurité (navigation) ────────────────────────────────────────
        let _smUrl = null;
        function openSecurityModal(url) {
            _smUrl = url;
            const overlay = document.getElementById('smOverlay');
            const box     = document.getElementById('smBox');
            overlay.style.display = 'flex';
            box.style.animation = 'none';
            void box.offsetHeight;
            box.style.animation = 'smIn 0.36s cubic-bezier(0.34,1.56,0.64,1) forwards';
            document.body.style.overflow = 'hidden';
        }
        function smClose() {
            const overlay = document.getElementById('smOverlay');
            const box     = document.getElementById('smBox');
            box.style.animation = 'smOut 0.24s ease forwards';
            setTimeout(() => { overlay.style.display = 'none'; document.body.style.overflow = ''; _smUrl = null; }, 240);
        }
        function smConfirmNavigate() { if (_smUrl) window.location.href = _smUrl; }

        // ── Modal upload PDF ───────────────────────────────────────────────────
        function openUploadModal() {
            const overlay = document.getElementById('uploadModalOverlay');
            const box     = document.getElementById('uploadModalBox');
            overlay.style.display = 'flex';
            box.style.animation = 'none';
            void box.offsetHeight;
            box.style.animation = 'modalIn 0.34s cubic-bezier(0.34,1.56,0.64,1) forwards';
            document.body.style.overflow = 'hidden';
        }
        function closeUploadModal() {
            const overlay = document.getElementById('uploadModalOverlay');
            const box     = document.getElementById('uploadModalBox');
            box.style.animation = 'modalOut 0.22s ease forwards';
            setTimeout(() => { overlay.style.display = 'none'; document.body.style.overflow = ''; }, 220);
        }

        // Fermer le modal upload en cliquant sur l'overlay
        document.getElementById('uploadModalOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });

        // ── Soumettre le formulaire d'upload ──────────────────────────────────
        function submitUpload() {
            const input = document.getElementById('pdfFicheInput');
            if (! input.files || input.files.length === 0) {
                alert('Veuillez sélectionner un fichier PDF.');
                return;
            }
            const file = input.files[0];
            if (file.type !== 'application/pdf' && ! file.name.toLowerCase().endsWith('.pdf')) {
                alert('Le fichier doit être au format PDF.');
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
                alert('Le fichier ne doit pas dépasser 10 Mo.');
                return;
            }
            const btn  = document.getElementById('uploadBtn');
            const text = document.getElementById('uploadBtnText');
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            text.textContent = 'Analyse en cours…';
            document.getElementById('uploadForm').submit();
        }

        // ── Modal confirmation sauvegarde ─────────────────────────────────────
        function confirmSave() {
            document.getElementById('saveConfirmOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeSaveConfirm() {
            document.getElementById('saveConfirmOverlay').style.display = 'none';
            document.body.style.overflow = '';
        }
        function doSave() {
            closeSaveConfirm();
            document.getElementById('saveForm').submit();
        }

        // ── Réinitialiser le formulaire upload ────────────────────────────────
        function resetUpload() {
            document.getElementById('pdfFicheInput').value = '';
            document.getElementById('uploadForm').reset();
            // Recharger la page pour effacer la session
            window.location.reload();
        }

        // ── Fermer modals sur Escape ──────────────────────────────────────────
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                smClose();
                closeSaveConfirm();
                closeUploadModal();
            }
        });

        document.getElementById('saveConfirmOverlay').addEventListener('click', function(e) {
            if (e.target === this) closeSaveConfirm();
        });

        // ── Ouvrir le modal upload automatiquement si résultat en session ─────
        @if($showModal)
            document.addEventListener('DOMContentLoaded', function() {
                openUploadModal();
                // Scroll vers le résultat d'analyse dans le modal
                setTimeout(function() {
                    const result = document.getElementById('analysisResult');
                    if (result) result.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 400);
            });
        @endif
        </script>

    @endif
@endauth

@endsection
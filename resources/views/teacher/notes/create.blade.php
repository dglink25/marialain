@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Ajout Notes";
@endphp

<div class="container mx-auto py-6">

    <h1 class="text-xl font-bold mb-4">
        Saisie des notes {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
    </h1>

    <div class="pt-2 mb-4">
        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-red-800 font-semibold">Veuillez corriger les erreurs suivantes :</h3>
            </div>
            <ul class="mt-2 text-red-700 list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif
        @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-red-800">{{ session('error') }}</span>
        </div>
        @endif
        @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800">{{ session('success') }}</span>
        </div>
        @endif
    </div>

    <form id="createNotesForm"
          action="{{ route('teacher.classes.notes.store', [
              'class'     => $classe->id,
              'subject'   => $subject->id,
              'type'      => $type,
              'num'       => $num,
              'trimestre' => $trimestre
          ]) }}"
          method="POST">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="trimestre"   value="{{ $trimestre }}">

        <table class="w-full border mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">N°</th>
                    <th class="px-3 py-2 border">Nom</th>
                    <th class="px-3 py-2 border">Prénoms</th>
                    <th class="px-3 py-2 border">Sexe</th>
                    <th class="px-3 py-2 border">Note (/20)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classe->students as $student)
                <tr>
                    <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                    <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->gender }}</td>
                    <td class="px-3 py-2 border">
                        <input type="number" step="0.01" min="0" max="20"
                               name="notes[{{ $student->id }}]"
                               class="border p-2 w-24">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex items-center space-x-3">
            <button type="button"
                    onclick="smOpen('createNotesForm', 'submit')"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Enregistrer
            </button>
            <button type="button"
                    onclick="window.history.back()"
                    class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Annuler
            </button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════
     MODAL DE SÉCURITÉ — soumission (blanc/bleu)
══════════════════════════════════════════════ --}}
<style>
    #smOverlay {
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(30, 64, 120, 0.45);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    #smBox {
        position: relative;
        width: 100%; max-width: 480px;
        background: #ffffff;
        border-radius: 24px;
        box-shadow:
            0 0 0 1px rgba(59,130,246,0.12),
            0 8px 16px rgba(59,130,246,0.08),
            0 32px 64px rgba(30,64,120,0.18);
        overflow: hidden;
        opacity: 0;
        transform: scale(0.93) translateY(10px);
    }
    .sm-top-bar {
        height: 5px;
        background: linear-gradient(90deg, #2563eb, #3b82f6, #60a5fa, #3b82f6, #2563eb);
        background-size: 200% auto;
        animation: smBarFlow 3s linear infinite;
    }
    @keyframes smBarFlow {
        0%   { background-position: 0%   center; }
        100% { background-position: 200% center; }
    }
    .sm-body { padding: 2rem 2rem 1.75rem; }
    .sm-icon-wrap {
        width: 72px; height: 72px;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 2px solid #93c5fd;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem;
        box-shadow: 0 0 0 6px #eff6ff;
        animation: smIconPulse 2.8s ease-in-out infinite;
    }
    @keyframes smIconPulse {
        0%,100% { box-shadow: 0 0 0 6px #eff6ff; }
        50%     { box-shadow: 0 0 0 10px #dbeafe; }
    }
    .sm-title {
        font-size: 1.2rem; font-weight: 700;
        color: #1e40af; text-align: center;
        margin: 0 0 0.25rem; letter-spacing: -0.01em;
    }
    .sm-divider {
        width: 40px; height: 3px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        border-radius: 2px; margin: 0 auto 1.1rem;
    }
    .sm-intro {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 12px; padding: 0.9rem 1.1rem;
        margin-bottom: 1.1rem; font-size: 0.875rem;
        color: #1e3a8a; line-height: 1.7; text-align: center;
    }
    .sm-intro strong { color: #1d4ed8; }
    .sm-measures { margin-bottom: 1.2rem; }
    .sm-row {
        display: flex; align-items: flex-start;
        gap: 0.75rem; padding: 0.65rem 0;
        border-bottom: 1px solid #f0f4ff;
    }
    .sm-row:last-child { border-bottom: none; }
    .sm-row-icon {
        min-width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-top: 1px;
    }
    .sm-row-icon.blue   { background: #dbeafe; border: 1.5px solid #93c5fd; }
    .sm-row-icon.sky    { background: #e0f2fe; border: 1.5px solid #7dd3fc; }
    .sm-row-icon.indigo { background: #e0e7ff; border: 1.5px solid #a5b4fc; }
    .sm-row p { font-size: 0.845rem; color: #374151; line-height: 1.65; margin: 0; }
    .sm-row p strong { color: #1d4ed8; font-weight: 600; }
    .sm-footnote {
        font-size: 0.76rem; color: #9ca3af;
        text-align: center; font-style: italic;
        line-height: 1.6; margin-bottom: 1.4rem;
    }
    .sm-btn-confirm {
        width: 100%; padding: 0.85rem 1.5rem;
        background: linear-gradient(135deg, #1d4ed8, #2563eb, #3b82f6);
        background-size: 200% auto;
        color: #ffffff; font-weight: 700; font-size: 0.92rem;
        letter-spacing: 0.04em; border: none;
        border-radius: 12px; cursor: pointer;
        box-shadow: 0 4px 18px rgba(37,99,235,0.35);
        transition: background-position .4s, box-shadow .3s, transform .15s;
        margin-bottom: 0.6rem;
    }
    .sm-btn-confirm:hover  { background-position: right center; box-shadow: 0 6px 28px rgba(37,99,235,0.5); }
    .sm-btn-confirm:active { transform: scale(0.97); }
    .sm-btn-cancel {
        background: none; border: 1px solid #dbeafe;
        color: #3b82f6; font-size: 0.845rem; font-weight: 500;
        width: 100%; padding: 0.7rem; border-radius: 10px;
        cursor: pointer; transition: background .2s, color .2s, border-color .2s;
    }
    .sm-btn-cancel:hover { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }
    .sm-bottom-bar { height: 3px; background: linear-gradient(90deg, #dbeafe, #93c5fd, #dbeafe); }
    @keyframes smIn  { from { opacity:0; transform:scale(0.91) translateY(14px); } to { opacity:1; transform:scale(1) translateY(0); } }
    @keyframes smOut { from { opacity:1; transform:scale(1) translateY(0); } to { opacity:0; transform:scale(0.91) translateY(14px); } }
</style>

<div id="smOverlay">
    <div id="smBox">
        <div class="sm-top-bar"></div>
        <div class="sm-body">
            <div class="sm-icon-wrap">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none"
                     stroke="#2563eb" stroke-width="1.7"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4" stroke-width="2"/>
                </svg>
            </div>
            <h2 class="sm-title" id="smTitle">Zone Sécurisée — Saisie de Notes</h2>
            <div class="sm-divider"></div>
            <div class="sm-intro" id="smIntro">
                Vous êtes sur le point d'<strong>enregistrer des notes</strong>,
                une opération hautement confidentielle. Veuillez lire ces mesures avant de confirmer.
            </div>
            <div class="sm-measures">
                <div class="sm-row">
                    <div class="sm-row-icon blue">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <p>Toutes vos actions sont <strong>enregistrées en votre nom</strong> et horodatées avec précision.</p>
                </div>
                <div class="sm-row">
                    <div class="sm-row-icon sky">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                            <line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                    </div>
                    <p>L'<strong>identifiant de votre appareil</strong> est collecté à des fins de traçabilité et d'audit.</p>
                </div>
                <div class="sm-row">
                    <div class="sm-row-icon indigo">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                             stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                    </div>
                    <p>Votre <strong>localisation géographique</strong> est enregistrée pour garantir la sécurité du système.</p>
                </div>
            </div>
            <p class="sm-footnote">Ces mesures s'appliquent conformément à la politique de sécurité et de confidentialité de l'établissement.</p>
            <button class="sm-btn-confirm" id="smConfirmBtn" onclick="smConfirm()">
                ✓ &nbsp; D'accord, j'ai compris — Enregistrer
            </button>
            <button class="sm-btn-cancel" onclick="smClose()">Annuler</button>
        </div>
        <div class="sm-bottom-bar"></div>
    </div>
</div>

<script>
    let _smFormId = null;

    function smOpen(formId) {
        _smFormId = formId;
        const overlay = document.getElementById('smOverlay');
        const box     = document.getElementById('smBox');
        overlay.style.display = 'flex';
        box.style.animation = 'none';
        void box.offsetHeight;
        box.style.animation = 'smIn 0.36s cubic-bezier(0.34,1.56,0.64,1) forwards';
        document.body.style.overflow = 'hidden';
    }

    function smClose() {
        const box = document.getElementById('smBox');
        box.style.animation = 'smOut 0.24s ease forwards';
        setTimeout(function() {
            document.getElementById('smOverlay').style.display = 'none';
            document.body.style.overflow = '';
            _smFormId = null;
        }, 240);
    }

    function smConfirm() {
        if (_smFormId) document.getElementById(_smFormId).submit();
    }

    document.getElementById('smOverlay').addEventListener('click', function(e) {
        if (e.target === this) smClose();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') smClose();
    });
</script>

@endsection
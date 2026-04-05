@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Edite Notes";
@endphp

<div class="container mx-auto py-6">

    <h1 class="text-2xl font-bold mb-6">
        Modifier Notes {{ $subject->name }} - {{ ucfirst($type) }} {{ $num }} - Classe {{ $classe->name }} / Trimestre {{ $trimestre }}
    </h1>

    <!-- Messages d'alerte -->
    <div class="px-8 pt-6">
        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-red-800 font-semibold">Veuillez corriger les erreurs suivantes :</h3>
            </div>
            <ul class="mt-2 text-red-700 list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-red-800">{{ session('error') }}</span>
        </div>
        @endif

        @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800">{{ session('success') }}</span>
        </div>
        @endif
    </div>

    {{-- Formulaire principal de modification --}}
    <form id="updateNotesForm"
          method="POST"
          action="{{ route('teacher.classes.notes.update', [
              'class'     => $classe->id,
              'subject'   => $subject->id,
              'type'      => $num,
              'num'       => $type,
              'trimestre' => $trimestre,
          ]) }}">
        @csrf
        @method('PUT')

        <table class="w-full border-collapse border mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 border">N°</th>
                    <th class="px-3 py-2 border">Nom</th>
                    <th class="px-3 py-2 border">Prénom</th>
                    <th class="px-3 py-2 border">Sexe</th>
                    <th class="px-3 py-2 border">Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classe->students as $student)
                <tr class="border-b">
                    <td class="px-3 py-2 border">{{ $loop->iteration }}</td>
                    <td class="px-3 py-2 border">{{ $student->last_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->first_name }}</td>
                    <td class="px-3 py-2 border">{{ $student->gender }}</td>
                    <td class="px-3 py-2 border">
                        <input type="number"
                               name="notes[{{ $student->id }}]"
                               step="0.01" min="0" max="20"
                               value="{{ $student->grades->first()->value ?? '' }}"
                               class="w-20 px-2 py-1 border rounded">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex items-center space-x-3 mt-2">

            {{-- Bouton Soumettre — déclenche le modal de sécurité --}}
            <button type="button"
                    onclick="openSubmitSecurityModal('updateNotesForm', 'update')"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Soumettre les modifications
            </button>

            {{-- Bouton Supprimer — déclenche le modal de confirmation sécurisé --}}
            <button type="button"
                    onclick="openSubmitSecurityModal('deleteNotesForm', 'delete')"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Supprimer toutes les notes
            </button>

            <a href="{{ route('teacher.classes.notes.read', [
                    'class'   => $classe->id,
                    'subject' => $subject->id,
                    'type'    => $type,
                    'num'     => $num,
                    'trimestre' => $trimestre,
                ]) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition font-medium">
                Retour
            </a>
        </div>
    </form>

    {{-- Formulaire de suppression (caché) --}}
    <form id="deleteNotesForm"
          action="{{ route('teacher.classes.notes.destroy', [$classe->id, $type, $num, $trimestre]) }}"
          method="POST"
          style="display:none;">
        @csrf
        @method('DELETE')
    </form>

</div>


{{-- ═══════════════════════════════════════════════
     MODAL DE SÉCURITÉ (soumission & suppression)
═══════════════════════════════════════════════ --}}
<div id="securityModal"
     style="display:none; position:fixed; inset:0; z-index:9999;
            background:rgba(15,23,42,0.80);
            backdrop-filter:blur(7px);
            -webkit-backdrop-filter:blur(7px);
            align-items:center; justify-content:center; padding:1rem;">

    <div id="securityModalBox"
         style="
            position:relative;
            width:100%; max-width:500px;
            background:linear-gradient(160deg,#0f172a 0%,#1e293b 55%,#0f172a 100%);
            border:1px solid rgba(251,191,36,0.22);
            border-radius:20px;
            box-shadow:0 0 0 1px rgba(251,191,36,0.07),
                       0 30px 70px rgba(0,0,0,0.65),
                       0 0 90px rgba(251,191,36,0.05);
            overflow:hidden;
            opacity:0;
            transform:scale(0.92);
         ">

        <div style="height:3px;background:linear-gradient(90deg,transparent,#f59e0b,#fbbf24,#f59e0b,transparent);"></div>

        <div style="display:flex;flex-direction:column;align-items:center;padding:2rem 2rem 1.5rem;">

            {{-- Icône — change selon le contexte (bouclier ou danger) --}}
            <div id="modalIconWrapper" style="
                width:76px;height:76px;
                background:linear-gradient(135deg,rgba(251,191,36,0.14),rgba(245,158,11,0.04));
                border:1.5px solid rgba(251,191,36,0.38);
                border-radius:50%;
                display:flex;align-items:center;justify-content:center;
                margin-bottom:1.25rem;
                animation:pulseShield 2.6s ease-in-out infinite;
                box-shadow:0 0 32px rgba(251,191,36,0.14);
                transition:all .3s ease;
            ">
                <svg id="modalIcon" width="36" height="36" viewBox="0 0 24 24" fill="none"
                     stroke="#fbbf24" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4" stroke-width="1.9"/>
                </svg>
            </div>

            {{-- Titre dynamique --}}
            <h2 id="modalTitle" style="
                font-family:Georgia,'Times New Roman',serif;
                font-size:1.3rem;font-weight:700;
                color:#fbbf24;letter-spacing:0.03em;
                text-align:center;margin:0 0 0.3rem;
                text-shadow:0 0 22px rgba(251,191,36,0.28);
            ">Zone Sécurisée — Saisie de Notes</h2>

            <div style="width:44px;height:2px;background:linear-gradient(90deg,transparent,#f59e0b,transparent);margin-bottom:1.2rem;"></div>

            {{-- Message dynamique --}}
            <div style="
                background:rgba(251,191,36,0.05);
                border:1px solid rgba(251,191,36,0.12);
                border-radius:12px;padding:1rem 1.2rem;
                margin-bottom:1.2rem;width:100%;
            ">
                <p id="modalIntro" style="color:#e2e8f0;font-size:0.9rem;line-height:1.75;text-align:center;margin:0;">
                    Vous êtes sur le point de soumettre une opération
                    <span style="color:#fbbf24;font-weight:600;">hautement confidentielle</span>.
                </p>
            </div>

            {{-- Liste des mesures --}}
            <div style="width:100%;margin-bottom:1.3rem;">

                <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.7rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <div style="min-width:30px;height:30px;background:rgba(99,102,241,0.13);border:1px solid rgba(99,102,241,0.32);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-top:1px;flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <p style="color:#cbd5e1;font-size:0.875rem;line-height:1.65;margin:0;">
                        Toutes vos <span style="color:#a5b4fc;font-weight:600;">actions sont enregistrées en votre nom</span> et horodatées avec précision dans notre système.
                    </p>
                </div>

                <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.7rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                    <div style="min-width:30px;height:30px;background:rgba(16,185,129,0.11);border:1px solid rgba(16,185,129,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-top:1px;flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                    </div>
                    <p style="color:#cbd5e1;font-size:0.875rem;line-height:1.65;margin:0;">
                        L'<span style="color:#6ee7b7;font-weight:600;">identifiant unique de votre appareil</span> est collecté à des fins de traçabilité et d'audit.
                    </p>
                </div>

                <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.7rem 0;">
                    <div style="min-width:30px;height:30px;background:rgba(239,68,68,0.11);border:1px solid rgba(239,68,68,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-top:1px;flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                    </div>
                    <p style="color:#cbd5e1;font-size:0.875rem;line-height:1.65;margin:0;">
                        Votre <span style="color:#fca5a5;font-weight:600;">localisation géographique</span> est enregistrée pour garantir la sécurité et la confidentialité du système.
                    </p>
                </div>

            </div>

            <p style="color:#475569;font-size:0.78rem;text-align:center;line-height:1.65;margin-bottom:1.5rem;font-style:italic;">
                Ces mesures sont appliquées conformément à la politique de sécurité et de confidentialité de l'établissement.
            </p>

            {{-- Bouton confirmer (libellé dynamique) --}}
            <button
                id="confirmBtn"
                onclick="confirmSubmit()"
                style="
                    width:100%;padding:0.9rem 1.5rem;
                    background:linear-gradient(135deg,#d97706,#f59e0b,#d97706);
                    background-size:200% auto;
                    color:#0f172a;font-weight:700;font-size:0.95rem;
                    letter-spacing:0.05em;text-transform:uppercase;
                    border:none;border-radius:10px;cursor:pointer;
                    box-shadow:0 4px 22px rgba(245,158,11,0.38);
                    transition:background-position .4s ease, box-shadow .3s ease, transform .15s ease;
                    margin-bottom:0.65rem;
                "
                onmouseover="this.style.backgroundPosition='right center';this.style.boxShadow='0 6px 32px rgba(245,158,11,0.58)';"
                onmouseout="this.style.backgroundPosition='left center';this.style.boxShadow='0 4px 22px rgba(245,158,11,0.38)';"
                onmousedown="this.style.transform='scale(0.97)';"
                onmouseup="this.style.transform='scale(1)';">
                ✓ &nbsp; D'accord, j'ai compris — Confirmer
            </button>

            <button
                onclick="closeSecurityModal()"
                style="background:none;border:none;color:#475569;font-size:0.82rem;cursor:pointer;padding:0.35rem 0.5rem;margin-bottom:0.5rem;text-decoration:underline;text-underline-offset:3px;transition:color .2s;"
                onmouseover="this.style.color='#94a3b8';"
                onmouseout="this.style.color='#475569';">
                Annuler
            </button>

        </div>

        <div style="height:2px;background:linear-gradient(90deg,transparent,rgba(251,191,36,0.35),transparent);"></div>
    </div>
</div>

<style>
    @keyframes pulseShield {
        0%,100% { box-shadow: 0 0 32px rgba(251,191,36,0.14); }
        50%      { box-shadow: 0 0 52px rgba(251,191,36,0.32); }
    }
    @keyframes modalIn {
        from { opacity:0; transform:scale(0.90) translateY(12px); }
        to   { opacity:1; transform:scale(1)    translateY(0);    }
    }
    @keyframes modalOut {
        from { opacity:1; transform:scale(1)    translateY(0);    }
        to   { opacity:0; transform:scale(0.90) translateY(12px); }
    }
</style>

<script>
    let _targetFormId = null;

    function openSubmitSecurityModal(formId, action) {
        _targetFormId = formId;

        const title    = document.getElementById('modalTitle');
        const intro    = document.getElementById('modalIntro');
        const confirmBtn = document.getElementById('confirmBtn');
        const iconWrap = document.getElementById('modalIconWrapper');

        if (action === 'delete') {
            // Thème rouge pour la suppression
            title.textContent = 'Attention — Suppression Définitive';
            title.style.color = '#f87171';
            title.style.textShadow = '0 0 22px rgba(248,113,113,0.28)';
            iconWrap.style.borderColor = 'rgba(239,68,68,0.45)';
            iconWrap.style.background  = 'linear-gradient(135deg,rgba(239,68,68,0.14),rgba(220,38,38,0.04))';
            iconWrap.style.boxShadow   = '0 0 32px rgba(239,68,68,0.2)';
            document.getElementById('modalIcon').setAttribute('stroke', '#f87171');
            intro.innerHTML = 'Vous êtes sur le point de <span style="color:#f87171;font-weight:600;">supprimer définitivement toutes les notes</span> de cette évaluation. Cette action est <span style="color:#f87171;font-weight:600;">irréversible</span>.';
            confirmBtn.style.background = 'linear-gradient(135deg,#b91c1c,#ef4444,#b91c1c)';
            confirmBtn.style.boxShadow  = '0 4px 22px rgba(239,68,68,0.38)';
            confirmBtn.textContent      = '✓  D\'accord, j\'ai compris — Supprimer';
        } else {
            // Thème doré pour la modification
            title.textContent = 'Zone Sécurisée — Saisie de Notes';
            title.style.color = '#fbbf24';
            title.style.textShadow = '0 0 22px rgba(251,191,36,0.28)';
            iconWrap.style.borderColor = 'rgba(251,191,36,0.38)';
            iconWrap.style.background  = 'linear-gradient(135deg,rgba(251,191,36,0.14),rgba(245,158,11,0.04))';
            iconWrap.style.boxShadow   = '0 0 32px rgba(251,191,36,0.14)';
            document.getElementById('modalIcon').setAttribute('stroke', '#fbbf24');
            intro.innerHTML = 'Vous êtes sur le point de <span style="color:#fbbf24;font-weight:600;">soumettre des modifications de notes</span>, une opération hautement confidentielle.';
            confirmBtn.style.background = 'linear-gradient(135deg,#d97706,#f59e0b,#d97706)';
            confirmBtn.style.boxShadow  = '0 4px 22px rgba(245,158,11,0.38)';
            confirmBtn.textContent      = '✓  D\'accord, j\'ai compris — Confirmer';
        }

        const overlay = document.getElementById('securityModal');
        const box     = document.getElementById('securityModalBox');

        overlay.style.display = 'flex';
        box.style.animation = 'none';
        void box.offsetHeight;
        box.style.animation = 'modalIn 0.38s cubic-bezier(0.34,1.56,0.64,1) forwards';
        document.body.style.overflow = 'hidden';
    }

    function closeSecurityModal() {
        const overlay = document.getElementById('securityModal');
        const box     = document.getElementById('securityModalBox');
        box.style.animation = 'modalOut 0.25s ease forwards';
        setTimeout(function() {
            overlay.style.display = 'none';
            document.body.style.overflow = '';
            _targetFormId = null;
        }, 260);
    }

    function confirmSubmit() {
        if (_targetFormId) {
            document.getElementById(_targetFormId).submit();
        }
    }

    document.getElementById('securityModal').addEventListener('click', function(e) {
        if (e.target === this) closeSecurityModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSecurityModal();
    });
</script>

@endsection
@extends('layouts.app')

@section('content')

@php
    $pageTitle = "Notes";
use Illuminate\Support\Facades\Auth;
@endphp

@auth
    @if (Auth::id() == 4 || Auth::id() == 6 || Auth::id() == 7)
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

            {{-- Messages flash --}}
            @if(session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Interrogations --}}
                @for($i = 1; $i <= 5; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">
                            Interrogation {{ $i }}
                        </h2>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('teacher.classes.notes.read', [$classe->id, $subject->id, 'interrogation', $i, $trimestre]) }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-center font-medium transition">
                                Lire
                            </a>
                        </div>
                    </div>
                @endfor

                {{-- Devoirs --}}
                @for($i = 1; $i <= 2; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">
                            Devoir {{ $i }}
                        </h2>
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

            {{-- Messages flash --}}
            @if(session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Interrogations --}}
                @for($i = 1; $i <= 5; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">
                            Interrogation {{ $i }}
                        </h2>
                        <div class="flex flex-col space-y-2">

                            {{-- Bouton Ajouter — déclenche le modal de sécurité --}}
                            <button
                                type="button"
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

                {{-- Devoirs --}}
                @for($i = 1; $i <= 2; $i++)
                    <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">
                            Devoir {{ $i }}
                        </h2>
                        <div class="flex flex-col space-y-2">

                            {{-- Bouton Ajouter — déclenche le modal de sécurité --}}
                            <button
                                type="button"
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


        {{-- ═══════════════════════════════════════════════
             MODAL DE SÉCURITÉ
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

                {{-- Bande dorée haut --}}
                <div style="height:3px;background:linear-gradient(90deg,transparent,#f59e0b,#fbbf24,#f59e0b,transparent);"></div>

                <div style="display:flex;flex-direction:column;align-items:center;padding:2rem 2rem 1.5rem;">

                    {{-- Icône bouclier --}}
                    <div style="
                        width:76px;height:76px;
                        background:linear-gradient(135deg,rgba(251,191,36,0.14),rgba(245,158,11,0.04));
                        border:1.5px solid rgba(251,191,36,0.38);
                        border-radius:50%;
                        display:flex;align-items:center;justify-content:center;
                        margin-bottom:1.25rem;
                        animation:pulseShield 2.6s ease-in-out infinite;
                        box-shadow:0 0 32px rgba(251,191,36,0.14);
                    ">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none"
                             stroke="#fbbf24" stroke-width="1.6"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="M9 12l2 2 4-4" stroke-width="1.9"/>
                        </svg>
                    </div>

                    {{-- Titre --}}
                    <h2 style="
                        font-family:Georgia,'Times New Roman',serif;
                        font-size:1.3rem;font-weight:700;
                        color:#fbbf24;letter-spacing:0.03em;
                        text-align:center;margin:0 0 0.3rem;
                        text-shadow:0 0 22px rgba(251,191,36,0.28);
                    ">Zone Sécurisée — Saisie de Notes</h2>

                    <div style="width:44px;height:2px;background:linear-gradient(90deg,transparent,#f59e0b,transparent);margin-bottom:1.2rem;"></div>

                    {{-- Message intro --}}
                    <div style="
                        background:rgba(251,191,36,0.05);
                        border:1px solid rgba(251,191,36,0.12);
                        border-radius:12px;padding:1rem 1.2rem;
                        margin-bottom:1.2rem;width:100%;
                    ">
                        <p style="color:#e2e8f0;font-size:0.9rem;line-height:1.75;text-align:center;margin:0;">
                            Vous êtes sur le point d'accéder à une fonctionnalité
                            <span style="color:#fbbf24;font-weight:600;">hautement confidentielle</span>.
                            Avant de continuer, veuillez prendre connaissance des mesures de sécurité suivantes.
                        </p>
                    </div>

                    {{-- Liste des mesures --}}
                    <div style="width:100%;margin-bottom:1.3rem;">

                        {{-- Ligne 1 --}}
                        <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.7rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                            <div style="
                                min-width:30px;height:30px;
                                background:rgba(99,102,241,0.13);
                                border:1px solid rgba(99,102,241,0.32);
                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                margin-top:1px;flex-shrink:0;
                            ">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="#818cf8" stroke-width="2.2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <p style="color:#cbd5e1;font-size:0.875rem;line-height:1.65;margin:0;">
                                Toutes vos <span style="color:#a5b4fc;font-weight:600;">actions sont enregistrées en votre nom</span> et horodatées avec précision dans notre système.
                            </p>
                        </div>

                        {{-- Ligne 2 --}}
                        <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.7rem 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                            <div style="
                                min-width:30px;height:30px;
                                background:rgba(16,185,129,0.11);
                                border:1px solid rgba(16,185,129,0.3);
                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                margin-top:1px;flex-shrink:0;
                            ">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="#34d399" stroke-width="2.2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                            </div>
                            <p style="color:#cbd5e1;font-size:0.875rem;line-height:1.65;margin:0;">
                                L'<span style="color:#6ee7b7;font-weight:600;">identifiant unique de votre appareil</span> est collecté à des fins de traçabilité et d'audit.
                            </p>
                        </div>

                        {{-- Ligne 3 --}}
                        <div style="display:flex;align-items:flex-start;gap:0.75rem;padding:0.7rem 0;">
                            <div style="
                                min-width:30px;height:30px;
                                background:rgba(239,68,68,0.11);
                                border:1px solid rgba(239,68,68,0.3);
                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                margin-top:1px;flex-shrink:0;
                            ">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                     stroke="#f87171" stroke-width="2.2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="2" y1="12" x2="22" y2="12"/>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                </svg>
                            </div>
                            <p style="color:#cbd5e1;font-size:0.875rem;line-height:1.65;margin:0;">
                                Votre <span style="color:#fca5a5;font-weight:600;">localisation géographique</span> est enregistrée pour garantir la sécurité et la confidentialité du système.
                            </p>
                        </div>

                    </div>{{-- fin liste --}}

                    {{-- Note de confidentialité --}}
                    <p style="
                        color:#475569;font-size:0.78rem;text-align:center;
                        line-height:1.65;margin-bottom:1.5rem;font-style:italic;
                    ">
                        Ces mesures sont appliquées conformément à la politique de sécurité et de confidentialité de l'établissement.
                    </p>

                    {{-- Bouton de confirmation --}}
                    <button
                        id="confirmSecurityBtn"
                        onclick="confirmAndProceed()"
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
                        ✓ &nbsp; D'accord, j'ai compris
                    </button>

                    {{-- Lien annuler --}}
                    <button
                        onclick="closeSecurityModal()"
                        style="
                            background:none;border:none;
                            color:#475569;font-size:0.82rem;
                            cursor:pointer;padding:0.35rem 0.5rem;
                            margin-bottom:0.5rem;
                            text-decoration:underline;text-underline-offset:3px;
                            transition:color .2s;
                        "
                        onmouseover="this.style.color='#94a3b8';"
                        onmouseout="this.style.color='#475569';">
                        Annuler
                    </button>

                </div>{{-- fin flex principal --}}

                {{-- Bande dorée bas --}}
                <div style="height:2px;background:linear-gradient(90deg,transparent,rgba(251,191,36,0.35),transparent);"></div>

            </div>{{-- fin modal box --}}
        </div>{{-- fin overlay --}}


        {{-- ═══════════════════════════════════════════════
             STYLES & SCRIPT
        ═══════════════════════════════════════════════ --}}
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
            let _securityTarget = null;

            function openSecurityModal(url) {
                _securityTarget = url;
                const overlay = document.getElementById('securityModal');
                const box     = document.getElementById('securityModalBox');

                overlay.style.display = 'flex';

                // Forcer un reflow avant de lancer l'animation
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
                    _securityTarget = null;
                }, 260);
            }

            function confirmAndProceed() {
                if (_securityTarget) {
                    window.location.href = _securityTarget;
                }
            }

            // Fermer en cliquant sur le fond
            document.getElementById('securityModal').addEventListener('click', function(e) {
                if (e.target === this) closeSecurityModal();
            });

            // Fermer avec la touche Échap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSecurityModal();
            });
        </script>

    @endif
@endauth

@endsection
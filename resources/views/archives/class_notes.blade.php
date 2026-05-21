@extends('layouts.app')
@section('content')
@php
    $pageTitle = 'Notes archivées – ' . $class->name . ' (' . $year->name . ')';
    $user = auth()->user();
    $canDownloadBulletins = true; // Admin, censeur et secrétaire
    $isRestrictedUser = ($user->id == 4); // Vérifie si l'utilisateur ID = 4
@endphp

<style>
/* ── Variables ─────────────────────────────────────────────── */
:root {
    --arc-primary: #1e40af;
    --arc-primary-light: #3b82f6;
    --arc-primary-ultra: #eff6ff;
    --arc-success: #059669;
    --arc-success-light: #d1fae5;
    --arc-danger: #dc2626;
    --arc-danger-light: #fee2e2;
    --arc-warning: #d97706;
    --arc-warning-light: #fef3c7;
    --arc-gray-50: #f8fafc;
    --arc-gray-100: #f1f5f9;
    --arc-gray-200: #e2e8f0;
    --arc-gray-600: #475569;
    --arc-gray-800: #1e293b;
    --arc-radius: 12px;
    --arc-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
    --arc-shadow-lg: 0 8px 32px rgba(0,0,0,.14), 0 2px 8px rgba(0,0,0,.08);
}

/* ── Modal overlay ────────────────────────────────────────── */
.arc-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(15,23,42,.65);
    backdrop-filter: blur(6px);
    z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    opacity: 0; pointer-events: none;
    transition: opacity .25s ease;
}
.arc-modal-overlay.active {
    opacity: 1; pointer-events: all;
}

/* ── Modal box ─────────────────────────────────────────────── */
.arc-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%; max-width: 980px;
    max-height: 92vh;
    overflow: hidden;
    display: flex; flex-direction: column;
    box-shadow: var(--arc-shadow-lg);
    transform: translateY(20px) scale(.98);
    transition: transform .28s cubic-bezier(.34,1.56,.64,1), opacity .25s ease;
    opacity: 0;
}
.arc-modal-overlay.active .arc-modal {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* ── Sécurité Modal spécifique (UNIQUEMENT BLANC ET BLEU) ──── */
.security-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(4px);
    z-index: 2000;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    opacity: 0; pointer-events: none;
    transition: opacity .3s ease;
}
.security-modal-overlay.active {
    opacity: 1; pointer-events: all;
}

.security-modal {
    background: #ffffff;
    border-radius: 24px;
    max-width: 480px;
    width: 100%;
    transform: scale(0.9) translateY(20px);
    transition: transform .4s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
}

.security-modal-overlay.active .security-modal {
    transform: scale(1) translateY(0);
}

.security-modal::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1e40af, #3b82f6, #1e40af);
}

.security-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: #eff6ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.security-icon i {
    font-size: 40px;
    color: #1e40af;
}

.security-content {
    padding: 2rem;
    text-align: center;
}

.security-content h3 {
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.security-content p {
    color: #475569;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.security-roles {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-left: 3px solid #1e40af;
}

.security-roles p {
    margin: 0;
    font-size: 0.85rem;
    color: #1e40af;
}

.security-roles i {
    margin-right: 0.5rem;
    color: #3b82f6;
}

.security-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.security-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.security-btn-cancel {
    background: #f1f5f9;
    color: #475569;
}

.security-btn-cancel:hover {
    background: #e2e8f0;
    transform: translateY(-2px);
}

.security-btn-confirm {
    background: #1e40af;
    color: white;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
}

.security-btn-confirm:hover {
    background: #1e3a8a;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(30, 64, 175, 0.3);
}

/* ── Modal header ──────────────────────────────────────────── */
.arc-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, var(--arc-primary) 0%, #1d4ed8 100%);
    color: #fff;
    flex-shrink: 0;
}
.arc-modal-header h3 { font-size: 1.1rem; font-weight: 700; margin: 0; }
.arc-modal-close {
    width: 34px; height: 34px; border-radius: 50%;
    border: none; background: rgba(255,255,255,.15);
    color: #fff; font-size: 1.2rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .2s;
}
.arc-modal-close:hover { background: rgba(255,255,255,.3); }

/* ── Modal tabs ────────────────────────────────────────────── */
.arc-tabs {
    display: flex; gap: .25rem;
    padding: .75rem 1.5rem 0;
    background: var(--arc-gray-50);
    border-bottom: 2px solid var(--arc-gray-200);
    flex-shrink: 0; flex-wrap: wrap;
}
.arc-tab-btn {
    padding: .55rem 1.1rem;
    border: none; background: transparent;
    border-radius: 8px 8px 0 0;
    font-size: .875rem; font-weight: 600;
    color: var(--arc-gray-600); cursor: pointer;
    transition: all .2s; border-bottom: 2px solid transparent;
    margin-bottom: -2px;
}
.arc-tab-btn:hover { background: var(--arc-gray-200); }
.arc-tab-btn.active {
    color: var(--arc-primary); background: #fff;
    border-bottom-color: var(--arc-primary);
}

/* ── Modal body ─────────────────────────────────────────────── */
.arc-modal-body {
    flex: 1; overflow-y: auto; padding: 1.5rem;
}
.arc-tab-pane { display: none; }
.arc-tab-pane.active { display: block; }

/* ── Stat cards ────────────────────────────────────────────── */
.arc-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: .75rem; margin-bottom: 1.25rem;
}
.arc-stat-card {
    background: var(--arc-gray-50);
    border: 1px solid var(--arc-gray-200);
    border-radius: var(--arc-radius);
    padding: .875rem;
    text-align: center;
    transition: box-shadow .2s;
}
.arc-stat-card:hover { box-shadow: var(--arc-shadow); }
.arc-stat-card .val {
    font-size: 1.4rem; font-weight: 800; line-height: 1;
    margin-bottom: .2rem;
}
.arc-stat-card .lbl { font-size: .72rem; color: var(--arc-gray-600); text-transform: uppercase; letter-spacing: .04em; }

/* ── Bulletin table ─────────────────────────────────────────── */
.arc-bul-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.arc-bul-table th {
    background: var(--arc-gray-800); color: #fff;
    padding: .6rem .75rem; text-align: center;
    font-size: .75rem; font-weight: 600;
    letter-spacing: .03em;
}
.arc-bul-table th:first-child { text-align: left; border-radius: 8px 0 0 0; }
.arc-bul-table th:last-child  { border-radius: 0 8px 0 0; }
.arc-bul-table td {
    padding: .5rem .75rem; border-bottom: 1px solid var(--arc-gray-200);
    vertical-align: middle;
}
.arc-bul-table td:first-child { font-weight: 600; font-size: .8rem; }
.arc-bul-table tr:hover td { background: var(--arc-primary-ultra); }
.arc-bul-table tfoot td {
    background: var(--arc-primary); color: #fff; font-weight: 700;
    padding: .65rem .75rem;
}

/* ── Moyenne badge ─────────────────────────────────────────── */
.moy-badge {
    display: inline-block; padding: .2rem .55rem;
    border-radius: 20px; font-weight: 700; font-size: .82rem;
    min-width: 48px; text-align: center;
}
.moy-badge.good { background: var(--arc-success-light); color: var(--arc-success); }
.moy-badge.bad  { background: var(--arc-danger-light);  color: var(--arc-danger); }
.moy-badge.neutral { background: var(--arc-gray-100);   color: var(--arc-gray-600); }

/* ── Download bar ──────────────────────────────────────────── */
.arc-download-bar {
    display: flex; flex-wrap: wrap; gap: .75rem; align-items: center;
    padding: 1rem 1.5rem;
    background: var(--arc-gray-50);
    border-top: 1px solid var(--arc-gray-200);
    flex-shrink: 0;
}
.arc-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .55rem 1.1rem; border-radius: 8px;
    font-size: .875rem; font-weight: 600;
    border: none; cursor: pointer; transition: all .2s;
    text-decoration: none;
}
.arc-btn-primary { background: var(--arc-primary); color: #fff; }
.arc-btn-primary:hover { background: #1d4ed8; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(30,64,175,.3); }
.arc-btn-success { background: var(--arc-success); color: #fff; }
.arc-btn-success:hover { background: #047857; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(5,150,105,.3); }
.arc-btn-outline {
    background: #fff; color: var(--arc-gray-600);
    border: 1.5px solid var(--arc-gray-200);
}
.arc-btn-outline:hover { border-color: var(--arc-primary); color: var(--arc-primary); }
.arc-btn-warning { background: var(--arc-warning); color: #fff; }
.arc-btn-warning:hover { background: #b45309; color: #fff; transform: translateY(-1px); }

/* ── Main table ────────────────────────────────────────────── */
.main-table { border-collapse: collapse; width: 100%; font-size: .82rem; }
.main-table th {
    background: var(--arc-gray-800); color: #fff;
    padding: .65rem .75rem; text-align: center;
    border: 1px solid #374151; font-size: .75rem;
}
.main-table td { padding: .55rem .75rem; border: 1px solid var(--arc-gray-200); vertical-align: middle; }
.main-table tr:hover td { background: #f0f9ff; }
.main-table tr:nth-child(even) td { background: var(--arc-gray-50); }
.main-table tr:nth-child(even):hover td { background: #f0f9ff; }

/* ── Trimestre selector (download all) ─────────────────────── */
.trim-selector { display: flex; gap: .5rem; flex-wrap: wrap; }
.trim-selector button {
    padding: .45rem .9rem; border-radius: 8px;
    border: 1.5px solid var(--arc-gray-200);
    background: #fff; color: var(--arc-gray-600);
    font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .2s;
}
.trim-selector button:hover,
.trim-selector button.active {
    border-color: var(--arc-primary); color: var(--arc-primary);
    background: var(--arc-primary-ultra);
}

/* ── Loading spinner ────────────────────────────────────────── */
.arc-spinner {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 3rem; gap: 1rem;
    color: var(--arc-gray-600);
}
.arc-spinner-icon {
    width: 48px; height: 48px; border-radius: 50%;
    border: 4px solid var(--arc-gray-200);
    border-top-color: var(--arc-primary);
    animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Toast ─────────────────────────────────────────────────── */
.arc-toast {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    background: var(--arc-gray-800); color: #fff;
    padding: .75rem 1.25rem; border-radius: 10px;
    font-size: .875rem; font-weight: 500;
    box-shadow: var(--arc-shadow-lg); z-index: 9999;
    transform: translateY(20px); opacity: 0;
    transition: all .3s ease; pointer-events: none;
}
.arc-toast.show { transform: translateY(0); opacity: 1; }

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 768px) {
    .arc-modal { max-width: 100%; max-height: 100vh; border-radius: 16px 16px 0 0; }
    .arc-modal-overlay { align-items: flex-end; padding: 0; }
    .arc-bul-table { font-size: .75rem; }
    .arc-bul-table th, .arc-bul-table td { padding: .4rem .5rem; }
}

/* ── Appreciation pill ──────────────────────────────────────── */
.appr-pill {
    display: inline-block; padding: .15rem .5rem;
    border-radius: 20px; font-size: .72rem; font-weight: 600;
}
.appr-TB  { background: #d1fae5; color: #065f46; }
.appr-B   { background: #dbeafe; color: #1e40af; }
.appr-AB  { background: #e0f2fe; color: #0369a1; }
.appr-P   { background: #fef9c3; color: #854d0e; }
.appr-I   { background: #fee2e2; color: #991b1b; }
.appr-F   { background: #fce7f3; color: #9d174d; }
.appr-M   { background: #f3e8ff; color: #6b21a8; }
.appr-TF  { background: #fff1f2; color: #9f1239; }

/* ── Decision badges ────────────────────────────────────────── */
.decision-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .4rem .85rem; border-radius: 20px;
    font-size: .8rem; font-weight: 700;
}
.decision-felic   { background: #d1fae5; color: #065f46; }
.decision-encou   { background: #dbeafe; color: #1e40af; }
.decision-honneur { background: #fef3c7; color: #92400e; }
.decision-avert   { background: #fee2e2; color: #991b1b; }
.decision-none    { background: var(--arc-gray-100); color: var(--arc-gray-600); }

/* ── Section annuelle dans modal ───────────────────────────── */
.annual-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem; margin-bottom: 1rem;
}
@media (max-width: 600px) {
    .annual-row { grid-template-columns: 1fr 1fr; }
}
.annual-card {
    border: 1px solid var(--arc-gray-200);
    border-radius: 10px; padding: .875rem;
    text-align: center; background: var(--arc-gray-50);
}
.annual-card .t-val { font-size: 1.2rem; font-weight: 800; }
.annual-card .t-lbl { font-size: .72rem; color: var(--arc-gray-600); margin-top: .2rem; }

/* ── Scroll table wrapper ─────────────────────────────────── */
.scroll-x { overflow-x: auto; border-radius: var(--arc-radius); border: 1px solid var(--arc-gray-200); box-shadow: var(--arc-shadow); }

/* ── Row action btn ─────────────────────────────────────────── */
.row-action {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .65rem; border-radius: 6px;
    font-size: .75rem; font-weight: 600;
    border: none; cursor: pointer; transition: all .15s;
    text-decoration: none;
}
.row-action-blue { background: var(--arc-primary-ultra); color: var(--arc-primary); }
.row-action-blue:hover { background: var(--arc-primary); color: #fff; }
.row-action-green { background: var(--arc-success-light); color: var(--arc-success); }
.row-action-green:hover { background: var(--arc-success); color: #fff; }
</style>

<div class="bg-white p-6 rounded-xl shadow-sm">

    {{-- En-tête --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:var(--arc-primary-ultra)">
                    <i class="fas fa-chart-bar" style="color:var(--arc-primary)"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 m-0">Notes archivées – {{ $class->name }}</h1>
                    <p class="text-sm text-gray-500 m-0">Année académique : <span class="font-semibold">{{ $year->name }}</span></p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            {{-- Télécharger tous les bulletins --}}
            @{{-- Télécharger tous les bulletins --}}
            @if($canDownloadBulletins)
            <div class="relative" id="bulk-download-wrapper">
                <button onclick="toggleBulkMenu()" class="arc-btn arc-btn-success">
                    <i class="fas fa-file-pdf"></i>
                    <span class="hidden sm:inline">Bulletins classe</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div id="bulk-menu" class="hidden absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-3 z-50 w-56">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2 px-1">Choisir le trimestre</p>
                    @foreach([1,2,3] as $t)
                    <button onclick="checkAccessAndDownloadBulk({{ $t }})"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-50 text-sm font-medium text-gray-700 hover:text-blue-700 transition w-full text-left">
                        <i class="fas fa-download text-xs"></i> Trimestre {{ $t }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
            <a href="{{ route('archives.show', $year->id) }}"
               class="arc-btn arc-btn-outline">
                <i class="fas fa-arrow-left"></i>
                <span class="hidden sm:inline">Retour</span>
            </a>
        </div>
    </div>

    @if(count($tableauNotes) === 0)
        <div class="text-center py-20">
            <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background:var(--arc-gray-100)">
                <i class="fas fa-chart-bar text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-1">Aucune donnée disponible</h3>
            <p class="text-gray-500 text-sm">Aucune donnée de notes pour cette classe archivée.</p>
        </div>
    @else

    {{-- Tableau principal --}}
    <div class="scroll-x mb-6">
        <table class="main-table">
            <thead>
                <tr>
                    <th rowspan="2" class="text-left w-8" style="border-radius:8px 0 0 0">N°</th>
                    <th rowspan="2" class="text-left" style="min-width:170px">Nom & Prénoms</th>
                    @foreach([1,2,3] as $t)
                        <th colspan="3" style="background:#1d4ed8">Trimestre {{ $t }}</th>
                    @endforeach
                    <th colspan="2" style="background:#065f46;border-radius:0 8px 0 0">Annuel</th>
                    <th rowspan="2" style="background:#78350f;white-space:nowrap;font-size:.7rem">Actions</th>
                </tr>
                <tr style="background:#374151">
                    @foreach([1,2,3] as $t)
                        <th style="font-size:.7rem;padding:.5rem .4rem">Moy.</th>
                        <th style="font-size:.7rem;padding:.5rem .4rem">Cond.</th>
                        <th style="font-size:.7rem;padding:.5rem .4rem">Rg.</th>
                    @endforeach
                    <th style="font-size:.7rem;padding:.5rem .4rem;background:#065f46">Moy.Ann.</th>
                    <th style="font-size:.7rem;padding:.5rem .4rem;background:#065f46">Rg.Ann.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tableauNotes as $i => $row)
                    @php
                        $moyAnn = $row['moy_annuelle'];
                        $statut = $moyAnn !== null ? ($moyAnn >= 10 ? 'good' : 'bad') : 'neutral';
                    @endphp
                    <tr>
                        <td class="text-center text-gray-500 text-xs">{{ $i + 1 }}</td>
                        <td>
                            <div class="font-semibold text-gray-800" style="font-size:.84rem">
                                {{ $row['student']->last_name }} {{ $row['student']->first_name }}
                            </div>
                            @if($row['record']->num_educ ?? null)
                            <div class="text-xs text-gray-400">{{ $row['record']->num_educ }}</div>
                            @endif
                        </td>

                        @foreach([1,2,3] as $t)
                            @php
                                $td  = $row['trimestres'][$t];
                                $moy = $td['moyenne'];
                                $cls = $moy === null ? 'neutral' : ($moy >= 10 ? 'good' : 'bad');
                            @endphp
                            <td class="text-center">
                                <span class="moy-badge {{ $cls }}">
                                    {{ $moy !== null ? number_format($moy,2,',','') : '–' }}
                                </span>
                            </td>
                            <td class="text-center text-xs text-gray-500">
                                {{ number_format($td['conduite'],2,',','') }}
                            </td>
                            <td class="text-center text-xs text-gray-400">
                                {{ $td['rang'] !== null ? $td['rang'].'ᵉ' : '–' }}
                            </td>
                        @endforeach

                        <td class="text-center">
                            <span class="moy-badge {{ $statut }}" style="font-size:.85rem">
                                {{ $moyAnn !== null ? number_format($moyAnn,2,',','') : '–' }}
                            </span>
                        </td>
                        <td class="text-center text-xs text-gray-400">
                            {{ isset($row['rang_annuel']) && $row['rang_annuel'] !== '-' ? $row['rang_annuel'].'ᵉ' : '–' }}
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="flex flex-wrap gap-1 justify-center" style="min-width:140px">
                                {{-- Voir les notes détaillées --}}
                                <button
                                    onclick="openStudentModal({{ $row['student']->id }}, '{{ addslashes($row['student']->last_name.' '.$row['student']->first_name) }}')"
                                    class="row-action row-action-blue">
                                    <i class="fas fa-eye"></i> Notes
                                </button>
                                {{-- Télécharger bulletin --}}
                                @if($canDownloadBulletins)
                                <div class="relative inline-block" id="dl-wrap-{{ $row['student']->id }}">
                                    <button onclick="checkAccessAndDownload('single', {{ $row['student']->id }})"
                                            class="row-action row-action-green">
                                        <i class="fas fa-file-pdf"></i> Bulletin
                                    </button>
                                    <div id="dl-menu-{{ $row['student']->id }}"
                                         class="hidden absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg py-2 z-50 w-40">
                                        @foreach([1,2,3] as $t)
                                        <a href="{{ route('archives.student.bulletin.pdf', [$year->id, $class->id, $row['student']->id, $t]) }}"
                                           class="block px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">
                                            <i class="fas fa-download text-xs"></i> Trimestre {{ $t }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Statistiques de synthèse --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach([1,2,3] as $t)
            @php
                $trimestreRows = array_map(fn($r) => $r['trimestres'][$t], $tableauNotes);
                $moys   = array_filter(array_map(fn($x) => $x['moyenne'], $trimestreRows), fn($v) => $v !== null);
                $admis  = count(array_filter($moys, fn($m) => $m >= 10));
                $effec  = count($moys);
                $moyC   = $effec > 0 ? round(array_sum($moys)/$effec, 2) : null;
                $taux   = $effec > 0 ? round($admis/$effec*100) : 0;
            @endphp
            <div class="border rounded-xl p-4" style="background:var(--arc-gray-50)">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-gray-700 text-sm">Trimestre {{ $t }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full font-semibold"
                          style="background:{{ $taux >= 60 ? 'var(--arc-success-light)' : 'var(--arc-danger-light)' }};
                                 color:{{ $taux >= 60 ? 'var(--arc-success)' : 'var(--arc-danger)' }}">
                        {{ $taux }}% de réussite
                    </span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Effectif noté</span>
                        <span class="font-semibold">{{ $effec }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Moyenne classe</span>
                        <span class="font-semibold {{ $moyC !== null && $moyC >= 10 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $moyC !== null ? number_format($moyC,2,',','') : '–' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Admis (≥10)</span>
                        <span class="font-semibold text-green-700">{{ $admis }}</span>
                    </div>
                    {{-- Barre de progression --}}
                    <div class="mt-2">
                        <div class="w-full rounded-full h-1.5" style="background:var(--arc-gray-200)">
                            <div class="h-1.5 rounded-full transition-all"
                                 style="width:{{ $taux }}%;background:{{ $taux >= 60 ? 'var(--arc-success)' : 'var(--arc-danger)' }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @endif
</div>


<div class="security-modal-overlay" id="securityModal">
    <div class="security-modal">
        <div class="security-content">
            <div class="security-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Accès restreint</h3>
            <p>
                Cette fonctionnalité de téléchargement des bulletins est exclusivement réservée 
                à la Secrétaire et au Directeur de l'établissement.
            </p>
            <div class="security-roles">
                <p>
                    <i class="fas fa-user-check"></i>
                    Rôles autorisés : Secrétaire & Directeur
                </p>
                <p class="mt-2" style="font-size:0.75rem; color:#64748b;">
                    <i class="fas fa-key"></i>
                    Contactez l'administrateur si vous avez besoin d'accéder à ces documents.
                </p>
            </div>
            <div class="security-buttons">
                <button onclick="closeSecurityModal()" class="security-btn security-btn-cancel">
                    <i class="fas fa-times"></i> Fermer
                </button>
                <button onclick="closeSecurityModalAndRedirect()" class="security-btn security-btn-confirm">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </button>
            </div>
        </div>
    </div>
</div>


<div class="arc-modal-overlay" id="studentModal" onclick="closeOnOverlay(event)">
    <div class="arc-modal" id="studentModalBox">

        {{-- Header --}}
        <div class="arc-modal-header">
            <div>
                <h3 id="modalStudentName">Chargement…</h3>
                <p style="font-size:.82rem;opacity:.8;margin:.15rem 0 0" id="modalSubtitle">
                    {{ $class->name }} · {{ $year->name }}
                </p>
            </div>
            <button class="arc-modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="arc-tabs" id="modalTabs">
            <button class="arc-tab-btn active" onclick="switchTab('annuel')">
                <i class="fas fa-calendar-year mr-1"></i> Bilan annuel
            </button>
            <button class="arc-tab-btn" onclick="switchTab('t1')">T1</button>
            <button class="arc-tab-btn" onclick="switchTab('t2')">T2</button>
            <button class="arc-tab-btn" onclick="switchTab('t3')">T3</button>
        </div>

        {{-- Body --}}
        <div class="arc-modal-body" id="modalBody">
            <div class="arc-spinner" id="modalSpinner">
                <div class="arc-spinner-icon"></div>
                <span>Chargement des notes…</span>
            </div>
            <div id="modalContent" style="display:none"></div>
        </div>

        {{-- Footer download --}}
        <div class="arc-download-bar" id="modalFooter" style="display:none">
            <span class="text-sm font-semibold text-gray-600 mr-auto">
                <i class="fas fa-download mr-1"></i> Télécharger le bulletin
            </span>
            <div class="trim-selector" id="downloadLinks"></div>
        </div>

    </div>
</div>

{{-- Toast --}}
<div class="arc-toast" id="arcToast"></div>


<script>
/* ─── Config ────────────────────────────────────────────────── */
const YEAR_ID  = {{ $year->id }};
const CLASS_ID = {{ $class->id }};
const IS_RESTRICTED_USER = {{ $isRestrictedUser ? 'true' : 'false' }};
let pendingDownload = null; // Stocke les infos du téléchargement en attente

const ROUTES   = {
    studentNotes : (sid)       => `/archives/${YEAR_ID}/classes/${CLASS_ID}/eleves/${sid}/notes`,
    bulletinPdf  : (sid, t)    => `/archives/${YEAR_ID}/classes/${CLASS_ID}/eleves/${sid}/bulletin/${t}/pdf`,
    classBulletinsAll : (t)    => `/archives/${YEAR_ID}/classes/${CLASS_ID}/bulletins/all/${t}/pdf`,
};

let currentData      = null;
let currentStudentId = null;
let currentTab       = 'annuel';

/* ─── Vérification d'accès pour téléchargement ───────────────── */
function checkAccessAndDownload(type, studentId = null, trimestre = null) {
    // Si l'utilisateur est restreint (ID = 4)
    if (IS_RESTRICTED_USER) {
        // Stocker les informations du téléchargement demandé
        pendingDownload = { type, studentId, trimestre };
        // Afficher le modal de sécurité
        showSecurityModal();
        return;
    }
    
    // Sinon, procéder au téléchargement
    executeDownload(type, studentId, trimestre);
}

function executeDownload(type, studentId, trimestre) {
    if (type === 'bulk' && trimestre) {
        // Téléchargement groupé des bulletins de la classe
        window.location.href = ROUTES.classBulletinsAll(trimestre);
        showToast('Téléchargement des bulletins de la classe en cours...', 2000);
    } else if (type === 'single' && trimestre) {
        // Téléchargement direct d'un trimestre spécifique
        window.location.href = ROUTES.bulletinPdf(studentId, trimestre);
        showToast('Téléchargement du bulletin en cours...', 2000);
    } else if (type === 'single') {
        // Afficher le menu déroulant
        toggleDlMenu(studentId);
    }
}

function checkAccessAndDownloadBulk(trimestre) {
    // Si l'utilisateur est restreint (ID = 4)
    if (IS_RESTRICTED_USER) {
        // Stocker les informations du téléchargement demandé
        pendingDownload = { type: 'bulk', trimestre: trimestre };
        // Afficher le modal de sécurité
        showSecurityModal();
        return;
    }
    
    // Sinon, procéder au téléchargement
    executeBulkDownload(trimestre);
}

function executeBulkDownload(trimestre) {
    window.location.href = ROUTES.classBulletinsAll(trimestre);
    showToast('Téléchargement des bulletins de la classe en cours...', 2000);
}

function showSecurityModal() {
    const modal = document.getElementById('securityModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeSecurityModal() {
    const modal = document.getElementById('securityModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    pendingDownload = null;
}


function closeSecurityModalAndRedirect() {
    closeSecurityModal();
    
    if (pendingDownload) {
        // On pourrait afficher un message ou juste ignorer
        pendingDownload = null;
        showToast('Accès refusé - Contactez l\'administration', 3000);
    } else {
        // Redirection vers la page d'accueil ou dashboard
        window.location.href = '/dashboard';
    }
}

/* ─── Utility ───────────────────────────────────────────────── */
function showToast(msg, ms = 3000) {
    const el = document.getElementById('arcToast');
    el.textContent = msg;
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), ms);
}

function getApprClass(appr) {
    const map = {
        'Très Bien':'TB','Bien':'B','Assez Bien':'AB','Passable':'P',
        'Insuffisant':'I','Faible':'F','Médiocre':'M','Très Faible':'TF','-':'neutral'
    };
    return 'appr-' + (map[appr] || 'I');
}

function moyBadge(val) {
    if (!val || val === '–' || val === '-' || val === '0,00') {
        return `<span class="moy-badge neutral">–</span>`;
    }
    const n = parseFloat(val.replace(',', '.'));
    const cls = isNaN(n) ? 'neutral' : (n >= 10 ? 'good' : 'bad');
    return `<span class="moy-badge ${cls}">${val}</span>`;
}

/* ─── Modal open / close ────────────────────────────────────── */
function openStudentModal(studentId, name) {
    currentStudentId = studentId;
    currentData      = null;
    currentTab       = 'annuel';

    document.getElementById('modalStudentName').textContent = name;
    document.getElementById('modalSpinner').style.display   = 'flex';
    document.getElementById('modalContent').style.display   = 'none';
    document.getElementById('modalFooter').style.display    = 'none';

    // Reset tabs
    document.querySelectorAll('.arc-tab-btn').forEach((b,i) => {
        b.classList.toggle('active', i === 0);
    });

    document.getElementById('studentModal').classList.add('active');
    document.body.style.overflow = 'hidden';

    // Fetch notes
    fetch(ROUTES.studentNotes(studentId))
        .then(r => { if (!r.ok) throw new Error('Erreur réseau'); return r.json(); })
        .then(data => {
            currentData = data;
            renderModal();
        })
        .catch(err => {
            document.getElementById('modalSpinner').innerHTML =
                `<div style="text-align:center;color:#dc2626">
                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                    <p style="font-size:.9rem">${err.message}</p>
                 </div>`;
        });
}

function closeModal() {
    document.getElementById('studentModal').classList.remove('active');
    document.body.style.overflow = '';
}

function closeOnOverlay(e) {
    if (e.target === document.getElementById('studentModal')) closeModal();
}

/* ─── Tab switch ────────────────────────────────────────────── */
function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.arc-tab-btn').forEach(b => {
        const labels = ['annuel','t1','t2','t3'];
        b.classList.toggle('active', b.getAttribute('onclick') === `switchTab('${tab}')`);
    });
    if (currentData) renderModal();
}

/* ─── Render modal content ──────────────────────────────────── */
function renderModal() {
    const data = currentData;
    document.getElementById('modalSpinner').style.display = 'none';
    document.getElementById('modalContent').style.display = 'block';
    document.getElementById('modalFooter').style.display  = 'flex';

    // Build download links (avec vérification d'accès)
    const dlDiv = document.getElementById('downloadLinks');
    dlDiv.innerHTML = [1,2,3].map(t =>
        `<button onclick="checkAccessAndDownload('single', ${currentStudentId}, ${t})"
            class="arc-btn arc-btn-primary"
            style="font-size:.8rem;padding:.4rem .85rem">
            <i class="fas fa-file-pdf"></i> Trimestre ${t}
         </button>`
    ).join('');

    const content = document.getElementById('modalContent');

    if (currentTab === 'annuel') {
        content.innerHTML = buildAnnualTab(data);
    } else {
        const t = parseInt(currentTab.replace('t',''));
        content.innerHTML = buildTrimestreTab(data, t);
    }
}

/* ─── Annual tab ────────────────────────────────────────────── */
function buildAnnualTab(data) {
    const r = data.record;
    const moy = data.moy_annuelle;
    const moyFmt = moy !== null ? moy.toFixed(2).replace('.',',') : '–';
    const moyN = parseFloat(moyFmt.replace(',','.'));
    const isBig = !isNaN(moyN) && moyN >= 10;

    // Statut délibération
    let statutHtml = '';
    if (r.statut === 'passed') {
        statutHtml = `<div class="decision-badge decision-felic">
            <i class="fas fa-graduation-cap"></i> Admis(e)
            ${r.rang_annuel ? '· Rang : ' + r.rang_annuel + 'ᵉ/' + r.total_eleves : ''}
        </div>`;
    } else if (r.statut === 'repeated') {
        statutHtml = `<div class="decision-badge decision-avert">
            <i class="fas fa-redo"></i> Redoublant(e)
        </div>`;
    } else {
        statutHtml = `<div class="decision-badge decision-none">
            <i class="fas fa-clock"></i> Statut non défini
        </div>`;
    }

    // Résumé par trimestre
    const trimestreCards = [1,2,3].map(t => {
        const td  = data.trimestres[t];
        const mTr = td.moyenneGenerale;
        const fmt = mTr !== null ? mTr.toFixed(2).replace('.',',') : '–';
        const cls = mTr !== null ? (mTr >= 10 ? '#059669' : '#dc2626') : '#64748b';
        return `
        <div class="annual-card">
            <div class="t-val" style="color:${cls}">${fmt}</div>
            <div class="t-lbl">Trimestre ${t}</div>
            <div style="font-size:.7rem;color:#64748b;margin-top:.3rem">
                Rang: ${td.rang}${td.rang !== '-' ? 'ᵉ/'+td.totalEleves : ''}
            </div>
        </div>`;
    }).join('');

    return `
    <div style="margin-bottom:1.25rem">
        <div class="flex items-center gap-3 mb-4">
            ${statutHtml}
        </div>

        <div class="arc-stat-grid" style="grid-template-columns:repeat(4,1fr)">
            <div class="arc-stat-card">
                <div class="val" style="color:${isBig ? '#059669' : '#dc2626'}">${moyFmt}</div>
                <div class="lbl">Moyenne annuelle</div>
            </div>
            <div class="arc-stat-card">
                <div class="val">${r.rang_annuel !== '-' ? r.rang_annuel+'ᵉ' : '–'}</div>
                <div class="lbl">Rang annuel</div>
            </div>
            <div class="arc-stat-card">
                <div class="val">${r.total_eleves}</div>
                <div class="lbl">Élèves classe</div>
            </div>
            <div class="arc-stat-card">
                <div class="val" style="font-size:1rem">${isBig ? '✓ Admis' : '✗ Non admis'}</div>
                <div class="lbl">Décision (≥10)</div>
            </div>
        </div>

        <h4 style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:.75rem">
            <i class="fas fa-chart-line mr-1" style="color:var(--arc-primary)"></i>
            Résumé par trimestre
        </h4>
        <div class="annual-row">${trimestreCards}</div>

        <p style="font-size:.8rem;color:#64748b;text-align:center;margin-top:.5rem">
            Cliquez sur les onglets T1, T2 ou T3 pour voir le détail des notes par matière.
        </p>
    </div>`;
}

/* ─── Trimestre tab ─────────────────────────────────────────── */
function buildTrimestreTab(data, t) {
    const td = data.trimestres[t];
    if (!td) return '<p class="text-gray-500 text-center py-8">Aucune donnée pour ce trimestre.</p>';

    const moyGen = td.moyenneGenerale;
    const moyFmt = moyGen !== null ? moyGen.toFixed(2).replace('.',',') : '–';

    // Décision
    let decision = `<span class="decision-badge decision-none"><i class="fas fa-minus"></i> Pas de décision</span>`;
    if (moyGen !== null && td.conduite > 0) {
        if (moyGen >= 16 && td.conduite >= 14)
            decision = `<span class="decision-badge decision-felic"><i class="fas fa-star"></i> Félicitations</span>`;
        else if (moyGen >= 14 && td.conduite >= 12)
            decision = `<span class="decision-badge decision-encou"><i class="fas fa-thumbs-up"></i> Encouragements</span>`;
        else if (moyGen >= 12 && td.conduite >= 10)
            decision = `<span class="decision-badge decision-honneur"><i class="fas fa-medal"></i> Tableau d'honneur</span>`;
        else if (moyGen < 10 || td.conduite < 8)
            decision = `<span class="decision-badge decision-avert"><i class="fas fa-exclamation-triangle"></i> Avertissement</span>`;
    }

    // Rows du bulletin
    const rows = td.bulletin.map(b => {
        const m = b.moyenne;
        const mFmt = m !== null ? m.toFixed(2).replace('.',',') : '–';

        const interroStr = b.interros && b.interros.length
            ? b.interros.map(v => v !== null ? v.toFixed(2).replace('.',',') : '-').join(' / ')
            : '–';

        const d1 = b.devoir1 !== null ? b.devoir1.toFixed(2).replace('.',',') : '–';
        const d2 = b.devoir2 !== null ? b.devoir2.toFixed(2).replace('.',',') : '–';
        const mi = b.moyInterro !== null ? b.moyInterro.toFixed(2).replace('.',',') : '–';

        return `
        <tr>
            <td style="min-width:140px">${b.subject}</td>
            <td class="text-center">${b.coef}</td>
            <td class="text-center" style="font-size:.75rem;color:#64748b;max-width:120px">${interroStr}</td>
            <td class="text-center">${mi}</td>
            <td class="text-center">${d1}</td>
            <td class="text-center">${d2}</td>
            <td class="text-center">${moyBadge(mFmt)}</td>
            <td class="text-center"><span class="appr-pill ${getApprClass(b.appreciation)}">${b.appreciation}</span></td>
        </tr>`;
    }).join('');

    const condFmt = td.conduite.toFixed(2).replace('.',',');
    const totalEleves = td.totalEleves || '?';

    return `
    <div>
        <!-- Stats rapides -->
        <div class="arc-stat-grid" style="margin-bottom:1.25rem">
            <div class="arc-stat-card">
                <div class="val" style="color:${moyGen !== null && moyGen >= 10 ? '#059669' : '#dc2626'}">${moyFmt}</div>
                <div class="lbl">Moyenne générale</div>
            </div>
            <div class="arc-stat-card">
                <div class="val">${td.rang !== '-' ? td.rang+'ᵉ/'+totalEleves : '–'}</div>
                <div class="lbl">Rang</div>
            </div>
            <div class="arc-stat-card">
                <div class="val" style="color:${td.conduite >= 10 ? '#059669' : '#dc2626'}">${condFmt}</div>
                <div class="lbl">Conduite</div>
            </div>
            <div class="arc-stat-card" style="min-width:0">
                ${decision}
            </div>
        </div>

        <!-- Tableau des matières -->
        <div style="overflow-x:auto">
            <table class="arc-bul-table" style="min-width:600px">
                <thead>
                    <tr>
                        <th class="text-left">Matière</th>
                        <th>Coef.</th>
                        <th>Interros</th>
                        <th>Moy.I</th>
                        <th>Devoir 1</th>
                        <th>Devoir 2</th>
                        <th>Moy./20</th>
                        <th>Appréciation</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right" style="border-radius:0 0 0 8px">
                            Moyenne générale du trimestre ${t}
                        </td>
                        <td class="text-center" style="font-size:1rem">${moyFmt}</td>
                        <td class="text-center" style="border-radius:0 0 8px 0">
                            <span class="appr-pill ${getApprClass(td.appreciation)}">${td.appreciation}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>`;
}

/* ─── Dropdown menus ─────────────────────────────────────────── */
function toggleBulkMenu() {
    const menu = document.getElementById('bulk-menu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

function toggleDlMenu(id) {
    // Fermer tous les autres
    document.querySelectorAll('[id^="dl-menu-"]').forEach(el => {
        if (el.id !== `dl-menu-${id}`) el.classList.add('hidden');
    });
    const menu = document.getElementById(`dl-menu-${id}`);
    if (menu) menu.classList.toggle('hidden');
}

// Fermer les menus quand on clique ailleurs
document.addEventListener('click', e => {
    if (!e.target.closest('#bulk-download-wrapper')) {
        const bulkMenu = document.getElementById('bulk-menu');
        if (bulkMenu) bulkMenu.classList.add('hidden');
    }
    if (!e.target.closest('[id^="dl-wrap-"]')) {
        document.querySelectorAll('[id^="dl-menu-"]').forEach(el => el.classList.add('hidden'));
    }
});

// Raccourci clavier ESC
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        closeSecurityModal();
    }
});

/* ─── Tab buttons click ─────────────────────────────────────── */
document.querySelectorAll('.arc-tab-btn').forEach((btn, i) => {
    const tabs = ['annuel','t1','t2','t3'];
    btn.setAttribute('onclick', `switchTab('${tabs[i]}')`);
});
</script>
@endsection
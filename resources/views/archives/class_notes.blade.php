@extends('layouts.app')
@section('content')
@php
    $pageTitle = 'Notes archivées – ' . $class->name . ' (' . $year->name . ')';
    $user = auth()->user();
    $canDownloadBulletins = true;
    $isRestrictedUser = ($user->id == 4);
@endphp

<style>
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
.arc-modal-overlay.active { opacity: 1; pointer-events: all; }

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
.arc-modal-overlay.active .arc-modal { transform: translateY(0) scale(1); opacity: 1; }

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
.security-modal-overlay.active { opacity: 1; pointer-events: all; }
.security-modal {
    background: #ffffff;
    border-radius: 24px;
    max-width: 480px; width: 100%;
    transform: scale(0.9) translateY(20px);
    transition: transform .4s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    border: 1px solid #e2e8f0;
    position: relative; overflow: hidden;
}
.security-modal-overlay.active .security-modal { transform: scale(1) translateY(0); }
.security-modal::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, #1e40af, #3b82f6, #1e40af);
}
.security-icon {
    width: 80px; height: 80px; margin: 0 auto 1rem;
    background: #eff6ff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.security-icon i { font-size: 40px; color: #1e40af; }
.security-content { padding: 2rem; text-align: center; }
.security-content h3 { color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; }
.security-content p { color: #475569; font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.5; }
.security-roles {
    background: #f8fafc; border-radius: 12px; padding: 1rem;
    margin-bottom: 1.5rem; border-left: 3px solid #1e40af;
}
.security-roles p { margin: 0; font-size: 0.85rem; color: #1e40af; }
.security-roles i { margin-right: 0.5rem; color: #3b82f6; }
.security-buttons { display: flex; gap: 1rem; justify-content: center; }
.security-btn {
    padding: 0.75rem 1.5rem; border-radius: 12px;
    font-weight: 600; font-size: 0.9rem; transition: all 0.2s; cursor: pointer; border: none;
}
.security-btn-cancel { background: #f1f5f9; color: #475569; }
.security-btn-cancel:hover { background: #e2e8f0; transform: translateY(-2px); }
.security-btn-confirm { background: #1e40af; color: white; box-shadow: 0 4px 12px rgba(30,64,175,.2); }
.security-btn-confirm:hover { background: #1e3a8a; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(30,64,175,.3); }

.arc-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, var(--arc-primary) 0%, #1d4ed8 100%);
    color: #fff; flex-shrink: 0;
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

.arc-tabs {
    display: flex; gap: .25rem;
    padding: .75rem 1.5rem 0;
    background: var(--arc-gray-50);
    border-bottom: 2px solid var(--arc-gray-200);
    flex-shrink: 0; flex-wrap: wrap;
}
.arc-tab-btn {
    padding: .55rem 1.1rem; border: none; background: transparent;
    border-radius: 8px 8px 0 0;
    font-size: .875rem; font-weight: 600;
    color: var(--arc-gray-600); cursor: pointer;
    transition: all .2s; border-bottom: 2px solid transparent; margin-bottom: -2px;
}
.arc-tab-btn:hover { background: var(--arc-gray-200); }
.arc-tab-btn.active { color: var(--arc-primary); background: #fff; border-bottom-color: var(--arc-primary); }

.arc-modal-body { flex: 1; overflow-y: auto; padding: 1.5rem; }
.arc-tab-pane { display: none; }
.arc-tab-pane.active { display: block; }

.arc-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
.arc-stat-card {
    background: var(--arc-gray-50); border: 1px solid var(--arc-gray-200);
    border-radius: var(--arc-radius); padding: .875rem;
    text-align: center; transition: box-shadow .2s;
}
.arc-stat-card:hover { box-shadow: var(--arc-shadow); }
.arc-stat-card .val { font-size: 1.4rem; font-weight: 800; line-height: 1; margin-bottom: .2rem; }
.arc-stat-card .lbl { font-size: .72rem; color: var(--arc-gray-600); text-transform: uppercase; letter-spacing: .04em; }

/* ── Tableau bulletin : colonnes I1-I5 + D1-D2 ─────────────── */
.arc-bul-table { width: 100%; border-collapse: collapse; font-size: .79rem; }
.arc-bul-table th {
    background: var(--arc-gray-800); color: #fff;
    padding: .5rem .45rem; text-align: center;
    font-size: .7rem; font-weight: 600; letter-spacing: .03em;
    white-space: nowrap;
}
.arc-bul-table th.left { text-align: left; border-radius: 8px 0 0 0; }
.arc-bul-table th:last-child { border-radius: 0 8px 0 0; }
.arc-bul-table td {
    padding: .42rem .45rem; border-bottom: 1px solid var(--arc-gray-200);
    vertical-align: middle; text-align: center; white-space: nowrap;
}
.arc-bul-table td.left { text-align: left; font-weight: 600; font-size: .78rem; min-width: 130px; }
.arc-bul-table tr:hover td { background: var(--arc-primary-ultra); }
.arc-bul-table tfoot td { background: var(--arc-primary); color: #fff; font-weight: 700; padding: .6rem .45rem; }

/* Groupe headers colorés */
.th-interro { background: #1d4ed8 !important; }
.th-devoir  { background: #065f46 !important; }
.th-moy     { background: #7c3aed !important; }
.th-appr    { background: #78350f !important; }

.moy-badge { display: inline-block; padding: .2rem .5rem; border-radius: 20px; font-weight: 700; font-size: .8rem; min-width: 44px; text-align: center; }
.moy-badge.good { background: var(--arc-success-light); color: var(--arc-success); }
.moy-badge.bad  { background: var(--arc-danger-light); color: var(--arc-danger); }
.moy-badge.neutral { background: var(--arc-gray-100); color: var(--arc-gray-600); }

.arc-download-bar {
    display: flex; flex-wrap: wrap; gap: .75rem; align-items: center;
    padding: 1rem 1.5rem; background: var(--arc-gray-50);
    border-top: 1px solid var(--arc-gray-200); flex-shrink: 0;
}
.arc-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .55rem 1.1rem; border-radius: 8px;
    font-size: .875rem; font-weight: 600; border: none; cursor: pointer;
    transition: all .2s; text-decoration: none;
}
.arc-btn-primary { background: var(--arc-primary); color: #fff; }
.arc-btn-primary:hover { background: #1d4ed8; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(30,64,175,.3); }
.arc-btn-success { background: var(--arc-success); color: #fff; }
.arc-btn-success:hover { background: #047857; color: #fff; transform: translateY(-1px); }
.arc-btn-outline { background: #fff; color: var(--arc-gray-600); border: 1.5px solid var(--arc-gray-200); }
.arc-btn-outline:hover { border-color: var(--arc-primary); color: var(--arc-primary); }
.arc-btn-warning { background: var(--arc-warning); color: #fff; }
.arc-btn-warning:hover { background: #b45309; color: #fff; transform: translateY(-1px); }

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

.trim-selector { display: flex; gap: .5rem; flex-wrap: wrap; }
.trim-selector button {
    padding: .45rem .9rem; border-radius: 8px; border: 1.5px solid var(--arc-gray-200);
    background: #fff; color: var(--arc-gray-600); font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .2s;
}
.trim-selector button:hover, .trim-selector button.active { border-color: var(--arc-primary); color: var(--arc-primary); background: var(--arc-primary-ultra); }

.arc-spinner { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; gap: 1rem; color: var(--arc-gray-600); }
.arc-spinner-icon { width: 48px; height: 48px; border-radius: 50%; border: 4px solid var(--arc-gray-200); border-top-color: var(--arc-primary); animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

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

@media (max-width: 768px) {
    .arc-modal { max-width: 100%; max-height: 100vh; border-radius: 16px 16px 0 0; }
    .arc-modal-overlay { align-items: flex-end; padding: 0; }
    .arc-bul-table { font-size: .7rem; }
    .arc-bul-table th, .arc-bul-table td { padding: .35rem .3rem; }
}

.appr-pill { display: inline-block; padding: .15rem .5rem; border-radius: 20px; font-size: .72rem; font-weight: 600; }
.appr-TB  { background: #d1fae5; color: #065f46; }
.appr-B   { background: #dbeafe; color: #1e40af; }
.appr-AB  { background: #e0f2fe; color: #0369a1; }
.appr-P   { background: #fef9c3; color: #854d0e; }
.appr-I   { background: #fee2e2; color: #991b1b; }
.appr-F   { background: #fce7f3; color: #9d174d; }
.appr-M   { background: #f3e8ff; color: #6b21a8; }
.appr-TF  { background: #fff1f2; color: #9f1239; }

.decision-badge { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .85rem; border-radius: 20px; font-size: .8rem; font-weight: 700; }
.decision-felic   { background: #d1fae5; color: #065f46; }
.decision-encou   { background: #dbeafe; color: #1e40af; }
.decision-honneur { background: #fef3c7; color: #92400e; }
.decision-avert   { background: #fee2e2; color: #991b1b; }
.decision-none    { background: var(--arc-gray-100); color: var(--arc-gray-600); }

.annual-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem; }
@media (max-width: 600px) { .annual-row { grid-template-columns: 1fr 1fr; } }
.annual-card { border: 1px solid var(--arc-gray-200); border-radius: 10px; padding: .875rem; text-align: center; background: var(--arc-gray-50); }
.annual-card .t-val { font-size: 1.2rem; font-weight: 800; }
.annual-card .t-lbl { font-size: .72rem; color: var(--arc-gray-600); margin-top: .2rem; }

.scroll-x { overflow-x: auto; border-radius: var(--arc-radius); border: 1px solid var(--arc-gray-200); box-shadow: var(--arc-shadow); }

.row-action {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .65rem; border-radius: 6px; font-size: .75rem; font-weight: 600;
    border: none; cursor: pointer; transition: all .15s; text-decoration: none;
}
.row-action-blue { background: var(--arc-primary-ultra); color: var(--arc-primary); }
.row-action-blue:hover { background: var(--arc-primary); color: #fff; }
.row-action-green { background: var(--arc-success-light); color: var(--arc-success); }
.row-action-green:hover { background: var(--arc-success); color: #fff; }

/* note vide dans tableau matière */
.nd { color: #94a3b8; font-size: .75rem; }
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

            <button onclick="openMatiereModal()" class="arc-btn" style="background:#7c3aed;color:#fff">
                <i class="fas fa-table"></i>
                <span class="hidden sm:inline">Notes par matière</span>
            </button>

            <a href="{{ route('archives.show', $year->id) }}" class="arc-btn arc-btn-outline">
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

                        <td>
                            <div class="flex flex-wrap gap-1 justify-center" style="min-width:140px">
                                <button
                                    onclick="openStudentModal({{ $row['student']->id }}, '{{ addslashes($row['student']->last_name.' '.$row['student']->first_name) }}')"
                                    class="row-action row-action-blue">
                                    <i class="fas fa-eye"></i> Notes
                                </button>
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
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Effectif noté</span><span class="font-semibold">{{ $effec }}</span></div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Moyenne classe</span>
                        <span class="font-semibold {{ $moyC !== null && $moyC >= 10 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $moyC !== null ? number_format($moyC,2,',','') : '–' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm"><span class="text-gray-500">Admis (≥10)</span><span class="font-semibold text-green-700">{{ $admis }}</span></div>
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


{{-- Modal sécurité --}}
<div class="security-modal-overlay" id="securityModal">
    <div class="security-modal">
        <div class="security-content">
            <div class="security-icon"><i class="fas fa-shield-alt"></i></div>
            <h3>Accès restreint</h3>
            <p>Cette fonctionnalité de téléchargement des bulletins est exclusivement réservée à la Secrétaire et au Directeur de l'établissement.</p>
            <div class="security-roles">
                <p><i class="fas fa-user-check"></i> Rôles autorisés : Secrétaire & Directeur</p>
                <p class="mt-2" style="font-size:.75rem;color:#64748b"><i class="fas fa-key"></i> Contactez l'administrateur si vous avez besoin d'accéder à ces documents.</p>
            </div>
            <div class="security-buttons">
                <button onclick="closeSecurityModal()" class="security-btn security-btn-cancel"><i class="fas fa-times"></i> Fermer</button>
                <button onclick="closeSecurityModalAndRedirect()" class="security-btn security-btn-confirm"><i class="fas fa-home"></i> Retour à l'accueil</button>
            </div>
        </div>
    </div>
</div>


{{-- Modal élève --}}
<div class="arc-modal-overlay" id="studentModal" onclick="closeOnOverlay(event)">
    <div class="arc-modal" id="studentModalBox">
        <div class="arc-modal-header">
            <div>
                <h3 id="modalStudentName">Chargement…</h3>
                <p style="font-size:.82rem;opacity:.8;margin:.15rem 0 0" id="modalSubtitle">{{ $class->name }} · {{ $year->name }}</p>
            </div>
            <button class="arc-modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="arc-tabs" id="modalTabs">
            <button class="arc-tab-btn active" data-tab="annuel"><i class="fas fa-calendar mr-1"></i> Bilan annuel</button>
            <button class="arc-tab-btn" data-tab="t1">T1</button>
            <button class="arc-tab-btn" data-tab="t2">T2</button>
            <button class="arc-tab-btn" data-tab="t3">T3</button>
        </div>
        <div class="arc-modal-body" id="modalBody">
            <div class="arc-spinner" id="modalSpinner">
                <div class="arc-spinner-icon"></div>
                <span>Chargement des notes…</span>
            </div>
            <div id="modalContent" style="display:none"></div>
        </div>
        <div class="arc-download-bar" id="modalFooter" style="display:none">
            <span class="text-sm font-semibold text-gray-600 mr-auto"><i class="fas fa-download mr-1"></i> Télécharger le bulletin</span>
            <div class="trim-selector" id="downloadLinks"></div>
        </div>
    </div>
</div>


{{-- Modal par matière --}}
<div class="arc-modal-overlay" id="matiereModal" onclick="closeMatiereOnOverlay(event)">
    <div class="arc-modal" id="matiereModalBox" style="max-width:1200px">
        <div class="arc-modal-header" style="background:linear-gradient(135deg,#7c3aed,#6d28d9)">
            <div>
                <h3><i class="fas fa-table mr-2"></i>Notes par matière</h3>
                <p style="font-size:.82rem;opacity:.8;margin:.15rem 0 0">{{ $class->name }} · {{ $year->name }}</p>
            </div>
            <button class="arc-modal-close" onclick="closeMatiereModal()"><i class="fas fa-times"></i></button>
        </div>
        <div style="padding:.75rem 1.5rem;background:#faf5ff;border-bottom:1px solid #e9d5ff;display:flex;align-items:center;gap:1rem;flex-wrap:wrap">
            <label style="font-size:.85rem;font-weight:700;color:#6d28d9"><i class="fas fa-book mr-1"></i> Matière :</label>
            <select id="matiereSelect" onchange="changeMatiereView()"
                    style="padding:.45rem .9rem;border-radius:8px;border:1.5px solid #c4b5fd;font-size:.85rem;font-weight:600;color:#4c1d95;background:#fff;min-width:220px">
                <option value="">— Choisir une matière —</option>
            </select>
            <label style="font-size:.85rem;font-weight:700;color:#6d28d9"><i class="fas fa-calendar mr-1"></i> Trimestre :</label>
            <div style="display:flex;gap:.4rem" id="matiereTriSelector">
                @foreach(['Annuel' => 0, 'T1' => 1, 'T2' => 2, 'T3' => 3] as $label => $val)
                <button onclick="setMatiereTrimestre({{ $val }})" id="matTri-{{ $val }}"
                        class="arc-btn arc-btn-outline"
                        style="padding:.35rem .75rem;font-size:.78rem;{{ $val === 0 ? 'background:#7c3aed;color:#fff;border-color:#7c3aed' : '' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <div style="margin-left:auto;display:flex;gap:.5rem;flex-wrap:wrap" id="matierePdfButtons"></div>
        </div>
        <div class="arc-modal-body" id="matiereModalBody">
            <div class="arc-spinner" id="matiereSpinner">
                <div class="arc-spinner-icon" style="border-top-color:#7c3aed"></div>
                <span>Chargement des données…</span>
            </div>
            <div id="matiereContent" style="display:none"></div>
        </div>
    </div>
</div>

<div class="arc-toast" id="arcToast"></div>


<script>
/* ─── Config ──────────────────────────────────────────────── */
const YEAR_ID  = {{ $year->id }};
const CLASS_ID = {{ $class->id }};
const IS_RESTRICTED_USER = {{ $isRestrictedUser ? 'true' : 'false' }};
let pendingDownload = null;

const ROUTES = {
    studentNotes     : (sid)    => `/archives/${YEAR_ID}/classes/${CLASS_ID}/eleves/${sid}/notes`,
    bulletinPdf      : (sid, t) => `/archives/${YEAR_ID}/classes/${CLASS_ID}/eleves/${sid}/bulletin/${t}/pdf`,
    classBulletinsAll: (t)      => `/archives/${YEAR_ID}/classes/${CLASS_ID}/bulletin/${t}/all-pdf`,
};

let currentData      = null;
let currentStudentId = null;
let currentTab       = 'annuel';

/* ─── Accès restreint ─────────────────────────────────────── */
function checkAccessAndDownload(type, studentId = null, trimestre = null) {
    if (IS_RESTRICTED_USER) { pendingDownload = { type, studentId, trimestre }; showSecurityModal(); return; }
    executeDownload(type, studentId, trimestre);
}
function executeDownload(type, studentId, trimestre) {
    if (type === 'bulk' && trimestre) {
        window.location.href = ROUTES.classBulletinsAll(trimestre);
        showToast('Téléchargement des bulletins en cours…', 2000);
    } else if (type === 'single' && trimestre) {
        window.location.href = ROUTES.bulletinPdf(studentId, trimestre);
        showToast('Téléchargement du bulletin en cours…', 2000);
    } else if (type === 'single') {
        toggleDlMenu(studentId);
    }
}
function checkAccessAndDownloadBulk(t) {
    if (IS_RESTRICTED_USER) { pendingDownload = { type: 'bulk', trimestre: t }; showSecurityModal(); return; }
    window.location.href = ROUTES.classBulletinsAll(t);
    showToast('Téléchargement des bulletins de la classe en cours…', 2000);
}
function showSecurityModal() { document.getElementById('securityModal').classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeSecurityModal() { document.getElementById('securityModal').classList.remove('active'); document.body.style.overflow = ''; pendingDownload = null; }
function closeSecurityModalAndRedirect() { closeSecurityModal(); showToast('Accès refusé – Contactez l\'administration', 3000); }

/* ─── Utils ───────────────────────────────────────────────── */
function showToast(msg, ms = 3000) {
    const el = document.getElementById('arcToast');
    el.textContent = msg; el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), ms);
}
function getApprClass(appr) {
    const map = {'Très Bien':'TB','Bien':'B','Assez Bien':'AB','Passable':'P','Insuffisant':'I','Faible':'F','Médiocre':'M','Très Faible':'TF','-':'neutral'};
    return 'appr-' + (map[appr] || 'I');
}
function moyBadge(val) {
    if (!val || val === '–' || val === '-' || val === '0,00') return `<span class="moy-badge neutral">–</span>`;
    const n = parseFloat(String(val).replace(',', '.'));
    const cls = isNaN(n) ? 'neutral' : (n >= 10 ? 'good' : 'bad');
    return `<span class="moy-badge ${cls}">${val}</span>`;
}
/** Formate une note brute (nombre|null|undefined) → "12,50" ou "–" */
function fmtNote(v) {
    if (v === null || v === undefined) return '<span class="nd">–</span>';
    const n = parseFloat(v);
    return isNaN(n) ? '<span class="nd">–</span>' : n.toFixed(2).replace('.', ',');
}
/** Version sans span HTML pour les calculs */
function fmtRaw(v) {
    if (v === null || v === undefined) return '–';
    const n = parseFloat(v);
    return isNaN(n) ? '–' : n.toFixed(2).replace('.', ',');
}

/* ─── Modal open / close ──────────────────────────────────── */
function openStudentModal(studentId, name) {
    currentStudentId = studentId;
    currentData      = null;
    currentTab       = 'annuel';

    document.getElementById('modalStudentName').textContent = name;
    document.getElementById('modalSpinner').style.display   = 'flex';
    document.getElementById('modalContent').style.display   = 'none';
    document.getElementById('modalFooter').style.display    = 'none';

    document.querySelectorAll('.arc-tab-btn').forEach((b, i) => b.classList.toggle('active', i === 0));
    document.getElementById('studentModal').classList.add('active');
    document.body.style.overflow = 'hidden';

    fetch(ROUTES.studentNotes(studentId))
        .then(r => { if (!r.ok) throw new Error('Erreur réseau'); return r.json(); })
        .then(data => { currentData = data; renderModal(); })
        .catch(err => {
            document.getElementById('modalSpinner').innerHTML =
                `<div style="text-align:center;color:#dc2626"><i class="fas fa-exclamation-circle fa-2x mb-3"></i><p>${err.message}</p></div>`;
        });
}
function closeModal() { document.getElementById('studentModal').classList.remove('active'); document.body.style.overflow = ''; }
function closeOnOverlay(e) { if (e.target === document.getElementById('studentModal')) closeModal(); }

/* ─── Tabs ────────────────────────────────────────────────── */
document.querySelectorAll('.arc-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        document.querySelectorAll('.arc-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTab = tab;
        if (currentData) renderModal();
    });
});

/* ─── Render modal ────────────────────────────────────────── */
function renderModal() {
    document.getElementById('modalSpinner').style.display = 'none';
    document.getElementById('modalContent').style.display = 'block';
    document.getElementById('modalFooter').style.display  = 'flex';

    // Liens de téléchargement
    document.getElementById('downloadLinks').innerHTML = [1,2,3].map(t =>
        `<button onclick="checkAccessAndDownload('single',${currentStudentId},${t})"
            class="arc-btn arc-btn-primary" style="font-size:.8rem;padding:.4rem .85rem">
            <i class="fas fa-file-pdf"></i> T${t}
         </button>`
    ).join('');

    const content = document.getElementById('modalContent');
    content.innerHTML = currentTab === 'annuel'
        ? buildAnnualTab(currentData)
        : buildTrimestreTab(currentData, parseInt(currentTab.replace('t', '')));
}

/* ─── Tab annuel ──────────────────────────────────────────── */
function buildAnnualTab(data) {
    const r   = data.record;
    const moy = data.moy_annuelle;
    const moyFmt = moy !== null ? parseFloat(moy).toFixed(2).replace('.', ',') : '–';
    const isBig  = moy !== null && moy >= 10;

    let statutHtml = '';
    if (r.statut === 'passed') {
        statutHtml = `<div class="decision-badge decision-felic"><i class="fas fa-graduation-cap"></i> Admis(e)${r.rang_annuel ? ' · Rang : '+r.rang_annuel+'ᵉ/'+r.total_eleves : ''}</div>`;
    } else if (r.statut === 'repeated') {
        statutHtml = `<div class="decision-badge decision-avert"><i class="fas fa-redo"></i> Redoublant(e)</div>`;
    } else {
        statutHtml = `<div class="decision-badge decision-none"><i class="fas fa-clock"></i> Statut non défini</div>`;
    }

    const trimestreCards = [1,2,3].map(t => {
        const td = data.trimestres[t];
        const mTr = td.moyenneGenerale;
        const fmt = mTr !== null ? parseFloat(mTr).toFixed(2).replace('.', ',') : '–';
        const clr = mTr !== null ? (mTr >= 10 ? '#059669' : '#dc2626') : '#64748b';
        return `<div class="annual-card">
            <div class="t-val" style="color:${clr}">${fmt}</div>
            <div class="t-lbl">Trimestre ${t}</div>
            <div style="font-size:.7rem;color:#64748b;margin-top:.3rem">Rang : ${td.rang}${td.rang !== '-' ? 'ᵉ/'+td.totalEleves : ''}</div>
        </div>`;
    }).join('');

    return `
    <div>
        <div class="flex items-center gap-3 mb-4">${statutHtml}</div>
        <div class="arc-stat-grid" style="grid-template-columns:repeat(4,1fr)">
            <div class="arc-stat-card"><div class="val" style="color:${isBig?'#059669':'#dc2626'}">${moyFmt}</div><div class="lbl">Moy. annuelle</div></div>
            <div class="arc-stat-card"><div class="val">${r.rang_annuel !== '-' ? r.rang_annuel+'ᵉ' : '–'}</div><div class="lbl">Rang annuel</div></div>
            <div class="arc-stat-card"><div class="val">${r.total_eleves}</div><div class="lbl">Élèves classe</div></div>
            <div class="arc-stat-card"><div class="val" style="font-size:1rem">${isBig ? '✓ Admis' : '✗ Non admis'}</div><div class="lbl">Décision (≥10)</div></div>
        </div>
        <h4 style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:.75rem">
            <i class="fas fa-chart-line mr-1" style="color:var(--arc-primary)"></i> Résumé par trimestre
        </h4>
        <div class="annual-row">${trimestreCards}</div>
        <p style="font-size:.8rem;color:#64748b;text-align:center;margin-top:.5rem">
            Cliquez sur T1, T2 ou T3 pour voir le détail des notes par matière.
        </p>
    </div>`;
}

/* ─── Tab trimestre — colonnes I1-I5 + D1-D2 ─────────────── */
function buildTrimestreTab(data, t) {
    const td = data.trimestres[t];
    if (!td) return '<p class="text-gray-500 text-center py-8">Aucune donnée pour ce trimestre.</p>';

    const moyGen = td.moyenneGenerale;
    const moyFmt = moyGen !== null ? parseFloat(moyGen).toFixed(2).replace('.', ',') : '–';

    // Décision conseil
    let decision = `<span class="decision-badge decision-none"><i class="fas fa-minus"></i> –</span>`;
    if (moyGen !== null && td.conduite > 0) {
        if      (moyGen >= 16 && td.conduite >= 14) decision = `<span class="decision-badge decision-felic"><i class="fas fa-star"></i> Félicitations</span>`;
        else if (moyGen >= 14 && td.conduite >= 12) decision = `<span class="decision-badge decision-encou"><i class="fas fa-thumbs-up"></i> Encouragements</span>`;
        else if (moyGen >= 12 && td.conduite >= 10) decision = `<span class="decision-badge decision-honneur"><i class="fas fa-medal"></i> Tableau d'honneur</span>`;
        else if (moyGen < 10  || td.conduite < 8)  decision = `<span class="decision-badge decision-avert"><i class="fas fa-exclamation-triangle"></i> Avertissement</span>`;
    }

    // Lignes
    const rows = td.bulletin.map(b => {
        const interros = b.interros || {};
        // I1 à I5 — clés 1..5 (renvoyés depuis le contrôleur)
        const iCells = [1,2,3,4,5].map(k => `<td>${fmtNote(interros[k])}</td>`).join('');
        const miCell  = `<td style="font-weight:600">${fmtNote(b.moyInterro)}</td>`;
        const d1Cell  = `<td>${fmtNote(b.devoir1)}</td>`;
        const d2Cell  = `<td>${fmtNote(b.devoir2)}</td>`;

        const moy = b.moyenne;
        const mFmt = moy !== null ? parseFloat(moy).toFixed(2).replace('.', ',') : '–';

        return `<tr>
            <td class="left">${b.subject}</td>
            <td style="text-align:center">${b.coef}</td>
            ${iCells}
            ${miCell}
            ${d1Cell}
            ${d2Cell}
            <td style="text-align:center">${moyBadge(mFmt)}</td>
            <td style="text-align:center"><span class="appr-pill ${getApprClass(b.appreciation)}">${b.appreciation}</span></td>
        </tr>`;
    }).join('');

    const condFmt      = parseFloat(td.conduite).toFixed(2).replace('.', ',');
    const totalEleves  = td.totalEleves || '?';

    return `
    <div>
        <div class="arc-stat-grid" style="margin-bottom:1.25rem">
            <div class="arc-stat-card">
                <div class="val" style="color:${moyGen!==null&&moyGen>=10?'#059669':'#dc2626'}">${moyFmt}</div>
                <div class="lbl">Moy. générale</div>
            </div>
            <div class="arc-stat-card">
                <div class="val">${td.rang!=='-' ? td.rang+'ᵉ/'+totalEleves : '–'}</div>
                <div class="lbl">Rang</div>
            </div>
            <div class="arc-stat-card">
                <div class="val" style="color:${td.conduite>=10?'#059669':'#dc2626'}">${condFmt}</div>
                <div class="lbl">Conduite</div>
            </div>
            <div class="arc-stat-card" style="min-width:0">${decision}</div>
        </div>

        <div style="overflow-x:auto">
            <table class="arc-bul-table" style="min-width:780px">
                <thead>
                    <tr>
                        <th class="left" rowspan="2" style="min-width:130px">Matière</th>
                        <th rowspan="2">Coef.</th>
                        <th colspan="5" class="th-interro">Interrogations</th>
                        <th rowspan="2" class="th-interro">Moy.I</th>
                        <th colspan="2" class="th-devoir">Devoirs</th>
                        <th rowspan="2" class="th-moy">Moy./20</th>
                        <th rowspan="2" class="th-appr">Appréciation</th>
                    </tr>
                    <tr>
                        <th class="th-interro">I1</th>
                        <th class="th-interro">I2</th>
                        <th class="th-interro">I3</th>
                        <th class="th-interro">I4</th>
                        <th class="th-interro">I5</th>
                        <th class="th-devoir">D1</th>
                        <th class="th-devoir">D2</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
                <tfoot>
                    <tr>
                        <td colspan="9" style="text-align:right;border-radius:0 0 0 8px">
                            Moyenne générale — Trimestre ${t}
                        </td>
                        <td style="text-align:center;font-size:1rem">${moyFmt}</td>
                        <td style="text-align:center;border-radius:0 0 8px 0">
                            <span class="appr-pill ${getApprClass(td.appreciation)}">${td.appreciation}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>`;
}

/* ─── Dropdown menus ──────────────────────────────────────── */
function toggleBulkMenu() {
    document.getElementById('bulk-menu')?.classList.toggle('hidden');
}
function toggleDlMenu(id) {
    document.querySelectorAll('[id^="dl-menu-"]').forEach(el => { if (el.id !== `dl-menu-${id}`) el.classList.add('hidden'); });
    document.getElementById(`dl-menu-${id}`)?.classList.toggle('hidden');
}
document.addEventListener('click', e => {
    if (!e.target.closest('#bulk-download-wrapper')) document.getElementById('bulk-menu')?.classList.add('hidden');
    if (!e.target.closest('[id^="dl-wrap-"]')) document.querySelectorAll('[id^="dl-menu-"]').forEach(el => el.classList.add('hidden'));
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(); closeSecurityModal(); closeMatiereModal(); }
});


/* ═══════════════════════════════════════════════════════════
   MODAL PAR MATIÈRE — colonnes I1-I5 + D1-D2
   ═══════════════════════════════════════════════════════════ */
const MATIERE_ROUTES = {
    json: ()          => `/archives/${YEAR_ID}/classes/${CLASS_ID}/notes-matiere/json`,
    pdf : (sid, t)    => `/archives/${YEAR_ID}/classes/${CLASS_ID}/notes-matiere/${sid}/trimestre/${t}/pdf`,
};

let matiereData       = null;
let currentMatiereIdx = null;
let currentMatiereTri = 0;

function openMatiereModal() {
    document.getElementById('matiereModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    if (matiereData) { populateMatiereSelect(); renderMatiereTable(); return; }

    document.getElementById('matiereSpinner').style.display = 'flex';
    document.getElementById('matiereContent').style.display = 'none';

    fetch(MATIERE_ROUTES.json())
        .then(r => { if (!r.ok) throw new Error('Erreur réseau'); return r.json(); })
        .then(data => { matiereData = data; populateMatiereSelect(); renderMatiereTable(); })
        .catch(err => {
            document.getElementById('matiereSpinner').innerHTML =
                `<div style="text-align:center;color:#dc2626"><i class="fas fa-exclamation-circle fa-2x mb-3"></i><p>${err.message}</p></div>`;
        });
}
function closeMatiereModal() { document.getElementById('matiereModal').classList.remove('active'); document.body.style.overflow = ''; }
function closeMatiereOnOverlay(e) { if (e.target === document.getElementById('matiereModal')) closeMatiereModal(); }

function populateMatiereSelect() {
    const sel = document.getElementById('matiereSelect');
    sel.innerHTML = '<option value="">— Toutes les matières —</option>';
    (matiereData.subjects || []).forEach((s, i) => {
        const opt = document.createElement('option');
        opt.value = i; opt.textContent = `${s.name} (coef. ${s.coef})`;
        sel.appendChild(opt);
    });
    currentMatiereIdx = null;
}
function changeMatiereView() {
    const val = document.getElementById('matiereSelect').value;
    currentMatiereIdx = val === '' ? null : parseInt(val);
    renderMatiereTable(); updateMatierePdfButtons();
}
function setMatiereTrimestre(t) {
    currentMatiereTri = t;
    document.querySelectorAll('[id^="matTri-"]').forEach(btn => {
        const active = btn.id === `matTri-${t}`;
        btn.style.background  = active ? '#7c3aed' : '#fff';
        btn.style.color       = active ? '#fff' : '#475569';
        btn.style.borderColor = active ? '#7c3aed' : '#e2e8f0';
    });
    renderMatiereTable(); updateMatierePdfButtons();
}
function updateMatierePdfButtons() {
    const wrap = document.getElementById('matierePdfButtons');
    if (!matiereData || currentMatiereIdx === null) { wrap.innerHTML = ''; return; }
    const subj   = matiereData.subjects[currentMatiereIdx];
    const tLabel = currentMatiereTri === 0 ? 'Annuel' : `T${currentMatiereTri}`;
    wrap.innerHTML = `<a href="${MATIERE_ROUTES.pdf(subj.id, currentMatiereTri)}"
           class="arc-btn" style="background:#7c3aed;color:#fff;font-size:.8rem;padding:.4rem .9rem" target="_blank">
        <i class="fas fa-file-pdf"></i> PDF – ${subj.name} (${tLabel})
    </a>`;
}

/* ─── Render table matière ──────────────────────────────── */
function renderMatiereTable() {
    if (!matiereData) return;
    document.getElementById('matiereSpinner').style.display = 'none';
    document.getElementById('matiereContent').style.display = 'block';

    const subjects = currentMatiereIdx !== null
        ? [matiereData.subjects[currentMatiereIdx]]
        : matiereData.subjects;

    if (!subjects || subjects.length === 0) {
        document.getElementById('matiereContent').innerHTML =
            '<p style="text-align:center;color:#64748b;padding:2rem">Aucune matière disponible.</p>';
        return;
    }

    const t   = currentMatiereTri;
    let html  = '';

    subjects.forEach(subject => {
        // En-tête matière
        html += `
        <div style="margin-bottom:2rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem;flex-wrap:wrap;gap:.5rem">
                <div style="display:flex;align-items:center;gap:.75rem">
                    <div style="width:36px;height:36px;border-radius:8px;background:#f5f3ff;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-book" style="color:#7c3aed;font-size:.9rem"></i>
                    </div>
                    <div>
                        <div style="font-size:.95rem;font-weight:800;color:#1e293b">${subject.name}</div>
                        <div style="font-size:.75rem;color:#64748b">Coefficient : ${subject.coef}</div>
                    </div>
                </div>
                <a href="${MATIERE_ROUTES.pdf(subject.id, t)}"
                   class="arc-btn" style="background:#7c3aed;color:#fff;font-size:.78rem;padding:.35rem .8rem" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF ${t === 0 ? 'annuel' : 'T'+t}
                </a>
            </div>`;

        // Stats rapides
        const moyennes = subject.eleves.map(e => {
            if (t === 0) {
                const ms = [1,2,3].map(x => e.trimestres[x]?.moyenne).filter(v => v !== null);
                return ms.length ? ms.reduce((a,b)=>a+b,0)/ms.length : null;
            }
            return e.trimestres[t]?.moyenne ?? null;
        }).filter(v => v !== null);

        const moyClass = moyennes.length ? (moyennes.reduce((a,b)=>a+b,0)/moyennes.length).toFixed(2).replace('.',',') : '–';
        const admis    = moyennes.filter(m => m >= 10).length;
        const taux     = moyennes.length ? Math.round(admis/moyennes.length*100) : 0;

        html += `<div style="display:flex;gap:.75rem;margin-bottom:.75rem;flex-wrap:wrap">
            ${statPill('Effectif noté', moyennes.length, '#1e40af')}
            ${statPill('Moy. classe', moyClass, '#059669')}
            ${statPill('Admis', admis, '#059669')}
            ${statPill('Taux', taux+'%', taux>=60?'#059669':'#dc2626')}
        </div>`;

        /* ── Vue annuelle ── */
        if (t === 0) {
            // Calcul rangs annuels
            const moyAnnEleves = subject.eleves.map(e => {
                const ms = [1,2,3].map(x => e.trimestres[x]?.moyenne).filter(v=>v!==null);
                return { id: e.id, moy: ms.length ? ms.reduce((a,b)=>a+b,0)/ms.length : null };
            });
            const sorted = [...moyAnnEleves].filter(e=>e.moy!==null).sort((a,b)=>b.moy-a.moy);
            const rangs = {}; sorted.forEach((e,i) => rangs[e.id] = i+1);

            html += `<div style="overflow-x:auto;border-radius:8px;border:1px solid #e9d5ff">
            <table style="width:100%;border-collapse:collapse;font-size:.8rem">
                <thead><tr style="background:#6d28d9;color:#fff">
                    <th style="padding:.5rem;border:1px solid #5b21b6;width:30px">#</th>
                    <th style="padding:.5rem;text-align:left;border:1px solid #5b21b6;min-width:150px">Nom & Prénoms</th>
                    <th style="padding:.5rem;border:1px solid #5b21b6">Moy. T1</th>
                    <th style="padding:.5rem;border:1px solid #5b21b6">Moy. T2</th>
                    <th style="padding:.5rem;border:1px solid #5b21b6">Moy. T3</th>
                    <th style="padding:.5rem;border:1px solid #5b21b6">Moy. Ann.</th>
                    <th style="padding:.5rem;border:1px solid #5b21b6">Rang</th>
                </tr></thead>
                <tbody>`;

            subject.eleves.forEach((e, i) => {
                const ms = [1,2,3].map(x => e.trimestres[x]?.moyenne).filter(v=>v!==null);
                const moyAnn = ms.length ? ms.reduce((a,b)=>a+b,0)/ms.length : null;
                const badge = (v) => {
                    if (v===null) return `<span class="nd">–</span>`;
                    const vf = parseFloat(v); const c = vf>=10?'#059669':'#dc2626'; const bg = vf>=10?'#d1fae5':'#fee2e2';
                    return `<span style="padding:1px 8px;border-radius:10px;background:${bg};color:${c};font-weight:700">${vf.toFixed(2).replace('.',',')}</span>`;
                };
                html += `<tr style="background:${i%2===0?'#fff':'#faf5ff'}">
                    <td style="text-align:center;border:1px solid #e9d5ff;color:#94a3b8">${i+1}</td>
                    <td style="text-align:left;border:1px solid #e9d5ff;padding:.4rem .6rem;font-weight:600">${e.nom} ${e.prenom}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff">${badge(e.trimestres[1]?.moyenne??null)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff">${badge(e.trimestres[2]?.moyenne??null)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff">${badge(e.trimestres[3]?.moyenne??null)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff">${badge(moyAnn)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff;color:#6d28d9;font-weight:700">${rangs[e.id] ? rangs[e.id]+'ᵉ' : '–'}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;

        /* ── Vue trimestrielle : I1-I5 + D1-D2 ── */
        } else {
            html += `<div style="overflow-x:auto;border-radius:8px;border:1px solid #e9d5ff">
            <table style="width:100%;border-collapse:collapse;font-size:.78rem">
                <thead>
                    <tr style="background:#6d28d9;color:#fff">
                        <th rowspan="2" style="padding:.5rem;border:1px solid #5b21b6;width:28px">#</th>
                        <th rowspan="2" style="padding:.5rem;text-align:left;border:1px solid #5b21b6;min-width:140px">Nom & Prénoms</th>
                        <th colspan="5" style="padding:.4rem;border:1px solid #5b21b6;background:#1d4ed8">Interrogations</th>
                        <th rowspan="2" style="padding:.5rem;border:1px solid #5b21b6;background:#1d4ed8">Moy.I</th>
                        <th colspan="2" style="padding:.4rem;border:1px solid #5b21b6;background:#065f46">Devoirs</th>
                        <th rowspan="2" style="padding:.5rem;border:1px solid #5b21b6;background:#7c3aed">Moy./20</th>
                        <th rowspan="2" style="padding:.5rem;border:1px solid #5b21b6">Rang</th>
                    </tr>
                    <tr style="background:#5b21b6">
                        ${[1,2,3,4,5].map(k=>`<th style="padding:.35rem .4rem;border:1px solid #4c1d95;background:#1d4ed8">I${k}</th>`).join('')}
                        ${[1,2].map(k=>`<th style="padding:.35rem .4rem;border:1px solid #4c1d95;background:#065f46">D${k}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>`;

            subject.eleves.forEach((e, i) => {
                const td  = e.trimestres[t] || {};
                const interros = td.interros || {};

                const badge = (v) => {
                    if (v===null||v===undefined) return `<span class="nd">–</span>`;
                    const n = parseFloat(v); const c = n>=10?'#059669':'#dc2626'; const bg = n>=10?'#d1fae5':'#fee2e2';
                    return `<span style="padding:1px 7px;border-radius:10px;background:${bg};color:${c};font-weight:700">${n.toFixed(2).replace('.',',')}</span>`;
                };

                const iCells = [1,2,3,4,5].map(k =>
                    `<td style="text-align:center;border:1px solid #e9d5ff">${fmtNote(interros[k])}</td>`
                ).join('');

                html += `<tr style="background:${i%2===0?'#fff':'#faf5ff'}">
                    <td style="text-align:center;border:1px solid #e9d5ff;color:#94a3b8">${i+1}</td>
                    <td style="text-align:left;border:1px solid #e9d5ff;padding:.4rem .6rem;font-weight:600">${e.nom} ${e.prenom}</td>
                    ${iCells}
                    <td style="text-align:center;border:1px solid #e9d5ff;font-weight:600">${fmtNote(td.moyInterro)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff">${fmtNote(td.devoir1)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff">${fmtNote(td.devoir2)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff">${badge(td.moyenne)}</td>
                    <td style="text-align:center;border:1px solid #e9d5ff;color:#6d28d9;font-weight:700">${td.rang ? td.rang+'ᵉ' : '–'}</td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
        }

        html += `</div>`; // fin du bloc matière
    });

    document.getElementById('matiereContent').innerHTML = html;
}

function statPill(label, val, color) {
    return `<div style="display:inline-flex;flex-direction:column;align-items:center;padding:.4rem .8rem;
                border-radius:8px;background:#faf5ff;border:1px solid #e9d5ff;min-width:70px">
        <span style="font-size:1rem;font-weight:800;color:${color}">${val}</span>
        <span style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em">${label}</span>
    </div>`;
}
</script>
@endsection
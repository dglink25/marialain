<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletins Fin d'Année — {{ $classe->name }}</title>
    <style>
        /* ── Base ──────────────────────────────────────────────────── */
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #e5e7eb;
        }

        /* ── Barre d'outils (masquée à l'impression) ──────────────── */
        #toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.4);
        }
        #toolbar .title { flex: 1; font-size: 13px; font-weight: bold; }
        #toolbar .count { background: #0ea5e9; border-radius: 99px; padding: 2px 10px; font-size: 11px; }

        /* Barre de progression */
        #progress-wrap {
            flex: 2;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #progress-bar-bg {
            flex: 1;
            height: 8px;
            background: #334155;
            border-radius: 99px;
            overflow: hidden;
        }
        #progress-bar {
            height: 100%;
            width: 0%;
            background: #22c55e;
            border-radius: 99px;
            transition: width .3s ease;
        }
        #progress-label { font-size: 11px; white-space: nowrap; min-width: 80px; text-align: right; }

        /* Boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-back  { background: #475569; color: #fff; }
        .btn-back:hover  { background: #334155; }
        .btn-print { background: #059669; color: #fff; }
        .btn-print:hover { background: #047857; }
        .btn-print:disabled { background: #6b7280; cursor: not-allowed; }

        /* ── Zone de contenu ─────────────────────────────────────── */
        #content {
            padding-top: 62px; /* espace sous toolbar */
        }

        /* ── Écran de chargement ─────────────────────────────────── */
        #loading-screen {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            gap: 16px;
            color: #374151;
        }
        #loading-screen .spinner {
            width: 48px; height: 48px;
            border: 5px solid #d1d5db;
            border-top-color: #059669;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        #loading-screen p { font-size: 14px; font-family: Arial, sans-serif; }

        /* ── Message d'erreur partielle ──────────────────────────── */
        .error-bulletin {
            width: 210mm;
            margin: 10px auto;
            padding: 16px;
            background: #fee2e2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            color: #b91c1c;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        /* ── Page bulletin ────────────────────────────────────────── */
        .bulletin-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 16px auto;
            padding: 10mm;
            border: 1px solid #d1d5db;
            page-break-after: always;
        }
        .bulletin-page:last-child { page-break-after: avoid; }

        /* ── En-tête ─────────────────────────────────────────────── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-left  { width: 40%; text-align: left; font-size: 9px; vertical-align: top; border: none; }
        .header-center{ width: 20%; text-align: center; vertical-align: middle; border: none; }
        .header-right { width: 40%; text-align: right; font-size: 9px; vertical-align: top; border: none; }
        .logo { width: 70px; }
        .school-name { font-size: 14px; font-weight: bold; }
        .title { text-align:center; font-size:16px; font-weight:bold; text-decoration:underline; margin:8px 0 4px; }
        .subtitle { text-align:center; font-size:9px; color:#444; margin-bottom:8px; font-style:italic; }

        /* ── Infos élève ──────────────────────────────────────────── */
        .info-section { width:100%; margin-bottom:8px; overflow:hidden; }
        .student-box  { float:left; width:70%; line-height:1.6; }
        .qr-box       { float:right; width:80px; text-align:right; }
        .qr-code      { width:60px; height:60px; border:1px solid #ccc; }
        .clear        { clear:both; }

        /* ── Bande trimestres ─────────────────────────────────────── */
        .trimestre-band { width:100%; border-collapse:collapse; margin-bottom:8px; font-size:9px; }
        .trimestre-band th, .trimestre-band td { border:1px solid #000; padding:3px 5px; text-align:center; }
        .trimestre-band th { background-color:#d0d8f0; }
        .t-label { background-color:#e8eaf6; font-weight:bold; }
        .t-ann   { background-color:#c8e6c9; font-weight:bold; }

        /* ── Tableau notes ────────────────────────────────────────── */
        table.notes { width:100%; border-collapse:collapse; margin-top:6px; }
        table.notes th, table.notes td { border:1px solid #000; padding:3px 4px; text-align:center; }
        table.notes th { background-color:#f2f2f2; font-size:9px; }
        .subject-name { text-align:left; font-weight:bold; padding-left:5px; }

        /* ── Domaines ─────────────────────────────────────────────── */
        .domain-averages { display:table; width:100%; margin:4px 0; font-size:9px; border-bottom:1px dashed #000; padding-bottom:3px; }
        .domain-item { display:table-cell; padding:2px 3px; }

        /* ── Résumé ──────────────────────────────────────────────── */
        .summary-wrapper { width:100%; margin-top:8px; display:table; }
        .summary-box { display:table-cell; width:32%; border:1px solid #000; vertical-align:top; }
        .summary-box-header { background-color:#e0e0e0; font-weight:bold; text-align:center; padding:3px; border-bottom:1px solid #000; font-size:9px; }
        .summary-content { padding:4px 6px; line-height:1.5; font-size:9px; }

        /* ── Signatures ──────────────────────────────────────────── */
        .footer-table { width:100%; margin-top:18px; border-collapse:collapse; }
        .footer-table td { border:none; }
        .mention-frame { border:2px solid #000; padding:5px 10px; font-weight:bold; display:inline-block; margin-top:6px; font-size:10px; }
        .motto { text-align:center; margin-top:20px; font-style:italic; border-top:1px solid #000; padding-top:4px; font-size:9px; }

        /* ── Règles d'impression ─────────────────────────────────── */
        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            body { background: #fff; }
            #toolbar, #loading-screen { display: none !important; }
            #content { padding-top: 0; }
            .bulletin-page {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 0;
                border: none;
                page-break-after: always;
            }
            .bulletin-page:last-child { page-break-after: avoid; }
        }
    </style>
</head>
<body>

{{-- ── Barre d'outils ──────────────────────────────────────────── --}}
<div id="toolbar">
    <div class="title">
        &#128438; Bulletins Fin d'Année &mdash; {{ $classe->name }}
        <span class="count">{{ count($studentIds) }} élève(s)</span>
    </div>

    <div id="progress-wrap">
        <div id="progress-bar-bg"><div id="progress-bar"></div></div>
        <div id="progress-label">En attente…</div>
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-back">&#8592; Retour</a>

    <button id="btn-print" class="btn btn-print" onclick="window.print()" disabled>
        &#128424; Imprimer
    </button>
</div>

{{-- ── Zone de contenu ─────────────────────────────────────────── --}}
<div id="content">
    <div id="loading-screen">
        <div class="spinner"></div>
        <p id="loading-msg">Chargement des bulletins…</p>
    </div>
    <div id="bulletins-container"></div>
</div>

<script>
(function () {
    const studentIds  = @json($studentIds);
    const urlTemplate = '{{ $bulletinUrl }}';
    const total       = studentIds.length;

    const container    = document.getElementById('bulletins-container');
    const progressBar  = document.getElementById('progress-bar');
    const progressLbl  = document.getElementById('progress-label');
    const loadingScr   = document.getElementById('loading-screen');
    const loadingMsg   = document.getElementById('loading-msg');
    const btnPrint     = document.getElementById('btn-print');

    let loaded = 0;

    // Charge les bulletins séquentiellement pour ne pas surcharger le serveur
    async function loadNext(index) {
        if (index >= total) {
            // Tout est chargé
            loadingScr.style.display = 'none';
            btnPrint.disabled = false;
            progressLbl.textContent = total + '/' + total + ' ✓';
            return;
        }

        const id  = studentIds[index];
        const url = urlTemplate.replace('__ID__', id);

        loadingMsg.textContent = 'Chargement bulletin ' + (index + 1) + ' / ' + total + '…';

        try {
            const resp = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const html = await resp.text();
            const div  = document.createElement('div');
            div.innerHTML = html;
            container.appendChild(div.firstElementChild || div);

        } catch (e) {
            const err = document.createElement('div');
            err.className = 'error-bulletin';
            err.textContent = 'Erreur élève #' + id + ' : ' + e.message;
            container.appendChild(err);
        }

        loaded++;
        const pct = Math.round((loaded / total) * 100);
        progressBar.style.width = pct + '%';
        progressLbl.textContent = loaded + '/' + total;

        // Petit délai pour laisser le navigateur respirer entre chaque requête
        setTimeout(() => loadNext(index + 1), 80);
    }

    if (total === 0) {
        loadingScr.innerHTML = '<p style="font-family:Arial;font-size:14px;color:#6b7280;">Aucun élève validé dans cette classe.</p>';
    } else {
        loadNext(0);
    }
})();
</script>

</body>
</html>

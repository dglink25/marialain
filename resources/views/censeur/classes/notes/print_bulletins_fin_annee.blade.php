<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletins Fin d'Année - {{ $classe->name }}</title>
    <style>
        /* ════════════════════════════════════════════════════════════
           STYLES IDENTIQUES AU PDF (all_bulletins_fin_annee_pdf)
           ════════════════════════════════════════════════════════════ */
        * { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #d1d5db;
        }

        /* ── Page bulletin ─────────────────────────────────────────
           Pas de min-height : le contenu détermine la hauteur,
           exactement comme DomPDF qui ne force pas de hauteur.       */
        .bulletin-page {
            width: 210mm;
            background: #fff;
            margin: 12px auto;
            padding: 10mm;
            page-break-after: always;
        }
        .bulletin-page:last-child { page-break-after: avoid; }

        .container { width: 100%; }

        /* ── En-tête ──────────────────────────────────────────────── */
        .header-table { width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; }
        .header-left  { width: 40%; text-align: left;   font-size: 9px; vertical-align: top; border: none; }
        .header-center{ width: 20%; text-align: center; vertical-align: middle; border: none; }
        .header-right { width: 40%; text-align: right;  font-size: 9px; vertical-align: top; border: none; }
        .logo { width: 70px; }
        .school-name { font-size: 14px; font-weight: bold; }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 8px 0 4px;
        }
        .subtitle {
            text-align: center;
            font-size: 9px;
            color: #444;
            margin-bottom: 8px;
            font-style: italic;
        }

        /* ── Infos élève ─────────────────────────────────────────── */
        .info-section { width: 100%; margin-bottom: 8px; overflow: hidden; }
        .student-box  { float: left; width: 70%; line-height: 1.6; }
        .qr-box       { float: right; width: 80px; text-align: right; }
        .qr-code      { width: 60px; height: 60px; border: 1px solid #ccc; }
        .clear        { clear: both; }

        /* ── Bande récap trimestriel ──────────────────────────────── */
        .trimestre-band { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 9px; }
        .trimestre-band th, .trimestre-band td { border: 1px solid #000; padding: 3px 5px; text-align: center; }
        .trimestre-band th { background-color: #d0d8f0; }
        .t-label { background-color: #e8eaf6; font-weight: bold; }
        .t-ann   { background-color: #c8e6c9; font-weight: bold; }

        /* ── Tableau des notes (sélecteur générique = identique PDF) */
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #000; padding: 3px 4px; text-align: center; }
        th { background-color: #f2f2f2; font-size: 9px; }
        .subject-name { text-align: left; font-weight: bold; padding-left: 5px; }

        /* ── Domaines ────────────────────────────────────────────── */
        .domain-averages {
            display: table;
            width: 100%;
            margin: 4px 0;
            font-size: 9px;
            border-bottom: 1px dashed #000;
            padding-bottom: 3px;
        }
        .domain-item { display: table-cell; padding: 2px 3px; }

        /* ── Blocs résumé ────────────────────────────────────────── */
        .summary-wrapper    { width: 100%; margin-top: 8px; display: table; }
        .summary-box        { display: table-cell; width: 32%; border: 1px solid #000; vertical-align: top; }
        .summary-box-header {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 3px;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }
        .summary-content { padding: 4px 6px; line-height: 1.5; font-size: 9px; }

        /* ── Signatures ──────────────────────────────────────────── */
        .footer-table { width: 100%; margin-top: 18px; }
        .mention-frame {
            border: 2px solid #000;
            padding: 5px 10px;
            font-weight: bold;
            display: inline-block;
            margin-top: 6px;
            font-size: 10px;
        }
        .motto {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-size: 9px;
        }

        /* ════════════════════════════════════════════════════════════
           BARRE D'OUTILS — masquée à l'impression
           ════════════════════════════════════════════════════════════ */
        #toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 9999;
            background: #1e293b;
            color: #fff;
            padding: 9px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.5);
            font-family: Arial, sans-serif;
        }
        #toolbar .tb-title { flex: 1; font-size: 13px; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        #toolbar .tb-count { background: #0ea5e9; border-radius: 99px; padding: 2px 10px; font-size: 11px; white-space: nowrap; }
        #progress-wrap { flex: 2; display: flex; align-items: center; gap: 8px; min-width: 160px; }
        #progress-bar-bg { flex: 1; height: 7px; background: #334155; border-radius: 99px; overflow: hidden; }
        #progress-bar { height: 100%; width: 0%; background: #22c55e; border-radius: 99px; transition: width .25s ease; }
        #progress-label { font-size: 11px; white-space: nowrap; min-width: 70px; text-align: right; }
        .tb-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 14px; border: none; border-radius: 7px;
            font-size: 12px; font-weight: bold; cursor: pointer;
            text-decoration: none; white-space: nowrap;
            transition: background .15s;
        }
        .tb-btn-back  { background: #475569; color: #fff; }
        .tb-btn-back:hover  { background: #334155; }
        .tb-btn-print { background: #059669; color: #fff; }
        .tb-btn-print:hover { background: #047857; }
        .tb-btn-print:disabled { background: #6b7280; cursor: not-allowed; opacity: .7; }

        /* ── Spinner de chargement ───────────────────────────────── */
        #loading-screen {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            min-height: 50vh; gap: 14px; font-family: Arial, sans-serif;
        }
        .spinner {
            width: 44px; height: 44px;
            border: 5px solid #d1d5db;
            border-top-color: #059669;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        #loading-msg { font-size: 13px; color: #374151; }

        /* ── Espace sous toolbar ─────────────────────────────────── */
        #content { padding-top: 58px; }

        /* ════════════════════════════════════════════════════════════
           IMPRESSION — on supprime tout sauf les bulletins
           ════════════════════════════════════════════════════════════ */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body { background: #fff; }

            /* Masquer toolbar et écran de chargement */
            #toolbar,
            #loading-screen { display: none !important; }

            /* Supprimer l'espace réservé au toolbar */
            #content { padding-top: 0 !important; }

            /* Chaque bulletin = une page, avec le même padding que le PDF */
            .bulletin-page {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;   /* @page margin: 10mm gère les marges */
                border: none !important;
                background: #fff !important;
                page-break-after: always;
                break-after: page;
            }
            .bulletin-page:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }

            /* Forcer l'impression des couleurs de fond */
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .trimestre-band th,
            .t-label, .t-ann,
            .summary-box-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

{{-- ── Barre d'outils ─────────────────────────────────────────── --}}
<div id="toolbar">
    <div class="tb-title">
        Bulletins Fin d'Année {{ $classe->name }}
        <span class="tb-count">{{ count($studentIds) }} élève(s)</span>
    </div>

    <div id="progress-wrap">
        <div id="progress-bar-bg"><div id="progress-bar"></div></div>
        <div id="progress-label">En attente…</div>
    </div>

    <a href="{{ url()->previous() }}" class="tb-btn tb-btn-back">&#8592; Retour</a>

    <button id="btn-print" class="tb-btn tb-btn-print" onclick="window.print()" disabled>
        &#128424; Imprimer
    </button>
</div>

{{-- ── Zone de contenu ─────────────────────────────────────────── --}}
<div id="content">
    <div id="loading-screen">
        <div class="spinner"></div>
        <p id="loading-msg">Préparation des bulletins…</p>
    </div>
    <div id="bulletins-container"></div>
</div>

<script>
(function () {
    const studentIds  = @json($studentIds);
    const urlTemplate = '{{ $bulletinUrl }}';
    const total       = studentIds.length;

    const container  = document.getElementById('bulletins-container');
    const bar        = document.getElementById('progress-bar');
    const lbl        = document.getElementById('progress-label');
    const screen     = document.getElementById('loading-screen');
    const msg        = document.getElementById('loading-msg');
    const btnPrint   = document.getElementById('btn-print');

    async function loadNext(i) {
        if (i >= total) {
            screen.style.display = 'none';
            btnPrint.disabled = false;
            lbl.textContent = total + '/' + total + ' ✓';
            return;
        }

        const url = urlTemplate.replace('__ID__', studentIds[i]);
        msg.textContent = 'Chargement ' + (i + 1) + ' / ' + total + '…';

        try {
            const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const html = await resp.text();
            // Injecter directement — le fragment commence par <div class="bulletin-page">
            container.insertAdjacentHTML('beforeend', html);
        } catch (e) {
            container.insertAdjacentHTML('beforeend',
                '<div style="width:210mm;margin:10px auto;padding:12px;background:#fee2e2;border:1px solid #fca5a5;font-family:Arial;font-size:11px;color:#b91c1c;">'
                + 'Erreur élève #' + studentIds[i] + ' : ' + e.message + '</div>'
            );
        }

        bar.style.width = Math.round(((i + 1) / total) * 100) + '%';
        lbl.textContent = (i + 1) + '/' + total;

        // 60 ms entre chaque requête pour ne pas saturer le serveur
        setTimeout(() => loadNext(i + 1), 60);
    }

    if (total === 0) {
        screen.innerHTML = '<p style="font-family:Arial;font-size:13px;color:#6b7280;">Aucun élève validé dans cette classe.</p>';
    } else {
        loadNext(0);
    }
})();
</script>

</body>
</html>

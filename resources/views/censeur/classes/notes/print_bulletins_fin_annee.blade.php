<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletins Fin d'Année — {{ $classe->name }}</title>
    <style>
        /* ── Reset & Base ─────────────────────────────────────────── */
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #f0f0f0;
        }

        /* ── Barre d'outils (masquée à l'impression) ─────────────── */
        .no-print {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .no-print .toolbar-title {
            font-size: 14px;
            font-weight: bold;
            flex: 1;
        }
        .no-print .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            background: #059669;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .no-print .btn-print:hover { background: #047857; }
        .no-print .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #475569;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .no-print .btn-back:hover { background: #334155; }
        .no-print .badge-count {
            background: #0ea5e9;
            color: #fff;
            border-radius: 99px;
            padding: 2px 10px;
            font-size: 12px;
        }

        /* ── Zone d'impression ───────────────────────────────────── */
        .print-area {
            padding-top: 60px; /* espace sous la barre fixe */
        }

        /* ── Page bulletin ────────────────────────────────────────── */
        .bulletin-page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 20px auto;
            padding: 10mm;
            border: 1px solid #ccc;
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

        /* ── Bande trimestres ─────────────────────────────────────── */
        .trimestre-band { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 9px; }
        .trimestre-band th,
        .trimestre-band td { border: 1px solid #000; padding: 3px 5px; text-align: center; }
        .trimestre-band th { background-color: #d0d8f0; }
        .t-label { background-color: #e8eaf6; font-weight: bold; }
        .t-ann   { background-color: #c8e6c9; font-weight: bold; }

        /* ── Tableau des notes ────────────────────────────────────── */
        table.notes { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.notes th,
        table.notes td { border: 1px solid #000; padding: 3px 4px; text-align: center; }
        table.notes th { background-color: #f2f2f2; font-size: 9px; }
        .subject-name { text-align: left; font-weight: bold; padding-left: 5px; }

        /* ── Domaines ─────────────────────────────────────────────── */
        .domain-averages {
            display: table;
            width: 100%;
            margin: 4px 0;
            font-size: 9px;
            border-bottom: 1px dashed #000;
            padding-bottom: 3px;
        }
        .domain-item { display: table-cell; padding: 2px 3px; }

        /* ── Résumé 3 blocs ──────────────────────────────────────── */
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
        .footer-table { width: 100%; margin-top: 18px; border-collapse: collapse; }
        .footer-table td { border: none; }
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

        /* ── Règles d'impression ─────────────────────────────────── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                background: #fff;
                font-size: 10px;
            }

            .no-print { display: none !important; }

            .print-area { padding-top: 0; }

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

{{-- ── Barre d'outils (non imprimée) ─────────────────────────────── --}}
<div class="no-print">
    <div class="toolbar-title">
        &#128438; Bulletins Fin d'Année &mdash; {{ $classe->name }}
        <span class="badge-count">{{ count($allBulletins) }} élève(s)</span>
    </div>
    <a href="{{ url()->previous() }}" class="btn-back">
        &#8592; Retour
    </a>
    <button class="btn-print" onclick="window.print()">
        &#128424; Imprimer tous les bulletins
    </button>
</div>

{{-- ── Bulletins ────────────────────────────────────────────────── --}}
<div class="print-area">
@foreach($allBulletins as $d)
<div class="bulletin-page">

    {{-- En-tête --}}
    <table class="header-table">
        <tr>
            <td class="header-left">
                MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE<br>
                <strong class="school-name">CS &laquo; MARIE-ALAIN &raquo;</strong><br>
                <small>AGORI AITCHEDJI - 08 BP : 559 Cotonou / T&eacute;l: 01 62 61 67 67</small>
            </td>
            <td class="header-center">
                <img src="{{ asset('logo.png') }}" class="logo" alt="Logo">
            </td>
            <td class="header-right">
                REPUBLIQUE DU BENIN<br>
                Ann&eacute;e scolaire : {{ $d['activeYear']->name }}<br>
                Classe : {{ $d['classe']->name }}<br>
                Effectif : {{ $d['classe']->students->count() }}
            </td>
        </tr>
    </table>

    <div class="title">BULLETIN DE FIN D'ANN&Eacute;E</div>
    <div class="subtitle">Les notes du tableau correspondent au Trimestre 3</div>

    {{-- Infos élève --}}
    <div class="info-section">
        <div class="student-box">
            Nom : <strong>{{ strtoupper($d['student']->last_name) }}</strong> &nbsp;&nbsp;
            Pr&eacute;noms : <strong>{{ $d['student']->first_name }}</strong><br>
            N&deg; Matricule : {{ $d['student']->num_educ ?? '&mdash;' }} &nbsp;&nbsp;
            Sexe : {{ $d['student']->gender == 'M' ? 'Masculin' : 'F&eacute;minin' }}
        </div>
        <div class="qr-box">
            <img src="{{ asset('qrcode.png') }}" class="qr-code" alt="QR">
        </div>
        <div class="clear"></div>
    </div>

    {{-- Récapitulatif trimestriel --}}
    <table class="trimestre-band">
        <thead>
            <tr>
                <th colspan="2" class="t-label">Trimestre 1</th>
                <th colspan="2" class="t-label">Trimestre 2</th>
                <th colspan="2" class="t-label">Trimestre 3</th>
                <th colspan="2" class="t-ann">Moyenne Annuelle</th>
            </tr>
            <tr>
                <th>Moyenne</th><th>Rang</th>
                <th>Moyenne</th><th>Rang</th>
                <th>Moyenne</th><th>Rang</th>
                <th>Moy. Ann.</th><th>Rang Ann.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ $d['moyT1'] }}</strong></td>
                <td>{{ $d['rangT1'] }}</td>
                <td><strong>{{ $d['moyT2'] }}</strong></td>
                <td>{{ $d['rangT2'] }}</td>
                <td><strong>{{ $d['moyT3'] }}</strong></td>
                <td>{{ $d['rangT3'] }}</td>
                <td style="background:#e8f5e9;"><strong style="font-size:11px;">{{ $d['moyAnnuelle'] }}</strong></td>
                <td style="background:#e8f5e9;"><strong>{{ $d['rangAnnuel'] }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Tableau des notes T3 --}}
    <table class="notes">
        <thead>
            <tr>
                <th rowspan="2">Mati&egrave;res</th>
                <th rowspan="2">Coef</th>
                <th colspan="3">Notes de Classe &mdash; Trimestre 3</th>
                <th rowspan="2">Moy. /20</th>
                <th rowspan="2">Note Coef.</th>
                <th rowspan="2">Rang T3</th>
                <th rowspan="2">Appr&eacute;ciations</th>
            </tr>
            <tr>
                <th>Moy. Interro</th>
                <th>Devoir N&deg;1</th>
                <th>Devoir N&deg;2</th>
            </tr>
        </thead>
        <tbody>
            @foreach($d['bulletin'] as $row)
            <tr>
                <td class="subject-name">{{ $row['subject'] }}</td>
                <td>{{ $row['coef'] }}</td>
                <td>{{ $row['moyenneInterro'] }}</td>
                <td>{{ $row['devoirs'][1] }}</td>
                <td>{{ $row['devoirs'][2] }}</td>
                <td>{{ $row['moyenne'] }}</td>
                <td>{{ $row['moyCoeff'] }}</td>
                <td>{{ $row['rang'] ?? '-' }}</td>
                <td>{{ $row['appreciation'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold; background:#eee;">
                <td>Totaux : {{ $d['totalCoeff'] }}</td>
                <td>{{ $d['totalCoeff'] }}</td>
                <td colspan="4"></td>
                <td>{{ $d['totalMoyCoeff'] }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Domaines --}}
    <div class="domain-averages">
        <div class="domain-item">Moy. Litt&eacute;raires : <strong>{{ $d['moyenneLitteraire'] }}</strong></div>
        <div class="domain-item">Moy. Scientifiques : <strong>{{ $d['moyenneScientifique'] }}</strong></div>
        <div class="domain-item">Moy. Autres : <strong>{{ $d['moyenneAutres'] }}</strong></div>
    </div>

    {{-- Résumé 3 blocs --}}
    <div class="summary-wrapper">
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">R&eacute;sultat de l&apos;apprenant</div>
            <div class="summary-content">
                Moy. Annuelle : <strong>{{ $d['moyAnnuelle'] }}</strong> / 20<br>
                Rang Annuel : <strong>{{ $d['rangAnnuel'] }}</strong><br>
                Moy. T3 : <strong>{{ $d['moyT3'] }}</strong> &mdash; Rang T3 : <strong>{{ $d['rangT3'] }}</strong><br>
                Mention : <strong>{{ $d['appreciationGenerale'] }}</strong>
            </div>
        </div>
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">R&eacute;sultat de la classe (T3)</div>
            <div class="summary-content">
                Plus forte moyenne : {{ $d['plusForte'] }}<br>
                Plus faible moyenne : {{ $d['plusFaible'] }}<br>
                Moyenne de la classe : {{ $d['moyClasse'] }}
            </div>
        </div>
        <div class="summary-box">
            <div class="summary-box-header">D&eacute;cision du Conseil</div>
            <div class="summary-content">
                {{ $d['felicitation']   ? '[X]' : '[ ]' }} F&eacute;licitations<br>
                {{ $d['encouragement']  ? '[X]' : '[ ]' }} Encouragement<br>
                {{ $d['tableauHonneur'] ? '[X]' : '[ ]' }} Tableau d&apos;Honneur<br>
                {{ $d['avertissement']  ? '[X]' : '[ ]' }} Avertissement
            </div>
        </div>
    </div>

    {{-- Signatures --}}
    <table class="footer-table">
        <tr>
            <td style="width:50%; text-align:center;">
                <u><strong>Le Titulaire</strong></u><br><br>
                <div class="mention-frame">{{ $d['appreciationGenerale'] }}</div>
            </td>
            <td style="width:50%; text-align:center;">
                <u><strong>Le Directeur</strong></u><br>
                <br><br><br><br>
                <strong>Firmin DIDAGBE</strong>
            </td>
        </tr>
    </table>

    <div class="motto">Discipline &mdash; Cr&eacute;ativit&eacute; &mdash; Excellence</div>

</div>
@endforeach
</div>

</body>
</html>

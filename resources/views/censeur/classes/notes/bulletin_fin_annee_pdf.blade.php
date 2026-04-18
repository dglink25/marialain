<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 10mm; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .container { width: 100%; }

        /* En-tête */
        .header-table { width: 100%; border: none; margin-bottom: 8px; }
        .header-left { width: 40%; text-align: left; font-size: 9px; vertical-align: top; border: none; }
        .header-center { width: 20%; text-align: center; vertical-align: middle; border: none; }
        .header-right { width: 40%; text-align: right; font-size: 9px; vertical-align: top; border: none; }
        .logo { width: 70px; }
        .school-name { font-size: 14px; font-weight: bold; }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 8px 0 6px;
        }
        .subtitle {
            text-align: center;
            font-size: 10px;
            color: #444;
            margin-bottom: 10px;
            font-style: italic;
        }

        /* Infos élève */
        .info-section { width: 100%; margin-bottom: 10px; overflow: hidden; }
        .student-box { float: left; width: 70%; line-height: 1.6; }
        .qr-box { float: right; width: 80px; text-align: right; }
        .qr-code { width: 60px; height: 60px; border: 1px solid #ccc; }
        .clear { clear: both; }

        /* Bande résumé trimestriel */
        .trimestre-band {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .trimestre-band th, .trimestre-band td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
        }
        .trimestre-band th { background-color: #d0d8f0; font-size: 9px; }
        .t-label { background-color: #e8eaf6; font-weight: bold; }
        .t-ann { background-color: #c8e6c9; font-weight: bold; }

        /* Tableau des notes */
        table.notes { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.notes th, table.notes td { border: 1px solid #000; padding: 3px 4px; text-align: center; }
        table.notes th { background-color: #f2f2f2; font-size: 9px; }
        .subject-name { text-align: left; font-weight: bold; padding-left: 5px; }

        /* Domaines */
        .domain-averages {
            display: table;
            width: 100%;
            margin: 5px 0;
            font-size: 9px;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
        }
        .domain-item { display: table-cell; padding: 2px 4px; }

        /* Blocs résumé */
        .summary-wrapper { width: 100%; margin-top: 10px; display: table; }
        .summary-box { display: table-cell; width: 32%; border: 1px solid #000; vertical-align: top; }
        .summary-box-header {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 3px;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }
        .summary-content { padding: 4px 6px; line-height: 1.5; font-size: 9px; }

        /* Signatures */
        .footer-table { width: 100%; margin-top: 20px; }
        .mention-frame {
            border: 2px solid #000;
            padding: 6px 10px;
            font-weight: bold;
            display: inline-block;
            margin-top: 8px;
            font-size: 10px;
        }
        .motto {
            text-align: center;
            margin-top: 25px;
            font-style: italic;
            border-top: 1px solid #000;
            padding-top: 4px;
            font-size: 9px;
        }
    </style>
</head>
<body>
@php $d = $data; @endphp

<div class="container">
    <!-- En-tête -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE<br>
                <strong class="school-name">CS « MARIE-ALAIN »</strong><br>
                <small>AGORI AITCHEDJI - 08 BP : 559 Cotonou / Tél: 01 62 61 67 67</small>
            </td>
            <td class="header-center">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo.png'))) }}" class="logo">
            </td>
            <td class="header-right">
                REPUBLIQUE DU BENIN<br>
                Année scolaire : {{ $d['activeYear']->name }}<br>
                Classe : {{ $d['classe']->name }}<br>
                Effectif : {{ $d['classe']->students->count() }}
            </td>
        </tr>
    </table>

    <div class="title">BULLETIN DE FIN D'ANNÉE</div>
    <div class="subtitle">Les notes du tableau correspondent au Trimestre 3</div>

    <!-- Infos élève -->
    <div class="info-section">
        <div class="student-box">
            Nom : <strong>{{ strtoupper($d['student']->last_name) }}</strong> &nbsp;&nbsp;
            Prénoms : <strong>{{ $d['student']->first_name }}</strong><br>
            N° Matricule : {{ $d['student']->num_educ ?? '—' }} &nbsp;&nbsp;
            Sexe : {{ $d['student']->gender == 'M' ? 'Masculin' : 'Féminin' }}
        </div>
        <div class="qr-box">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('qrcode.png'))) }}" class="qr-code">
        </div>
        <div class="clear"></div>
    </div>

    <!-- Bande récapitulatif trimestriel -->
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
                <th>Moyenne Ann.</th><th>Rang Ann.</th>
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

    <!-- Tableau des notes (T3) -->
    <table class="notes">
        <thead>
            <tr>
                <th rowspan="2">Matières</th>
                <th rowspan="2">Coef</th>
                <th colspan="3">Notes de Classe — Trimestre 3</th>
                <th rowspan="2">Moy. /20</th>
                <th rowspan="2">Note Coef.</th>
                <th rowspan="2">Rang T3</th>
                <th rowspan="2">Appréciations</th>
            </tr>
            <tr>
                <th>Moy. Interro</th>
                <th>Devoir N°1</th>
                <th>Devoir N°2</th>
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

    <!-- Moyennes par domaine -->
    <div class="domain-averages">
        <div class="domain-item">Moy. Matières Littéraires : <strong>{{ $d['moyenneLitteraire'] }}</strong></div>
        <div class="domain-item">Moy. Matières Scientifiques : <strong>{{ $d['moyenneScientifique'] }}</strong></div>
        <div class="domain-item">Moy. Autres Matières : <strong>{{ $d['moyenneAutres'] }}</strong></div>
    </div>

    <!-- Résumé en 3 blocs -->
    <div class="summary-wrapper">
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">Résultat de l'apprenant</div>
            <div class="summary-content">
                Moy. Annuelle : <strong>{{ $d['moyAnnuelle'] }}</strong> / 20<br>
                Rang Annuel : <strong>{{ $d['rangAnnuel'] }}</strong><br>
                Moy. T3 : <strong>{{ $d['moyT3'] }}</strong> — Rang T3 : <strong>{{ $d['rangT3'] }}</strong><br>
                Mention : <strong>{{ $d['appreciationGenerale'] }}</strong>
            </div>
        </div>
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">Résultat de la classe (T3)</div>
            <div class="summary-content">
                Plus forte moyenne : {{ $d['plusForte'] }}<br>
                Plus faible moyenne : {{ $d['plusFaible'] }}<br>
                Moyenne de la classe : {{ $d['moyClasse'] }}
            </div>
        </div>
        <div class="summary-box">
            <div class="summary-box-header">Décision du Conseil</div>
            <div class="summary-content">
                {{ $d['felicitation']   ? '[X]' : '[ ]' }} Félicitations<br>
                {{ $d['encouragement']  ? '[X]' : '[ ]' }} Encouragement<br>
                {{ $d['tableauHonneur'] ? '[X]' : '[ ]' }} Tableau d'Honneur<br>
                {{ $d['avertissement']  ? '[X]' : '[ ]' }} Avertissement
            </div>
        </div>
    </div>

    <!-- Signatures -->
    <table class="footer-table" style="border:none;">
        <tr>
            <td style="width:50%; border:none; text-align:center;">
                <u><strong>Le Titulaire</strong></u><br><br>
                <div class="mention-frame">{{ $d['appreciationGenerale'] }}</div>
            </td>
            <td style="width:50%; border:none; text-align:center;">
                <u><strong>Le Directeur</strong></u><br>
                <br><br><br><br>
                <strong>Firmin DIDAGBE</strong>
            </td>
        </tr>
    </table>

    <div class="motto">Discipline &mdash; Créativité &mdash; Excellence</div>
</div>
</body>
</html>
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
        .header-table { width: 100%; border: none; margin-bottom: 10px; }
        .header-left { width: 40%; text-align: left; font-size: 9px; }
        .header-center { width: 20%; text-align: center; }
        .header-right { width: 40%; text-align: right; }
        .logo { width: 70px; }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin: 15px 0;
        }

        /* Infos Élève */
        .info-section { width: 100%; margin-bottom: 15px; }
        .student-box { float: left; width: 70%; line-height: 1.5; }
        .qr-box { float: right; width: 80px; text-align: right; }
        .qr-code { width: 60px; height: 60px; border: 1px solid #ccc; }

        /* Tableau des notes */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f2f2f2; font-size: 9px; }
        .subject-name { text-align: left; font-weight: bold; padding-left: 5px; }

        /* Moyennes par domaine */
        .domain-averages {
            display: table;
            width: 100%;
            margin: 5px 0;
            font-size: 9px;
            border-bottom: 1px dashed #000;
        }
        .domain-item { display: table-cell; padding: 3px; }

        /* Blocs de résumé (Bas de page) */
        .summary-wrapper { width: 100%; margin-top: 15px; display: table; }
        .summary-box {
            display: table-cell;
            width: 32%;
            border: 1px solid #000;
            vertical-align: top;
        }
        .summary-box-header {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 3px;
            border-bottom: 1px solid #000;
        }
        .summary-content { padding: 5px; line-height: 1.4; }

        /* Signatures */
        .footer-table { width: 100%; margin-top: 30px; }
        .mention-frame {
            border: 2px solid #000;
            padding: 8px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .motto {
            text-align: center;
            margin-top: 40px;
            font-style: italic;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .clear { clear: both; }
    </style>
</head>
<body>

<div class="container">
    <table class="header-table">
        <tr>
            <td class="header-left" style="border:none;">
                MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE<br>
                <strong>CS « MARIE-ALAIN »</strong><br>
                <small>AGORI AITCHEDJI - 08 BP : 559 Cotonou / Tél: 61 67 67 67</small>
            </td>
            <td class="header-center" style="border:none;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo.png'))) }}" class="logo">
            </td>
            <td class="header-right" style="border:none;">
                REPUBLIQUE DU BENIN<br>
                Année scolaire : {{ $activeYear->name }}<br>
                Trimestre : {{ $trimestre }}<br>
                Classe : {{ $classe->name }}<br>
                Effectif : {{ $classe->students->count() }}
            </td>
        </tr>
    </table>

    <div class="title">BULLETIN DE NOTES</div>

    <div class="info-section">
        <div class="student-box">
            Nom : <strong>{{ $student->last_name }}</strong><br>
            Prénoms : <strong>{{ $student->first_name }}</strong><br>
            N° Matricule : {{ $student->registration_number ?? '1000' }}<br>
            Sexe : {{ $student->gender == 'M' ? 'Masculin' : 'Féminin' }}
        </div>
        <div class="qr-box">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('qrcode.png'))) }}" class="qr-code">
        </div>
        <div class="clear"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Matières</th>
                <th rowspan="2">Coef</th>
                <th colspan="3">Notes de Classe</th>
                <th rowspan="2">Moy. sur 20</th>
                <th rowspan="2">Note Coef.</th>
                <th rowspan="2">Rang</th>
                <th rowspan="2">Appréciations des Enseignants</th>
            </tr>
            <tr>
                <th>Moy. Interro</th>
                <th>Devoir N°1</th>
                <th>Devoir N°2</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bulletin as $row)
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
                <td>Totaux : {{ $totalCoeff }}</td>
                <td>{{ $totalCoeff }}</td>
                <td colspan="4"></td>
                <td>{{ $totalMoyCoeff }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="domain-averages">
        <div class="domain-item">Moyenne Matières Littéraires : <strong>{{ $moyenneLitteraire }}</strong></div>
        <div class="domain-item">Moyenne Matières Scientifiques : <strong>{{ $moyenneScientifique }}</strong></div>
        <div class="domain-item">Moyenne Autres Matières : <strong>{{ $moyenneAutres }}</strong></div>
    </div>

    <div class="summary-wrapper">
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">Résultat de l'apprenant</div>
            <div class="summary-content">
                Moyenne : <strong>{{ $moyenneGenerale }}</strong> / 20<br>
                Rang : {{ $rang }} sur {{ $classe->students->count() }}<br>
                Mention : <strong>{{ $appreciationGenerale }}</strong>
            </div>
        </div>
        <div class="summary-box" style="margin-right:2%">
            <div class="summary-box-header">Résultat de la classe</div>
            <div class="summary-content">
                Plus forte moyenne : {{ $plusForte }}<br>
                Plus faible moyenne : {{ $plusFaible }}<br>
                Moyenne de la classe : {{ $moyClasse }}
            </div>
        </div>
        <div class="summary-box">
            <div class="summary-header">Décision du Conseil</div>
            <div class="summary-content">
                {{ $felicitation ? '[X]' : '[ ]' }} Félicitations<br>
                {{ $encouragement ? '[X]' : '[ ]' }} Encouragement<br>
                {{ $tableauHonneur ? '[X]' : '[ ]' }} Tableau d'Honneur<br>
                {{ $avertissement ? '[X]' : '[ ]' }} Avertissement
            </div>
        </div>
    </div>

    <table class="footer-table" style="border:none;">
        <tr>
            <td style="width:50%; border:none; text-align:center;">
                <strong>Le Titulaire</strong><br>
                <div class="mention-frame">{{ $appreciationGenerale }}</div>
            </td>
            <td style="width:50%; border:none; text-align:center;">
                <strong>Le Directeur</strong><br>
                <br><br><br>
                <strong>Firmin DIDAGBE</strong>
            </td>
        </tr>
    </table>

    <div class="motto">Discipline - Créativité - Excellence</div>
</div>

</body>
</html>
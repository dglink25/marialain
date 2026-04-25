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
        
        /* Styles pour chaque bulletin */
        .bulletin-page {
            page-break-after: always;
        }
        
        .bulletin-page:last-child {
            page-break-after: avoid;
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
            /* Nouveau style pour centrer entre les colonnes */
            position: absolute;
            left: 0;
            right: 0;
            top: 155px; /* Ajustez cette valeur selon la hauteur de votre en-tête */
        }

        /* Infos Élève */
        .info-section { 
            width: 100%; 
            margin-bottom: 15px; 
            position: relative;
            padding-top: 50px; /* Pour faire de la place pour le titre centré */
        }
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
        
        /* Style spécifique pour le nom de l'école */
        .school-name {
            font-size: 20px; /* Augmenté de 9px à 12px */
            font-weight: bold;
        }
    </style>
</head>
<body>

@foreach($allBulletins as $data)
    <div class="bulletin-page">
        <div class="container">
            <!-- En-tête identique pour chaque bulletin -->
            <table class="header-table">
                <tr>
                    <td class="header-left" style="border:none;">
                        MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE<br>
                        <strong class="school-name">CS « MARIE-ALAIN »</strong><br>
                        <small>AGORI AITCHEDJI - 08 BP : 559 Cotonou / Tél: 01 62 61 67 67</small>
                    </td>
                    <td class="header-center" style="border:none;">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo.png'))) }}" class="logo">
                    </td>
                    <td class="header-right" style="border:none;">
                        REPUBLIQUE DU BENIN<br>
                        Année scolaire : {{ $data['activeYear']->name }}<br>
                        Trimestre : {{ $data['trimestre'] }}<br>
                        Classe : {{ $data['classe']->name }}<br>
                        Effectif : {{ $data['classe']->students->count() }}
                    </td>
                </tr>
            </table>

            <div class="title">BULLETIN DE NOTES</div>

            <div class="info-section">
                <div class="student-box">
                    Nom : <strong>{{ $data['student']->last_name }}</strong><br>
                    Prénoms : <strong>{{ $data['student']->first_name }}</strong><br>
                    N° Matricule : {{ $data['student']->num_educ ?? '1000' }}<br>
                    Sexe : {{ $data['student']->gender == 'M' ? 'Masculin' : 'Féminin' }}
                </div>
                <div class="qr-box">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('qrcode.png'))) }}" class="qr-code">
                </div>
                <div class="clear"></div>
            </div>

            <!-- Tableau des notes -->
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
                    @foreach($data['bulletin'] as $row)
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
                        <td>Totaux : {{ $data['totalCoeff'] }}</td>
                        <td>{{ $data['totalCoeff'] }}</td>
                        <td colspan="4"></td>
                        <td>{{ $data['totalMoyCoeff'] }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>

            <div class="domain-averages">
                <div class="domain-item">Moyenne Matières Littéraires : <strong>{{ $data['moyenneLitteraire'] }}</strong></div>
                <div class="domain-item">Moyenne Matières Scientifiques : <strong>{{ $data['moyenneScientifique'] }}</strong></div>
                <div class="domain-item">Moyenne Autres Matières : <strong>{{ $data['moyenneAutres'] }}</strong></div>
            </div>

            <div class="summary-wrapper">
                <div class="summary-box" style="margin-right:2%">
                    <div class="summary-box-header">Résultat de l'apprenant</div>
                    <div class="summary-content">
                        Moyenne : <strong>{{ $data['moyenneGenerale'] }}</strong> / 20<br>
                        Rang : {{ $data['rang'] }} sur {{ $data['classe']->students->count() }}<br>
                        Mention : <strong>{{ $data['appreciationGenerale'] }}</strong>
                    </div>
                </div>
                <div class="summary-box" style="margin-right:2%">
                    <div class="summary-box-header">Résultat de la classe</div>
                    <div class="summary-content">
                        Plus forte moyenne : {{ $data['plusForte'] }}<br>
                        Plus faible moyenne : {{ $data['plusFaible'] }}<br>
                        Moyenne de la classe : {{ $data['moyClasse'] }}
                    </div>
                </div>
                <div class="summary-box">
                    <div class="summary-box-header">Décision du Conseil</div>
                    <div class="summary-content">
                        {{ $data['felicitation'] ? '[X]' : '[ ]' }} Félicitations<br>
                        {{ $data['encouragement'] ? '[X]' : '[ ]' }} Encouragement<br>
                        {{ $data['tableauHonneur'] ? '[X]' : '[ ]' }} Tableau d'Honneur<br>
                        {{ $data['avertissement'] ? '[X]' : '[ ]' }} Avertissement
                    </div>
                </div>
            </div>

            <table class="footer-table" style="border:none;">
                <tr>
                    <td style="width:50%; border:none; text-align:center;">
                        <u><strong>Le Titulaire</strong><br></u>
                        <div class="mention-frame">{{ $data['appreciationGenerale'] }}</div>
                    </td>
                    <td style="width:50%; border:none; text-align:center;">
                        <u><strong>Le Directeur</strong></u><br>
                        <br><br><br>
                        <br><br>
                        <strong>Firmin DIDAGBE</strong>
                    </td>
                </tr>
            </table>
            
            <div class="motto">Discipline - Créativité - Excellence</div>
        </div>
    </div>
@endforeach

</body>
</html>
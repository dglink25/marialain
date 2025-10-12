<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin Trimestre {{ $trimestre }}</title>
    <style>
        /* STYLES UNIQUEMENT POUR PDF - NE PAS AFFICHER DANS BROWSER */
        @page {
            size: A4;
            margin: 12mm 15mm 0mm 06mm; /* Marge gauche réduite */
        }
        
        body { 
            font-family: 'Times New Roman',sans-serif; 
            font-size: 15pt;
            color: #000;
            margin: 0;
            padding: 0;
            width: 200mm;
            height: 200mm;
            background-color: white;
        }
        
        .page-container {
            width: 180mm; /* Légèrement réduit */
            height: 250mm;
            margin: 0 auto;
            padding: 7mm;
            border: 2px solid #000;
            box-sizing: border-box;
            position: relative;
        }
        
        /* Header */
        .header {
            position: relative;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #000;
        }
        
        .header-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 55mm; /* Réduit */
            font-size: 8pt;
            line-height: 1.1;
        }
        
        .header-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 40mm; /* Réduit */
            text-align: right;
            font-size: 8pt;
            line-height: 1.1;
        }
        
        .header-center {
            text-align: center;
            margin: 0 auto;
            width: 65mm; /* Réduit */
        }
        
        .logo {
            width: 100px; /* Taille ajustée pour le logo */
            height: 100px;
            margin: 0 auto 3px auto;
            display: block;
            object-fit: contain; /* Pour bien ajuster l'image */
        }
        
        .title {
            font-size: 15pt; /* Réduit */
            font-weight: bold;
            text-align: center;
            padding: 1px 0;
            margin: 30px 0 0 0;
        }
        
        /* Student Info */
        .student-info {
            display: table;
            width: 100%;
            border: 1px solid #000;
            margin: 6px 0;
            padding: 4px;
            background: #f8f9fa;
            font-size: 8pt;
        }
        
        .student-details {
            display: table-cell;
            width: 85%;
            vertical-align: top;
        }

        .trimestre-center {
            display: table-cell;
            width: 75%; /* Colonne pour le trimestre */
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 9pt;
        }
                
        .qr-code {
            display: table-cell;
            width: 25%;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000;
            padding: 2px;
            background: white;
            font-size: 7pt;
        }
        
        /* Main Table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 7pt;
            table-layout: fixed;
        }
        
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            height: 16px;
        }
        
        .main-table th {
            background: #e0e0e0;
            font-weight: bold;
        }
        
        .col-matiere { 
            width: 21%; /* Réduit */
            text-align: left;
            padding-left: 3px;
        }
        .col-coef { width: 4%; }
        .col-interro { width: 4%; }
        .col-devoir { width: 4%; }
        .col-moy { width: 5%; }
        .col-moy-coef { width: 7%; }
        .col-appreciation { 
            width: 14%; /* Réduit */
            text-align: left;
            padding-left: 3px;
        }
        
        /* Totals Section */
        .totals-section {
            text-align: center;
            margin: 5px 0;
            font-weight: bold;
            font-size: 8pt;
        }
        
        /* Three Columns Section */
        .three-columns {
            display: table;
            width: 100%;
            margin: 8px 0;
        }
        
        .column {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #000;
            padding: 4px; /* Réduit */
            vertical-align: top;
        }
        
        .column h4 {
            text-align: center;
            margin: 0 0 4px 0;
            font-size: 8pt;
            text-decoration: underline;
            font-weight: bold;
        }
        
        .column div {
            margin-bottom: 3px;
            font-size: 7pt;
        }
        
        .decision-item {
            margin-bottom: 2px;
            padding-left: 6px; /* Réduit */
            font-size: 7pt;
        }
        
        /* Signatures */
        .signatures {
            display: table;
            width: 100%;
            margin: 8px 0 5px 0; /* Réduit */
        }
        
        .signature {
            display: table-cell;
            width: 50%; /* Modifié pour 2 signatures */
            text-align: center;
            font-size: 7pt;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 18px; /* Réduit */
            margin: 3px 15px 0 15px; /* Ajusté */
        }
        
        /* Footer */
        .footer {
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 8px;
            display: table;
            width: 100%;
            font-size: 6pt;
        }
        
        .footer-left, .footer-center, .footer-right {
            display: table-cell;
            vertical-align: middle;
        }
        
        .footer-center {
            text-align: center;
            font-style: italic;
        }
        
        .footer-right {
            text-align: right;
        }
        
        .barcode {
            border: 1px dashed #000;
            padding: 1px 4px; /* Réduit */
            font-size: 5pt;
            display: inline-block;
        }
        
        /* Utility */
        .keep-together {
            page-break-inside: avoid;
        }

        /* Pour les totaux sur une ligne */
        .totals-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            font-size: 8pt;
        }
        .total-left { text-align: left; }
        .total-center { text-align: center; }
        .total-right { text-align: right; }

        .print-line {
            border: none;
            height: 1px;
            background: transparent;
            border-top: 1px dashed #000;
            margin: 4px 0;
            width: 50%;
            margin-left:24px;
            margin-right: 15px;
        }

        .print-lin {
            border: none;
            height: 1px;
            background: transparent;
            border-top: 1px dashed #000;
            margin: 4px 0;
            width: 25%;
            margin-left:40px;
            margin-right: 15px;
        }

        .cs-marie{
            display: flex;
            margin-left: 15px;
            
        }
        
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Header -->
        <div class="header keep-together">
            <div class="header-left">
                <strong>Ministère des Enseignements Secondaires,Techniques et de
                <br>la Formation Professionnelle</strong><br>
                <hr class="print-lin">
                <strong class="cs-marie">CS « MARIE-ALAIN »</strong>
            </div>
            
            <div class="header-center">
                <!-- Logo de l'école -->
                <img src="{{ $logoPath ?? 'logo.png' }}" alt="Logo de l'école" class="logo">
                <div class="title">BULLETIN DE NOTES</div>
            </div>
            
            <div class="header-right">
                <div style="text-align: justify;"> 
                <strong>République du Bénin</strong><br>
                Fraternité - Justice - Travail<br>
                <hr class="print-line">
                <strong>Année scolaire:</strong> {{ $classe->academicYear->name ?? '2025-2026' }}<br>
                <strong>Classe:</strong>{{ $classe->name }}<br>
                <strong>Effectif:</strong> {{ $classe->students->count() }}
                </div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="student-info keep-together">
            <div class="student-details">
                <strong>Nom:</strong> {{ strtoupper($student->last_name) }} <br>
                <strong>Prénom:</strong> {{ ucfirst($student->first_name) }} <br>
                <strong>Matricule:</strong> {{ $student->matricule ?? '-' }} <br>
                <strong>Genre:</strong> {{ $student->gender ?? '-' }}<br>
                <strong>Né(e) le:</strong> {{ $student->birth_date ?? '-' }} <br>
                <strong>Classe:</strong> {{ $classe->name }} <br>
            </div>
               <div class="trimestre-center">
            Trimestre: {{ $trimestre }}
                </div>
            <div class="qr-code">
                QR CODE
            </div>
        </div>

        <!-- Grades Table -->
        <table class="main-table keep-together">
            <thead>
                <tr>
                    <th class="col-matiere">Matière</th>
                    <th class="col-coef">Coef</th>
                    <th colspan="5">Interrogations</th>
                    <th colspan="2">Devoirs</th>
                    <th class="col-moy">Moy</th>
                    <th class="col-moy-coef">Moy x Coef</th>
                    <th class="col-appreciation">Appréciation</th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                    @for ($i = 1; $i <= 5; $i++)
                    <th class="col-interro">I{{ $i }}</th>
                    @endfor
                    <th class="col-devoir">D1</th>
                    <th class="col-devoir">D2</th>
                    <th colspan="3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bulletin as $ligne)
                <tr>
                    <td class="col-matiere">{{ $ligne['subject'] }}</td>
                    <td class="col-coef">{{ $ligne['coef'] }}</td>
                    @for ($i = 1; $i <= 5; $i++)
                    <td class="col-interro">{{ $ligne['interros'][$i] ?? '-' }}</td>
                    @endfor
                    <td class="col-devoir">{{ $ligne['devoirs'][1] ?? '-' }}</td>
                    <td class="col-devoir">{{ $ligne['devoirs'][2] ?? '-' }}</td>
                    <td class="col-moy"><strong>{{ $ligne['moyenne'] ?? '-' }}</strong></td>
                    <td class="col-moy-coef"><strong>{{ $ligne['moyCoeff'] ?? '-' }}</strong></td>
                    <td class="col-appreciation">{{ $ligne['appreciation'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="9" align="right"><strong>Conduite:</strong></td>
                    <td colspan="3">{{ $conduiteFinale }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Totals Line -->
        <div class="totals-section">
            <strong>Totaux:............................................................</strong> {{ $totalPoints ?? '-' }}
        </div>

        <!-- Moyennes -->
        <div class="totals-section">
            <div class="totals-line">
                <div class="total-left">
                    <strong>Moyenne Littéraires:</strong> {{ $moyenneLitteraires ?? '-' }}
                </div>
                <div class="total-right">
                    <strong>Moyenne Scientifiques:</strong> {{ $moyenneScientifiques ?? '-' }}
                </div>
            </div>
        </div>

        <!-- Three Columns -->
        <div class="three-columns keep-together">
            <div class="column">
                <h4>Résultat de l'apprenant</h4>
                <div>Moyenne: <strong>{{ $moyenneGenerale }}</strong></div>
                <div>Rang: <strong>{{ $rang }} / {{ $classe->students->count() }}</strong></div>
                <div>Mention: <strong>{{ $mention ?? '-' }}</strong></div>
            </div>
            
            <div class="column">
                <h4>Résultat de la classe</h4>
                <div>Plus forte moyenne: <strong>{{ $plusForte ?? '-' }}</strong></div>
                <div>Plus faible moyenne: <strong>{{ $plusFaible ?? '-' }}</strong></div>
                <div>Moyenne de la classe: <strong>{{ $moyClasse ?? '-' }}</strong></div>
            </div>
            
            <div class="column">
                <h4>Décision du Conseil des Enseignants</h4>
                <div class="decision-item">□ Félicitation</div>
                <div class="decision-item">□ Encouragement</div>
                <div class="decision-item">□ Tableau d'Honneur</div>
                <div class="decision-item">□ Avertissement</div>
            </div>
        </div>
        <br>
        <br>

        <!-- Signatures -->
        <div class="signatures keep-together">
            <div class="signature">
                <div>Le Titulaire</div>
                <div class="signature-line"></div>
            </div>
            <div class="signature">
                <div>Le Directeur</div>
                <div class="signature-line"></div>
            </div>
        </div>
        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                <span class="barcode">CODE BARRE</span>
            </div>
            <div class="footer-center">
                Discipline - Créativité - Excellence
            </div>
            <div class="footer-right">
                Imprimé le {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>
</body>
</html>
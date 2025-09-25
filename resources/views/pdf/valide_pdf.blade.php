<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste des élèves - {{ $className ?? '' }}</title>
    <style>
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 11px; 
            margin: 20px; 
        }

        /* --- Ligne tricolore --- */
        .tricolor-line {
            width: 70%;
            margin-bottom: 8px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .tricolor-line td {
            height: 3px;
            padding: 0;
            border: none;
            width: 33.33%;
        }
        .tricolor-line .green { background-color: #008751; }
        .tricolor-line .yellow { background-color: #FCD116; }
        .tricolor-line .red { background-color: #E8112D; }

        /* --- Header --- */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header-left, .header-right {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
            text-align: center;
        }
        .header-left img, .header-right img {
            height: 70px;
            object-fit: contain;
        }
        .school-info {
            display: table-cell;
            width: 70%;
            text-align: center;
            font-size: 11px;
            line-height: 1.3;
        }
        .school-info .bold { font-weight: bold; }

        /* --- Tableau --- */
        table {
            border-collapse: collapse;
            margin: auto;
            width: 100%;
            table-layout: fixed;
            font-family: "Times New Roman", Times, serif;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-family: "Times New Roman", Times, serif;
            font-size: 10px;
        }
        th { 
            background-color: #f0f0f0; 
            font-size: 10px;
            font-weight: bold;
        }

        /* Titre */
        .title {
            text-align: center;
            margin-bottom: 10px;
            font-family: "Times New Roman", Times, serif;
        }

        /* --- Footer --- */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            font-family: "Times New Roman", Times, serif;
        }

        .signature {
            margin-top: 60px;
            text-align: right;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }

        /* --- Pagination PDF --- */
        @page {
            margin: 20mm;
        }
        .pagenum:before {
            content: counter(page);
        }
        .pagecount:before {
            content: counter(pages);
        }
        .pdf-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #555;
            font-family: "Times New Roman", Times, serif;
        }

        /* Styles spécifiques pour le tableau des élèves */
        .date-download {
            text-align: right;
            font-size: 11px;
            margin-bottom: 10px;
            font-family: "Times New Roman", Times, serif;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('logo.png') }}" alt="Logo gauche">
        </div>
        <div class="school-info">
            <table class="tricolor-line">
                <tr>
                    <td class="green"></td>
                    <td class="yellow"></td>
                    <td class="red"></td>
                </tr>
            </table>
           
            <div class="bold">REPUBLIQUE DU BENIN</div>
            <div>MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
            <div>DIRECTION DEPARTEMENTALE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE DE L'ATLANTIQUE</div>
            <div class="bold">CPEG MARIE-ALAIN</div>
        </div>
        <div class="header-right">
            <img src="{{ public_path('logo.png') }}" alt="Logo droit">
        </div>
    </div>

    <div class="info">
        <center>
            <div class="">
                <u><h2>Reçu de Paiement</h2></u>
            </div>
        </center>
        <p><strong>Élève :</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
        <p><strong>Classe :</strong> {{ $student->classe->name ?? '' }}</p>
        <p><strong>Montant payé :</strong> {{ number_format($student->amount_paid, 0, ',', ' ') }} FCFA</p>
        <p><strong>Date :</strong> {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="footer">
        <p>Merci pour votre confiance.</p>
    </div>
        <!-- SIGNATURE -->
    <div class="signature">
        Fait à Calavi, le {{ now()->format('d/m/Y') }}<br><br><br><br> 
        La Secrétaire
    </div>
</body>
</html>

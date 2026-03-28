<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des élèves - {{ $classe->name ?? '' }}</title>
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
            font-size: 9px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-family: "Times New Roman", Times, serif;
        }
        th { 
            background-color: #f0f0f0; 
            font-weight: bold;
        }

        /* Titre */
        .title {
            text-align: center;
            margin-bottom: 10px;
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            font-weight: bold;
        }

        /* Date de téléchargement */
        .date-download {
            text-align: right;
            font-size: 10px;
            margin-bottom: 5px;
            font-family: "Times New Roman", Times, serif;
            color: #666;
        }

        /* Informations de la classe */
        .class-info {
            text-align: center;
            margin: 10px 0;
            font-size: 12px;
        }
        .class-info span {
            margin: 0 10px;
        }

        /* Styles pour les notes */
        .note-good { color: #008751; }
        .note-bad { color: #E8112D; }
        .note-coef { color: #0056b3; }
        .note-empty { color: #999; }

        /* --- Pagination PDF --- */
        @page {
            margin: 20mm;
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
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        /* Statistiques */
        .stats {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #333;
            background-color: #f9f9f9;
            font-size: 10px;
        }
        .stats h4 {
            margin: 0 0 8px 0;
            text-align: center;
            font-weight: bold;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            text-align: center;
        }
        .stat-item {
            padding: 5px;
        }
        .stat-value {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .stat-label {
            font-size: 9px;
            color: #666;
        }

        /* Signature */
        .signature {
            margin-top: 40px;
            text-align: right;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }

        /* Numérotation des pages */
        .page-number {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
            color: #666;
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

    <!-- Date de téléchargement -->
    <div class="date-download">
        Calavi le {{ $dateDownload }}
    </div>

    <!-- Titre -->
    <div class="title">
        ETAT DES NOTES - {{ strtoupper($trimestre) }}<sup>e</sup> TRIMESTRE
    </div>

    <!-- Informations de la classe -->
    <div class="class-info">
        <span><strong>Classe :</strong> {{ $classe->name }}</span>
        <span><strong>Année scolaire :</strong> {{ $activeYear->name }}</span>
        <span><strong>Effectif :</strong> {{ count($classe->students) }} élèves</span>
    </div>

    <!-- Tableau des notes -->
    <table>
        <thead>
            <tr>
                <!-- Colonne Élève -->
                <th rowspan="2" style="width: 8%;">N°</th>
                <th rowspan="2" style="width: 15%;">Élève</th>
                
                <!-- En-têtes des matières -->
                @foreach($subjects as $subject)
                <th colspan="3" style="font-size: 8px;">{{ $subject->name }}</th>
                @endforeach
                
                <!-- Colonnes finales -->
                <th rowspan="2" style="width: 4%;">Cond.</th>
                <th rowspan="2" style="width: 6%;">Moy. Gén.</th>
                <th rowspan="2" style="width: 4%;">Rang</th>
            </tr>
            
            <!-- Sous-en-têtes pour chaque matière -->
            <tr>
                @foreach($subjects as $subject)
                    <th style="width: 4%; font-size: 8px;">Moy</th>
                    <th style="width: 3%; font-size: 8px;">Coef</th>
                    <th style="width: 4%; font-size: 8px;">M.Coef</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach($classe->students as $index => $student)
            @php
                $studentData = $gradesData[$student->id] ?? [];
                $moyenneGenerale = $studentData['moyenne_generale'] ?? 0;
                $rang = $studentData['rang_general'] ?? '-';
                $conduiteFinale = $studentData['conduite_finale'] ?? 0;
            @endphp
            <tr>
                <!-- Numéro et nom de l'élève -->
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: left; padding-left: 5px;">
                    {{ strtoupper($student->last_name) }} {{ ucfirst($student->first_name) }}
                </td>

                <!-- Notes par matière -->
                @foreach($subjects as $subject)
                    @php
                        $matiere = $studentData[$subject->id] ?? [];
                        $moyenneMatiere = $matiere['moyenneMatiere'] ?? null;
                        $coef = $matiere['coef'] ?? 1;
                        $moyenneCoef = $matiere['moyenneCoef'] ?? null;
                    @endphp
                    
                    <!-- Moyenne Matière -->
                    <td style="color: black;" class="{{ $moyenneMatiere !== null ? ($moyenneMatiere >= 10 ? 'note-good' : 'note-bad') : 'note-empty' }}">
                        <strong>{{ $moyenneMatiere !== null ? number_format($moyenneMatiere, 2) : '-' }}</strong>
                    </td>
                    
                    <!-- Coefficient -->
                    <td><strong>{{ $coef }}</strong></td>
                    
                    <!-- Moyenne Coefficientée -->
                    <td style="color: black;" class="{{ $moyenneCoef !== null ? 'note-coef' : 'note-empty' }}">
                        <strong>{{ $moyenneCoef !== null ? number_format($moyenneCoef, 2) : '-' }}</strong>
                    </td>
                @endforeach

                <!-- Conduite -->
                <td style="color: black;" class="{{ $conduiteFinale > 0 ? 'note-coef' : 'note-empty' }}">
                    {{ $conduiteFinale > 0 ? number_format($conduiteFinale, 2) : '-' }}
                </td>
                
                <!-- Moyenne Générale -->
                <td style="color: black;" class="{{ $moyenneGenerale >= 10 ? 'note-good' : 'note-bad' }}" style="font-weight: bold;">
                    <strong>{{ $moyenneGenerale > 0 ? number_format($moyenneGenerale, 2) : '-' }}</strong>
                </td>
                
                <!-- Rang -->
                <td style="font-weight: bold;">{{ $rang }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pied de page avec numérotation -->
    <div class="pdf-footer">
        CPEG MARIE-ALAIN - Fiche de notes du {{ $trimestre }}<sup>e</sup> trimestre - Classe : {{ $classe->name }}
        <div class="page-number">
            <span class="pagenum"></span>  <span class="pagecount"></span>
        </div>
    </div>
</body>
</html>
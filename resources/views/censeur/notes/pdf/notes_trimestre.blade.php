<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notes - {{ $classe->name }} - T{{ $trimestre }}</title>
    <style>
        /* ===== BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            padding: 10mm;
            background: #fff;
        }

        /* ===== EN-TÊTE PARFAITEMENT ALIGNÉ ===== */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8mm;
            page-break-after: avoid;
        }

        .logo-container {
            flex: 0 0 60mm; /* Largeur fixe pour les logos */
            text-align: center;
            height: 25mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-img {
            max-height: 25mm;
            max-width: 45mm;
            width: auto;
            height: auto;
        }

        .center-content {
            flex: 1;
            text-align: center;
            padding: 0 5mm;
            min-width: 0;
        }

        .flag-line {
            display: flex;
            justify-content: center;
            margin: 1mm auto;
            height: 1mm;
            width: 50mm;
            gap: 1mm;
        }

        .flag-segment {
            flex: 1;
            background-color: #000;
        }

        .school-name {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 1mm 0;
            letter-spacing: 0.5pt;
        }

        .ministry {
            font-size: 8pt;
            font-style: italic;
            margin-top: 1mm;
        }

        /* ===== TITRE ===== */
        .title-section {
            text-align: center;
            margin: 6mm 0 4mm 0;
            border: 1pt solid #000;
            padding: 3mm;
        }

        .main-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 1mm;
            text-decoration: underline;
        }

        .class-info {
            font-size: 10pt;
            font-weight: bold;
        }

        /* ===== INFOS RAPIDES ===== */
        .quick-info {
            display: flex;
            justify-content: space-between;
            margin: 3mm 0;
            padding: 2mm;
            border-top: 0.5pt solid #000;
            border-bottom: 0.5pt solid #000;
            font-size: 9pt;
        }

        /* ===== TABLEAU ULTRA COMPACT ===== */
        .table-container {
            margin: 4mm 0;
            overflow-x: auto;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
        }

        /* En-tête du tableau */
        .table-header th {
            border: 0.5pt solid #000;
            padding: 1mm 0.3mm;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 7pt;
            line-height: 1;
            background: #f8f8f8;
        }

        /* Colonne élève fixe */
        .student-col {
            width: 12%;
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 1;
            border-right: 0.5pt solid #000;
        }

        /* Colonnes matières */
        .subject-col {
            width: 7.5%;
        }

        .subject-name {
            font-size: 6.5pt;
            font-weight: bold;
            line-height: 1;
            word-break: break-word;
            padding: 0.5mm;
        }

        /* Abréviations dans l'en-tête */
        .abbr-header {
            font-size: 6pt;
            font-weight: normal;
            display: block;
            margin-top: 0.5mm;
            color: #444;
        }

        /* Corps du tableau */
        .grades-table td {
            border: 0.5pt solid #000;
            padding: 0.5mm 0.3mm;
            text-align: center;
            vertical-align: middle;
            font-size: 8pt;
            height: 8mm;
        }

        /* Cellules élèves */
        .student-info {
            text-align: left;
            padding-left: 1mm !important;
        }

        .student-num {
            display: inline-block;
            width: 4mm;
            height: 4mm;
            background: #000;
            color: #fff;
            border-radius: 50%;
            text-align: center;
            line-height: 4mm;
            font-weight: bold;
            margin-right: 1mm;
            font-size: 6pt;
        }

        .student-lastname {
            font-weight: bold;
            font-size: 8pt;
            line-height: 1;
            margin-bottom: 0.3mm;
        }

        .student-firstname {
            font-size: 7pt;
            line-height: 1;
            margin-bottom: 0.3mm;
        }

        .student-id {
            font-size: 6pt;
            font-style: italic;
            color: #666;
        }

        /* Styles pour les notes */
        .note-bold {
            font-weight: bold;
        }

        .coeff-cell {
            font-weight: bold;
            background: #f8f8f8;
        }

        .conduct-cell {
            font-weight: bold;
            background: #f8f8f8;
        }

        .rank-cell {
            font-weight: bold;
            background: #f8f8f8;
        }

        /* Lignes alternées */
        .grades-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        /* ===== LÉGENDE ===== */
        .legend-section {
            margin: 4mm 0 6mm 0;
            padding: 2mm;
            border: 0.5pt solid #000;
            font-size: 7pt;
        }

        .legend-title {
            font-weight: bold;
            margin-bottom: 1mm;
            text-align: center;
            font-size: 8pt;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1mm 3mm;
        }

        .legend-item {
            display: flex;
            align-items: flex-start;
        }

        .legend-abbr {
            font-weight: bold;
            margin-right: 1.5mm;
            min-width: 8mm;
            flex-shrink: 0;
        }

        .legend-text {
            font-size: 7pt;
            line-height: 1.2;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin-top: 8mm;
            padding-top: 3mm;
            border-top: 0.5pt solid #000;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 9pt;
        }

        .date-info {
            font-style: italic;
        }

        .signature-box {
            text-align: center;
            width: 40mm;
        }

        .signature-line {
            width: 35mm;
            height: 0.2pt;
            background: #000;
            margin: 0 auto 1mm;
        }

        .signature-text {
            font-weight: bold;
        }

        /* ===== PAGINATION ===== */
        @page {
            margin: 10mm;
            size: A4 landscape;
        }

        /* ===== IMPRESSION ===== */
        @media print {
            body {
                font-size: 9pt;
                padding: 0;
            }
            
            .grades-table {
                font-size: 7pt;
            }
            
            .logo-container {
                height: 22mm;
            }
            
            .logo-img {
                max-height: 22mm;
            }
        }

        /* ===== UTILITAIRES ===== */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-italic { font-style: italic; }
        .mb-1 { margin-bottom: 1mm; }
        .mt-1 { margin-top: 1mm; }
    </style>
</head>
<body>
    <!-- EN-TÊTE PARFAITEMENT ALIGNÉ -->
    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('logo.png') }}" class="logo-img" alt="Logo">
        </div>
        
        <div class="center-content">
            <div class="flag-line">
                <div class="flag-segment"></div>
                <div class="flag-segment"></div>
                <div class="flag-segment"></div>
            </div>
            
            <div class="school-name">CPEG MARIE-ALAIN</div>
            <div class="ministry">Ministère des Enseignements Secondaire, Technique et de la Formation Professionnelle</div>
        </div>
        
        <div class="logo-container">
            <img src="{{ public_path('logo.png') }}" class="logo-img" alt="Logo">
        </div>
    </div>

    <!-- TITRE PRINCIPAL -->
    <div class="title-section">
        <div class="main-title">FICHE RÉCAPITULATIVE DES NOTES</div>
        <div class="class-info">Classe: {{ $classe->name }} | Trimestre: {{ $trimestre }} | Année: {{ $activeYear->name }}</div>
    </div>

    <!-- INFOS RAPIDES -->
    <div class="quick-info">
        <div>Date: {{ now()->format('d/m/Y') }}</div>
        <div>Élèves: {{ count($classe->students) }}</div>
        <div>Généré par: Système de Gestion Scolaire</div>
    </div>

    <!-- TABLEAU COMPACT AVEC ABRÉVIATIONS -->
    <div class="table-container">
        <table class="grades-table">
            <thead class="table-header">
                <!-- Première ligne: Nom des matières avec abréviation -->
                <tr>
                    <th rowspan="3" class="student-col">N°</th>
                    <th rowspan="3" class="student-col">Élève</th>
                    
                    @foreach($subjects as $subject)
                    <th colspan="7" class="subject-col">
                        <div class="subject-name">
                            @php
                                $subjectName = $subject->subject->name;
                                if(strlen($subjectName) > 12) {
                                    echo substr($subjectName, 0, 10) . '...';
                                } else {
                                    echo $subjectName;
                                }
                            @endphp
                        </div>
                    </th>
                    @endforeach
                    
                    <th rowspan="3" style="width: 4%;" class="conduct-cell">Cond.</th>
                    <th rowspan="3" style="width: 5%;">M.G.</th>
                    <th rowspan="3" style="width: 4%;" class="rank-cell">Rang</th>
                </tr>
                
                <!-- Deuxième ligne: Abréviations principales -->
                <tr>
                    @foreach($subjects as $subject)
                        <th colspan="2">
                            Moyennes
                            <span class="abbr-header">(MI-MD)</span>
                        </th>
                        <th>
                            Moy./20
                            <span class="abbr-header">(M)</span>
                        </th>
                        <th>
                            Coef.
                            <span class="abbr-header">(C)</span>
                        </th>
                        <th>
                            M×C
                            <span class="abbr-header">(MC)</span>
                        </th>
                        <th colspan="2">
                            Détails
                            <span class="abbr-header">(D1-D2)</span>
                        </th>
                    @endforeach
                </tr>
                
                <!-- Troisième ligne: Abréviations détaillées -->
                <tr>
                    @foreach($subjects as $subject)
                        <th>MI</th>
                        <th>MD</th>
                        <th>M</th>
                        <th>C</th>
                        <th>MC</th>
                        <th>D1</th>
                        <th>D2</th>
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
                    <!-- Numéro et informations élève -->
                    <td class="student-info">
                        <span class="student-num">{{ $index + 1 }}</span>
                    </td>
                    <td class="student-info">
                        <div class="student-lastname">{{ strtoupper($student->last_name) }}</div>
                        <div class="student-firstname">{{ ucfirst($student->first_name) }}</div>
                        @if($student->num_educ)
                        <div class="student-id">#{{ $student->num_educ }}</div>
                        @endif
                    </td>

                    <!-- Notes par matière -->
                    @foreach($subjects as $subject)
                        @php
                            $matiere = $studentData[$subject->id] ?? [];
                            $moyenneInterro = $matiere['moyenneInterro'] ?? null;
                            $devoir1 = $matiere['devoir1'] ?? null;
                            $devoir2 = $matiere['devoir2'] ?? null;
                            $moyenneMatiere = $matiere['moyenneMatiere'] ?? null;
                            $moyenneCoef = $matiere['moyenneCoef'] ?? null;
                            $coef = $matiere['coef'] ?? 1;
                            
                            // Calculer la moyenne des devoirs
                            $moyenneDevoir = null;
                            if ($devoir1 !== null && $devoir2 !== null) {
                                $moyenneDevoir = round(($devoir1 + $devoir2) / 2, 2);
                            } elseif ($devoir1 !== null) {
                                $moyenneDevoir = $devoir1;
                            } elseif ($devoir2 !== null) {
                                $moyenneDevoir = $devoir2;
                            }
                        @endphp
                        
                        <!-- Moyenne Interro (MI) -->
                        <td>
                            {{ $moyenneInterro !== null ? number_format($moyenneInterro, 1) : '-' }}
                        </td>
                        
                        <!-- Moyenne Devoir (MD) -->
                        <td>
                            {{ $moyenneDevoir !== null ? number_format($moyenneDevoir, 1) : '-' }}
                        </td>
                        
                        <!-- Moyenne Matière (M) -->
                        <td class="note-bold">
                            {{ $moyenneMatiere !== null ? number_format($moyenneMatiere, 1) : '-' }}
                        </td>
                        
                        <!-- Coefficient (C) -->
                        <td class="coeff-cell">
                            {{ $coef }}
                        </td>
                        
                        <!-- Moyenne × Coefficient (MC) -->
                        <td class="note-bold">
                            {{ $moyenneCoef !== null ? number_format($moyenneCoef, 1) : '-' }}
                        </td>
                        
                        <!-- Devoir 1 (D1) -->
                        <td>
                            {{ $devoir1 !== null ? number_format($devoir1, 1) : '-' }}
                        </td>
                        
                        <!-- Devoir 2 (D2) -->
                        <td>
                            {{ $devoir2 !== null ? number_format($devoir2, 1) : '-' }}
                        </td>
                    @endforeach

                    <!-- Conduite -->
                    <td class="conduct-cell">
                        {{ $conduiteFinale > 0 ? number_format($conduiteFinale, 1) : '-' }}
                    </td>
                    
                    <!-- Moyenne Générale -->
                    <td class="note-bold">
                        {{ $moyenneGenerale > 0 ? number_format($moyenneGenerale, 2) : '-' }}
                    </td>
                    
                    <!-- Rang -->
                    <td class="rank-cell">
                        {{ $rang }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- LÉGENDE DÉTAILLÉE -->
    <div class="legend-section">
        <div class="legend-title">LÉGENDE DES ABRÉVIATIONS</div>
        <div class="legend-grid">
            <div>
                <div class="legend-item"><span class="legend-abbr">MI</span><span class="legend-text">= Moyenne Interrogations</span></div>
                <div class="legend-item"><span class="legend-abbr">MD</span><span class="legend-text">= Moyenne Devoirs</span></div>
                <div class="legend-item"><span class="legend-abbr">M</span><span class="legend-text">= Moyenne Matière (sur 20)</span></div>
            </div>
            <div>
                <div class="legend-item"><span class="legend-abbr">C</span><span class="legend-text">= Coefficient</span></div>
                <div class="legend-item"><span class="legend-abbr">MC</span><span class="legend-text">= Moyenne × Coefficient</span></div>
                <div class="legend-item"><span class="legend-abbr">D1, D2</span><span class="legend-text">= Devoir 1, Devoir 2</span></div>
            </div>
            <div>
                <div class="legend-item"><span class="legend-abbr">Cond.</span><span class="legend-text">= Conduite (coefficient 1)</span></div>
                <div class="legend-item"><span class="legend-abbr">M.G.</span><span class="legend-text">= Moyenne Générale</span></div>
                <div class="legend-item"><span class="legend-abbr">-</span><span class="legend-text">= Note non saisie</span></div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 2mm; font-style: italic; font-size: 7pt;">
            Note: Les moyennes matière (colonne M) sont en gras pour une meilleure lisibilité
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="date-info">
            Document généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
        
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-text">LE CENSEUR</div>
        </div>
    </div>
</body>
</html>
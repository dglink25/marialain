<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste de paye - {{ $subject->name }}</title>
    <style>
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 11px; 
            margin: 20px; 
            line-height: 1.4;
            color: #333;
        }

        /* --- Ligne tricolore --- */
        .tricolor-line {
            width: 70%;
            margin: 0 auto 8px auto;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .tricolor-line td {
            height: 4px;
            padding: 0;
            border: none;
        }
        .tricolor-line .green { background-color: #008751; width: 33.33%; }
        .tricolor-line .yellow { background-color: #FCD116; width: 33.33%; }
        .tricolor-line .red { background-color: #E8112D; width: 33.33%; }

        /* --- Header --- */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header-left, .header-right {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
            text-align: center;
        }
        .header-left img, .header-right img {
            height: 80px;
            object-fit: contain;
        }
        .school-info {
            display: table-cell;
            width: 70%;
            text-align: center;
            font-size: 12px;
            line-height: 1.4;
            padding: 0 15px;
        }
        .school-info .bold { 
            font-weight: bold; 
            font-size: 13px;
        }
        .school-info .ministry {
            font-size: 11px;
            margin: 3px 0;
        }

        /* --- Titre principal --- */
        .main-title {
            text-align: center;
            margin: 25px 0 20px 0;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .main-title h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        .main-title h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            font-style: italic;
        }

        /* --- Période --- */
        .period-info {
            text-align: center;
            margin: 15px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-weight: bold;
        }

        /* --- Tableau principal --- */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            table-layout: fixed;
        }
        
        /* En-têtes de colonnes */
        .main-table thead {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
        }
        
        .main-table th {
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #34495e;
            vertical-align: middle;
        }
        
        /* Colonne Enseignant avec sous-colonnes */
        .teacher-header {
            background: linear-gradient(135deg, #34495e, #2c3e50);
        }
        
        .sub-columns th {
            background: linear-gradient(135deg, #4a6572, #34495e);
            font-size: 10px;
            padding: 8px 4px;
        }

        /* Cellules du tableau */
        .main-table td {
            padding: 10px 8px;
            border: 1px solid #e0e0e0;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            word-wrap: break-word;
        }

        /* Lignes alternées pour une meilleure lisibilité */
        .main-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .main-table tbody tr:hover {
            background-color: #e8f4fd;
            transition: background-color 0.3s ease;
        }

        /* Séparateur entre les enseignants */
        .teacher-separator {
            background: linear-gradient(90deg, transparent, #667eea, transparent);
            height: 2px;
            margin: 5px 0;
        }

        /* Ligne de total par enseignant */
        .teacher-total {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb) !important;
            font-weight: bold;
            border-top: 2px solid #2196f3 !important;
            border-bottom: 2px solid #2196f3 !important;
        }
        
        .teacher-total td {
            font-weight: bold;
            color: #1976d2;
        }

        /* Ligne de total général */
        .grand-total {
            background: linear-gradient(135deg, #c8e6c9, #a5d6a7) !important;
            font-weight: bold;
            border-top: 3px double #4caf50 !important;
            border-bottom: 3px double #4caf50 !important;
        }
        
        .grand-total td {
            font-weight: bold;
            color: #2e7d32;
            font-size: 11px;
        }

        /* Style pour les cellules vides dans les sous-lignes */
        .empty-cell {
            background-color: transparent;
            border: none;
        }

        /* Badges pour les informations importantes */
        .info-badge {
            display: inline-block;
            padding: 3px 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            margin: 2px;
        }

        /* --- Footer et signature --- */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .signature {
            margin-top: 80px;
            text-align: right;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 10px;
        }

        .signature .place-date {
            font-style: italic;
            margin-bottom: 40px;
        }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            body {
                font-size: 10px;
                margin: 10px;
            }
            
            .header-left, .header-right {
                width: 20%;
            }
            
            .school-info {
                width: 60%;
                font-size: 10px;
            }
            
            .main-table {
                font-size: 9px;
            }
            
            .main-table th,
            .main-table td {
                padding: 6px 4px;
                font-size: 9px;
            }
        }

        /* --- Pagination PDF --- */
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .no-break {
            page-break-inside: avoid;
        }

        /* Numérotation des pages */
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
            font-size: 9px;
            color: #666;
            padding: 5px 0;
            background: white;
            border-top: 1px solid #ddd;
        }

        /* Animation subtile pour les totaux */
        @keyframes highlight {
            0% { background-color: transparent; }
            50% { background-color: #fff3cd; }
            100% { background-color: transparent; }
        }
        
        .teacher-total, .grand-total {
            animation: highlight 2s ease-in-out;
        }

        /* Style pour les montants */
        .amount {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .positive-amount {
            color: #2e7d32;
        }
        
        /* Améliorations visuelles */
        .header {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .main-table {
            border-radius: 5px;
            overflow: hidden;
        }
        
        .main-table th {
            border-bottom: 2px solid #1a2530;
        }
        
        .teacher-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .class-info {
            background-color: #f0f7ff;
            font-weight: 500;
        }
        
        .stat-box {
            background: white;
            border-radius: 5px;
            padding: 10px;
            margin: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            flex: 1;
            min-width: 120px;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .stat-teachers { color: #667eea; }
        .stat-hours { color: #28a745; }
        .stat-amount { color: #ffc107; }
        .stat-aib { color: #dc3545; }
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
            <div class="ministry">MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
            <div class="ministry">DIRECTION DEPARTEMENTALE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE DE L'ATLANTIQUE</div>
            <div class="bold" style="font-size: 14px; margin-top: 8px;">CPEG MARIE-ALAIN</div>
        </div>
        <div class="header-right">
            <img src="{{ public_path('logo.png') }}" alt="Logo droit">
        </div>
    </div>

    <!-- TITRE PRINCIPAL -->
     <center>
        <h1>LISTE DES ENSEIGNANTS : {{ strtoupper($subject->name) }}</h1>
     </center>

    <!-- PÉRIODE -->
    <div class="period-info">
         Période du <strong>{{ date('d/m/Y', strtotime($start)) }}</strong> au <strong>{{ date('d/m/Y', strtotime($end)) }}</strong>
    </div>

    <table class="main-table">
        <thead>
            <tr class="teacher-header">
                <th colspan="4" style="color: black">ENSEIGNANTS</th>
                <th colspan="6" style="color: black">INFORMATIONS PÉDAGOGIQUES ET FINANCIÈRES</th>
            </tr>
            <tr class="sub-columns">
                <!-- Sous-colonnes Enseignant -->
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">Nom complet</th>
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">Email</th>
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">Téléphone</th>
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">N° Pièce</th>

                <!-- Sous-colonnes Informations -->
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">Classe assignée</th>
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">Heures total</th>
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">Montant reçu </th>
                <th style="font-weight: bold; background-color: #e8f5e8;color: black;">MOntant brut total</th>
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">AIB (5%)</th>
                <th style="font-weight: bold; background-color: #e8f5e8; color: black;">Emmagement</th>

            </tr>
        </thead>
        <tbody>
            @php
                $grand_total_heures = 0;
                $grand_total_brut_heure = 0;
                $grand_total_brut_total = 0;
                $grand_total_aib = 0;
            @endphp

            @foreach($teachers as $teacherIndex => $teacher)
                @php
                    $teacher_total_heures = 0;
                    $teacher_total_brut_heure = 0;
                    $teacher_total_brut_total = 0;
                    $teacher_total_aib = 0;
                    $class_count = count($teacher->classes_for_subject);
                @endphp

                @foreach($teacher->classes_for_subject as $classIndex => $classe)
                    <tr class="no-break">
                        <!-- Colonnes Enseignant (affichées seulement sur la première ligne) -->
                        @if($classIndex === 0)
                            <td rowspan="{{ $class_count }}" class="teacher-name">
                                {{ $teacher->name }}
                                @if($teacher->email)
                                    <div class="info-badge">{{ $teacher->email }}</div>
                                @endif
                            </td>
                            <td rowspan="{{ $class_count }}">{{ $teacher->email ?? '--' }}</td>
                            <td rowspan="{{ $class_count }}">
                                @if($teacher->phone)
                                    {{ $teacher->phone }}
                                @else
                                    --
                                @endif
                            </td>
                            <td rowspan="{{ $class_count }}">
                                @if($teacher->id_card_number)
                                    {{ $teacher->id_card_number }}
                                @else
                                    --
                                @endif
                            </td>
                        @endif

                        <!-- Colonnes Informations (toujours affichées) -->
                        <td class="class-info">
                            {{ $classe->class_name }}
                        </td>
                        <td class="amount">{{ abs($teacher->total_hours) }}h</td>
                        <td class="amount positive-amount">{{ number_format($classe->amount_brut ?? 0, 0, ',', ' ') }} F</td>
                        <td class="amount positive-amount">{{ abs($classe->total_brut) }} F</td>
                        <td class="amount">{{ abs($classe->aib) }} F</td>
                        <td style="font-style: italic;">{{ $classe->emmagement ?? '--' }}</td>
                    </tr>

                    @php
                        $teacher_total_heures += $teacher->total_hours;
                        $teacher_total_brut_heure += $classe->amount_brut ?? 0;
                        $teacher_total_brut_total += $classe->total_brut ?? 0;
                        $teacher_total_aib += $classe->aib ?? 0;
                    @endphp
                @endforeach
                <!-- Séparateur entre les enseignants -->
                @if(!$loop->last)
                    <tr>
                        <td colspan="10" class="empty-cell">
                            <div class="teacher-separator"></div>
                        </td>
                    </tr>
                @endif

                @php
                    $grand_total_heures += $teacher_total_heures;
                    $grand_total_brut_heure += $teacher_total_brut_heure;
                    $grand_total_brut_total += $teacher_total_brut_total;
                    $grand_total_aib += $teacher_total_aib;
                @endphp
            @endforeach

            <!-- Ligne de total général -->
            <tr class="grand-total no-break">
                <td colspan="4" style="text-align: right; font-weight: bold; font-size: 12px;">
                    <center>TOTAL</center> 
                </td>
                <td style="font-weight: bold; text-align: center; font-size: 12px;">
                    {{ count($teachers) }} enseignant(s)
                </td>
                <td class="amount" style="font-size: 12px;">{{abs($grand_total_heures) }}h</td>
                <td class="amount positive-amount" style="font-size: 12px;">
                    {{ abs($grand_total_brut_heure/count($teachers)) }} F
                </td>
                <td class="amount positive-amount" style="font-size: 12px;">
                    {{ abs($grand_total_brut_total) }} F
                </td>
                <td class="amount" style="font-size: 12px;">{{ abs($grand_total_aib) }} F</td>
                <td style="font-weight: bold; font-size: 12px;">-----</td>
            </tr>
        </tbody>
    </table>

    <!-- SIGNATURE -->
    <div class="signature">
        <div class="place-date">
            Fait à Calavi, le {{ now()->format('d/m/Y') }}
        </div>
        <div style="margin-top: 60px;">
            La Secrétaire
        </div>
    </div>

    <!-- PIED DE PAGE PDF -->
    <div class="pdf-footer">
        CPEG MARIE-ALAIN - Liste des enseignants {{ $subject->name }} - 
        Généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
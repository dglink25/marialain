<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste de paye - {{ $subject->name }}</title>
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

        /* --- Date de téléchargement --- */
        .date-download {
            text-align: right;
            font-size: 10px;
            margin-bottom: 5px;
            font-family: "Times New Roman", Times, serif;
            color: #666;
        }

        /* --- Titre --- */
        .title {
            text-align: center;
            margin-bottom: 10px;
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            font-weight: bold;
        }

        /* --- Informations de la période --- */
        .period-info {
            text-align: center;
            margin: 10px 0;
            font-size: 12px;
        }
        .period-info span {
            margin: 0 10px;
        }

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

        /* En-têtes groupés */
        th.group-header {
            background-color: #e0e0e0;
        }

        /* Styles pour les montants */
        .amount-positive { color: #008751; }
        .amount-negative { color: #E8112D; }
        .amount-normal { color: #000; }

        /* --- Ligne de séparation entre enseignants --- */
        .teacher-separator {
            height: 2px;
            background-color: #333;
            margin: 2px 0;
        }

        /* --- Total par enseignant --- */
        .teacher-total {
            background-color: #e6f0fa;
            font-weight: bold;
        }

        /* --- Total général --- */
        .grand-total {
            background-color: #d4edda;
            font-weight: bold;
        }

        /* --- Badge d'information --- */
        .info-badge {
            font-size: 8px;
            color: #666;
            font-style: italic;
        }

        /* --- Style pour le nom de l'enseignant --- */
        .teacher-name {
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }

        /* --- Statistiques --- */
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

        /* --- Signature --- */
        .signature {
            margin-top: 40px;
            text-align: right;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }

        /* --- Pagination PDF --- */
        @page {
            margin: 20mm;
            size: A4 landscape;
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

        /* --- Numérotation des pages --- */
        .page-number {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
            color: #666;
        }

        /* --- Optimisation d'impression --- */
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    @php
        // Initialisation des variables de calcul
        $grand_total_heures = 0;
        $grand_total_brut = 0;
        $grand_total_aib = 0;
        $grand_total_net = 0;
        $total_teachers = count($teachers);
        $total_classes = 0;
        
        // Calcul des totaux
        foreach($teachers as $teacher) {
            $teacher_classes = $teacher->classes_for_subject ?? [];
            $total_classes += count($teacher_classes);
            
            foreach($teacher_classes as $classe) {
                $grand_total_heures += abs($teacher->total_hours ?? 0);
                $grand_total_brut += $classe->total_brut ?? 0;
                $grand_total_aib += $classe->aib ?? 0;
                $grand_total_net += ($classe->total_brut ?? 0) - ($classe->aib ?? 0);
            }
        }
    @endphp

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
        Calavi le {{ now()->format('d/m/Y') }}
    </div>

    <!-- Titre -->
    <div class="title">
        LISTE DE PAYE - {{ strtoupper($subject->name) }}
    </div>

    <!-- Informations de la période -->
    <div class="period-info">
        <span><strong>Période du :</strong> {{ date('d/m/Y', strtotime($start)) }}</span>
        <span><strong>au :</strong> {{ date('d/m/Y', strtotime($end)) }}</span>
        <span><strong>Enseignants :</strong> {{ $total_teachers }}</span>
        <span><strong>Classes :</strong> {{ $total_classes }}</span>
    </div>

    <!-- Tableau principal -->
    <table>
        <thead>
            <tr>
                <th colspan="4" class="group-header">INFORMATIONS ENSEIGNANT</th>
                <th colspan="6" class="group-header">DÉTAILS PÉDAGOGIQUES ET FINANCIERS</th>
            </tr>
            <tr>
                <th style="width: 12%;">Nom complet</th>
                <th style="width: 8%;">Contact</th>
                <th style="width: 8%;">N° Pièce</th>
                <th style="width: 10%;">Email</th>
                <th style="width: 8%;">Classe</th>
                <th style="width: 5%;">Heures</th>
                <th style="width: 7%;">Taux horaire</th>
                <th style="width: 7%;">Brut</th>
                <th style="width: 7%;">AIB (5%)</th>
                <th style="width: 8%;">Net à payer</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Réinitialisation pour le tableau
                $grand_total_heures = 0;
                $grand_total_brut = 0;
                $grand_total_aib = 0;
                $grand_total_net = 0;
            @endphp

            @foreach($teachers as $teacherIndex => $teacher)
                @php
                    $teacher_total_heures = 0;
                    $teacher_total_brut = 0;
                    $teacher_total_aib = 0;
                    $teacher_total_net = 0;
                    $class_count = count($teacher->classes_for_subject ?? []);
                @endphp

                @foreach($teacher->classes_for_subject as $classIndex => $classe)
                    @php
                        $net = ($classe->total_brut ?? 0) - ($classe->aib ?? 0);
                        $teacher_total_heures += abs($teacher->total_hours ?? 0);
                        $teacher_total_brut += $classe->total_brut ?? 0;
                        $teacher_total_aib += $classe->aib ?? 0;
                        $teacher_total_net += $net;
                    @endphp
                    
                    <tr class="no-break">
                        @if($classIndex === 0)
                            <td rowspan="{{ $class_count }}" class="teacher-name">
                                {{ $teacher->name }}
                                @if($teacher->phone || $teacher->email)
                                    <div class="info-badge">
                                        {{ $teacher->phone ?? $teacher->email }}
                                    </div>
                                @endif
                            </td>
                            <td rowspan="{{ $class_count }}">{{ $teacher->phone ?? '—' }}</td>
                            <td rowspan="{{ $class_count }}">{{ $teacher->id_card_number ?? '—' }}</td>
                            <td rowspan="{{ $class_count }}">{{ $teacher->email ?? '—' }}</td>
                        @endif

                        <td>{{ $classe->class_name }}</td>
                        <td>{{ abs($teacher->total_hours ?? 0) }}h</td>
                        <td class="amount-normal">{{ number_format($classe->amount_brut ?? 0, 0, ',', ' ') }} F</td>
                        <td class="amount-positive">{{ number_format($classe->total_brut ?? 0, 0, ',', ' ') }} F</td>
                        <td class="amount-negative">{{ number_format($classe->aib ?? 0, 0, ',', ' ') }} F</td>
                        <td class="amount-positive">{{ number_format($net, 0, ',', ' ') }} F</td>
                    </tr>
                @endforeach

                <!-- Total par enseignant -->
                <tr class="teacher-total no-break">
                    <td colspan="4" style="text-align: right;">Total {{ $teacher->name }} :</td>
                    <td>{{ count($teacher->classes_for_subject ?? []) }} classe(s)</td>
                    <td>{{ abs($teacher_total_heures) }}h</td>
                    <td>—</td>
                    <td>{{ number_format($teacher_total_brut, 0, ',', ' ') }} F</td>
                    <td>{{ number_format($teacher_total_aib, 0, ',', ' ') }} F</td>
                    <td>{{ number_format($teacher_total_net, 0, ',', ' ') }} F</td>
                </tr>

                @php
                    $grand_total_heures += $teacher_total_heures;
                    $grand_total_brut += $teacher_total_brut;
                    $grand_total_aib += $teacher_total_aib;
                    $grand_total_net += $teacher_total_net;
                @endphp

                @if(!$loop->last)
                    <tr>
                        <td colspan="10" style="padding: 0; border: none;">
                            <div class="teacher-separator"></div>
                        </td>
                    </tr>
                @endif
            @endforeach

            <!-- TOTAL GÉNÉRAL -->
            <tr class="grand-total no-break">
                <td colspan="4" style="text-align: right;">TOTAUX GÉNÉRAUX</td>
                <td>{{ $total_classes }} classes</td>
                <td>{{ abs($grand_total_heures) }}h</td>
                <td>—</td>
                <td>{{ number_format($grand_total_brut, 0, ',', ' ') }} F</td>
                <td>{{ number_format($grand_total_aib, 0, ',', ' ') }} F</td>
                <td>{{ number_format($grand_total_net, 0, ',', ' ') }} F</td>
            </tr>
        </tbody>
    </table>

    <!-- Statistiques -->
    <div class="stats no-break">
        <h4>RÉCAPITULATIF FINANCIER</h4>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ number_format($grand_total_brut, 0, ',', ' ') }} F</div>
                <div class="stat-label">Masse salariale brute</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ number_format($grand_total_aib, 0, ',', ' ') }} F</div>
                <div class="stat-label">Total AIB (5%)</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ number_format($grand_total_net, 0, ',', ' ') }} F</div>
                <div class="stat-label">Net à payer</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $total_teachers }}</div>
                <div class="stat-label">Enseignants</div>
            </div>
        </div>
    </div>

    <!-- SIGNATURE -->
    <div class="signature">
        La Secrétaire Générale
    </div>

    <!-- PIED DE PAGE PDF -->
    <div class="pdf-footer">
        CPEG MARIE-ALAIN - Liste de paye {{ $subject->name }} - Période du {{ date('d/m/Y', strtotime($start)) }} au {{ date('d/m/Y', strtotime($end)) }}
        <div class="page-number">
            Page <span class="pagenum"></span> sur <span class="pagecount"></span>
        </div>
    </div>
</body>
</html>
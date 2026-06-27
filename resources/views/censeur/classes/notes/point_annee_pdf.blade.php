<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Point de l'Année - {{ $classe->name ?? '' }}</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 7px;
            margin: 0;
            color: #000;
        }

        /* --- Ligne tricolore --- */
        .tricolor-line {
            width: 70%;
            margin-bottom: 5px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .tricolor-line td {
            height: 3px;
            padding: 0;
            border: none;
            width: 33.33%;
        }
        .tricolor-line .green  { background-color: #008751; }
        .tricolor-line .yellow { background-color: #FCD116; }
        .tricolor-line .red    { background-color: #E8112D; }

        /* --- Header --- */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 6px;
        }
        .header-left, .header-right {
            display: table-cell;
            width: 12%;
            vertical-align: middle;
            text-align: center;
        }
        .header-left img, .header-right img {
            height: 50px;
            object-fit: contain;
        }
        .school-info {
            display: table-cell;
            width: 76%;
            text-align: center;
            font-size: 8px;
            line-height: 1.3;
        }
        .school-info .bold { font-weight: bold; }

        /* Date */
        .date-download {
            text-align: right;
            font-size: 7px;
            margin-bottom: 3px;
        }

        /* Titre */
        .title {
            text-align: center;
            margin-bottom: 5px;
            font-size: 10px;
            font-weight: bold;
            text-decoration: underline;
        }

        /* Informations de la classe */
        .class-info {
            text-align: center;
            margin: 4px 0;
            font-size: 8px;
        }
        .class-info span { margin: 0 6px; }

        /* --- Tableau principal --- */
        table.main-table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            margin-top: 6px;
            font-size: 6.5px;
        }
        table.main-table th,
        table.main-table td {
            border: 0.5px solid #000;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            color: #000;
        }

        /* En-têtes groupes trimestres */
        .th-group {
            background-color: #d0d0d0;
            color: #000;
            font-weight: bold;
            font-size: 7px;
        }

        /* En-têtes colonnes */
        .th-col {
            background-color: #e8e8e8;
            color: #000;
            font-weight: bold;
            font-size: 6px;
        }

        /* En-têtes fixes (N°, Matricule, Nom) */
        .th-base {
            background-color: #f0f0f0;
            color: #000;
            font-weight: bold;
            font-size: 7px;
        }

        /* Zébrure légère */
        .row-even { background-color: #f7f7f7; }
        .row-odd  { background-color: #ffffff; }

        /* Moyenne annuelle */
        .moy-ann  { font-weight: bold; font-size: 7px; }

        /* Statut */
        .statut-passe    { font-weight: bold; font-size: 6px; }
        .statut-redouble { font-weight: bold; font-size: 6px; }

        /* --- Statistiques --- */
        .stats-table {
            width: 60%;
            margin: 8px auto 0 auto;
            border-collapse: collapse;
            font-size: 8px;
        }
        .stats-table td {
            border: 0.5px solid #000;
            padding: 5px 8px;
            text-align: center;
            vertical-align: middle;
            background-color: #fff;
        }
        .stat-value { font-size: 14px; font-weight: bold; }
        .stat-label { font-size: 7px; margin-top: 2px; }

        /* Signature */
        .signature-block {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
        }

        /* Pied de page */
        .pdf-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            border-top: 0.5px solid #000;
            padding-top: 3px;
        }

        @page {
            margin: 8mm 8mm 12mm 8mm;
            size: A4 landscape;
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

    <!-- Date -->
    <div class="date-download">Calavi, le {{ $dateDownload }}</div>

    <!-- Titre -->
    <div class="title">
        POINT DE L'ANNÉE ACADÉMIQUE {{ strtoupper($activeYear->name) }}
    </div>

    <!-- Infos classe -->
    <div class="class-info">
        <span><strong>Classe :</strong> {{ $classe->name }}</span>
        <span><strong>Effectif :</strong> {{ $nbTotal }} élèves</span>
        <span><strong>Admis :</strong> {{ $nbPasses }}</span>
        <span><strong>Redoublants :</strong> {{ $nbRedoubles }}</span>
        <span><strong>Taux de réussite :</strong> {{ $tauxReussite }}%</span>
    </div>

    <!-- Tableau -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="th-base" style="width: 2%;">N°</th>
                <th rowspan="2" class="th-base" style="width: 7%;">Matricule</th>
                <th rowspan="2" class="th-base" style="width: 17%; text-align: left; padding-left: 3px;">Nom &amp; Prénom(s)</th>
                {{-- Trimestre 1 : 3 col × 6% = 18% --}}
                <th colspan="3" class="th-group">Trimestre 1</th>
                {{-- Trimestre 2 : 3 col × 6% = 18% --}}
                <th colspan="3" class="th-group">Trimestre 2</th>
                {{-- Trimestre 3 : 3 col × 6% = 18% --}}
                <th colspan="3" class="th-group">Trimestre 3</th>
                {{-- Fin d'année : 3 col --}}
                <th colspan="3" class="th-group">Fin d'Année</th>
            </tr>
            <tr>
                {{-- T1 --}}
                <th class="th-col" style="width: 5%;">Conduite</th>
                <th class="th-col" style="width: 5%;">Moy.</th>
                <th class="th-col" style="width: 4%;">Rang</th>
                {{-- T2 --}}
                <th class="th-col" style="width: 5%;">Conduite</th>
                <th class="th-col" style="width: 5%;">Moy.</th>
                <th class="th-col" style="width: 4%;">Rang</th>
                {{-- T3 --}}
                <th class="th-col" style="width: 5%;">Conduite</th>
                <th class="th-col" style="width: 5%;">Moy.</th>
                <th class="th-col" style="width: 4%;">Rang</th>
                {{-- Fin année --}}
                <th class="th-col" style="width: 6%;">Moy. Ann.</th>
                <th class="th-col" style="width: 4%;">Rang</th>
                <th class="th-col" style="width: 6%;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableauEleves as $row)
            @php
                $rowClass = $loop->even ? 'row-even' : 'row-odd';
                $fmtMoy   = fn($v) => $v !== null ? number_format($v, 2, ',', '') : '—';
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $row['num'] }}</td>
                <td style="font-size: 5.5px;">{{ $row['student']->num_educ ?? '-' }}</td>
                <td style="text-align: left; padding-left: 3px; font-size: 6.5px;">
                    <strong>{{ strtoupper($row['student']->last_name) }}</strong>
                    {{ $row['student']->first_name }}
                </td>

                {{-- Trimestre 1 --}}
                <td>{{ $row['conduite_t1'] > 0 ? number_format($row['conduite_t1'], 2, ',', '') : '—' }}</td>
                <td><strong>{{ $fmtMoy($row['moy_t1']) }}</strong></td>
                <td>{{ $row['rang_t1'] }}</td>

                {{-- Trimestre 2 --}}
                <td>{{ $row['conduite_t2'] > 0 ? number_format($row['conduite_t2'], 2, ',', '') : '—' }}</td>
                <td><strong>{{ $fmtMoy($row['moy_t2']) }}</strong></td>
                <td>{{ $row['rang_t2'] }}</td>

                {{-- Trimestre 3 --}}
                <td>{{ $row['conduite_t3'] > 0 ? number_format($row['conduite_t3'], 2, ',', '') : '—' }}</td>
                <td><strong>{{ $fmtMoy($row['moy_t3']) }}</strong></td>
                <td>{{ $row['rang_t3'] }}</td>

                {{-- Fin d'Année --}}
                <td class="moy-ann">{{ $fmtMoy($row['moy_annuelle']) }}</td>
                <td><strong>{{ $row['rang_annuel'] }}</strong></td>
                <td>
                    @if($row['statut'] === 'Passé')
                        <span class="statut-passe">PASSE</span>
                    @elseif($row['statut'] === 'Redouble')
                        <span class="statut-redouble">REDOUBLE</span>
                    @else
                        <span>—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Statistiques -->
    <table class="stats-table">
        <tr>
            <td>
                <div class="stat-value">{{ $nbPasses }}</div>
                <div class="stat-label">Élèves passent en classe supérieure</div>
            </td>
            <td>
                <div class="stat-value">{{ $nbRedoubles }}</div>
                <div class="stat-label">Élèves redoublent</div>
            </td>
            <td>
                <div class="stat-value">{{ $tauxReussite }}%</div>
                <div class="stat-label">Taux de Réussite</div>
            </td>
        </tr>
    </table>

    <!-- Signature -->
    <div class="signature-block">
        <p>Le Censeur,</p>
        <br><br>
        <p>___________________________</p>
    </div>

    <!-- Pied de page -->
    <div class="pdf-footer">
        CPEG MARIE-ALAIN &mdash; Point de l'Année Académique {{ $activeYear->name }} &mdash; Classe : {{ $classe->name }}
    </div>

</body>
</html>
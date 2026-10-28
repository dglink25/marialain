<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cahier de texte - {{ $classe->name }}</title>
    <style>
        @page {
            margin: 15mm 12mm 18mm 12mm;
            size: A4 portrait;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        /* --- En-tête Institutionnel --- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 8px;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .header-logo {
            width: 15%;
            text-align: center;
        }

        .header-logo img {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }

        .header-info {
            width: 70%;
            text-align: center;
            font-size: 8.5pt;
            line-height: 1.25;
        }

        .header-info .bold {
            font-weight: bold;
        }

        .header-info .school-name {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        /* Ligne de séparation noire institutionnelle */
        .header-divider {
            border: none;
            border-top: 2px solid #000000;
            margin: 6px 0 10px 0;
        }

        /* --- Titre du Document --- */
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 6px 0 4px 0;
            text-decoration: underline;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 9.5pt;
            font-style: italic;
            margin-bottom: 10px;
        }

        /* --- Boîte des Informations du Filtre --- */
        .filter-info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border: 1.5px solid #000000;
        }

        .filter-info-box td {
            border: 1px solid #000000;
            padding: 5px 8px;
            font-size: 9pt;
            vertical-align: middle;
        }

        .filter-info-box .label {
            font-weight: bold;
            width: 22%;
            background-color: #f7f7f7;
        }

        .filter-info-box .value {
            width: 28%;
        }

        /* --- Tableau Principal du Cahier de Texte --- */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 5px;
        }

        .content-table thead {
            display: table-header-group;
        }

        .content-table tr {
            page-break-inside: avoid;
        }

        .content-table th {
            border: 1px solid #000000;
            padding: 6px 4px;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            background-color: #eeeeee;
            color: #000000;
            text-transform: uppercase;
        }

        .content-table td {
            border: 1px solid #000000;
            padding: 6px 5px;
            font-size: 8.5pt;
            vertical-align: top;
            color: #000000;
        }

        /* En-tête de regroupement par Matière */
        .subject-group-header {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9pt;
            text-align: left !important;
            padding: 6px 8px !important;
            border: 1px solid #000000;
            page-break-after: avoid;
        }

        /* Alignements */
        .text-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
            text-justify: inter-word;
            word-wrap: break-word;
            line-height: 1.35;
        }

        /* Colonne Texte */
        .entry-content {
            white-space: pre-line;
            word-break: break-word;
        }

        /* Colonne Observation (laissée vide) */
        .observation-cell {
            background-color: #ffffff;
        }

        /* Ligne aucun cours */
        .no-data-row td {
            text-align: center;
            font-style: italic;
            padding: 12px;
            color: #333333;
        }

        /* Ligne de totalisation */
        .summary-row td {
            font-weight: bold;
            font-size: 8.5pt;
            background-color: #f7f7f7;
            padding: 5px;
        }

        /* --- Signatures Institutionnelles --- */
        .signatures-section {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .signatures-table td {
            border: none;
            vertical-align: top;
            font-size: 9pt;
            padding: 0;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 50px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* --- Pagination Bas de Page Fixe --- */
        .footer-pagination {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            border-top: 0.5px solid #000000;
            padding-top: 4px;
            color: #000000;
        }

        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>
<body>

    {{-- PIED DE PAGE FIXE POUR TOUTES LES PAGES --}}
    <div class="footer-pagination">
        CPEG MARIE-ALAIN - Cahier de texte officiel | Année scolaire : {{ $academicYear->name }} - Classe : {{ $classe->name }} | Page <span class="pagenum"></span>
    </div>

    {{-- EN-TÊTE INSTITUTIONNEL --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('logo.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo.png'))) }}" alt="Logo">
                @endif
            </td>
            <td class="header-info">
                <div class="bold">RÉPUBLIQUE DU BÉNIN</div>
                <div>MINISTÈRE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
                <div>DIRECTION DÉPARTEMENTALE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE DE L'ATLANTIQUE</div>
                <div class="school-name">CPEG « MARIE-ALAIN »</div>
                <div style="font-size: 7.5pt; font-style: italic;">Discipline - Travail - Succès</div>
            </td>
            <td class="header-logo">
                @if(file_exists(public_path('logo.png')))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('logo.png'))) }}" alt="Logo">
                @endif
            </td>
        </tr>
    </table>

    <hr class="header-divider">

    {{-- TITRE DU DOCUMENT --}}
    <div class="doc-title">CAHIER DE TEXTES</div>
    <div class="doc-subtitle">
        Document officiel de suivi pédagogique des enseignements et apprentissages
    </div>

    {{-- INFORMATIONS DU FILTRE DE TÉLÉCHARGEMENT --}}
    <table class="filter-info-box">
        <tr>
            <td class="label">Année académique :</td>
            <td class="value"><strong>{{ $academicYear->name }}</strong></td>
            <td class="label">Classe :</td>
            <td class="value"><strong>{{ $classe->name }}</strong></td>
        </tr>

        @if($filterType === 'subject_class')
            <tr>
                <td class="label">Matière :</td>
                <td class="value"><strong>{{ $subject->name ?? 'N/A' }}</strong></td>
                <td class="label">Enseignant(s) :</td>
                <td class="value">
                    <strong>{{ !empty($teacherNames) ? $teacherNames : 'Non spécifié' }}</strong>
                </td>
            </tr>
        @elseif($filterType === 'teacher_class')
            <tr>
                <td class="label">Enseignant :</td>
                <td class="value"><strong>{{ $teacher->name ?? 'N/A' }}</strong></td>
                <td class="label">Matières enseignées :</td>
                <td class="value">
                    <strong>{{ !empty($subjectNames) ? $subjectNames : 'Toutes les matières assignées' }}</strong>
                </td>
            </tr>
        @else
            <tr>
                <td class="label">Périmètre :</td>
                <td class="value"><strong>Toutes les matières de la classe</strong></td>
                <td class="label">Matières concernées :</td>
                <td class="value"><strong>{{ count($groupedData) }} matière(s)</strong></td>
            </tr>
        @endif

        <tr>
            <td class="label">Période d'édition :</td>
            <td class="value">
                @if($startDate && $endDate)
                    Du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @elseif($startDate)
                    À partir du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                @elseif($endDate)
                    Jusqu'au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @else
                    Toute l'année scolaire
                @endif
            </td>
            <td class="label">Date d'édition :</td>
            <td class="value">{{ now()->format('d/m/Y à H:i') }}</td>
        </tr>

        <tr>
            <td class="label">Volume global :</td>
            <td class="value" colspan="3">
                <strong>{{ $grandTotalCount }} séance(s)</strong> au total
                @if($grandTotalCount > 0)
                    - Durée cumulée : <strong>{{ $grandTotalDuration }}</strong>
                @endif
            </td>
        </tr>
    </table>

    {{-- TABLEAU PRINCIPAL DES ENTRÉES --}}
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 14%;">Horaires</th>
                <th style="width: 9%;">Durée</th>
                <th style="width: 49%;">Texte</th>
                <th style="width: 16%;">Observation</th>
            </tr>
        </thead>
        <tbody>
            @if(empty($groupedData) || $grandTotalCount === 0)
                <tr class="no-data-row">
                    <td colspan="5">
                        Aucun enregistrement de cahier de texte trouvé pour les critères sélectionnés.
                    </td>
                </tr>
            @else
                @foreach($groupedData as $group)
                    {{-- Si filtre 2 ou 3 (ou s'il y a plusieurs matières), on affiche un séparateur propre par matière --}}
                    @if($filterType !== 'subject_class' || count($groupedData) > 1)
                        <tr>
                            <td colspan="5" class="subject-group-header">
                                MATIÈRE : {{ strtoupper($group['subject']->name ?? 'Matière inconnue') }}
                                @if(!empty($group['teachers_label']))
                                    &nbsp;|&nbsp; Enseignant : {{ $group['teachers_label'] }}
                                @endif
                                &nbsp;|&nbsp; ({{ $group['count'] }} séance{{ $group['count'] > 1 ? 's' : '' }} - {{ $group['duration'] }})
                            </td>
                        </tr>
                    @endif

                    @if(count($group['entries']) === 0)
                        <tr class="no-data-row">
                            <td colspan="5">Aucune séance enregistrée pour cette matière.</td>
                        </tr>
                    @else
                        @foreach($group['entries'] as $entry)
                            @php
                                $start = $entry->course_start_date ? \Carbon\Carbon::parse($entry->course_start_date) : null;
                                $end   = $entry->course_end_date   ? \Carbon\Carbon::parse($entry->course_end_date)   : null;

                                // Format Date
                                $dateStr = $start ? $start->format('d/m/Y') : '-';

                                // Format Horaires (ex: 10h-12h ou 10h05-12h05)
                                if ($start && $end) {
                                    $hStart = $start->minute === 0 ? $start->format('H\h') : $start->format('H\hi');
                                    $hEnd   = $end->minute === 0   ? $end->format('H\h')   : $end->format('H\hi');
                                    $horairesStr = $hStart . ' - ' . $hEnd;
                                } else {
                                    $horairesStr = '-';
                                }

                                // Format Durée
                                if ($start && $end) {
                                    $diffMin = abs($start->diffInMinutes($end));
                                    $dHours = floor($diffMin / 60);
                                    $dMins = $diffMin % 60;
                                    $dureeStr = $dMins === 0 ? ($dHours . 'h') : ($dHours . 'h' . str_pad($dMins, 2, '0', STR_PAD_LEFT));
                                } else {
                                    $dureeStr = $entry->formatted_duration ?? '-';
                                }
                            @endphp
                            <tr>
                                <td class="text-center">{{ $dateStr }}</td>
                                <td class="text-center">{{ $horairesStr }}</td>
                                <td class="text-center">{{ $dureeStr }}</td>
                                <td class="text-justify">
                                    <div class="entry-content">{!! nl2br(e($entry->content)) !!}</div>
                                </td>
                                <td class="observation-cell"></td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach

                {{-- Ligne récapitulative générale --}}
                <tr class="summary-row">
                    <td colspan="2" class="text-center">TOTAL CUMULÉ</td>
                    <td class="text-center">{{ $grandTotalDuration }}</td>
                    <td colspan="2">{{ $grandTotalCount }} séance(s) dispensée(s)</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- SECTION SIGNATURES OFFICIELLES --}}
    <div class="signatures-section">
        <table class="signatures-table">
            <tr>
                <td style="width: 50%; text-align: left;">
                    Fait à Abomey-Calavi, le {{ now()->format('d/m/Y') }}<br><br>
                    @if($filterType === 'subject_class' || $filterType === 'teacher_class')
                        <div class="signature-title">L'Enseignant</div>
                        <div style="height: 45px;"></div>
                        <div class="signature-name">
                            {{ $teacher->name ?? ($teacherNames ?: 'Signature') }}
                        </div>
                    @else
                        <div class="signature-title">Le Chef de Département / Titulaire</div>
                        <div style="height: 45px;"></div>
                        <div class="signature-name">Pour la coordination</div>
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    Visé par la Direction Pédagogique,<br><br>
                    <div class="signature-title" style="text-align: right;">Le Censeur des Études</div>
                    <div style="height: 45px;"></div>
                    <div class="signature-name" style="text-align: right;">Direction Pédagogique</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>

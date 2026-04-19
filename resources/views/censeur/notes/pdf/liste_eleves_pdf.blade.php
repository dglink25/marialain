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
        .tricolor-line .green  { background-color: #008751; }
        .tricolor-line .yellow { background-color: #FCD116; }
        .tricolor-line .red    { background-color: #E8112D; }

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

        /* --- Tableau principal --- */
        table.main-table {
            border-collapse: collapse;
            margin: auto;
            width: 100%;
            table-layout: fixed;
            font-family: "Times New Roman", Times, serif;
            margin-top: 15px;
            font-size: 9px;
        }
        table.main-table th,
        table.main-table td {
            border: 1px solid #333;
            padding: 4px 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-family: "Times New Roman", Times, serif;
        }
        table.main-table th {
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

        /* Largeurs colonnes */
        .col-num     { width: 4%; }
        .col-mat     { width: 7%; }
        .col-nom     { width: 14%; text-align: left !important; padding-left: 4px !important; }
        .col-prenom  { width: 16%; text-align: left !important; padding-left: 4px !important; }
        .col-sexe    { width: 4%; }
        .col-note    { width: 5.5%; }

        /* Alternance de lignes */
        tbody tr:nth-child(even) { background-color: #fafafa; }

        /* Notes */
        .note-val  { font-weight: bold; }
        .note-none { color: #aaa; font-size: 8px; }

        /* En-tête groupe */
        .group-header-interro { background-color: #dbeafe !important; color: #1e3a8a; }
        .group-header-devoir  { background-color: #dcfce7 !important; color: #14532d; }
        .sub-header-interro   { background-color: #eff6ff !important; }
        .sub-header-devoir    { background-color: #f0fdf4 !important; }

        /* Pied de page PDF */
        @page { margin: 15mm; }

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

        /* Signature */
        .signature {
            margin-top: 40px;
            text-align: right;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }
    </style>
</head>
<body>

    <!-- ============================================================ -->
    <!-- HEADER (identique à notes_trimestre_blade.php)               -->
    <!-- ============================================================ -->
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
        LISTE ALPHABÉTIQUE DES ÉLÈVES &mdash; {{ strtoupper($subject->name) }}
        &mdash; {{ $trimestre }}<sup>e</sup> TRIMESTRE
    </div>

    <div class="class-info">
        <span><strong>Classe :</strong> {{ $classe->name }}</span>
        <span><strong>Année scolaire :</strong> {{ $activeYear->name }}</span>
        <span><strong>Effectif :</strong> {{ count($listeEleves) }} élève(s)</span>
        <span><strong>Matière :</strong> {{ $subject->name }}</span>
        <span><strong>Coefficient :</strong> {{ $subjectPivot->coefficient ?? 1 }}</span>
    </div>

    <table class="main-table">
        <thead>

            <tr>
                <th rowspan="2" class="col-num">N°</th>
                <th rowspan="2" class="col-mat">N° Matricule</th>
                <th rowspan="2" class="col-nom">Nom</th>
                <th rowspan="2" class="col-prenom">Prénoms</th>
                <th rowspan="2" class="col-sexe">Sexe</th>
                <!-- Groupe Interrogations -->
                <th colspan="5" class="group-header-interro">Interrogations</th>
                <!-- Groupe Devoirs -->
                <th colspan="2" class="group-header-devoir">Devoirs</th>
            </tr>
            <!-- Ligne 2 : sous-colonnes -->
            <tr>
                {{-- Interrogations I1 à I5 --}}
                @for($i = 1; $i <= 5; $i++)
                    <th class="col-note sub-header-interro">I{{ $i }}</th>
                @endfor
                {{-- Devoirs D1, D2 --}}
                @for($i = 1; $i <= 2; $i++)
                    <th class="col-note sub-header-devoir">D{{ $i }}</th>
                @endfor
            </tr>
        </thead>

        <tbody>
            @foreach($listeEleves as $index => $item)
            @php $student = $item['student']; @endphp
            <tr>
                <!-- Numéro d'ordre -->
                <td>{{ $index + 1 }}</td>

                <!-- N° Matricule -->
                <td>{{ $student->num_educ ?? '-' }}</td>

                <!-- Nom -->
                <td class="col-nom">{{ strtoupper($student->last_name) }}</td>

                <!-- Prénoms -->
                <td class="col-prenom">{{ ucfirst($student->first_name) }}</td>

                <!-- Sexe -->
                <td>{{ strtoupper($student->gender ?? $student->sexe ?? '-') }}</td>

                <!-- Interrogations I1 à I5 -->
                @for($i = 1; $i <= 5; $i++)
                    <td>
                        @if($item['interros'][$i] !== null)
                            <span class="note-val">{{ number_format($item['interros'][$i], 2) }}</span>
                        @else
                            <span class="note-none">&mdash;</span>
                        @endif
                    </td>
                @endfor

                <!-- Devoirs D1, D2 -->
                @for($i = 1; $i <= 2; $i++)
                    <td>
                        @if($item['devoirs'][$i] !== null)
                            <span class="note-val">{{ number_format($item['devoirs'][$i], 2) }}</span>
                        @else
                            <span class="note-none">&mdash;</span>
                        @endif
                    </td>
                @endfor
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature -->
    <div class="signature">
        Le Censeur
    </div>

    <!-- Pied de page -->
    <div class="pdf-footer">
        CPEG MARIE-ALAIN &mdash; Fiche de notes de l'enseignant {{ $subject->name }} &mdash; {{ $trimestre }}<sup>e</sup> trimestre &mdash; Classe : {{ $classe->name }}
    </div>

</body>
</html>
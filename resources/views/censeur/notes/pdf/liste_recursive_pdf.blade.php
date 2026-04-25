<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste récursive – Notes manquantes – Trimestre {{ $trimestre }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            margin: 18px 20px;
            color: #000000;
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
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header-left,
        .header-right {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
            text-align: center;
        }
        .header-left img,
        .header-right img {
            height: 70px;
            object-fit: contain;
        }
        .school-info {
            display: table-cell;
            width: 70%;
            text-align: center;
            font-size: 11px;
            line-height: 1.4;
            vertical-align: middle;
            color: #000;
        }
        .school-info .bold { font-weight: bold; }

        /* --- Date --- */
        .date-download {
            text-align: right;
            font-size: 10px;
            margin-bottom: 6px;
            color: #000;
        }

        /* --- Titre --- */
        .title {
            text-align: center;
            margin: 10px 0 6px 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
        }

        /* --- Sous-titre --- */
        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 14px;
            color: #000;
        }
        .subtitle span { margin: 0 10px; }

        /* --- Aucun manquant --- */
        .no-data {
            text-align: center;
            margin: 40px 0;
            padding: 20px;
            border: 2px solid #000;
            color: #000;
            font-size: 13px;
        }

        /* --- Tableau principal --- */
        table.main-table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            margin-top: 12px;
            font-size: 10px;
        }

        table.main-table th,
        table.main-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            color: #000;
            background-color: #fff;
        }

        table.main-table th {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #000;
        }

        /* Colonnes */
        .col-num     { width: 5%;  text-align: center; }
        .col-nom     { width: 22%; text-align: left; padding-left: 5px !important; }
        .col-classe  { width: 14%; text-align: center; }
        .col-matiere { width: 28%; text-align: left; padding-left: 4px !important; }
        .col-manque  { width: 31%; text-align: left; padding-left: 4px !important; }

        /* Nom enseignant (cellule du haut du groupe) */
        .td-num-first {
            text-align: center;
            font-weight: bold;
            vertical-align: top;
            padding-top: 6px !important;
        }
        .td-nom-first {
            font-weight: bold;
            font-size: 10.5px;
            vertical-align: top;
            padding-top: 6px !important;
            border-right: 2px solid #000;
        }

        /* Ligne de séparation entre enseignants (via border-top épais) */
        .row-teacher-first td {
            border-top: 2.5px solid #000 !important;
        }

        /* Texte notes manquantes */
        .badge-aucune {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000;
        }
        .badge-partiel {
            font-weight: bold;
            font-size: 9px;
            color: #000;
        }

        /* Signature */
        .signature {
            margin-top: 35px;
            text-align: right;
            font-weight: bold;
            font-size: 11px;
            color: #000;
        }

        /* Pied de page */
        @page { margin: 14mm 15mm; }

        .pdf-footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 4px;
        }

        .page-counter:before {
            content: "Page " counter(page) " / " counter(pages);
        }
    </style>
</head>
<body>

    <!-- Pied de page (fixed, déclaré en premier pour DomPDF) -->
    <div class="pdf-footer">
        CPEG MARIE-ALAIN &mdash; Liste récursive des notes manquantes &mdash;
        {{ $trimestre }}<sup>e</sup> trimestre &mdash; Année scolaire {{ $activeYear->name }}
        &nbsp;|&nbsp; <span class="page-counter"></span>
    </div>

    <!-- Header -->
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
    <div class="date-download">
        Calavi, le {{ $dateDownload }}
    </div>

    <!-- Titre -->
    <div class="title">
        Liste récursive des enseignants avec notes manquantes
        &mdash; {{ $trimestre }}<sup>e</sup> trimestre
    </div>

    <div class="subtitle">
        <span><strong>Année scolaire :</strong> {{ $activeYear->name }}</span>
        <span><strong>Trimestre :</strong> {{ $trimestre }}<sup>e</sup></span>
        <span><strong>Total enseignants concernés :</strong> {{ count($listeFinale) }}</span>
    </div>

    <!-- Cas : aucun manquant -->
    @if(count($listeFinale) === 0)
        <div class="no-data">
            <strong>Félicitations !</strong><br>
            Tous les enseignants ont saisi au moins 2 interrogations et 2 devoirs
            pour le {{ $trimestre }}<sup>e</sup> trimestre.
        </div>
    @else

    <!-- Tableau -->
    {{--
        IMPORTANT DomPDF : on N'utilise PAS rowspan car DomPDF gère mal rowspan
        combiné avec des sauts de page. À la place, on répète le numéro et le nom
        sur chaque ligne du groupe, et on distingue visuellement le premier rang
        par un border-top épais + une mise en gras. Les lignes suivantes du même
        groupe ont le N° et le nom vides mais conservent la bordure normale.
    --}}
    <table class="main-table">
        <thead>
            <tr>
                <th class="col-num">N°</th>
                <th class="col-nom">Nom complet de l'enseignant</th>
                <th class="col-classe">Classe</th>
                <th class="col-matiere">Matière</th>
                <th class="col-manque">Notes manquantes</th>
            </tr>
        </thead>
        <tbody>

            @php $compteur = 1; @endphp

            @foreach($listeFinale as $idx => $entry)
                @php
                    $teacher    = $entry['teacher'];
                    $manquants  = $entry['manquants'];
                    $nomComplet = strtoupper($teacher->name ?? 'INCONNU');
                @endphp

                @foreach($manquants as $ligneIdx => $manquant)
                    @php
                        $isFirst  = ($ligneIdx === 0);
                        $isAucune = (strpos($manquant['details'], 'Aucune note') !== false);
                    @endphp

                    {{--
                        Chaque ligne est autonome (pas de rowspan).
                        La première ligne de chaque enseignant reçoit la classe
                        CSS "row-teacher-first" qui applique un border-top épais
                        (sauf pour le tout premier enseignant qui a déjà le header).
                    --}}
                    <tr class="{{ $isFirst && $idx > 0 ? 'row-teacher-first' : '' }}">

                        {{-- Numéro : affiché seulement sur la 1ère ligne, vide sinon --}}
                        <td class="col-num td-num-first" style="{{ !$isFirst ? 'border-top: 1px solid #ccc;' : '' }}">
                            {{ $isFirst ? $compteur : '' }}
                        </td>

                        {{-- Nom : affiché seulement sur la 1ère ligne, vide sinon --}}
                        <td class="col-nom {{ $isFirst ? 'td-nom-first' : '' }}" style="{{ !$isFirst ? 'border-top: 1px solid #ccc; border-right: 2px solid #000;' : '' }}">
                            {{ $isFirst ? $nomComplet : '' }}
                        </td>

                        <td class="col-classe" style="text-align:center;">
                            {{ $manquant['classe']->name ?? '-' }}
                        </td>

                        <td class="col-matiere">
                            {{ $manquant['subject']->name ?? '-' }}
                        </td>

                        <td class="col-manque">
                            @if($isAucune)
                                <span class="badge-aucune">*** AUCUNE NOTE SAISIE ***</span>
                            @else
                                <span class="badge-partiel">{{ $manquant['details'] }}</span>
                            @endif
                        </td>
                    </tr>

                    @if($isFirst)
                        @php $compteur++; @endphp
                    @endif

                @endforeach

            @endforeach

        </tbody>
    </table>

    @endif

    <div class="signature">
        Le Censeur
    </div>

</body>
</html>
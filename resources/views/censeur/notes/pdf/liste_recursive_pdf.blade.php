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
            color: #000;
        }

        /* ── Ligne tricolore ─────────────────────────────── */
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
            width: 33.33%;
        }
        .tricolor-line .green  { background-color: #008751; }
        .tricolor-line .yellow { background-color: #FCD116; }
        .tricolor-line .red    { background-color: #E8112D; }

        /* ── En-tête ─────────────────────────────────────── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        .header-table td { border: none; vertical-align: middle; }
        .header-logo     { width: 13%; text-align: center; }
        .header-logo img { height: 65px; object-fit: contain; }
        .header-info     {
            width: 74%;
            text-align: center;
            font-size: 10.5px;
            line-height: 1.45;
            color: #000;
        }
        .header-info .bold { font-weight: bold; }

        /* ── Date ────────────────────────────────────────── */
        .date-download {
            text-align: right;
            font-size: 10px;
            margin-bottom: 6px;
            color: #000;
        }

        /* ── Titre ───────────────────────────────────────── */
        .title {
            text-align: center;
            margin: 8px 0 5px;
            font-size: 13.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #000;
        }

        /* ── Sous-titre ──────────────────────────────────── */
        .subtitle {
            text-align: center;
            font-size: 10.5px;
            margin-bottom: 12px;
            color: #000;
        }
        .subtitle span { margin: 0 8px; }

        /* ── Message aucun manquant ──────────────────────── */
        .no-data {
            text-align: center;
            margin: 40px 0;
            padding: 20px;
            border: 2px solid #000;
            font-size: 13px;
            color: #000;
        }
        table.main-table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            margin-top: 10px;
            font-size: 10px;
        }

        table.main-table th,
        table.main-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            color: #000;
            background-color: #fff;
        }

        table.main-table th {
            font-weight: bold;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2.5px solid #000;
            background-color: #f0f0f0;
            padding: 6px 4px;
        }

        /* ── Largeurs des colonnes ───────────────────────── */
        .col-num     { width: 5%;  }
        .col-nom     { width: 22%; }
        .col-classe  { width: 13%; }
        .col-matiere { width: 32%; }
        .col-manque  { width: 28%; }

        /* ── Cellule N° (rowspan enseignant) ─────────────── */
        .td-num {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            vertical-align: middle;
            border-right: 1px solid #000;
        }

        /* ── Cellule Nom (rowspan enseignant) ────────────── */
        .td-nom {
            font-weight: bold;
            font-size: 10.5px;
            vertical-align: middle;
            padding-left: 6px !important;
            border-right: 2px solid #000;  /* bordure droite plus épaisse pour délimiter le nom */
        }

        /* ── Cellule Classe (rowspan matières) ───────────── */
        .td-classe {
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
            border-right: 1px solid #000;
        }

        /* ── Séparateur entre enseignants ────────────────── */
        /* On épaissit le border-top de la PREMIÈRE ligne de chaque enseignant (sauf le 1er) */
        .sep-teacher td {
            border-top: 2.5px solid #000 !important;
        }

        /* ── Séparateur entre classes d'un même enseignant ── */
        .sep-class td {
            border-top: 1.5px dashed #555 !important;
        }

        /* ── Badges notes manquantes ─────────────────────── */
        .badge-aucune {
            display: inline-block;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000;
            border: 1px solid #cc0000;
            padding: 1px 4px;
            border-radius: 2px;
        }
        .badge-partiel {
            font-size: 11px;
            color: #000;
        }
        .signature {
            margin-top: 35px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
            color: #000;
        }

        /* ── Pied de page ────────────────────────────────── */
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
            padding-top: 3px;
        }
        .page-counter:before {
            content: "Page " counter(page) " / " counter(pages);
        }
    </style>
</head>
<body>

    {{-- Pied de page fixe (déclaré en premier pour DomPDF) --}}
    <div class="pdf-footer">
        CPEG MARIE-ALAIN &mdash; Liste récursive des notes manquantes &mdash;
        {{ $trimestre }}<sup>e</sup> trimestre &mdash; Année scolaire {{ $activeYear->name }}
        &nbsp;|&nbsp; <span class="page-counter"></span>
    </div>

    {{-- ── En-tête ─────────────────────────────────────── --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('logo.png') }}" alt="Logo">
            </td>
            <td class="header-info">
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
            </td>
            <td class="header-logo">
                <img src="{{ public_path('logo.png') }}" alt="Logo">
            </td>
        </tr>
    </table>

    {{-- ── Date ───────────────────────────────────────── --}}
    <div class="date-download">Calavi, le {{ $dateDownload }}</div>

    {{-- ── Titre ──────────────────────────────────────── --}}
    <div class="title">
        Liste récursive des enseignants avec notes manquantes
        &mdash; {{ $trimestre }}<sup>e</sup> trimestre
    </div>

    <div class="subtitle">
        <span><strong>Année scolaire :</strong> {{ $activeYear->name }}</span>
        <span><strong>Trimestre :</strong> {{ $trimestre }}<sup>e</sup></span>
        <span><strong>Total enseignants concernés :</strong> {{ count($listeFinale) }}</span>
    </div>

    {{-- ── Aucun manquant ─────────────────────────────── --}}
    @if(count($listeFinale) === 0)
        <div class="no-data">
            <strong>Félicitations !</strong><br>
            Tous les enseignants ont saisi au moins 2 interrogations et 2 devoirs
            pour le {{ $trimestre }}<sup>e</sup> trimestre.
        </div>
    @else

    {{-- ══════════════════════════════════════════════════
         TABLEAU
         Données reçues du contrôleur (structure 2 niveaux) :
           $listeFinale[i]
             ├─ teacher           : User
             └─ classes[]
                  ├─ classe       : Classe
                  └─ matieres[]
                       ├─ subject : Subject
                       └─ details : string

         Rowspan :
           - td N° et td Nom  → rowspan = somme de toutes les matières de toutes les classes
           - td Classe        → rowspan = nb de matières dans cette classe
    ════════════════════════════════════════════════════ --}}
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

        @foreach($listeFinale as $teacherIdx => $entry)
            @php
                $teacher    = $entry['teacher'];
                $classes    = $entry['classes'];   // tableau de { classe, matieres[] }
                $nomComplet = strtoupper($teacher->name ?? 'INCONNU');

                // Calculer le rowspan total de l'enseignant
                // = somme du nombre de matières de toutes ses classes
                $totalLignes = 0;
                foreach ($classes as $grp) {
                    $totalLignes += count($grp['matieres']);
                }

                $firstRowOfTeacher = true; // pour émettre N° et Nom une seule fois
            @endphp

            @foreach($classes as $classIdx => $grp)
                @php
                    $classe        = $grp['classe'];
                    $matieres      = $grp['matieres'];
                    $nbMatieres    = count($matieres); // rowspan pour la colonne Classe
                    $firstRowOfClass = true;           // pour émettre Classe une seule fois
                @endphp

                @foreach($matieres as $matIdx => $matiere)
                    @php
                        $isAucune = (strpos($matiere['details'], 'Aucune note saisie') !== false);

                        // Classes CSS de séparation pour la ligne courante
                        $sepClass = '';
                        if ($firstRowOfTeacher && $teacherIdx > 0) {
                            $sepClass = 'sep-teacher';  // trait épais entre enseignants
                        } elseif ($firstRowOfClass && !$firstRowOfTeacher) {
                            $sepClass = 'sep-class';    // trait fin entre classes du même enseignant
                        }
                    @endphp

                    <tr class="{{ $sepClass }}">

                        {{-- ── Colonne 1 : N° (rowspan enseignant) ── --}}
                        @if($firstRowOfTeacher)
                        <td class="col-num td-num" rowspan="{{ $totalLignes }}">
                            {{ $compteur }}
                        </td>
                        @endif

                        {{-- ── Colonne 2 : Nom (rowspan enseignant) ── --}}
                        @if($firstRowOfTeacher)
                        <td class="col-nom td-nom" rowspan="{{ $totalLignes }}">
                            {{ $nomComplet }}
                        </td>
                        @endif

                        {{-- ── Colonne 3 : Classe (rowspan matières de cette classe) ── --}}
                        @if($firstRowOfClass)
                        <td class="col-classe td-classe" rowspan="{{ $nbMatieres }}">
                            {{ $classe->name ?? '-' }}
                        </td>
                        @endif

                        {{-- ── Colonne 4 : Matière ── --}}
                        <td class="col-matiere" style="padding-left:5px;">
                            {{ $matiere['subject']->name ?? '-' }}
                        </td>

                        {{-- ── Colonne 5 : Notes manquantes ── --}}
                        <td class="col-manque" style="padding-left:5px;">
                            @if($isAucune)
                                <span class="badge-aucune">&#9888; Aucune note saisie</span>
                            @else
                                <span class="badge-partiel">{{ $matiere['details'] }}</span>
                            @endif
                        </td>

                    </tr>

                    @php $firstRowOfClass = false; @endphp
                    @php $firstRowOfTeacher = false; @endphp

                @endforeach {{-- fin matieres --}}
            @endforeach {{-- fin classes --}}

            @php $compteur++; @endphp

        @endforeach {{-- fin enseignants --}}

        </tbody>
    </table>

    @endif

    <div class="signature">Le Censeur</div>

</body>
</html>
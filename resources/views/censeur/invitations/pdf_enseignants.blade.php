<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des enseignants</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            margin: 15px;
        }

        /* Ligne tricolore */
        .tricolor-line { width: 70%; margin-bottom: 6px; border-collapse: collapse; table-layout: fixed; }
        .tricolor-line td { height: 3px; padding: 0; border: none; }
        .tricolor-line .green  { background-color: #008751; }
        .tricolor-line .yellow { background-color: #FCD116; }
        .tricolor-line .red    { background-color: #E8112D; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 12px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .header-left, .header-right { display: table-cell; width: 13%; vertical-align: middle; text-align: center; }
        .header-left img, .header-right img { height: 65px; object-fit: contain; }
        .school-info { display: table-cell; width: 74%; text-align: center; font-size: 10px; line-height: 1.5; vertical-align: middle; }
        .school-info .bold { font-weight: bold; }

        /* Titre */
        .title { text-align: center; margin: 10px 0 4px; font-size: 13px; font-weight: bold; text-decoration: underline; }
        .subtitle { text-align: center; font-size: 10px; color: #555; margin-bottom: 12px; }

        /* Tableau */
        table.main {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        table.main th {
            background-color: #263f91;
            color: white;
            font-size: 10px;
            padding: 6px 5px;
            border: 1px solid #263f91;
            text-align: left;
        }
        table.main td {
            border: 1px solid #ccc;
            padding: 5px 5px;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
        }
        table.main tr:nth-child(even) td { background-color: #f4f6fb; }
        table.main tr:hover td { background-color: #e8edf8; }

        /* Largeurs colonnes */
        .col-num     { width: 4%; text-align: center; }
        .col-nom     { width: 24%; }
        .col-tel     { width: 16%; }
        .col-email   { width: 22%; }
        .col-matieres{ width: 34%; }

        .matieres-list { margin: 0; padding: 0; list-style: none; }
        .matieres-list li::before { content: "• "; color: #263f91; font-weight: bold; }

        /* Footer */
        .footer { margin-top: 30px; text-align: center; font-size: 11px; }
        .pdf-footer {
            position: fixed; bottom: 5mm; left: 0; right: 0;
            text-align: center; font-size: 9px; color: #777;
            border-top: 1px solid #ddd; padding-top: 3px;
        }
        @page { margin: 15mm; }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <img src="{{ public_path('logo.png') }}" alt="Logo">
    </div>
    <div class="school-info">
        <table class="tricolor-line" align="center">
            <tr><td class="green"></td><td class="yellow"></td><td class="red"></td></tr>
        </table>
        <div class="bold">REPUBLIQUE DU BENIN</div>
        <div>MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
        <div>DIRECTION DEPARTEMENTALE DES ENSEIGNEMENTS SECONDAIRE,<br>TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE DE L'ATLANTIQUE</div>
        <div class="bold">CPEG MARIE-ALAIN</div>
    </div>
    <div class="header-right">
        <img src="{{ public_path('logo.png') }}" alt="Logo">
    </div>
</div>

<!-- TITRE -->
<div class="title">Liste des Enseignants</div>
<div class="subtitle">Année académique : {{ $activeYear->name ?? '—' }} — Matières enseignées</div>

<!-- TABLEAU -->
<table class="main">
    <thead>
        <tr>
            <th class="col-num">N°</th>
            <th class="col-nom">Nom complet</th>
            <th class="col-tel">Téléphone</th>
            <th class="col-email">Email</th>
            <th class="col-matieres">Matières</th>
        </tr>
    </thead>
    <tbody>
        @forelse($enseignants as $i => $ens)
        <tr>
            <td class="col-num" style="text-align:center;">{{ $i + 1 }}</td>
            <td class="col-nom"><strong>{{ $ens['name'] }}</strong></td>
            <td class="col-tel">{{ $ens['phone'] }}</td>
            <td class="col-email">{{ $ens['email'] }}</td>
            <td class="col-matieres">
                @if(!empty($ens['matieres']))
                    <ul class="matieres-list">
                        @foreach($ens['matieres'] as $matiere)
                            <li>{{ $matiere }}</li>
                        @endforeach
                    </ul>
                @else
                    <span style="color:#999; font-style:italic;">Aucune matière</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center; padding: 20px; color:#999; font-style:italic;">
                Aucun enseignant trouvé
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<br>

<!-- SIGNATURE -->
<div class="footer">
    Fait à Calavi, le {{ now()->format('d/m/Y') }}
    <br><br><br><br>
    Le Censeur
</div>

<!-- PAGINATION -->
<script type="text/php">
    if (isset($pdf)) {
        $x    = $pdf->get_width() / 2;
        $y    = $pdf->get_height() - 14;
        $font = $fontMetrics->getFont("Times New Roman");
        $pdf->page_text($x, $y, "Page {PAGE_NUM} / {PAGE_COUNT}", $font, 8, [0,0,0]);
    }
</script>

</body>
</html>

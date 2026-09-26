<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Emploi du temps - {{ $class->name }}</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            margin: 15px;
        }
        .tricolor-line {
            width: 70%; margin-bottom: 6px; border-collapse: collapse; table-layout: fixed;
        }
        .tricolor-line td { height: 3px; padding: 0; border: none; }
        .tricolor-line .green  { background-color: #008751; }
        .tricolor-line .yellow { background-color: #FCD116; }
        .tricolor-line .red    { background-color: #E8112D; }

        .header { display: table; width: 100%; margin-bottom: 12px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .header-left, .header-right { display: table-cell; width: 13%; vertical-align: middle; text-align: center; }
        .header-left img, .header-right img { height: 65px; object-fit: contain; }
        .school-info { display: table-cell; width: 74%; text-align: center; font-size: 10px; line-height: 1.4; vertical-align: middle; }
        .school-info .bold { font-weight: bold; }

        table.main {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
            font-family: "Times New Roman", Times, serif;
        }
        table.main th, table.main td {
            border: 1px solid #333;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
        }
        table.main th { background-color: #e8e8e8; font-size: 10px; font-weight: bold; }
        table.main td:first-child { width: 55px; font-size: 10px; font-weight: bold; background-color: #f5f5f5; }

        .course { background-color: #cce5ff; font-size: 9px; font-weight: bold; }
        .teacher { font-size: 8px; color: #333; }
        .time-range { font-size: 8px; color: #555; font-style: italic; }
        .empty { background-color: #fafafa; }

        .title { text-align: center; margin: 8px 0; font-size: 13px; font-weight: bold; text-decoration: underline; }

        .footer { margin-top: 30px; text-align: center; font-size: 11px; }

        .pdf-footer {
            position: fixed; bottom: 5mm; left: 0; right: 0;
            text-align: center; font-size: 9px; color: #777;
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
<div class="title">Emploi du temps - {{ $class->name }}</div>

@php
    $days = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

    // Construire une grille : day => heure_debut => timetable
    // On marque aussi les cellules "couvertes" par un rowspan
    $grid    = [];   // $grid[day][slotIndex] = timetable|null|'skip'
    $rowspan = [];   // $rowspan[day][slotIndex] = nb de lignes à fusionner

    foreach ($days as $day) {
        foreach ($hours as $idx => $slot) {
            $grid[$day][$idx]    = null;
            $rowspan[$day][$idx] = 1;
        }
    }

    // Pour chaque timetable, trouver le slot de départ et calculer le rowspan
    foreach ($timetables as $tt) {
        $ttStart    = strtotime($tt->start_time);
        $ttEnd      = strtotime($tt->end_time);
        $startSlot  = null;
        $slotSpan   = 0;

        foreach ($hours as $idx => $slot) {
            // Heure de début du slot (ex: "07h-08h" → 07:00)
            $slotStartStr = substr($slot, 0, 2) . ':00';
            $slotEndStr   = substr($slot, 4, 2) . ':00';
            $slotStart    = strtotime($slotStartStr);
            $slotEnd      = strtotime($slotEndStr);

            // Le cours démarre dans ce slot (ou exactement à son début)
            if ($startSlot === null && $ttStart >= $slotStart && $ttStart < $slotEnd) {
                $startSlot = $idx;
            }
            // Compter les slots couverts par ce cours
            if ($startSlot !== null && $ttEnd > $slotStart) {
                $slotSpan++;
            }
        }

        if ($startSlot !== null && $slotSpan > 0) {
            $grid[$tt->day][$startSlot]    = $tt;
            $rowspan[$tt->day][$startSlot] = $slotSpan;
            // Marquer les slots suivants comme 'skip'
            for ($s = $startSlot + 1; $s < $startSlot + $slotSpan; $s++) {
                if (isset($grid[$tt->day][$s])) {
                    $grid[$tt->day][$s] = 'skip';
                }
            }
        }
    }
@endphp

<!-- TABLEAU -->
<table class="main">
    <thead>
        <tr>
            <th>Heure</th>
            @foreach($days as $day)
                <th>{{ $day }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($hours as $idx => $slot)
        <tr>
            <td>{{ $slot }}</td>
            @foreach($days as $day)
                @php $cell = $grid[$day][$idx] ?? null; @endphp
                @if($cell === 'skip')
                    {{-- cellule couverte par rowspan, ne pas afficher --}}
                @elseif($cell !== null)
                    <td class="course" rowspan="{{ $rowspan[$day][$idx] }}">
                        <div>{{ $cell->subject->name }}</div>
                        <div class="teacher">{{ $cell->teacher->name }}</div>
                        <div class="time-range">{{ date('H:i', strtotime($cell->start_time)) }} - {{ date('H:i', strtotime($cell->end_time)) }}</div>
                    </td>
                @else
                    <td class="empty"></td>
                @endif
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

<br><br><br>

<!-- SIGNATURE -->
<div class="footer">
    Fait à Calavi, le {{ isset($dateDownload) ? $dateDownload : now()->format('d/m/Y') }}<br><br><br><br>
    Le Censeur
</div>

<!-- PAGINATION (DomPDF compatible) -->
<div class="pdf-footer">
    Page <span class="pagenum"></span>
</div>

<script type="text/php">
    if (isset($pdf)) {
        $x = $pdf->get_width() / 2;
        $y = $pdf->get_height() - 20;
        $pdf->page_text($x, $y, "Page {PAGE_NUM} / {PAGE_COUNT}", null, 8, array(0, 0, 0));
    }
</script>

</body>
</html>

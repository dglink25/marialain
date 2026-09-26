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
        .tricolor-line { width: 70%; margin-bottom: 6px; border-collapse: collapse; table-layout: fixed; }
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
        table.main td:first-child { width: 50px; font-size: 10px; font-weight: bold; background-color: #f5f5f5; }

        .course { background-color: #cce5ff; font-size: 9px; font-weight: bold; }
        .teacher { font-size: 8px; color: #333; }
        .time-range { font-size: 8px; color: #555; font-style: italic; }

        .title { text-align: center; margin: 8px 0 10px; font-size: 13px; font-weight: bold; text-decoration: underline; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; }

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

    // Construire la grille slot par slot
    // grid[day][slotIdx] = timetable | 'skip' | null
    $grid    = [];
    $rowspan = [];

    foreach ($days as $day) {
        foreach ($hours as $idx => $slot) {
            $grid[$day][$idx]    = null;
            $rowspan[$day][$idx] = 1;
        }
    }

    foreach ($timetables as $tt) {
        $ttStart = strtotime($tt->start_time);
        $ttEnd   = strtotime($tt->end_time);

        $startSlot = null;
        $span      = 0;

        foreach ($hours as $idx => $slot) {
            // Extraire heure début/fin du slot : "07h-08h" → 07:00 / 08:00
            preg_match('/^(\d{2})h-(\d{2})h$/', $slot, $m);
            $slotStart = strtotime(sprintf('%02d:00', (int)$m[1]));
            $slotEnd   = strtotime(sprintf('%02d:00', (int)$m[2]));

            if ($startSlot === null) {
                // Le cours démarre dans ce slot (debut compris entre slotStart et slotEnd exclus)
                if ($ttStart >= $slotStart && $ttStart < $slotEnd) {
                    $startSlot = $idx;
                    $span = 1;
                }
            } else {
                // On est dans les slots suivants : le cours couvre ce slot si ttEnd > slotStart
                if ($ttEnd > $slotStart) {
                    $span++;
                } else {
                    break;
                }
            }
        }

        if ($startSlot !== null && $span > 0) {
            $grid[$tt->day][$startSlot]    = $tt;
            $rowspan[$tt->day][$startSlot] = $span;
            for ($s = $startSlot + 1; $s < $startSlot + $span; $s++) {
                if (array_key_exists($s, $grid[$tt->day])) {
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
                    {{-- fusionné par rowspan --}}
                @elseif($cell !== null)
                    <td class="course" rowspan="{{ $rowspan[$day][$idx] }}">
                        <div>{{ $cell->subject->name }}</div>
                        <div class="teacher">{{ $cell->teacher->name }}</div>
                        <div class="time-range">
                            {{ date('H:i', strtotime($cell->start_time)) }} - {{ date('H:i', strtotime($cell->end_time)) }}
                        </div>
                    </td>
                @else
                    <td></td>
                @endif
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

<br><br><br>

<!-- SIGNATURE -->
<div class="footer">
    Fait à Calavi, le {{ isset($dateDownload) ? $dateDownload : now()->format('d/m/Y') }}
    <br><br><br><br>
    Le Censeur
</div>

<script type="text/php">
    if (isset($pdf)) {
        $x     = $pdf->get_width() / 2;
        $y     = $pdf->get_height() - 14;
        $font  = $fontMetrics->getFont("Times New Roman");
        $pdf->page_text($x, $y, "Page {PAGE_NUM} / {PAGE_COUNT}", $font, 8, [0,0,0]);
    }
</script>

</body>
</html>

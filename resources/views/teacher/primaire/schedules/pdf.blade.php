<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - {{ $classe->name }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        /* Ligne tricolore */
        .tricolor { width: 60%; margin: 0 auto 6px; border-collapse: collapse; table-layout: fixed; }
        .tricolor td { height: 3px; padding: 0; border: none; width: 33.33%; }
        .tri-green  { background-color: #008751; }
        .tri-yellow { background-color: #FCD116; }
        .tri-red    { background-color: #E8112D; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 10px; border-bottom: 1.5px solid #333; padding-bottom: 8px; }
        .hd-logo { display: table-cell; width: 12%; vertical-align: middle; text-align: center; }
        .hd-logo img { height: 60px; object-fit: contain; }
        .hd-info  { display: table-cell; width: 76%; text-align: center; font-size: 9px; line-height: 1.4; vertical-align: middle; }
        .hd-info .bold { font-weight: bold; font-size: 10px; }

        /* Titre */
        .title { text-align: center; font-size: 13px; font-weight: bold; margin: 8px 0 6px; text-decoration: underline; }

        /* Grille */
        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            table-layout: fixed;
        }
        table.grid th,
        table.grid td {
            border: 1px solid #999;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.grid th {
            background-color: #dde4f0;
            font-weight: bold;
            font-size: 9px;
        }
        /* Colonne horaire plus étroite */
        table.grid th:first-child,
        table.grid td:first-child {
            width: 72px;
            font-size: 8px;
        }

        /* Ligne heure : fond légèrement coloré */
        .time-cell {
            background-color: #f0f4fa;
            font-weight: bold;
            font-size: 8px;
            color: #2d3a5e;
            line-height: 1.4;
        }

        /* Cellule vide */
        .empty-cell {
            background-color: #fafafa;
        }

        /* Cellule cours — fond coloré, lisible */
        .course-cell {
            background-color: #e8f0fe;
            vertical-align: middle;
            padding: 4px 3px;
        }

        /* Un bloc par cours empilé dans la case (ex: plusieurs créneaux de 15 min) */
        .course-block {
            padding: 2px 0;
        }
        .course-block + .course-block {
            border-top: 1px dashed #b6c6e8;
            margin-top: 2px;
        }

        .subject-name {
            font-weight: bold;
            font-size: 9px;
            color: #1a237e;
            margin-bottom: 1px;
        }

        .time-range {
            font-size: 7.5px;
            color: #374151;
            font-style: italic;
        }

        /* Footer */
        .signature { margin-top: 40px; text-align: right; font-size: 10px; }

        @page { size: A4 landscape; margin: 12mm; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="hd-logo"><img src="{{ public_path('logo.png') }}" alt="Logo"></div>
        <div class="hd-info">
            <table class="tricolor"><tr>
                <td class="tri-green"></td>
                <td class="tri-yellow"></td>
                <td class="tri-red"></td>
            </tr></table>
            <div>REPUBLIQUE DU BENIN</div>
            <div>MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
            <div class="bold">CPEG MARIE-ALAIN</div>
        </div>
        <div class="hd-logo"><img src="{{ public_path('logo.png') }}" alt="Logo"></div>
    </div>

    <div class="title">Emploi du temps &mdash; {{ $classe->name }}</div>

    <table class="grid">
        <thead>
            <tr>
                <th>Horaire</th>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($timeSlots as $slot)
            @php
                // Chaque ligne représente une heure : 07h00–08h00, 08h00–09h00...
                $h       = (int)substr($slot, 0, 2);
                $nextH   = $h + 1;
                $startFmt = $h . 'h00';
                $endFmt   = $nextH . 'h00';
            @endphp
            <tr>
                {{-- Colonne horaire : toujours affichée --}}
                <td class="time-cell">{{ $startFmt }}<br>–{{ $endFmt }}</td>

                @foreach($days as $day)
                @php
                    $cell = $grid[$day][$slot] ?? ['entries' => [], 'span' => 1, 'skip' => false];
                @endphp

                @if($cell['skip'])
                    {{-- Couvert par rowspan d'une ligne précédente : ne rien rendre --}}
                @elseif(count($cell['entries']))
                @php
                    $span = $cell['span'];
                @endphp
                <td rowspan="{{ $span }}" class="course-cell">
                    @foreach($cell['entries'] as $s)
                    @php
                        $sfmt = str_replace(':', 'h', substr($s->start_time, 0, 5));
                        $efmt = str_replace(':', 'h', substr($s->end_time,   0, 5));
                    @endphp
                    <div class="course-block">
                        <div class="subject-name">{{ $s->subject->name ?? '—' }}</div>
                        <div class="time-range">{{ $sfmt }} – {{ $efmt }}</div>
                    </div>
                    @endforeach
                </td>
                @else
                <td class="empty-cell"></td>
                @endif

                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        Fait à Calavi, le {{ now()->format('d/m/Y') }}<br><br><br>
        L'Enseignant
    </div>

</body>
</html>
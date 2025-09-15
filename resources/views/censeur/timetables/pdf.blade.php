<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Emploi du temps - {{ $class->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: center; vertical-align: middle; }
        th { background-color: #f0f0f0; }
        .course { background-color: #cce5ff; font-weight: bold; }
        .teacher { font-size: 10px; }
        .header { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:2px solid #333; padding-bottom:10px; }
        .header-left { display:flex; align-items:center; gap:15px; }
        .header-left img { height:80px; }
        .header-left .school-info { font-size:14px; line-height:1.3; }
        .header-left .school-info .bold { font-weight:bold; }
        .header-right img { height:60px; }
        .header { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:2px solid #333; padding-bottom:10px; }
    </style>
</head>
<body>

        <div class="header"style="text-align:center;">
            <div class="header-left">
                <div class="school-info">
                    <div class="bold">REPUBLIQUE DU BENIN</div>
                    <div>MINISTERE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
                    <div>DIRECTION DEPARTEMENTALE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE DE L'ATLANTIQUE</div>
                    <div class="bold">CPEG MARIE-ALAIN</div>
                </div>
            </div>

        </div>

    <h2 style="text-align:center;">Emploi du temps - {{ $class->name }}</h2>
    <div>
        <table>
        <thead>
            <tr>
                <th>Heure</th>
                @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($hours as $hourSlot)
                <tr>
                    <td>{{ $hourSlot }}</td>

                    @foreach(['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'] as $day)
                        @php
                            $startHour = explode('-', $hourSlot)[0];
                            $startHourFormatted = str_replace('h', ':00', $startHour);

                            // Cours qui commence à cette heure
                            $course = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                return $t->day === $day && date('H:i', strtotime($t->start_time)) === $startHourFormatted;
                            });

                            // Vérifier si cette heure est déjà couverte par un rowspan
                            $overlap = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                return $t->day === $day && strtotime($t->start_time) < strtotime($startHourFormatted) && strtotime($t->end_time) > strtotime($startHourFormatted);
                            });
                        @endphp

                        @if($course)
                            @php
                                $duration = (strtotime($course->end_time) - strtotime($course->start_time)) / 3600;
                            @endphp
                            <td class="course" rowspan="{{ $duration }}">
                                <div>{{ $course->subject->name }}</div>
                                <div class="teacher">{{ $course->teacher->name }}</div>
                                <div class="teacher">{{ date('H:i', strtotime($course->start_time)) }} - {{ date('H:i', strtotime($course->end_time)) }}</div>
                            </td>
                        @elseif($overlap)
                            {{-- Cellule déjà fusionnée, on saute --}}
                        @else
                            <td></td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    </div>

    <div>
        <div class="footer">
            Fait à Calavi, le {{ now()->format('d/m/Y') }}<br> <br>
            Le censeur
        </div>
    </div>
</body>
</html>

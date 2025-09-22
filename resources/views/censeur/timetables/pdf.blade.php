<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Emploi du temps - {{ $class->name }}</title>
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
            width: 33,33%;
        }
        .tricolor-line .green { background-color: #008751; } /* Vert du Bénin */
        .tricolor-line .yellow { background-color: #FCD116; } /* Jaune du Bénin */
        .tricolor-line .red { background-color: #E8112D; } /* Rouge du Bénin */

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

        /* --- Tableau --- */
        table {
            border-collapse: collapse;
            margin: auto;
            width: 100%;
            table-layout: fixed; /* empêche les débordements */
            font-family: "Times New Roman", Times, serif;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word; /* coupe le texte trop long */
            overflow: hidden;
            font-family: "Times New Roman", Times, serif;
        }
        th { 
            background-color: #f0f0f0; 
            font-size: 11px;
        }

        /* Largeurs adaptées */
        th:nth-child(1), td:nth-child(1) { width: 60px; }   /* Heure */
        th:nth-child(2), td:nth-child(2),
        th:nth-child(3), td:nth-child(3),
        th:nth-child(4), td:nth-child(4),
        th:nth-child(5), td:nth-child(5),
        th:nth-child(6), td:nth-child(6),
        th:nth-child(7), td:nth-child(7) { width: 90px; }

        /* Cours */
        .course {
            background-color: #cce5ff;
            font-weight: bold;
            padding: 3px;
            font-size: 10px;
            font-family: "Times New Roman", Times, serif;
        }
        .teacher { 
            font-size: 9px; 
            font-family: "Times New Roman", Times, serif;
        }

        /* Titre */
        .title {
            text-align: center;
            margin-bottom: 10px;
            font-family: "Times New Roman", Times, serif;
        }

        /* --- Footer --- */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            font-family: "Times New Roman", Times, serif;
        }

        /* --- Pagination PDF (DOMPDF / mPDF) --- */
        @page {
            margin: 20mm;
        }
        .pagenum:before {
            content: counter(page);
        }
        .pagecount:before {
            content: counter(pages);
        }
        .pdf-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #555;
            font-family: "Times New Roman", Times, serif;
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
            <!-- Ligne tricolore ajoutée ici avec un tableau pour meilleur support PDF -->
            
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

    <!-- TITRE -->
    <h2 class="title"><u>Emploi du temps - {{ $class->name }}</u></h2>
        <br>
    <!-- TABLEAU -->
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

                            $course = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                return $t->day === $day && date('H:i', strtotime($t->start_time)) === $startHourFormatted;
                            });

                            $overlap = $timetables->first(function($t) use ($day, $startHourFormatted) {
                                return $t->day === $day && strtotime($t->start_time) < strtotime($startHourFormatted) && strtotime($t->end_time) > strtotime($startHourFormatted);
                            });
                        @endphp

                        @if($course)
                            @php
                                $duration = max(1, round((strtotime($course->end_time) - strtotime($course->start_time)) / 3600));
                            @endphp
                            <td class="course" rowspan="{{ $duration }}">
                                <div>{{ $course->subject->name }}</div>
                                <div class="teacher">{{ $course->teacher->name }}</div>
                                <div class="teacher">{{ date('H:i', strtotime($course->start_time)) }} - {{ date('H:i', strtotime($course->end_time)) }}</div>
                            </td>
                        @elseif($overlap)
                            {{-- cellule fusionnée --}}
                        @else
                            <td></td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <!-- SIGNATURE -->
    <div class="footer">
        Fait à Calavi, le {{ now()->format('d/m/Y') }}<br><br><br><br> 
        Le Censeur
    </div>

    <!-- NUMÉRO DE PAGE -->
    <div class="pdf-footer">
        Page <span class="pagenum"></span> / <span class="pagecount"></span>
    </div>

</body>
</html>
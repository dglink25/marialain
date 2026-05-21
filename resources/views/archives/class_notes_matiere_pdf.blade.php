<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des élèves - {{ $class->name ?? '' }}</title>
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
        .tricolor-line .green { background-color: #008751; }
        .tricolor-line .yellow { background-color: #FCD116; }
        .tricolor-line .red { background-color: #E8112D; }

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
            table-layout: fixed;
            font-family: "Times New Roman", Times, serif;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-family: "Times New Roman", Times, serif;
            font-size: 10px;
        }
        th { 
            background-color: #f0f0f0; 
            font-size: 10px;
            font-weight: bold;
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

        .signature {
            margin-top: 60px;
            text-align: right;
            font-weight: bold;
            font-family: "Times New Roman", Times, serif;
        }

        /* --- Pagination PDF --- */
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

        /* Styles spécifiques pour le tableau des élèves */
        .date-download {
            text-align: right;
            font-size: 11px;
            margin-bottom: 10px;
            font-family: "Times New Roman", Times, serif;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 11px;
        }
        
        .stats-left, .stats-right {
            flex: 1;
        }
        
        .stats-center {
            flex: 1;
            text-align: center;
        }
        
        .stats-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            @if(file_exists(public_path('logo.png')))
                <img src="{{ public_path('logo.png') }}" alt="Logo gauche">
            @endif
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
            @if(file_exists(public_path('logo.png')))
                <img src="{{ public_path('logo.png') }}" alt="Logo droit">
            @endif
        </div>
    </div>

    <div class="details">
        <center>
            <div>
                <u><h2>FICHE DE NOTES - TRIMESTRE {{ $trimestre }}</h2></u>
               
                <h3>Classe : {{ $class->name }} | Matière : {{ $subject->name }}</h3>

                <p>Année académique : {{ $year->name }} | Coefficient : {{ $coef ?? 1 }}</p>
            </div>
        </center>
    </div>

    @if($trimestre == 0)
        {{-- Vue annuelle --}}
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Nom</th>
                    <th>Prénoms</th>
                    <th>Sexe</th>
                    <th>Moy. T1</th>
                    <th>Moy. T2</th>
                    <th>Moy. T3</th>
                    <th>Moy. Annuelle</th>
                    <th>Rang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ strtoupper($row['student']->last_name) }}</td>
                        <td>{{ ucfirst($row['student']->first_name) }}</td>
                        <td>{{ $row['student']->gender }}</td>
                        <td>{{ $fmt($row['moy_t1']) }}</td>
                        <td>{{ $fmt($row['moy_t2']) }}</td>
                        <td>{{ $fmt($row['moy_t3']) }}</td>
                        <td><strong>{{ $fmt($row['moy_ann']) }}</strong></td>
                        <td><strong>{{ $row['rang'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        {{-- Vue trimestrielle --}}
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Nom</th>
                    <th>Prénoms</th>
                    <th>Sexe</th>
                    <th colspan="5">Interrogations</th>
                    <th>Moy. I</th>
                    <th>Coef</th>
                    <th colspan="2">Devoirs</th>
                    <th>Moy./20</th>
                    <th>Rang</th>
                </tr>
                <tr>
                    <th colspan="4"></th>
                    @for($i = 1; $i <= 5; $i++)
                        <th>I{{ $i }}</th>
                    @endfor
                    <th></th>
                    <th></th>
                    <th>D1</th>
                    <th>D2</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ strtoupper($row['student']->last_name) }}</td>
                        <td>{{ ucfirst($row['student']->first_name) }}</td>
                        <td>{{ $row['student']->gender }}</td>
                        
                        {{-- Interrogations (max 5) --}}
                        @for($i = 0; $i < 5; $i++)
                            <td>{{ isset($row['interros'][$i]) ? $row['interros'][$i] : '-' }}</td>
                        @endfor
                        
                        <td>{{ $fmt($row['moyInterro']) }}</td>
                        <td>{{ $coef ?? 1 }}</td>
                        
                        {{-- Devoirs --}}
                        <td>{{ $fmt($row['devoir1']) }}</td>
                        <td>{{ $fmt($row['devoir2']) }}</td>
                        
                        <td><strong>{{ $fmt($row['moyenne']) }}</strong></td>
                        <td><strong>{{ $row['rang'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <br>
    
    <!-- Statistiques -->
    <div class="stats-container">
        <div class="stats-left">
            <strong>Total élèves :</strong> {{ $total }}
        </div>
        <div class="stats-center">
            <strong>Admis (≥10/20) :</strong> {{ $admis }}
        </div>
        <div class="stats-right">
            <strong>Date d'édition :</strong> {{ now()->format('d/m/Y') }}
        </div>
    </div>
    
    <div class="stats-container" style="margin-top: 10px;">
        <div class="stats-left">
            <strong>Moyenne min :</strong> {{ $minMoy }}
        </div>
        <div class="stats-center">
            <strong>Moyenne classe :</strong> {{ $moyClass }}
        </div>
        <div class="stats-right">
            <strong>Moyenne max :</strong> {{ $maxMoy }}
        </div>
    </div>

    <div class="footer">
        <p style="margin-top: 50px;">
            <span style="display: inline-block; width: 200px; border-top: 1px solid #333; padding-top: 5px;">
                <strong>Le Censeur</strong>
            </span>
        </p>
    </div>
</body>
</html>
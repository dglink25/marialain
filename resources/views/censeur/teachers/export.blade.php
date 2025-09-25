<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enseignants - {{ $class->name }}</title>
    <style>
        /* Styles de base */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            position: relative;
        }
        
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* --- Ligne tricolore --- */
        .tricolor-line {
            width: 50%;
            margin: 0 auto 8px auto;
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

        /* --- HEADER --- */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .logo-container {
            display: table-cell;
            width: 15%;
            vertical-align: middle;
            text-align: center;
        }

        .logo {
            width: 70px;
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
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-family: "Times New Roman", Times, serif;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px 4px;
            font-size: 11px;
            word-wrap: break-word;
            text-align: center;
            font-family: "Times New Roman", Times, serif;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Titre */
        .title {
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
            font-weight: bold;
            padding: 5px;
        }

        /* Pied de page */
        .footer {
            width: 100%;
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 11px;
            color: #666;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
            font-style: italic;
        }

        /* Largeurs des colonnes */
        th:nth-child(1), td:nth-child(1) { width: 5%; }   /* N° */
        th:nth-child(2), td:nth-child(2) { width: 20%; }  /* Nom & Prénoms */
        th:nth-child(3), td:nth-child(3) { width: 8%; }   /* Sexe */
        th:nth-child(4), td:nth-child(4) { width: 20%; }  /* Email */
        th:nth-child(5), td:nth-child(5) { width: 15%; }  /* Téléphone */
        th:nth-child(6), td:nth-child(6) { width: 32%; }  /* Matières enseignées */

        /* Styles pour l'impression/PDF */
        @media print {
            body {
                margin: 0;
                padding: 15px;
                position: relative;
            }
            
            .header {
                page-break-after: avoid;
            }
            
            .title {
                page-break-after: avoid;
            }
            
            table {
                page-break-inside: auto;
            }
            
            /* EMPÊCHER LA RÉPÉTITION DE L'EN-TÊTE DU TABLEAU */
            thead {
                display: table-row-group;
            }
            
            tbody {
                display: table-row-group;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            /* Numérotation des pages */
            .page-number:before {
                content: "Page " counter(page);
            }
            
            @page {
                margin: 1cm;
                @bottom-center {
                    content: "Page " counter(page) " sur " counter(pages);
                    font-family: "Times New Roman", Times, serif;
                    font-size: 10px;
                }
            }
        }
        
        /* Pagination pour PDF */
        .pagination {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            font-family: "Times New Roman", Times, serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Ligne tricolore avec tableau pour meilleur support PDF -->
        <table class="tricolor-line">
            <tr>
                <td class="green" style="width: 33.33%;"></td>
                <td class="yellow" style="width: 33.33%;"></td>
                <td class="red" style="width: 33.34%;"></td>
            </tr>
        </table>

        <!-- HEADER -->
        <div class="header">
            <div class="logo-container">
                <img src="{{ public_path('logo.png') }}" class="logo" alt="Logo Gauche">
            </div>
            
            <div class="school-info">
                <div class="republic"><strong>RÉPUBLIQUE DU BÉNIN</strong></div>
                <div class="ministry">MINISTÈRE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
                <div class="direction">DIRECTION DÉPARTEMENTALE DES ENSEIGNEMENTS SECONDAIRE, TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE DE L'ATLANTIQUE</div>
                <div class="school-name"><strong>CPEG MARIE-ALAIN</strong></div>
            </div>
            
            <div class="logo-container">
                <img src="{{ public_path('logo.png') }}" class="logo" alt="Logo Droit">
            </div>
        </div>

        <!-- TITRE -->
        <div class="title"><u>Liste des enseignants de la classe : {{ $class->name }}</u></div>

        <!-- TABLEAU -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Nom & Prénoms</th>
                        <th>Sexe</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Matières enseignées</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data['teacher']->name }}</td>
                            <td>{{ $data['teacher']->gender ?? '--' }}</td>
                            <td>{{ $data['teacher']->email ?? '--' }}</td>
                            <td>{{ $data['teacher']->phone ?? '--' }}</td>
                            <td>{{ $data['subjects']->join(', ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br>
        <br>
        <br>
        <!-- PIED DE PAGE -->
        <div class="footer">
            <div class="signature">Fait à Calavi, le {{ now()->format('d/m/Y') }}<br>Le Censeur</div>
        </div>

        <!-- PAGINATION 
        <div class="pagination">
            Page <span class="page-number"></span> 
        </div>
        -->
    </div>
    
    <script>
        // Compteur de pages pour l'affichage navigateur
        document.addEventListener('DOMContentLoaded', function() {
            // Simuler l'affichage du nombre de pages (approximatif)
            const rows = document.querySelectorAll('tbody tr');
            const rowsPerPage = 25; // Estimation du nombre de lignes par page
            const pageCount = Math.ceil(rows.length / rowsPerPage);
            
            if (pageCount > 0) {
                document.querySelector('.page-number').textContent = `1/${pageCount}`;
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des élèves</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 30px 40px; /* marges adaptées pour que rien ne se coupe */
        }

        table {
            width: 100%;
            margin: auto;
            border-collapse: collapse;
            table-layout: fixed;
        }

         th, td {
            border: 1px solid #333;
            padding: 5px;
            font-size: 10px;
            word-wrap: break-word;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        /* --- HEADER --- */
        .header {
            position: relative;
            text-align: center;
            margin-bottom: 10px;
            height: 90px;
        }

        .logo-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 70px;
        }

        .logo-right {
            position: absolute;
            right: 0;
            top: 0;
            width: 70px;
        }

        .school-info {
            font-size: 10px;
            line-height: 1.3;
            display: inline-block;
        }

        .bold { font-weight: bold; }

        /* --- FOOTER pagination --- */
        @page {
            margin: 30px 40px;
        }

        .footer {
            position: fixed;
            bottom: 5px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }

        .footer:after {
            content: "Page " counter(page) ;
        }

        /* Largeurs adaptées */
        th:nth-child(1), td:nth-child(1) { width: 7%; text-align: center; }   /* N° */
        th:nth-child(2), td:nth-child(2) { width: 14%; }  /* Numéro Éduque Master */
        th:nth-child(3), td:nth-child(3) { width: 21%; }  /* Nom */
        th:nth-child(4), td:nth-child(4) { width: 15%; }  /* Prénoms */
        th:nth-child(5), td:nth-child(5) { width: 15%; }  /* Date naissance */
        th:nth-child(6), td:nth-child(6) { width: 15%; }  /* Lieu naissance */
        th:nth-child(7), td:nth-child(7) { width: 10%; text-align: center; }   /* Sexe */
        th:nth-child(8), td:nth-child(8) { width: 17%; }  /* Nom parent */
        th:nth-child(9), td:nth-child(9) { width: 30%; }  /* Email parent */
        th:nth-child(10), td:nth-child(10) { width: 17%; }  /* Téléphone parent */
        

        /* Footer 
        @page {
            margin: 20mm;
            @bottom-center {
                content: "Page " counter(page) " / " counter(pages);
                font-size: 10px;
            }
        }*/
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <img src="{{ public_path('logo.png') }}" class="logo-left" alt="Logo Gauche">
        <div class="school-info">
            <div class="bold">REPUBLIQUE DU BENIN</div>
            <div>MINISTERE DES ENSEIGNEMENTS SECONDAIRE,<br>
                TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE</div>
            <div>DIRECTION DEPARTEMENTALE DES ENSEIGNEMENTS SECONDAIRE,<br>
                TECHNIQUE ET DE LA FORMATION PROFESSIONNELLE DE L'ATLANTIQUE</div>
            <div class="bold">CPEG MARIE-ALAIN</div>
        </div>
        <img src="{{ public_path('logo.png') }}" class="logo-right" alt="Logo Droit">
    </div>
     <hr>
    <!-- TITRE -->
     <h1>Année académique {{ $annee_academique -> name }}</h1>
    <h1>Élèves de la classe : {{ $class->name }}</h1>

    <!-- TABLEAU avec colonnes fixes adaptées -->
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>N° ÉducMaster</th>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Date de naissance</th>
                <th>Lieu de naissance</th>
                <th>Sexe</th>
                <th>Nom parent</th>
                <th>Email parent</th>
                <th>Téléphone parent</th>  
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $student->num_educ ?? '-' }}</td>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->birth_date }}</td>
                <td>{{ $student->birth_place }}</td>
                <td>{{ $student->gender ?? '-' }}</td>
                <td>{{ $student->parent_full_name}}</td>
                <td>{{ $student->parent_email }}</td>
                <td>{{ $student->parent_phone }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer"></div>

</body>
</html>
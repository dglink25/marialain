<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Élèves - {{ $class->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        h1 { text-align: center; }
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
    <h1>Élèves de la classe : {{ $class->name }}</h1>

    <table>
        
        <thead>
            <tr>
                <th>N°</th>
                <th>Numéro Éduque Master</th>
                <th>Nom</th>
                <th>Prénoms</th>
                <th>Date de naissance</th>
                <th>Lieu de naissance</th>
                <th>Sexe</th>
                <th>Email parent</th>
                <th>Téléphone parent</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
            <tr>
                <td>{{ $loop->iteration }}</td> {{-- Numéro automatique --}}
                <td>{{ $student->num_educ ?? '-' }}</td>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->birth_date }}</td>
                <td>{{ $student->birth_place }}</td>
                <td>{{ $student->gender ?? '-' }}</td>
                <td>{{ $student->parent_email }}</td>
                <td>{{ $student->parent_phone }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>

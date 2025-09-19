<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enseignants - {{ $class->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Liste des enseignants - Classe {{ $class->name }}</h2>
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
</body>
</html>

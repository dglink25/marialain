<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des étudiants</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin-top: 15px; color: #1d4ed8; }
        h3 { margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Liste alphabétique des étudiants</h1>

    @foreach($entities as $entity)
        <h2>{{ $entity->name }}</h2>

        @foreach($entity->classes as $classe)
            <h3>{{ $classe->name }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénoms</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classe->students as $student)
                        <tr>
                            <td>{{ $student->last_name }}</td>
                            <td>{{ $student->first_name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2">Aucun étudiant</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endforeach
</body>
</html>

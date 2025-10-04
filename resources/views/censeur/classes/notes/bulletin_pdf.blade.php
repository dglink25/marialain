<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin Trimestre {{ $trimestre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 5px; text-align: center; }
        th { background-color: #e3e3e3; }
        .header { text-align: center; margin-bottom: 15px; }
        .info { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>BULLETIN DU TRIMESTRE {{ $trimestre }}</h2>
        <p>Année académique : {{ $classe->academicYear->name ?? '' }}</p>
    </div>

    <div class="info">
        <strong>Nom :</strong> {{ strtoupper($student->last_name) }} |
        <strong>Prénom :</strong> {{ ucfirst($student->first_name) }} |
        <strong>Matricule :</strong> {{ $student->matricule ?? '-' }} |
        <strong>Classe :</strong> {{ $classe->name }} |
        <strong>Effectif :</strong> {{ $classe->students->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Matière</th><th>Coef</th>
                <th colspan="5">Interros</th>
                <th colspan="2">Devoirs</th>
                <th>Moy</th><th>Moy x Coef</th><th>Appréciation</th>
            </tr>
            <tr>
                <th colspan="2"></th>
                @for ($i = 1; $i <= 5; $i++) <th>I{{ $i }}</th> @endfor
                <th>D1</th><th>D2</th><th colspan="3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bulletin as $ligne)
                <tr>
                    <td>{{ $ligne['subject'] }}</td>
                    <td>{{ $ligne['coef'] }}</td>
                    @for ($i = 1; $i <= 5; $i++)
                        <td>{{ $ligne['interros'][$i] ?? '-' }}</td>
                    @endfor
                    <td>{{ $ligne['devoirs'][1] ?? '-' }}</td>
                    <td>{{ $ligne['devoirs'][2] ?? '-' }}</td>
                    <td>{{ $ligne['moyenne'] ?? '-' }}</td>
                    <td>{{ $ligne['moyCoeff'] ?? '-' }}</td>
                    <td>{{ $ligne['appreciation'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="9" align="right"><strong>Conduite :</strong></td><td colspan="3">{{ $conduiteFinale }}</td></tr>
            <tr><td colspan="9" align="right"><strong>Moyenne Générale :</strong></td><td colspan="3">{{ $moyenneGenerale }}</td></tr>
            <tr><td colspan="9" align="right"><strong>Appréciation Générale :</strong></td><td colspan="3">{{ $appreciationGenerale }}</td></tr>
            <tr><td colspan="9" align="right"><strong>Rang :</strong></td><td colspan="3">{{ $rang }} / {{ $classe->students->count() }}</td></tr>
        </tfoot>
    </table>
</body>
</html>

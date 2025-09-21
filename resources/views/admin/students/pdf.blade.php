<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 5px; }
        .footer { margin-top: 40px; font-size: 11px; }
        .signature { margin-top: 60px; text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Liste des élèves de la Classe de {{ $className ?? '' }}</h2>
    <p style="text-align: right; font-size: 11px;">
        Date de téléchargement : {{ now()->format('d/m/Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">N°</th>
                <th rowspan="2">N° Éduc Master</th>
                <th rowspan="2">Nom</th>
                <th rowspan="2">Prénoms</th>
                <th rowspan="2">Sexe</th>
                <th rowspan="2">Niveau</th>
                <th rowspan="2">Classe</th>
                <th colspan="2" style="text-align: center;">Frais de Scolarité</th>
                <th rowspan="2">Date de naissance</th>
                <th rowspan="2">Parents/Tuteurs</th>
                <th rowspan="2">Date d'inscription</th>
            </tr>
            <tr>
                <th>Total payé</th>
                <th>Reste à payer</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $student->num_educ ?? '- -' }}</td>
                <td>{{ $student->last_name }}</td>
                <td>{{ $student->first_name }}</td>
                <td>{{ $student->gender ?? '- -' }}</td>
                <td>{{ $student->entity->name ?? '-' }}</td>
                <td>{{ $student->classe->name ?? '-' }}</td>
                <td>{{ $student->school_fees_paid ?? '- -' }} FCFA</td>
                <td>{{ number_format($student->remaining_fees,2) }} FCFA</td>
                <td>{{ $student->birth_date }}</td>
                <td>{{ $student->parent_full_name ?? ' - - ' }} <br> {{ $student->parent_phone ?? ' - - ' }}</td>
                <td>{{ $student->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align: center;">Aucun élève inscrit dans cette classe.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        La Secrétaire
    </div>
</body>
</html>

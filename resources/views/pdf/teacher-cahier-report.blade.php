@php
    use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Cahier de texte - {{ $teacher->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { color: #4f46e5; margin-bottom: 5px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { text-align: center; padding: 10px; border-radius: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background-color: #4f46e5; color: white; padding: 8px; }
        .table td { border: 1px solid #ddd; padding: 8px; }
        .validated { background-color: #d1fae5; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport Cahier de texte</h1>
        <p>Enseignant: {{ $teacher->name }} | Classe: {{ $class->name }} | Matière: {{ $subject->name }}</p>
        <p>Généré le: {{ $generated_at->format('d/m/Y H:i') }} par {{ $generated_by }}</p>
    </div>
    
    <div class="stats">
        <div class="stat-box" style="background-color: #dbeafe;">
            <strong>{{ $stats['total'] }}</strong><br>Total
        </div>
        <div class="stat-box" style="background-color: #d1fae5;">
            <strong>{{ $stats['validated'] }}</strong><br>Validés
        </div>
        <div class="stat-box" style="background-color: #fef3c7;">
            <strong>{{ $stats['pending'] }}</strong><br>En attente
        </div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Durée</th>
                <th>Contenu</th>
                <th>Statut</th>
                <th>Validation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr class="{{ $entry->is_validated ? 'validated' : '' }}">
                <td>{{ $entry->course_start_date->format('d/m/Y') }}</td>
                <td>{{ $entry->course_start_date->format('H:i') }} - {{ $entry->course_end_date->format('H:i') }}</td>
                <td>{{ $entry->formatted_duration }}</td>
                <td>{{ Str::limit($entry->content, 50) }}</td>
                <td>{{ $entry->isCourseOngoing() ? 'En cours' : ($entry->isCourseFinished() ? 'Terminé' : 'Planifié') }}</td>
                <td>
                    @if($entry->is_validated)
                        Validé par {{ $entry->validator->name ?? 'N/A' }}<br>
                        Le {{ $entry->validated_at->format('d/m/Y H:i') }}
                    @else
                        En attente
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        Rapport généré automatiquement - © {{ date('Y') }} Établissement
    </div>
</body>
</html>
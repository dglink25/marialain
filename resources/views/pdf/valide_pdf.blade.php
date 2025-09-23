<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .info { margin-bottom: 20px; }
        .info p { margin: 5px 0; }
        .footer { margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CPEG MARIE-ALAIN</h2>
        <p><strong>Reçu de paiement</strong></p>
    </div>

    <div class="info">
        <p><strong>Élève :</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
        <p><strong>Classe :</strong> {{ $student->classe->name ?? '' }}</p>
        <p><strong>Montant payé :</strong> {{ number_format($student->amount_paid, 0, ',', ' ') }} FCFA</p>
        <p><strong>Date :</strong> {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="footer">
        <p>Merci pour votre confiance.</p>
    </div>
    <div>
        <p style="margin-top: 30px;">La Secrétaire</p>
    </div>
    
</body>
</html>

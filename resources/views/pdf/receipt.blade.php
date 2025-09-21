<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CPEG MARIE-ALAIN</h2>
        <p>Reçu de Paiement</p>
    </div>

    <div class="details">
        <p><strong>Élève :</strong>{{ $payment->student->last_name }} {{ $payment->student->first_name }}</p>
        <p><strong>Classe :</strong> {{ $payment->student->classe->name }}</p>
        <p><strong>Parent :</strong> {{ $payment->student->parent_full_name ?? 'N/A' }}</p>
        <p><strong>Email Parent :</strong> {{ $payment->student->parent_email ?? 'N/A' }}</p>
    </div>

    <table>
        <tr>
            <th>Tranche</th>
            <th>Montant</th>
            <th>Date</th>
        </tr>
        <tr>
            <td>{{ $payment->tranche }}</td>
            <td>{{ number_format($payment->amount,2) }} FCFA</td>
            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
        </tr>
    </table>

    <p style="margin-top: 30px;">La Secrétaire</p>
</body>
</html>

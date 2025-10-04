<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Notification de Scolarité</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset de base pour emails */
        body {
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
        }
        table {
            border-spacing: 0;
            width: 100%;
        }
        td {
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .header {
            background: #2563eb;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .content {
            padding: 20px;
            font-size: 15px;
            line-height: 1.6;
        }
        .student-info {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .student-info p {
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            padding: 15px;
            background: #f9fafb;
        }
        @media screen and (max-width: 600px) {
            .content {
                padding: 15px;
            }
            .header {
                font-size: 18px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" width="100%">
        <tr>
            <td>
                <div class="container">
                    <!-- En-tête -->
                    <div class="header">
                        Notification de Scolarité
                    </div>

                    <!-- Contenu -->
                    <div class="content">
                        <p>Bonjour <strong>M./Mme {{ $student->parent_full_name }}</strong>,</p>

                        <p>{{ $messageBody }}</p>

                        <!-- Infos élève -->
                        <div class="student-info">
                            <p><strong>Élève :</strong> {{ $student->last_name }} {{ $student->first_name }}</p>
                            <p><strong>Classe :</strong> {{ $student->classe->name ?? '---' }}</p>
                            <p><strong>Frais exigés :</strong> {{ number_format($student->classe->school_fees ?? 0, 0, ',', ' ') }} FCFA</p>
                            <p><strong>Montant payé :</strong> {{ number_format($student->school_fees_paid, 0, ',', ' ') }} FCFA</p>
                            <p><strong>Reste dû :</strong> <span style="color:#dc2626;font-weight:bold;">
                                {{ number_format(($student->classe->school_fees ?? 0) - $student->school_fees_paid, 0, ',', ' ') }} FCFA
                            </span></p>
                        </div>

                        <p>Cordialement,<br>
                        <strong>La Secrétaire</strong></p><br><br><br>
                        
                    </div>

                    <!-- Pied de page -->
                    <div class="footer">
                        Ceci est un message automatique – merci de ne pas y répondre directement.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Invitation Enseignant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* Styles généraux pour clients modernes */
        body {
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            font-family: Arial, Helvetica, sans-serif;
            color: #333333;
        }
        table {
            border-spacing: 0;
            width: 100%;
        }
        img {
            border: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #2563eb;
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
            font-size: 22px;
            font-weight: bold;
        }
        .content {
            padding: 25px 20px;
            font-size: 15px;
            line-height: 1.6;
        }
        .student-info {
            background-color: #f3f4f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 15px;
        }
        .footer {
            background-color: #f3f4f6;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            padding: 20px;
        }
        /* Responsive */
        @media screen and (max-width: 600px) {
            .content {
                padding: 20px 15px;
            }
            .header {
                font-size: 20px;
                padding: 20px;
            }
            .btn {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <table role="presentation" width="100%" bgcolor="#f9fafb">
        <tr>
            <td align="center" style="padding:20px;">
                <div class="container">
                    
                    <!-- HEADER -->
                    <div class="header">
                        Invitation Enseignant
                    </div>

                    <!-- CONTENT -->
                    <div class="content">
                        <p>Bonjour <strong>{{ $invitation->user->name }}</strong>,</p>
                        <p>
                            Vous avez été invité à rejoindre la plateforme scolaire de 
                            <strong>{{ config('app.name') }}</strong>.
                        </p>

                        <div class="student-info">
                            <h3>Identifiant de connexion</h3>
                            <p><strong>Email :</strong> {{ $invitation->user->email }}</p>
                            <p><strong>Mot de passe :</strong> {{ $plainPassword }}</p>
                            <p><strong>Classe assignée :</strong> {{ $invitation->classe->name ?? '---' }}</p>
                        </div>

                        <p style="margin-bottom: 20px;">
                            Pour confirmer votre compte et accéder à la plateforme, cliquez sur le bouton ci-dessous :
                        </p>

                        <p style="text-align: center;">
                            <a href="{{ route('primaire.enseignants.accept', $invitation->token) }}" class="btn">
                                Confirmer mon compte
                            </a>
                        </p>

                        <p style="margin-top: 25px;">
                            Cordialement,<br>
                            <strong>Le Directeur Primaire</strong>
                        </p>
                    </div>

                    <!-- FOOTER -->
                    <div class="footer">
                        Ceci est un message automatique – merci de ne pas répondre.<br>
                        &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

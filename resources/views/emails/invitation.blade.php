<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Invitation</title>
    </head>
    <body style="font-family: Arial, sans-serif; background:#f9f9f9; padding:20px;">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px;">
            <tr><td align="center"><img src="{{ asset('logo.png') }}" alt="MARI ALAIN" height="60"></td></tr>
            <tr><td>
            <h2 style="color:#1e40af;">Invitation à rejoindre la plateforme</h2>
            <p>Bonjour,</p>
            <p>Vous avez été invité à rejoindre la plateforme <strong>MARI ALAIN</strong> en tant que <strong>{{ $invitation->role->display_name ?? 'Acteur' }}</strong>.</p>
            <p>Cliquez sur le lien ci-dessous pour compléter votre inscription :</p>
            <p><a href="{{ url('/register?token='.$invitation->token) }}" style="display:inline-block; padding:10px 20px; background:#1e40af; color:#fff; text-decoration:none; border-radius:4px;">Accepter l'invitation</a></p>
            <p style="color:#6b7280; font-size:12px;">Si vous n'êtes pas concerné, ignorez simplement cet email.</p>
            </td></tr>
        </table>
    </body>
</html>
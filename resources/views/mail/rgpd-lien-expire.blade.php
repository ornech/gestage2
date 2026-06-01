<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lien expiré — {{ config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; display: flex; justify-content: center; padding: 60px 16px; }
        .card { background: white; max-width: 520px; width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); padding: 40px; text-align: center; }
        .icon { font-size: 48px; margin-bottom: 16px; }
        h1 { font-size: 22px; color: #c0392b; margin-bottom: 12px; }
        p { font-size: 15px; line-height: 1.6; color: #555; }
        a { color: #3273dc; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⏱️</div>
        <h1>Ce lien a expiré</h1>
        <p>
            Le lien de suppression de votre adresse e-mail n'est valable que 90 jours
            et n'est plus actif.
        </p>
        <p style="margin-top:16px;">
            Pour exercer vos droits (suppression, accès, rectification, opposition),
            contactez-nous directement par e-mail à l'adresse suivante :<br><br>
            <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
        </p>
        <p style="margin-top:16px; font-size:13px; color:#888;">
            Mentionnez votre nom, prénom et l'établissement concerné dans votre message.
            Nous traiterons votre demande dans un délai d'un mois conformément au RGPD.
        </p>
    </div>
</body>
</html>

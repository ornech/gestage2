<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 15px; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .header { background: #3273dc; color: white; padding: 20px 24px; border-radius: 6px 6px 0 0; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { background: #f9f9f9; padding: 24px; border: 1px solid #e0e0e0; }
        .footer { font-size: 12px; color: #888; padding: 16px 24px; border-top: 1px solid #e0e0e0; }
        .btn { display: inline-block; background: #3273dc; color: white; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 16px; }
        .info-box { background: #fff8e1; border: 1px solid #ffe082; border-radius: 4px; padding: 14px 16px; margin-top: 20px; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>Bienvenue sur {{ config('app.name') }}</h1>
    </div>

    <div class="body">
        <p>Bonjour <strong>{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</strong>,</p>

        <p>
            Un compte vient d'être créé pour vous sur l'application
            <strong>{{ config('app.name') }}</strong> (gestion des stages BTS SIO).
        </p>

        <p>Pour l'activer, choisissez votre mot de passe personnel en cliquant sur le bouton ci-dessous :</p>

        <a href="{{ $lien }}" class="btn">Choisir mon mot de passe</a>

        <p style="font-size:13px; color:#666; margin-top:20px;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
            <a href="{{ $lien }}">{{ $lien }}</a>
        </p>

        <div class="info-box">
            <strong>À savoir</strong><br>
            Ce lien est personnel, utilisable une seule fois et valable {{ $validiteJours }} jour(s).
            Passé ce délai, rendez-vous sur la page de connexion et utilisez « Mot de passe oublié »
            avec l'adresse {{ $utilisateur->email }}.
        </div>
    </div>

    <div class="footer">
        {{ config('app.name') }} &mdash; Application de gestion des stages BTS SIO<br>
        Si vous n'attendiez pas ce message, ignorez-le : aucun accès n'est possible sans ce lien.
    </div>

</div>
</body>
</html>

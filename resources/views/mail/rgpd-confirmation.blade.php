<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression confirmée — {{ config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; display: flex; justify-content: center; padding: 60px 16px; }
        .card { background: white; max-width: 520px; width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); padding: 40px; text-align: center; }
        .icon { font-size: 48px; margin-bottom: 16px; }
        h1 { font-size: 22px; color: #2e7d32; margin-bottom: 12px; }
        p { font-size: 15px; line-height: 1.6; color: #555; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <h1>Vos coordonnées ont été supprimées</h1>
        <p>
            Conformément à votre demande et au Règlement Général sur la Protection des Données (RGPD),
            votre adresse e-mail et votre numéro de téléphone ont bien été retirés de notre base de données.
        </p>
        <p style="margin-top:16px; font-size:13px; color:#888;">
            Vos nom et prénom restent enregistrés pour les besoins administratifs du stage.<br>
            Pour toute autre demande, contactez l'établissement directement.
        </p>
    </div>
</body>
</html>

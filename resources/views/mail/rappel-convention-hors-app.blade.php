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
        <h1>Rappel — Informations de votre stage</h1>
    </div>

    <div class="body">
        <p>Bonjour <strong>{{ $etudiant->prenom }} {{ $etudiant->nom }}</strong>,</p>

        <p>
            Nous avons bien reçu votre convention de stage remise en dehors de l'application
            <strong>{{ config('app.name') }}</strong>.
        </p>

        <p>
            Cependant, il vous reste à renseigner dans l'application les informations essentielles
            de votre stage : <strong>l'entreprise</strong> et <strong>le maître de stage</strong>.
            Cette saisie est nécessaire pour que votre tuteur puisse vous suivre et que vous puissiez
            accéder à votre journal de compétences.
        </p>

        <a href="{{ config('app.url') }}" class="btn">
            Compléter mon stage sur {{ config('app.name') }}
        </a>

        <div class="info-box">
            <strong>Besoin d'aide ?</strong><br>
            Contactez votre tuteur pédagogique ou un professeur de la section SIO.
            @if($etudiant->tuteur?->email)
                <br>Votre tuteur référent : <a href="mailto:{{ $etudiant->tuteur->email }}">{{ $etudiant->tuteur->prenom }} {{ $etudiant->tuteur->nom }}</a>
            @endif
        </div>
    </div>

    <div class="footer">
        {{ config('app.name') }} &mdash; Application de gestion des stages BTS SIO<br>
        Ce message est envoyé automatiquement. Si les informations sont déjà saisies, ignorez ce message.
    </div>

</div>
</body>
</html>

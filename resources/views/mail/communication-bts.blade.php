<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; font-size: 15px; line-height: 1.7; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .header { background: #3273dc; color: white; padding: 20px 24px; border-radius: 6px 6px 0 0; }
        .header h1 { margin: 0; font-size: 18px; }
        .body { background: #f9f9f9; padding: 28px; border: 1px solid #e0e0e0; }
        .footer { font-size: 12px; color: #888; padding: 16px 24px; border-top: 1px solid #e0e0e0; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>{{ config('app.name') }}</h1>
    </div>

    <div class="body">
        <p>Madame, Monsieur <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,</p>

        <div style="white-space: pre-line;">{{ $corps }}</div>

        @if($expediteur)
        <p style="margin-top:24px; border-top:1px solid #e0e0e0; padding-top:16px; font-size:13px; color:#555;">
            <strong>{{ $expediteur->prenom }} {{ $expediteur->nom }}</strong><br>
            BTS SIO &mdash; {{ config('app.name') }}<br>
            <a href="mailto:{{ $expediteur->email }}">{{ $expediteur->email }}</a>
            @if($expediteur->telephone)
                &nbsp;&middot;&nbsp; {{ $expediteur->telephone }}
            @endif
        </p>
        @endif
    </div>

    <div class="footer">
        {{ config('app.name') }} &mdash; BTS SIO<br>
        Ce message vous est adressé en tant que maître de stage ou contact professionnel de notre section.
        @if($expediteur?->email)
            Pour vous désinscrire de ces communications, répondez à ce mail en indiquant votre souhait.
        @endif
    </div>

</div>
</body>
</html>

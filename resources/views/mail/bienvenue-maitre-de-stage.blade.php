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
        .rgpd-box { background: #fff8e1; border: 1px solid #ffe082; border-radius: 4px; padding: 16px; margin-top: 24px; font-size: 13px; }
        .rgpd-box a { color: #c0392b; }
        .contact-box { background: #e8f4fd; border-left: 4px solid #3273dc; padding: 12px 16px; margin: 16px 0; border-radius: 0 4px 4px 0; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>Accueil en stage &mdash; {{ config('app.name') }}</h1>
    </div>

    <div class="body">
        <p>Madame, Monsieur <strong>{{ $employe->prenom }} {{ $employe->nom }}</strong>,</p>

        <p style="white-space:pre-line;">{{ str_replace(['[PRENOM]', '[NOM]'], [$etudiant->prenom, $etudiant->nom], $introTexte) }}</p>

        @if($complementTexte)
        <p style="white-space:pre-line;">{{ $complementTexte }}</p>
        @endif

        @if($tuteur)
        <div class="contact-box">
            <strong>Votre interlocuteur pédagogique :</strong><br>
            {{ $tuteur->prenom }} {{ $tuteur->nom }}<br>
            <a href="mailto:{{ $tuteur->email }}">{{ $tuteur->email }}</a>
            @if($tuteur->telephone)
                &nbsp;&middot;&nbsp; {{ $tuteur->telephone }}
            @endif
        </div>
        <p>
            N'hésitez pas à contacter directement {{ $tuteur->prenom }} {{ $tuteur->nom }}
            pour toute question relative au déroulement du stage.
            Vous pouvez également répondre à ce mail, la réponse lui parviendra directement.
        </p>
        @endif
    </div>

    <div class="body" style="margin-top:1px;">
        <div class="rgpd-box">
            <strong>Information relative à la protection de vos données personnelles (RGPD)</strong><br><br>

            <strong>Responsable du traitement :</strong><br>
            {{ config('app.name') }}
            @if($tuteur)
                &mdash; contact : <a href="mailto:{{ $tuteur->email }}">{{ $tuteur->email }}</a>
            @endif
            <br><br>

            <strong>Données collectées :</strong><br>
            Dans le cadre du suivi administratif des stages, vos nom, prénom, adresse e-mail
            (<em>{{ $employe->email }}</em>)
            @if($employe->telephone)
                et numéro de téléphone (<em>{{ $employe->telephone }}</em>)
            @endif
            ont été enregistrés dans notre application par l'étudiant(e) que vous accueillez.<br><br>

            <strong>Base légale :</strong><br>
            Ce traitement est fondé sur l'intérêt légitime de l'établissement d'enseignement
            pour la gestion et le suivi des conventions de stage (art. 6.1.f du RGPD).<br><br>

            <strong>Durée de conservation :</strong><br>
            Vos données sont conservées pendant la durée du stage et les 5 années suivantes
            à des fins d'archivage administratif.<br><br>

            <strong>Vos droits :</strong><br>
            Conformément au RGPD, vous disposez des droits suivants sur vos données :
            <ul style="margin:6px 0; padding-left:18px;">
                <li><strong>Accès</strong> — obtenir une copie des données vous concernant</li>
                <li><strong>Rectification</strong> — corriger des données inexactes</li>
                <li><strong>Effacement</strong> — demander la suppression de votre adresse e-mail</li>
                <li><strong>Opposition</strong> — vous opposer au traitement</li>
            </ul>

            Pour supprimer votre adresse e-mail
            @if($employe->telephone) et votre numéro de téléphone @endif
            de notre base, utilisez le lien ci-dessous <em>(valable 90 jours)</em> :<br><br>
            <a href="{{ $lienRgpd }}">
                &rarr; Supprimer mes coordonnées de contact de la base de données
            </a><br><br>

            <em>
                Après expiration de ce lien ou pour exercer tout autre droit (accès, rectification,
                opposition), contactez-nous par e-mail :
                @if($tuteur)
                    <a href="mailto:{{ $tuteur->email }}">{{ $tuteur->email }}</a>.
                @else
                    <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
                @endif
                <br>
                Votre nom et prénom resteront enregistrés pour les besoins administratifs du stage,
                conformément à nos obligations légales.
            </em>
        </div>
    </div>

    <div class="footer">
        {{ config('app.name') }} &mdash; Application de gestion des stages BTS SIO<br>
        Ce message est envoyé automatiquement suite à la validation de la convention de stage.
        @if($tuteur)
            Contact pédagogique : <a href="mailto:{{ $tuteur->email }}">{{ $tuteur->email }}</a>
        @endif
    </div>

</div>
</body>
</html>

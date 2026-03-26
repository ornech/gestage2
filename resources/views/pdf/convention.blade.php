<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Convention de Stage</title>
</head>

<body style="font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height:1.4;">

    <!-- TITRE PRINCIPAL -->
    <h1 style="text-align:center; margin-bottom:30px;">CONVENTION DE STAGE</h1>

    <!-- PARTIE 1 : ENTREPRISE / LYCÉE -->
    <h2 style="font-size:16px; margin-top:20px;">ENTRE</h2>

    <p><strong>Nom de l’entreprise :</strong> {{ $stage->entreprise->nom }}</p>
    <p><strong>Adresse :</strong> {{ $stage->entreprise->adresse }}</p>
    <p><strong>SIRET :</strong> {{ $stage->entreprise->siret }}</p>

    <h2 style="font-size:16px; margin-top:20px;">ET</h2>

    <p><strong>LE LYCÉE MERLEAU-PONTY – NT Formation</strong></p>
    <p>Représenté par : <strong>Sylvie KOCIK</strong>, Proviseure</p>
    <p>Adresse : 3, Rue Raymonde Maous – BP 229 – 17304 ROCHEFORT CEDEX</p>
    <p>Tél : 05 46 99 23 20</p>
    <p>Mél : ce.0170022G@ac-poitiers.fr</p>

    <h3 style="margin-top:20px;">Professeur responsable :</h3>
    <p><strong>Nom :</strong> {{ $stage->professeur->name ?? '________________' }}</p>
    <p><strong>Mél :</strong> {{ $stage->professeur->email ?? '________________' }}</p>

    <h3 style="margin-top:20px;">Tuteur du stagiaire :</h3>
    <p><strong>Nom :</strong> {{ $stage->maitreDeStage->nom ?? '________________' }}</p>
    <p><strong>Fonction :</strong> {{ $stage->maitreDeStage->fonction ?? '________________' }}</p>
    <p><strong>Service :</strong> {{ $stage->maitreDeStage->service ?? '________________' }}</p>
    <p><strong>Tél :</strong> {{ $stage->maitreDeStage->telephone ?? '________________' }}</p>
    <p><strong>Mél :</strong> {{ $stage->maitreDeStage->email ?? '________________' }}</p>

    <h3 style="margin-top:20px;">Concernant le stage de :</h3>
    <p><strong>Nom :</strong> {{ $stage->etudiant->name }}</p>
    <p><strong>Section :</strong> {{ $stage->etudiant->section ?? 'SIO2' }}</p>
    <p><strong>Adresse :</strong> {{ $stage->etudiant->adresse ?? '________________' }}</p>
    <p><strong>Téléphone :</strong> {{ $stage->etudiant->telephone ?? '________________' }}</p>
    <p><strong>Mél :</strong> {{ $stage->etudiant->email }}</p>

    <!-- SAUT DE PAGE -->
    <div style="page-break-after: always;"></div>

    <!-- TITRE I -->
    <h2 style="text-align:center; margin-bottom:20px;">TITRE I : Dispositions générales</h2>

    <h3>Article 1 — Objet</h3>
    <p>
        Cette convention encadre la mise en situation professionnelle d’un étudiant du lycée,
        conformément aux textes réglementaires en vigueur. Elle vise à organiser une période
        d’apprentissage en entreprise permettant au stagiaire de développer ses compétences
        professionnelles dans un cadre sécurisé et validé par l’établissement.
    </p>

    <h3>Article 2 — Programme</h3>
    <p>
        Le stage a pour objectif d’offrir à l’étudiant une immersion dans le domaine des services
        informatiques. Le contenu du stage est défini par l’entreprise d’accueil et validé par
        l’équipe pédagogique. Le sujet du stage est présenté ci‑dessous :
    </p>

    <p style="margin-top:10px;"><strong>Sujet du stage :</strong></p>
    <p>{{ $stage->titre }}</p>

    <p>Une annexe peut être ajoutée si une description détaillée est nécessaire.</p>

    <h3>Article 3 — Durée</h3>
    <p>
        Le stage se déroule du <strong>{{ $stage->date_debut }}</strong> au
        <strong>{{ $stage->date_fin }}</strong> inclus.
    </p>

    <h3>Article 4 — Statut du stagiaire</h3>
    <p>
        Le stagiaire conserve son statut d’étudiant durant toute la période du stage. Il est suivi
        conjointement par un tuteur en entreprise et un professeur référent du lycée.
    </p>

    <h3>Article 5 — Assiduité et discipline</h3>
    <p>
        Le stagiaire respecte les règles de l’entreprise, notamment les horaires. En cas de manquement,
        l’entreprise peut mettre fin au stage après en avoir informé le lycée.
    </p>

    <h3>Article 6 — Accidents</h3>
    <p>
        Le stagiaire bénéficie de la législation relative aux accidents du travail. L’entreprise
        s’engage à déclarer tout incident au lycée dans les plus brefs délais.
    </p>

    <h3>Article 7 — Rémunération</h3>
    <p>
        Le stage n’est pas considéré comme un emploi salarié. Aucune rémunération n’est due.
    </p>

    <h3>Article 8 — Avantages en nature</h3>
    <p>
        Les frais engagés par le stagiaire restent à sa charge, sauf mission spécifique confiée par
        l’entreprise.
    </p>

    <h3>Article 9 — Attestation</h3>
    <p>
        À l’issue du stage, une attestation est remise au stagiaire, précisant les dates et la durée
        du stage.
    </p>

    <h3>Article 10 — Confidentialité</h3>
    <p>
        Le stagiaire s’engage à respecter la confidentialité des informations auxquelles il a accès
        et à ne commettre aucune infraction informatique.
    </p>

    <h3>Article 11 — Visite</h3>
    <p>
        Une visite en entreprise est réalisée par un enseignant du lycée durant le stage.
    </p>

    <!-- SAUT DE PAGE -->
    <div style="page-break-after: always;"></div>

    <!-- TITRE II -->
    <h2 style="text-align:center; margin-bottom:20px;">TITRE II : Dispositions particulières</h2>

    <h3>Article 1</h3>
    <p>
        Le stagiaire ne peut prétendre à aucune rémunération. Certaines entreprises peuvent toutefois
        accorder une gratification.
    </p>

    <h3>Article 2</h3>
    <p>
        Le stagiaire est suivi par un professeur référent. Une visite ou un entretien téléphonique
        est prévu pour évaluer le déroulement du stage.
    </p>

    <p style="margin-top:30px;">
        Fait en trois exemplaires à Rochefort, le ____ / ____ / ______
    </p>

    <!-- SIGNATURES -->
    <table style="width:100%; margin-top:40px;">
        <tr>
            <td style="width:33%; text-align:center;">
                <strong>Le chef d’entreprise</strong><br><br><br>
                __________________________
            </td>
            <td style="width:33%; text-align:center;">
                <strong>Le proviseur</strong><br><br><br>
                __________________________
            </td>
            <td style="width:33%; text-align:center;">
                <strong>Le stagiaire</strong><br><br><br>
                __________________________
            </td>
        </tr>
    </table>

</body>
</html>

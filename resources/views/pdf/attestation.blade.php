<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation de stage</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12pt; }
        h1   { text-align: center; }
        .info { margin-top: 2em; line-height: 2; }
    </style>
</head>
<body>
    <h1>Attestation de stage</h1>

    <div class="info">
        {{-- TODO : compléter avec les données réelles du stage --}}
        <p>Nous attestons que <strong>{{ $stage->etudiant->prenom ?? '' }} {{ $stage->etudiant->nom ?? '' }}</strong>
        a effectué un stage au sein de <strong>{{ $stage->entreprise->raison_sociale ?? '' }}</strong>
        du <strong>{{ $stage->date_debut?->format('d/m/Y') }}</strong>
        au <strong>{{ $stage->date_fin?->format('d/m/Y') }}</strong>.</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Convention de stage</title>
    <style>
        /* Marges gérées par mPDF dans le controller */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; font-size: 10pt; line-height: 1.35; }

        .table-parties td, .table-parties th {
            border: 0.75pt solid #000;
            padding: 5pt 7pt;
        }
        .table-parties th {
            background: #d8d8d8;
            font-weight: bold;
            text-align: center;
            font-size: 10.5pt;
        }
        .table-etudiant td {
            border: 0.75pt solid #000;
            padding: 5pt 8pt;
        }
        .table-sigs td {
            border: 0.75pt solid #000;
            padding: 5pt 8pt;
            text-align: center;
        }
        .noborder td { border: none; padding: 2pt 0; }

        /* ── Titres ── */
        .doc-title-box {
            border: 2pt solid #000;
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            padding: 7pt 10pt;
            letter-spacing: 0.5pt;
        }
        .titre-section {
            border: 1.5pt solid #000;
            text-align: center;
            font-size: 12.5pt;
            font-weight: bold;
            padding: 5pt;
            margin: 12pt 0 8pt;
            text-transform: uppercase;
        }

        /* ── Articles ── */
        .article-titre { font-weight: bold; text-decoration: underline; margin: 7pt 0 2pt; font-size: 10pt; }
        .article-corps { text-align: left; text-indent: 15pt; margin-bottom: 4pt; word-wrap: break-word; overflow-wrap: break-word; font-size: 10pt; line-height: 1.35; }
        .article-corps-noindent { text-align: left; margin-bottom: 3pt; word-wrap: break-word; font-size: 10pt; }

        /* ── Lignes pointillées ── */
        .dotline {
            display: block;
            border-bottom: 0.5pt dotted #444;
            min-height: 13pt;
            margin: 1pt 0;
            width: 100%;
        }

        /* ── Encadré mission ── */
        .encadre-mission {
            border: 0.75pt solid #000;
            padding: 6pt 8pt;
            margin: 5pt 0;
        }
        .ligne-mission {
            height: 16pt;
            border-bottom: 0.5pt dotted #555;
            margin: 0;
            display: block;
            width: 100%;
        }

        /* ── Article sans coupure ── */
        .article-bloc {
            page-break-inside: avoid;
        }

        /* ── Saut de page ── */
        .page-break { page-break-before: always; }

        .bold { font-weight: bold; }
        .center { text-align: center; }
    </style>
</head>
<body>

{{-- En-tête et pied de page gérés par mPDF via SetHTMLHeader/SetHTMLFooter dans le controller --}}

{{-- ════════════════════════════ PAGE 1 ════════════════════════════ --}}

{{-- ── Logo + Titre ── --}}
<table class="noborder" style="margin-bottom:10pt;">
    <tr>
        <td style="width:145pt; vertical-align:middle; text-align:center; padding:0; border:none;">
            @if(file_exists(public_path('img/logo-lmp.png')))
                <img src="{{ asset('img/logo-lmp.png') }}" width="130" alt="">
            @else
                <div style="font-weight:bold; font-size:12pt; line-height:1.3; text-align:center;">
                    Lycée<br>MERLEAU-PONTY
                </div>
            @endif
        </td>
        <td style="vertical-align:middle; padding-left:14pt; border:none;">
            <div class="doc-title-box">CONVENTION DE STAGE</div>
        </td>
    </tr>
</table>

{{-- ── Tableau des parties ── --}}
<table class="table-parties" style="margin-bottom:8pt;">
    <thead>
        <tr>
            <th style="width:50%;">ENTRE</th>
            <th style="width:50%;">ET</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align:center; padding:6pt 7pt;">
                <span class="bold" style="font-size:12pt;">LE LYCÉE<br>{{ mb_strtoupper($p['etablissement_nom'], 'UTF-8') }}</span>
            </td>
            <td style="padding:6pt 7pt;">
                <span class="dotline">{{ $stage->entreprise?->raison_sociale }}</span>
            </td>
        </tr>
        <tr>
            <td style="padding:6pt 7pt;">
                <span class="bold">Représenté par :</span><br>
                {{ $p['proviseur_civilite'] }} {{ $p['proviseur_nom'] }}<br>
                <span class="bold">{{ $p['proviseur_titre'] }}</span><br><br>
                <span class="bold">Adresse de l'établissement :</span><br>
                {{ $p['adresse'] }}<br>
                @if($p['bp']){{ $p['bp'] }}<br>@endif
                {{ $p['cp_ville'] }}<br>
                Tél : {{ $p['tel'] }}<br>
                Mél : {{ $p['mel'] }}<br><br>
                <span class="bold">Professeur responsable :</span><br>
                Nom : {{ $profPrincipal?->prenom }} {{ $profPrincipal?->nom }}<br>
                Tél : {{ $p['tel'] }}<br>
                Courriel : {{ $profPrincipal?->email }}
            </td>
            <td style="padding:6pt 7pt;">
                <span class="bold">Représenté par :</span><br>
                <span class="dotline">{{ $stage->maitreDeStage?->prenom }} {{ $stage->maitreDeStage?->nom }}</span>
                <br>
                <span class="bold">Fonction ;</span><br>
                <span class="dotline">{{ $stage->maitreDeStage?->fonction }}</span>
                <br>
                <span class="bold">Nom et adresse de l'entreprise :</span><br>
                <span class="dotline">{{ $stage->entreprise?->adresse }}</span>
                <span class="dotline">{{ $stage->entreprise?->complement_adresse }}</span>
                <span class="dotline">{{ $stage->entreprise?->code_postal }} {{ $stage->entreprise?->ville }}</span>
                <span class="dotline">&nbsp;</span>
                <span class="dotline">&nbsp;</span>
                <br>
                <span class="bold">Tuteur du stagiaire :</span><br>
                Nom : <span class="dotline">{{ $stage->maitreDeStage?->prenom }} {{ $stage->maitreDeStage?->nom }}</span>
                Fonction : <span class="dotline">{{ $stage->maitreDeStage?->fonction }}</span>
                Service : <span class="dotline">{{ $stage->maitreDeStage?->service }}</span>
                Tél : <span class="dotline">{{ $stage->maitreDeStage?->telephone }}</span>
                Courriel : <span class="dotline">{{ $stage->maitreDeStage?->email }}</span>
            </td>
        </tr>
    </tbody>
</table>

{{-- ── Étudiant ── --}}
<p style="font-weight:bold; font-size:11pt; text-transform:uppercase; margin:8pt 0 5pt;">
    Concernant le stage de formation professionnelle de :
</p>
<table class="table-etudiant" style="margin-bottom:6pt; page-break-inside:avoid;">
    <tr>
        <td style="width:22%;">Nom :</td>
        <td colspan="3" style="font-size:13pt; font-weight:bold; letter-spacing:1pt;">
            {{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}
        </td>
    </tr>
    <tr>
        <td>Section :</td>
        <td colspan="3">{{ $stage->classe }}</td>
    </tr>
    <tr>
        <td>Adresse :</td>
        <td colspan="3" style="height:44pt;">&nbsp;</td>
    </tr>
    <tr>
        <td>Tél :</td>
        <td style="width:28%;">
            {{ $stage->etudiant->telephone ? preg_replace('/(\d{2})(?=\d)/', '$1 ', $stage->etudiant->telephone) : '' }}
        </td>
        <td style="width:18%;">Courriel :</td>
        <td>{{ $stage->etudiant->email }}</td>
    </tr>
</table>

{{-- ════════════════════════════ PAGE 2+ ═══════════════════════════ --}}
<div class="page-break"></div>

<div class="titre-section">Titre I : Dispositions générales</div>

@foreach($articles as $i => $article)
@php
    // Échapper d'abord, puis injecter les balises bold sur les dates
    $corpsHtml = nl2br(e($article['corps']));
    if ($stage->date_debut && str_contains($article['corps'], '{DATE_DEBUT}')) {
        $dDebut = \Carbon\Carbon::parse($stage->date_debut)->locale('fr')->isoFormat('dddd D MMMM YYYY');
        $dFin   = $stage->date_fin
            ? \Carbon\Carbon::parse($stage->date_fin)->locale('fr')->isoFormat('dddd D MMMM YYYY')
            : '?';
        $corpsHtml = str_replace(
            ['{DATE_DEBUT}', '{DATE_FIN}'],
            ['<strong>' . e($dDebut) . '</strong>', '<strong>' . e($dFin) . '</strong>'],
            $corpsHtml
        );
    }
@endphp

<div class="article-bloc">
    <p class="article-titre">{{ $article['titre'] }} :</p>
    <p class="article-corps">{!! $corpsHtml !!}</p>
</div>

@if($i === 1)
{{-- Encadré mission — après article 2 --}}
<div class="article-bloc">
    <p class="article-corps-noindent bold" style="margin-top:3pt;">
        Le sujet proposé est obligatoirement décrit sommairement ci-après :
    </p>
    <div class="encadre-mission">
        <div class="ligne-mission">&nbsp;</div>
        <div class="ligne-mission">&nbsp;</div>
        <div class="ligne-mission">&nbsp;</div>
        <div class="ligne-mission">&nbsp;</div>
        <div class="ligne-mission">&nbsp;</div>
        <div class="ligne-mission">&nbsp;</div>
        <div class="ligne-mission" style="border-bottom:none;">&nbsp;</div>
    </div>
    <p class="article-corps-noindent" style="margin-bottom:6pt;">
        En cas de besoin, il fait l'objet d'une annexe qui le décrit de façon détaillée.
    </p>
</div>
@endif

@endforeach

<div class="titre-section">Titre II : Dispositions particulières</div>

@foreach($articlesParticuliers as $article)
<div class="article-bloc">
    <p class="article-titre">{{ $article['titre'] }} :</p>
    <p class="article-corps">{!! nl2br(e($article['corps'])) !!}</p>
</div>
@endforeach

{{-- ── Signatures ── --}}
<table class="noborder" style="margin-top:14pt; margin-bottom:8pt;">
    <tr>
        <td style="width:50%; padding:0; border:none;">Fait en trois exemplaires,</td>
        <td style="padding:0; border:none;">
            À {{ $p['lieu'] }}, le ___________________________
        </td>
    </tr>
</table>

<table class="table-sigs">
    <tr>
        <td style="width:33%;">
            <span class="bold">Le chef d'entreprise</span><br>
            <span style="font-size:8.5pt;">(Cachet et signature)</span>
        </td>
        <td style="width:33%;">
            <span class="bold">Le {{ $p['proviseur_titre'] }}</span>
        </td>
        <td style="width:34%;">
            <span class="bold">Le(la) stagiaire</span><br>
            <span style="font-size:8.5pt;">ou son représentant légal</span>
    </tr>
    <tr>
        <td style="height:60pt; vertical-align:bottom; text-align:left; font-size:9pt; color:#666; padding:4pt 8pt;">
            (Lu et approuvé)
        </td>
        <td style="height:60pt;">&nbsp;</td>
        <td style="height:60pt;">&nbsp;</td>

    </tr>
    
</table>

</body>
</html>

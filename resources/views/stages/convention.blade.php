<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Convention de stage</title>
    <style>
        @page {
            margin-left: 18mm;
            margin-right: 18mm;
            margin-top: 24mm;
            margin-bottom: 18mm;
            margin-header: 12mm;
            margin-footer: 12mm;
            header: html_entete;
            footer: html_pied;
        }
        @page :first {
            margin-top: 14mm;
            header: _blank;
            footer: _blank;
        }

        body {
            font-family: 'FreeSerif', 'Times New Roman', serif;
            font-size: 11.6pt;
            line-height: 1.06;
            color: #000;
        }

        p { margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        /* ── Page 1 ── */
        .titre-convention {
            background: #b3b3b3;
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            padding: 2pt 0;
        }
        .entre-et { font-weight: bold; text-align: center; font-size: 11pt; }
        .table-parties td { border: 0.75pt solid #000; padding: 3pt 5pt; }
        .table-parties td.gris { background: #b3b3b3; text-align: center; font-weight: bold; }
        .bold { font-weight: bold; }
        .pt { margin: 0; }
        .pt td { border: none; border-bottom: 0.5pt dotted #000; height: 12.4pt; padding: 0; line-height: 1.15; }
        .table-etudiant { border: 0.75pt solid #000; }
        .table-etudiant td { padding: 5pt 5pt; }

        /* ── Titres et articles ── */
        .titre-section {
            border: 0.75pt solid #000;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            padding: 2pt;
            margin: 0 0 14pt;
        }
        .article-titre {
            font-weight: bold;
            text-decoration: underline;
            margin: 22pt 0 6pt;
        }
        .corps { text-align: justify; }
        .retrait { text-indent: 1cm; }
        .puce { text-align: left; }

        .encadre-mission {
            border: 2pt solid #ff0000;
            height: 125pt;
            margin: 4pt 0 6pt;
        }
    </style>
</head>
<body>

{{-- En-tête (pages 2+) et pied de page, repris du modèle --}}
<htmlpageheader name="entete">
    <table style="font-size:10pt; padding:0 7mm;">
        <tr>
            <td style="width:25%; text-align:left;">{{ $p['etablissement_nom'] }}</td>
            <td style="width:50%; text-align:center;">STS Services Informatiques aux Organisations</td>
            <td style="width:25%; text-align:right;">{{ $p['lieu'] }}</td>
        </tr>
    </table>
</htmlpageheader>
<htmlpagefooter name="pied">
    <table style="font-size:9pt; font-style:italic; padding:0 7mm;">
        <tr>
            <td style="width:45%; text-align:left;">{{ str_replace('-', '/ ', $stage->annee_scolaire ?? '') }}</td>
            <td style="width:55%; text-align:left;">Page {PAGENO} sur {nbpg}</td>
        </tr>
    </table>
</htmlpagefooter>

@php
    // Rendu du corps d'un article : paragraphes séparés par une ligne vide, puces "• "
    $rendreCorps = function (array $article) use ($stage) {
        $cle = $article['cle'] ?? '';
        $retrait = ! in_array($cle, ['conv_art11', 'conv_art12']);
        $espace = in_array($cle, ['conv_art10', 'conv_art11']);
        // Retrait des puces, repris du modèle (art. 10 : tirets, art. 11 : puces)
        [$gauche, $creux] = $cle === 'conv_art10' ? ['13mm', '5.9mm'] : ['5.8mm', '6.9mm'];
        $retraitPuce = "margin-left:{$gauche}; padding-left:{$creux}; text-indent:-{$creux};";
        $html = '';
        foreach (explode("\n\n", $article['corps']) as $n => $para) {
            $intro = [];
            $puces = [];
            foreach (explode("\n", $para) as $ligne) {
                if (str_starts_with($ligne, '• ') || str_starts_with($ligne, '- ')) {
                    $puces[] = $ligne;
                } else {
                    $intro[] = $ligne;
                }
            }
            $marge = ($n > 0 && $espace) ? 'margin-top:14pt;' : '';
            if ($intro) {
                $html .= '<p class="corps'.($retrait ? ' retrait' : '').'" style="'.$marge.'">'.preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', e(implode(' ', $intro))).'</p>';
                $marge = '';
            }
            foreach ($puces as $i => $puce) {
                $html .= '<div class="puce" style="'.($i === 0 ? $marge : '').$retraitPuce.'">'.e($puce).'</div>';
            }
        }
        if ($stage->date_debut && str_contains($html, '{DATE_DEBUT}')) {
            $dDebut = \Carbon\Carbon::parse($stage->date_debut)->locale('fr')->isoFormat('dddd D MMMM YYYY');
            $dFin = $stage->date_fin
                ? \Carbon\Carbon::parse($stage->date_fin)->locale('fr')->isoFormat('dddd D MMMM YYYY')
                : '?';
            $html = str_replace(
                ['{DATE_DEBUT}', '{DATE_FIN}'],
                ['<strong>'.e($dDebut).'</strong>', '<strong>'.e($dFin).'</strong>'],
                $html
            );
        }
        return $html;
    };
    $tuteur = $stage->maitreDeStage;
@endphp

{{-- ════════════════════════════ PAGE 1 ════════════════════════════ --}}

<table style="margin-bottom:6pt;">
    <tr>
        <td style="width:45%; text-align:center; vertical-align:middle;">
            <img src="{{ public_path('img/logo-sio.png') }}" style="width:150pt;" alt="">
        </td>
        <td style="width:55%; text-align:center; vertical-align:bottom;">
            <img src="{{ public_path('img/logo-lycee.png') }}" style="width:230pt;" alt=""><br>
            <div class="titre-convention">CONVENTION DE STAGE</div>
        </td>
    </tr>
</table>

<table style="margin-bottom:2pt;">
    <tr>
        <td class="entre-et" style="width:50%;">ENTRE</td>
        <td class="entre-et" style="width:50%;">ET</td>
    </tr>
</table>

<table class="table-parties" style="margin-bottom:18pt;">
    <tr>
        <td class="gris" style="width:50%; height:36pt;">LE LYCÉE<br>{{ mb_strtoupper($p['etablissement_nom'], 'UTF-8') }}</td>
        <td class="gris" style="width:50%; vertical-align:bottom;">{{ $stage->entreprise?->raison_sociale }}</td>
    </tr>
    <tr>
        <td style="border-top:none;">
            <span class="bold">Représenté par :</span><br>
            <span class="bold">{{ $p['proviseur_civilite'] }} {{ $p['proviseur_nom'] }}</span><br>
            <span class="bold">{{ $p['proviseur_titre'] }}</span><br><br>
            <span class="bold">Adresse de l’établissement</span> :<br>
            {{ $p['adresse'] }}<br>
            @if($p['bp']){{ $p['bp'] }}<br>@endif
            {{ $p['cp_ville'] }}<br>
            Tél : {{ $p['tel'] }}<br>
            Fax : {{ $p['fax'] }}<br>
            Courriel : {{ $p['mel'] }}<br><br>
            <span class="bold">Professeur responsable</span> :<br>
            Nom : {{ $profPrincipal?->prenom }} {{ $profPrincipal?->nom }}<br>
            Tél : {{ $p['tel'] }}<br>
            Courriel : {{ $profPrincipal?->email }}
        </td>
        <td style="border-top:none;">
            <span class="bold">Représenté par :</span>
            <table class="pt"><tr><td>{{ $tuteur?->prenom }} {{ $tuteur?->nom }}</td></tr></table>
            <span class="bold">Fonction :</span>
            <table class="pt"><tr><td>{{ $tuteur?->fonction }}</td></tr></table>
            <span class="bold">Nom et adresse de l’organisation :</span>
            <table class="pt"><tr><td>{{ $stage->entreprise?->adresse }}</td></tr></table>
            <table class="pt"><tr><td>{{ $stage->entreprise?->complement_adresse }}</td></tr></table>
            <table class="pt"><tr><td>{{ $stage->entreprise?->code_postal }} {{ $stage->entreprise?->ville }}</td></tr></table>
            <table class="pt"><tr><td>&nbsp;</td></tr></table>
            <table class="pt"><tr><td>&nbsp;</td></tr></table>
            <table class="pt"><tr><td>&nbsp;</td></tr></table>
            <br>
            <span class="bold">Tuteur du/de la stagiaire :</span>
            <table class="pt"><tr><td>Nom : {{ $tuteur?->prenom }} {{ $tuteur?->nom }}</td></tr></table>
            <table class="pt"><tr><td>Fonction : {{ $tuteur?->fonction }}</td></tr></table>
            <table class="pt"><tr><td>Service : {{ $tuteur?->service }}</td></tr></table>
            <table class="pt"><tr><td>Tél : {{ $tuteur?->telephone }}</td></tr></table>
            <table class="pt"><tr><td>Courriel : {{ $tuteur?->email }}</td></tr></table>
        </td>
    </tr>
</table>

<p class="bold" style="margin:0 0 6pt 8pt;">CONCERNANT LE STAGE DE FORMATION PROFESSIONNELLE DE :</p>
<table class="table-etudiant">
    <tr><td>Nom : {{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}</td></tr>
    <tr><td>Section : {{ $stage->classe }}</td></tr>
    <tr><td style="height:82pt;">Adresse :</td></tr>
    <tr><td>Téléphone : {{ $stage->etudiant->telephone ? preg_replace('/(\d{2})(?=\d)/', '$1 ', $stage->etudiant->telephone) : '' }}<br>
        Courriel : {{ $stage->etudiant->email }}</td></tr>
</table>

{{-- ════════════════════════════ PAGES 2+ ═══════════════════════════ --}}
<pagebreak />
<div style="margin: 0 7mm;">

<div class="titre-section">TITRE I : DISPOSITIONS GÉNÉRALES</div>

@foreach($articles as $i => $article)
{{-- Le modèle commence la page 3 à l'article 6 et la page 4 à l'article 11 --}}
@if($i === 5 || $i === 10)<pagebreak />@endif
<div style="page-break-inside:avoid;">
    <p class="article-titre">{{ $article['titre'] }}</p>
    {!! $rendreCorps($article) !!}

@if($i === 1)
    <p class="bold" style="margin:12pt 0 0 1cm;">Le sujet proposé est obligatoirement décrit sommairement ci-après :</p>
    <div class="encadre-mission"></div>
    <p class="retrait">En cas de besoin, il fait l’objet d’une annexe qui le décrit de façon détaillée.</p>
@endif
</div>
@endforeach

<div class="titre-section" style="margin-top:14pt;">TITRE II : DISPOSITIONS PARTICULIÈRES</div>

@foreach($articlesParticuliers as $article)
<div style="page-break-inside:avoid;">
    <p class="article-titre">{{ $article['titre'] }}</p>
    {!! $rendreCorps($article) !!}
</div>
@endforeach

{{-- ── Signatures ── --}}
<div style="page-break-inside:avoid; margin-top:10pt;">
    <table>
        <tr>
            <td style="width:50%;">Fait en deux exemplaires,</td>
            <td>À {{ $p['lieu'] }}, le</td>
        </tr>
    </table>
    <table style="margin-top:14pt; text-align:center; font-size:11pt;">
        <tr>
            <td style="width:25%;">Responsable<br>dans l’organisation</td>
            <td style="width:25%; vertical-align:middle;">Proviseur.e</td>
            <td style="width:25%;">Responsable<br>pédagogique</td>
            <td style="width:25%;">Stagiaire<br>ou son représentant légal</td>
        </tr>
        <tr><td style="height:40pt;">&nbsp;</td><td></td><td></td><td></td></tr>
    </table>
</div>
</div>

</body>
</html>

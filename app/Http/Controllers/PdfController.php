<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationStage;
use App\Models\Parametre;
use App\Models\Stage;
use Mpdf\Mpdf;

class PdfController extends Controller
{
    public function convention(Stage $stage)
    {
        $this->authorize('view', $stage);

        $stage->load(['etudiant', 'entreprise', 'maitreDeStage']);

        // ── Paramètres éditables de l'établissement ──────────────────────
        $p = [
            'etablissement_nom' => Parametre::get('convention_etablissement_nom', config('app.name')),
            'proviseur_civilite' => Parametre::get('convention_proviseur_civilite', 'Mme'),
            'proviseur_nom'      => Parametre::get('convention_proviseur_nom', ''),
            'proviseur_titre'    => Parametre::get('convention_proviseur_titre', 'Proviseur(e)'),
            'adresse' => Parametre::get('convention_etablissement_adresse', ''),
            'bp' => Parametre::get('convention_etablissement_bp', ''),
            'cp_ville' => Parametre::get('convention_etablissement_cp_ville', ''),
            'tel' => Parametre::get('convention_etablissement_tel', ''),
            'fax' => Parametre::get('convention_etablissement_fax', '05 46 87 05 72'),
            'mel' => Parametre::get('convention_etablissement_mel', ''),
            'lieu' => Parametre::get('convention_lieu', ''),
        ];

        // ── Professeur principal de la classe ────────────────────────────
        $annee = Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));
        $configStage = ConfigurationStage::where('annee_scolaire', $annee)
            ->where('classe', $stage->classe)
            ->with('profPrincipal')
            ->first();
        $profPrincipal = $configStage?->profPrincipal;

        // ── Articles Titre I (éditables via Parametre) ───────────────────
        $articles = $this->articlesConvention();

        // ── Articles Titre II ────────────────────────────────────────────
        $articlesParticuliers = $this->articlesParticuliers();

        $html = view('stages.convention', compact(
            'stage', 'p', 'profPrincipal', 'articles', 'articlesParticuliers'
        ))->render();

        $mpdf = new Mpdf([
            'format'        => 'A4',
            'orientation'   => 'P',
            'margin_top'    => 24,
            'margin_bottom' => 18,
            'margin_left'   => 18,
            'margin_right'  => 18,
            'margin_header' => 8,
            'margin_footer' => 5,
            'tempDir'       => sys_get_temp_dir() . '/mpdf_' . substr(md5(config('app.key')), 0, 12),
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'convention-' . $stage->classe . '-' . $stage->etudiant->nom . '-' . $stage->etudiant->prenom . '.pdf';

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function attestation(Stage $stage)
    {
        $this->authorize('view', $stage);

        // TODO : générer l'attestation
        return response('%PDF-1.4', 200)->header('Content-Type', 'application/pdf');
    }

    // ── Articles juridiques Titre I ──────────────────────────────────────
    // Stockés dans Parametre pour être éditables. Valeurs par défaut = texte réglementaire.
    public static function articlesConvention(): array
    {
        $defauts = [
            'conv_art1' => [
                'titre' => 'Article 1 – objet',
                'corps' => "La présente convention a pour objet la mise en œuvre, au bénéfice des étudiants du lycée, d’une action d’éducation concertée, organisée, conformément aux dispositions du décret n°2006-1093 du 29 août 2006, modifié par le décret n°2010-956 du 25 août 2010, pris en application de l’article 9 de la loi n°2006-396 du 31 mars 2006 pour l’égalité des chances. Si le stage se déroule à l’étranger, la convention pourra être adaptée pour tenir compte des contraintes imposées par la législation du pays d’accueil.",
            ],
            'conv_art2' => [
                'titre' => 'Article 2 – Programme',
                'corps' => "Les stages sont destinés à donner à l’étudiant.e une représentation concrète du milieu professionnel des services informatiques et de l’emploi, tout en lui permettant d’acquérir et d’éprouver les compétences professionnelles prévues par le référentiel. **Le programme du stage est établi par la personne en charge de l'accueil de l'étudiant.e dans l'organisation. Le contenu de ce projet est soumis à l’approbation de l’équipe pédagogique**, en fonction du programme général des études et de la spécialisation du stagiaire.",
            ],
            'conv_art3' => [
                'titre' => 'Article 3 – Durée',
                'corps' => '**Le stage est fixé aux dates suivantes : du {DATE_DEBUT} au {DATE_FIN} inclus.**',
            ],
            'conv_art4' => [
                'titre' => 'Article 4 – Statut du/de la stagiaire',
                'corps' => "Le ou la stagiaire, pendant la durée de son séjour dans l’organisation, conserve son statut d’étudiant.e. Il ou elle est suivi.e par un tuteur de stage, en accord formel avec l’organisation d’accueil.",
            ],
            'conv_art5' => [
                'titre' => 'Article 5 – Assiduité et discipline',
                'corps' => "Durant son stage, le/la stagiaire est soumis.e à la discipline de l’organisation, notamment en ce qui concerne les horaires. En cas de manquement à la discipline, l’organisation peut mettre fin au stage, après avoir prévenu le/la chef.fe d’établissement. Avant son départ, l’organisation s’assurera que cet avertissement a bien été reçu par ce.tte dernier.e, et, s’il s’agit d’un/d’une stagiaire logé.e par l’organisation, que toutes dispositions ont été prises pour le/la recevoir.",
            ],
            'conv_art6' => [
                'titre' => 'Article 6 – Accidents',
                'corps' => "Les étudiant.e.s bénéficient de la législation sur les accidents du travail, en application de l’article 410, 2e, 1er paragraphes du code de la Sécurité Sociale.\n\nToutefois il leur est conseillé de contracter eux-mêmes/elles-mêmes, ou par l’intermédiaire de leur représentant légal, une assurance garantissant leur responsabilité civile pour tout dommage qu’ils/elles pourraient causer à autrui de leur propre fait.\n\nEn cas d’accident survenant à l’étudiant.e stagiaire, soit au cours du travail, soit au cours du trajet, l’organisation s’engage à faire parvenir toutes les déclarations, le plus rapidement possible à Monsieur le/Madame la Proviseur.e ; elle utilise à cet effet, les imprimés spéciaux mis à sa disposition par le Lycée.\n\nL’organisation contractera une assurance, garantissant sa propre responsabilité civile, chaque fois qu’elle sera engagée.",
            ],
            'conv_art7' => [
                'titre' => 'Article 7 – Rémunération',
                'corps' => "Le stage ne pourra être considéré comme une période d’activité salariée. Le/la stagiaire ne perçoit aucune rémunération et est exclu du bénéfice des avantages sociaux et salariés. En cas d’engagement ultérieur, la période du stage ne sera pas prise en compte au titre de l’ancienneté.",
            ],
            'conv_art8' => [
                'titre' => 'Article 8 – Avantages en nature',
                'corps' => "L’ensemble des frais occasionnés, hors mission spécifique confiée au/à la stagiaire par l’organisation pendant le déroulement de ce stage, reste à l’entière charge du/de la stagiaire.",
            ],
            'conv_art9' => [
                'titre' => 'Article 9 – Attestation',
                'corps' => "En fin de stage, une attestation est remise au/à la stagiaire par le responsable de l’organisation d’accueil. Elle précise les dates et la durée du stage effectives.\n\n**Ce document, dont la forme est imposée par la circulaire d’examen du BTS SIO, constitue la seule preuve de réalité de la durée de stage effectuée. De plus, elle constitue un document constitutif obligatoire et nécessaire à l’obtention du diplôme.**",
            ],
            'conv_art10' => [
                'titre' => 'Article 10 – Cybersécurité',
                'corps' => "Les étudiant.e.s stagiaires sont tenu.e.s à une obligation de discrétion absolue. À cet égard l’étudiant.e s’engage à ne divulguer à qui que ce soit aucune information ou donnée à caractère confidentiel qu’il ou elle serait en mesure de connaître lors de son stage. L’étudiant.e doit respecter les biens matériels ainsi que logiciels de l’entreprise. Il ou elle s’engage à ne commettre aucune infraction informatique, à titre d’exemple et de manière non exhaustive :\n- piratage de logiciel,\n- dégradation volontaire de données,\n- introduction de virus ou logiciels malveillants.\n\nEn cas de non respect de l’une des obligations citées ci-dessus, l’organisation se réserve le droit de mettre fin au stage de l’étudiant.e fautif.ve après avoir prévenu le chef ou la cheffe d’établissement. Dans le cas d’une faute grave (acte de malveillance dûment constaté) des poursuites pénales pourront être engagées.",
            ],
            'conv_art11' => [
                'titre' => 'Article 11 – Travail à distance',
                'corps' => "Les pratiques récentes dans les entreprises et organisations intègrent de plus en plus souvent des périodes de travail à distance. Dans le cas présent du stage obligatoire pour des étudiant.e.s en formation scolaire, il est évidemment préférable de pratiquer une activité de terrain dans l’objectif d’accéder à une expérience la plus enrichissante possible.\n\nNéanmoins, pour ne pas ignorer certaines opportunités d’offres de stage, le travail à distance est admis pour la réalisation du stage de BTS SIO si les conditions suivantes peuvent être réunies ; conditions que l’équipe pédagogique se réserve le droit d’accepter ou non :\n\n• la quotité (toute ou partie) de la durée de stage effectuée à distance doit être précisée,\n• un encadrement technique du travail au cours du stage doit être clairement identifié,\n• une liste de tâches des travaux à réaliser doit être établie conformément à l’article 2 de la présente convention,\n• deux réunions – au minimum – intégrant tous les acteurs, doivent être programmées en début (définition des objectifs) et fin (présentation et recette des travaux effectués) de la période de stage.\n\nCes conditions doivent être connues et annoncées à l’équipe enseignante avant la signature de la présente convention. Au besoin, elles pourront alors être précisées dans un document sur papier libre annexé à la présente convention.",
            ],
            'conv_art12' => [
                'titre' => 'Article 12 – Communication',
                'corps' => "Madame, Monsieur, le Proviseur du Lycée et le/la représentant.e de l’organisation se tiendront mutuellement informés des difficultés qui pourraient naître de l’application de la présente convention et prendront, d’un commun accord, et en liaison avec l’équipe pédagogique, les dispositions propres à les résoudre, notamment en cas de manquement à la discipline.",
            ],
        ];

        return array_map(function ($cle, $defaut) {
            return [
                'cle'   => $cle,
                'titre' => Parametre::get($cle.'_titre', $defaut['titre']),
                'corps' => Parametre::get($cle.'_corps', $defaut['corps']),
            ];
        }, array_keys($defauts), $defauts);
    }

    public static function articlesParticuliers(): array
    {
        $defauts = [
            'conv_part1' => [
                'titre' => 'Article 1',
                'corps' => "L’étudiant.e en stage ne peut prétendre à aucune rémunération.\n\nToutefois, certaines organisations accordent une gratification aux stagiaires en fonction du sérieux de leur travail et de la qualité des services rendus.",
            ],
            'conv_part2' => [
                'titre' => 'Article 2',
                'corps' => "L’étudiant.e est suivi.e durant son stage par un/une professeur.e. Un entretien (visite, entretien téléphonique ou par visioconférence) d’un.e enseignant.e aura lieu dans la deuxième partie du stage et sera l’occasion de rencontrer le tuteur du stagiaire qui donnera son avis sur le déroulement du stage et l’implication du/de la stagiaire. Le tuteur dans l’organisation s’engage à communiquer le plus rapidement à l’enseignant responsable tout problème qui se poserait durant la période de stage.",
            ],
        ];

        return array_map(function ($cle, $defaut) {
            return [
                'cle'   => $cle,
                'titre' => Parametre::get($cle.'_titre', $defaut['titre']),
                'corps' => Parametre::get($cle.'_corps', $defaut['corps']),
            ];
        }, array_keys($defauts), $defauts);
    }
}

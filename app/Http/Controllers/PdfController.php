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
            'margin_top'    => 22,
            'margin_bottom' => 14,
            'margin_left'   => 18,
            'margin_right'  => 18,
            'margin_header' => 8,
            'margin_footer' => 5,
            'tempDir'       => sys_get_temp_dir() . '/mpdf_' . substr(md5(config('app.key')), 0, 12),
        ]);

        // En-tête courant (pages 2+)
        $mpdf->SetHTMLHeader('
            <table style="width:100%; font-size:8pt; font-family:Arial,sans-serif; border-bottom:0.5pt solid #555;">
                <tr>
                    <td style="text-align:left; width:33%;">' . e($p['etablissement_nom']) . '</td>
                    <td style="text-align:center; width:34%;">STS Services Informatiques aux Organisations</td>
                    <td style="text-align:right; width:33%;">' . e($p['lieu']) . '</td>
                </tr>
            </table>
        ', 'OE'); // OE = odd + even, à partir de la page 2

        // Pied de page avec numéros
        $mpdf->SetHTMLFooter('
            <p style="text-align:center; font-size:8pt; font-family:Arial,sans-serif; color:#666;">
                Page {PAGENO} sur {nbpg}
            </p>
        ');

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
    private function articlesConvention(): array
    {
        $defauts = [
            'conv_art1' => [
                'titre' => 'Article 1 (objet)',
                'corps' => "La présente convention a pour objet la mise en œuvre, au bénéfice des étudiants du lycée, d'une action d'éducation concertée, organisée, conformément aux dispositions du décret n°2006-1093 du 29 août 2006, modifié par le décret n°2010-956 du 25 août 2010, pris en application de l'article 9 de la loi n°2006-396 du 31 mars 2006 pour l'égalité des chances. Si le stage se déroule à l'étranger, la convention pourra être adaptée pour tenir compte des contraintes imposées par la législation du pays d'accueil.",
            ],
            'conv_art2' => [
                'titre' => 'Article 2 (programme)',
                'corps' => "Les stages sont destinés à donner à l'étudiant une représentation concrète du milieu professionnel des services informatiques et de l'emploi, tout en lui permettant d'acquérir et d'éprouver les compétences professionnelles prévues par le référentiel. Le programme du stage est établi par le chef d'entreprise. Le contenu de ce projet est soumis à l'approbation de l'équipe pédagogique, en fonction du programme général des études et de la spécialisation du stagiaire.",
            ],
            'conv_art3' => [
                'titre' => 'Article 3 (durée)',
                'corps' => 'Le stage est fixé aux dates suivantes : du {DATE_DEBUT} au {DATE_FIN} inclus.',
            ],
            'conv_art4' => [
                'titre' => 'Article 4 (statut du stagiaire)',
                'corps' => "Le stagiaire, pendant la durée de son séjour en entreprise, conserve son statut d'étudiant. Il est suivi par un directeur de stage, en accord formel avec le chef d'entreprise d'accueil.",
            ],
            'conv_art5' => [
                'titre' => 'Article 5 (assiduité et discipline)',
                'corps' => "Durant son stage, le stagiaire est soumis à la discipline de l'entreprise, notamment en ce qui concerne l'horaire. En cas de manquement à la discipline, le chef d'entreprise peut mettre fin au stage, après avoir prévenu le chef d'établissement. Avant son départ, le chef d'entreprise s'assurera que l'avertissement a bien été reçu par ce dernier, et, s'il s'agit d'un stagiaire logé par l'entreprise, que toutes dispositions ont été prises pour le recevoir.",
            ],
            'conv_art6' => [
                'titre' => 'Article 6 (accidents)',
                'corps' => "Les étudiants bénéficient de la législation sur les accidents du travail, en application de l'article 410, 2e, 1er paragraphes du code de la Sécurité Sociale.\n\nToutefois il leur est conseillé de contracter eux-mêmes, ou par l'intermédiaire de leur représentant légal, une assurance garantissant leur responsabilité civile pour tout dommage qu'ils pourraient causer à autrui de leur propre fait.\n\nEn cas d'accident survenant à l'étudiant stagiaire, soit au cours du travail, soit au cours du trajet, le Chef d'Entreprise s'engage à faire parvenir toutes les déclarations, le plus rapidement possible à Monsieur le Proviseur ; il utilise à cet effet, les imprimés spéciaux mis à sa disposition par le Lycée. Le chef d'entreprise contractera une assurance, garantissant sa propre responsabilité civile, chaque fois qu'elle sera engagée.",
            ],
            'conv_art7' => [
                'titre' => 'Article 7 (rémunération)',
                'corps' => "Le stage ne pourra être considéré comme une période d'activité salariée. Le stagiaire ne perçoit aucune rémunération et est exclu du bénéfice des avantages sociaux et salariés. En cas d'engagement ultérieur, la période du stage ne sera pas prise en compte au titre de l'ancienneté.",
            ],
            'conv_art8' => [
                'titre' => 'Article 8 (avantages en nature)',
                'corps' => "L'ensemble des frais occasionnés, hors mission spécifique confiée au stagiaire par l'entreprise pendant le déroulement de ce stage, reste à l'entière charge du stagiaire.",
            ],
            'conv_art9' => [
                'titre' => 'Article 9 (Attestation)',
                'corps' => "En fin de stage, une attestation est remise au stagiaire par le responsable de l'organisation d'accueil. Elle précise les dates et durée effectives du stage ainsi que l'éventuelle gratification versée au stagiaire.\n\nCette attestation de stage constitue un document d'examen. Le modèle, publié par la circulaire nationale d'organisation du BTS SIO, doit être impérativement utilisé. Ce modèle d'attestation est mis à disposition des étudiants par l'équipe pédagogique.",
            ],
            'conv_art10' => [
                'titre' => 'Article 10 (confidentialité)',
                'corps' => "Les étudiants stagiaires sont tenus à une obligation de discrétion absolue. À cet égard l'étudiant s'engage à ne divulguer à qui que ce soit aucune information ou donnée à caractère confidentiel qu'il sera en mesure de connaître lors de son stage. L'étudiant doit respecter les biens matériels de l'entreprise ; en matière de logiciel, l'étudiant s'engage à ne commettre aucune infraction informatique :\n- Piratage de logiciel\n- Dégradation volontaire de données\n- Intrusion de virus\n\nEn cas de non-respect de l'une des obligations citées ci-dessus, le chef d'entreprise se réserve le droit de mettre fin au stage de l'étudiant fautif après avoir prévenu le chef d'établissement. Dans le cas d'une faute grave (acte de malveillance dûment constaté), des poursuites pénales pourront être engagées.",
            ],
            'conv_art11' => [
                'titre' => 'Article 11 (visite)',
                'corps' => "Une visite en entreprise sera réalisée par un enseignant durant la période de stage. Elle fera l'objet d'un entretien entre le tuteur de stage et l'enseignant concerné.",
            ],
        ];

        return array_map(function ($cle, $defaut) {
            return [
                'titre' => Parametre::get($cle.'_titre', $defaut['titre']),
                'corps' => Parametre::get($cle.'_corps', $defaut['corps']),
            ];
        }, array_keys($defauts), $defauts);
    }

    private function articlesParticuliers(): array
    {
        $defauts = [
            'conv_part1' => [
                'titre' => 'Article 1',
                'corps' => "L'étudiant en stage ne peut prétendre à aucune rémunération.\n\nToutefois, certaines entreprises accordent une gratification aux stagiaires en fonction du sérieux de leur travail et de la qualité des services rendus.",
            ],
            'conv_part2' => [
                'titre' => 'Article 2',
                'corps' => "L'étudiant est suivi durant son stage par un professeur. Une visite d'un enseignant aura lieu dans la deuxième partie du stage et sera l'occasion de rencontrer le tuteur du stagiaire qui donnera son avis sur le déroulement du stage et l'implication du stagiaire. En cas de stage éloigné, la visite peut être remplacée par un entretien téléphonique. Le tuteur s'engage à communiquer le plus rapidement à l'enseignant responsable tout problème qui se poserait durant la période de stage.",
            ],
        ];

        return array_map(function ($cle, $defaut) {
            return [
                'titre' => Parametre::get($cle.'_titre', $defaut['titre']),
                'corps' => Parametre::get($cle.'_corps', $defaut['corps']),
            ];
        }, array_keys($defauts), $defauts);
    }
}

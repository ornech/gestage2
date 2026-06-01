<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use HasFactory;

    public const COMPETENCES = [
        1  => "Gérer le patrimoine informatique",
        2  => "Répondre aux incidents et aux demandes d'assistance et d'évolution",
        4  => "Développer la présence en ligne de l'organisation",
        8  => "Travailler en mode projet",
        16 => "Mettre à disposition des utilisateurs un service informatique",
        32 => "Organiser son développement professionnel",
    ];

    public const COMPETENCES_DESCRIPTIONS = [
        1  => "- Recenser et identifier les ressources numériques\n- Exploiter des référentiels, normes et standards adoptés par le prestataire informatique\n- Mettre en place et vérifier les niveaux d'habilitation associés à un service\n- Vérifier les conditions de la continuité d'un service informatique\n- Gérer des sauvegardes\n- Vérifier le respect des règles d'utilisation des ressources numériques",
        2  => "- Collecter, suivre et orienter des demandes\n- Traiter des demandes concernant les services réseau et système, applicatifs\n- Traiter des demandes concernant les applications",
        4  => "- Participer à la valorisation de l'image de l'organisation sur les médias numériques en tenant compte du cadre juridique et des enjeux économiques\n- Référencer les services en ligne de l'organisation et mesurer leur visibilité\n- Participer à l'évolution d'un site Web exploitant les données de l'organisation",
        8  => "- Analyser les objectifs et les modalités d'organisation d'un projet\n- Planifier les activités\n- Évaluer les indicateurs de suivi d'un projet et analyser les écarts",
        16 => "- Réaliser les tests d'intégration et d'acceptation d'un service\n- Déployer un service\n- Accompagner les utilisateurs dans la mise en place d'un service",
        32 => "- Mettre en place son environnement d'apprentissage personnel\n- Mettre en œuvre des outils et stratégies de veille informationnelle\n- Gérer son identité professionnelle\n- Développer son projet professionnel",
    ];

    protected $fillable = [
        'stage_id',
        'user_id',
        'semaine',
        'date_debut_semaine',
        'titre',
        'activites',
        'competences',
    ];

    protected $casts = [
        'date_debut_semaine' => 'date',
        'competences'        => 'integer',
    ];

    /** Retourne la liste des libellés de compétences correspondant au bitmask. */
    public function competencesList(): array
    {
        $mask   = (int) $this->competences;
        $result = [];

        foreach (array_reverse(self::COMPETENCES, true) as $id => $label) {
            if ($mask >= $id) {
                $result[] = $label;
                $mask -= $id;
            }
        }

        return array_reverse($result);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Enums;

use App\Models\ConventionPapier;

/**
 * Cycle de signature de la convention d'un stage (stages.statut_convention).
 * a_faire_signer → en_attente (déposée à la direction) → validee (remise à l'étudiant).
 */
enum StatutConvention: string
{
    case AFaireSigner = 'a_faire_signer';
    case EnAttente    = 'en_attente';
    case Validee      = 'validee';

    public function precedent(): ?self
    {
        return match ($this) {
            self::EnAttente => self::AFaireSigner,
            self::Validee   => self::EnAttente,
            default         => null,
        };
    }

    /**
     * Statut du stage à créer quand l'étudiant a (ou non) déjà une convention papier.
     * « hors_app » = l'employeur a déjà signé : côté stage, c'est « en_attente ».
     */
    public static function depuisConventionPapier(?ConventionPapier $papier): self
    {
        return match ($papier?->statut) {
            null, StatutConventionPapier::AFaireSigner->value => self::AFaireSigner,
            StatutConventionPapier::HorsApp->value,
            StatutConventionPapier::EnAttente->value          => self::EnAttente,
            StatutConventionPapier::Validee->value            => self::Validee,
        };
    }
}

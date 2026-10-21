<?php

namespace App\Enums;

/**
 * Convention remise hors application (conventions_papier.statut).
 * hors_app → en_attente → validee
 */
enum StatutConventionPapier: string
{
    case HorsApp      = 'hors_app';
    case AFaireSigner = 'a_faire_signer';
    case EnAttente    = 'en_attente';
    case Validee      = 'validee';

    public function suivant(): ?self
    {
        return match ($this) {
            self::HorsApp   => self::EnAttente,
            self::EnAttente => self::Validee,
            default         => null,
        };
    }

    public function precedent(): ?self
    {
        return match ($this) {
            self::EnAttente => self::HorsApp,
            self::Validee   => self::EnAttente,
            default         => null,
        };
    }
}

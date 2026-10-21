<?php

namespace App\Enums;

/**
 * Validation administrative d'un stage (stages.statut_validation).
 * Les valeurs doivent rester identiques à l'ENUM MySQL — vérifié par EnumsMatchSchemaTest.
 */
enum StatutValidation: string
{
    case EnAttente = 'en_attente';
    case Valide    = 'valide';
    case Rejete    = 'rejete';
}

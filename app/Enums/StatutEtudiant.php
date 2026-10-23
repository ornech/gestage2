<?php

namespace App\Enums;

/**
 * users.statut. « diplome » est posé par un professeur, jamais déduit de la promo.
 * « archive » : ancien dont le résultat est inconnu ; « echec » : BTS non obtenu.
 */
enum StatutEtudiant: string
{
    case Actif          = 'actif';
    case Demissionnaire = 'demissionnaire';
    case Diplome        = 'diplome';
    case Archive        = 'archive';
    case Echec          = 'echec';

    /** Sorti du cycle, accès désactivé (le démissionnaire est traité à part). */
    public function estSortie(): bool
    {
        return in_array($this, [self::Diplome, self::Archive, self::Echec], true);
    }

    public function libelle(): string
    {
        return match ($this) {
            self::Actif          => 'Actif',
            self::Demissionnaire => 'Démissionnaire',
            self::Diplome        => 'Diplômé',
            self::Archive        => 'Archivé',
            self::Echec          => 'Échec',
        };
    }
}

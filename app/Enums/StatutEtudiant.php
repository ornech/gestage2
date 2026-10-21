<?php

namespace App\Enums;

/** users.statut. « diplome » est posé par un professeur, jamais déduit de la promo. */
enum StatutEtudiant: string
{
    case Actif          = 'actif';
    case Demissionnaire = 'demissionnaire';
    case Diplome        = 'diplome';
}

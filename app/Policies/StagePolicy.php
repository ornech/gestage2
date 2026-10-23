<?php

namespace App\Policies;

use App\Enums\StatutValidation;
use App\Models\Stage;
use App\Models\User;

class StagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrateur', 'Professeur', 'Etudiant']);
    }

    public function view(User $user, Stage $stage): bool
    {
        return $user->hasAnyRole(['Professeur', 'Administrateur'])
            || $user->id === $stage->etudiant_id;
    }

    /** Voir ou télécharger la convention PDF : le staff toujours, l'étudiant pour son stage tant qu'il n'est pas rejeté. */
    public function convention(User $user, Stage $stage): bool
    {
        return $user->hasAnyRole(['Professeur', 'Administrateur'])
            || ($user->id === $stage->etudiant_id && $stage->statut_validation !== StatutValidation::Rejete->value);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Etudiant');
    }

    public function update(User $user, Stage $stage): bool
    {
        // Profs et admins peuvent tout modifier
        if ($user->hasAnyRole(['Professeur', 'Administrateur'])) {
            return true;
        }

        // Étudiant : uniquement son propre stage, et seulement si rejeté ou en attente
        return $user->id === $stage->etudiant_id
            && in_array($stage->statut_validation, [StatutValidation::EnAttente->value, StatutValidation::Rejete->value]);
    }

    public function manageJournal(User $user, Stage $stage): bool
    {
        if ($user->hasAnyRole(['Professeur', 'Administrateur'])) {
            return true;
        }

        return $user->id === $stage->etudiant_id;
    }

    public function validate(User $user): bool
    {
        return $user->hasAnyRole(['Professeur', 'Administrateur']);
    }

    /** Correction d'une saisie erronée : réservée au staff (le contrôle du journal est fait par le contrôleur). */
    public function delete(User $user, Stage $stage): bool
    {
        return $user->hasAnyRole(['Professeur', 'Administrateur']);
    }

    public function restore(User $user, Stage $stage): bool
    {
        return false;
    }

    public function forceDelete(User $user, Stage $stage): bool
    {
        return false;
    }
}

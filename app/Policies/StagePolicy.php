<?php

namespace App\Policies;

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
        return true;
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
            && in_array($stage->statut_validation, ['en_attente', 'rejete']);
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

    public function delete(User $user, Stage $stage): bool
    {
        return $user->hasAnyRole(['Administrateur', 'Professeur'])
            || $user->id === $stage->etudiant_id;
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

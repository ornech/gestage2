<?php

namespace App\Policies;

use App\Models\Employe;
use App\Models\User;

class EmployePolicy
{
    public function update(User $user, Employe $employe): bool
    {
        if ($user->hasAnyRole(['Professeur', 'Administrateur'])) {
            return true;
        }

        // Le créateur garde la main tant que la fiche ne sert qu'à ses propres stages :
        // dès qu'un autre étudiant s'y rattache, modifier l'e-mail relancerait des mails à son insu.
        return $user->id === $employe->creator_id
            && ! $employe->stages()->where('etudiant_id', '!=', $user->id)->exists();
    }
}

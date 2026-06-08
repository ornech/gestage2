<?php

namespace App\Policies;

use App\Models\Employe;
use App\Models\User;

class EmployePolicy
{
    public function update(User $user, Employe $employe): bool
    {
        return $user->hasAnyRole(['Professeur', 'Administrateur'])
            || ($user->id === $employe->creator_id);
    }
}

<?php

namespace App\Policies;

use App\Models\Stage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    //un étudiant peut voir la liste des stages
    public function viewAny(User $user): bool
{
     //on autorise les étudiants, les professeurs et les administrateurs à voir la liste des stages
    return $user->hasAnyRole(['Administrateur', 'Professeur', 'Etudiant']);
}


    /**
     * Determine whether the user can view the model.
     */
    //l'étudiant peut voir la un stage si c'est le sien
    public function view(User $user, Stage $stage)
{
    return true;
}


    /**
     * Determine whether the user can create models.
     */
    //un étudiant peut créer un stage que pour sa propre année
    public function create(User $user): bool
    {
    return $user->hasRole('Etudiant');
}


    /**
     * Determine whether the user can update the model.
     */
    //un étudiant ne peut modifier un stage uniquement si c'est le sien 
    public function update(User $user, Stage $stage): bool
    {
        // Autorisé si c'est un Admin OU si c'est le professeur responsable du stage
    return $user->hasRole('Administrateur') || $user->id === $stage->professeur_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    //un étudiant ne peut supprimer un stage si c'est le sien
    public function delete(User $user, Stage $stage): bool
    {
       return $user->hasRole('Administrateur');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Stage $stage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Stage $stage): bool
    {
        return false;
    }
}

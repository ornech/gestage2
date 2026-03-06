<?php
namespace App\Http\Responses;

//on importe l'interface que Fortify veut que notre classe implémente
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract {
   ////C'est la méthode que Fortify appelle après la connexion
public function toResponse($request)
{
    //récupération de l'utilisateur connecté
    $user = $request->user();

    if($user->hasRole('Administrateur') ) {
        return redirect('/admin');
    }
    if ($user->hasRole('Professeur')) {
        return redirect('/dashboard');
    }
    if ($user->hasRole('Etudiant')) {
        return redirect('/stages');
    }
    //si aucun role ne correspond, on redirige vers la page d'accueil
 return redirect('/'); 
}
}
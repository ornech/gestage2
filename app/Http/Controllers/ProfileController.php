<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Affiche la page de profil de l'utilisateur.
     */
    public function show()
    {
        // On récupère l'utilisateur actuellement authentifié
        $user = Auth::user();

        // On retourne la vue en lui passant les données de l'utilisateur
        return view('profile.show', compact('user'));
    }
}

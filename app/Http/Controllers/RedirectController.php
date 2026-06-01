<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    /**
     * Redirect the user to the appropriate dashboard based on their role.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // 1. Si l'utilisateur n'est pas connecté (Visiteur)
        if (! Auth::check()) {
            return redirect('/login');
        }

        // 2. Si l'utilisateur est connecté, on récupère son profil
        $user = Auth::user();

        // 3. Redirections dynamiques basées sur les rôles Spatie
        if ($user->hasRole('Administrateur')) {
            return redirect('/admin');
        }

        if ($user->hasRole('Professeur')) {
            return redirect('/dashboard');
        }

        if ($user->hasRole('Etudiant')) {
            return redirect('/etudiant');
        }

        // Sécurité de repli si le compte a un bug de rôle
        auth()->logout();

        return redirect('/login')->withErrors(['erreur' => 'Votre compte ne possède aucun rôle.']);
    }
}

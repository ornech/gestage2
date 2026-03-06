<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route; // <- Import très important

// --- L'AIGUILLEUR PRINCIPAL (Racine du site) ---
Route::get('/', function () {

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
        return redirect('/stages');
    }

    // Sécurité de repli si le compte a un bug de rôle
    auth()->logout();

    return redirect('/login')->withErrors(['erreur' => 'Votre compte ne possède aucun rôle.']);
});

// Espace professeur (Synchronisé avec LoginResponse)
Route::middleware(['auth', 'role:Professeur'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboards.professeur'); // Appel de la vue Blade professeur
    });
});

// Espace étudiant (Synchronisé avec LoginResponse)
Route::middleware(['auth', 'role:Etudiant'])->group(function () {
    Route::get('/stages', function () {
        return view('dashboards.etudiant'); // Appel de la vue Blade pour les stages
    });
});

// Espace administrateur (Nom du rôle corrigé + URL synchronisée)
Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::get('/admin', function () {
        return view('dashboards.admin'); // Appel de la vue Blade administrateur
    });
});

// Route de secours pour forcer la déconnexion lors des tests
Route::get('/force-logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/login');

});

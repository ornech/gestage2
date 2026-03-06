<?php

use Illuminate\Support\Facades\Route;

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// Espace professeur (Synchronisé avec LoginResponse)
Route::middleware(['auth', 'role:Professeur'])->group(function () {
    Route::get('/dashboard', function () {
        return 'Espace professeur - Accès autorisé';
    });
});

// Espace étudiant (Synchronisé avec LoginResponse)
Route::middleware(['auth', 'role:Etudiant'])->group(function () {
    Route::get('/stages', function () {
        return 'Espace étudiant - Accès autorisé';
    });
});

// Espace administrateur (Nom du rôle corrigé + URL synchronisée)
Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::get('/admin', function () {
        return 'Espace administrateur - Accès autorisé';
    });
});

// Route de secours pour forcer la déconnexion lors des tests
Route::get('/force-logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();

    return 'Déconnecté avec succès.';
});

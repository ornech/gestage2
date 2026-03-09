<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedirectController; // Importer le nouveau contrôleur
use Illuminate\Support\Facades\Route; // <- Import très important

// --- L'AIGUILLEUR PRINCIPAL (Racine du site) ---
Route::get('/', [RedirectController::class, 'index']);

// --- Routes communes à tous les utilisateurs connectés ---
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
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

//ajout de la route pour les employes
Route::resource('employes', EmployeController::class);

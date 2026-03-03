<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;


// Page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// Espace professeur
Route::middleware(['auth', 'role:Professeur'])->group(function () {
    Route::get('/prof/dashboard', function () {
        return 'Espace professeur';
    });
});

// Espace étudiant
Route::middleware(['auth', 'role:Etudiant'])->group(function () {
    Route::get('/etu/dashboard', function () {
        return 'Espace étudiant';
    });
});

// Espace administrateur
Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return 'Espace administrateur';
    });
});

Route::get('/force-logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return 'Déconnecté';
});


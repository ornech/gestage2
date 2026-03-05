<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Auth\LoginController;

// Page d'accueil
Route::get('/', function () {
    return redirect()->route('login');

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
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return 'Espace administrateur';
    });
});



//la route de logout 
Route::post('/logout', function () {
    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');


//route de login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

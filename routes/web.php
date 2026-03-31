<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\AdminStageController;

/*
|--------------------------------------------------------------------------
| Routes Entreprises
|--------------------------------------------------------------------------
*/
// Page d’import
// Formulaire d’import
Route::get('/entreprises/create', [CompanyController::class, 'create'])
    ->name('entreprises.create');

Route::get('/entreprises/import', [CompanyController::class, 'importForm'])
    ->name('entreprises.import.form');
// Traitement du SIRET (interface)
Route::post('/entreprises/import', [CompanyController::class, 'import'])
    ->name('entreprises.import');
    //création d’une entreprise à partir du SIRET (test)
    Route::post('/entreprises', [CompanyController::class, 'store'])
    ->name('entreprises.store');
//mise à jour d’une entreprise à partir du SIRET (test)
Route::put('/entreprises/{entreprise}', [CompanyController::class, 'update'])
    ->name('entreprises.update');

//Liste des entreprises
Route::get('/entreprises', [CompanyController::class, 'index'])
    ->name('entreprises.index');
 //Fiche entreprise
Route::get('/entreprises/{entreprise}', [CompanyController::class, 'show'])
    ->name('entreprises.show');




// API SIRET (utilisée par les tests)
Route::post('/companies/import-siret', [CompanyController::class, 'importSiret']);

// Création entreprise (test)
Route::post('/companies', [CompanyController::class, 'store']);


/*
|--------------------------------------------------------------------------
| Aiguilleur principal
|--------------------------------------------------------------------------
*/

Route::get('/', [RedirectController::class, 'index']);


/*
|--------------------------------------------------------------------------
| Espace Étudiant
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Etudiant'])->group(function () {

    // Dashboard étudiant (RENOMMÉ pour éviter le conflit)
    Route::get('/etudiant', function () {
        return view('dashboards.etudiant');
    })->name('etudiant.dashboard');

    // Liste des stages de l'étudiant
    Route::get('/etudiant/stages', [StageController::class, 'mesStages'])
        ->name('etudiant.stages.index');
});


/*
|--------------------------------------------------------------------------
| Routes communes (profil)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');
});


/*
|--------------------------------------------------------------------------
| Espace Professeur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Professeur'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboards.professeur');
    });
});


/*
|--------------------------------------------------------------------------
| Employés & Stages (modules officiels)
|--------------------------------------------------------------------------
*/
Route::get('/entreprises/{entreprise}/employes/create', [EmployeController::class, 'create'])
    ->name('employes.create');

Route::post('/entreprises/{entreprise}/employes', [EmployeController::class, 'store'])
    ->name('employes.store');

Route::resource('employes', EmployeController::class);
Route::resource('stages', StageController::class);


/*
|--------------------------------------------------------------------------
| PDF
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/stages/{stage}/pdf/convention', [PdfController::class, 'convention'])
        ->name('pdf.convention');
});


/*
|--------------------------------------------------------------------------
| Espace Administrateur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Administrateur'])->group(function () {

    Route::get('/admin', function () {
        return view('dashboards.admin');
    })->name('admin.dashboard');

    Route::get('/admin/stages', [AdminStageController::class, 'index'])
        ->name('admin.stages.index');

    Route::put('/admin/stages/{stage}/assign', [AdminStageController::class, 'assign'])
        ->name('admin.stages.assign');
});

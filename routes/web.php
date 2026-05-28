<?php

use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminImportController;
use App\Http\Controllers\AdminParametreController;
use App\Http\Controllers\AdminSpeController;
use App\Http\Controllers\AdminStageController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CguController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\StageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Aiguilleur principal
|--------------------------------------------------------------------------
*/

Route::get('/', [RedirectController::class, 'index']);


/*
|--------------------------------------------------------------------------
| CGU — UC_CGU
|--------------------------------------------------------------------------
*/

Route::get('/cgu', [CguController::class, 'show'])->name('cgu.show');
Route::post('/cgu/accept', [CguController::class, 'accept'])
    ->name('cgu.accept')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| Profil (commun à tous les rôles)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/profile',       [ProfileController::class, 'show'])  ->name('profile.show');
    Route::get('/profile/edit',  [ProfileController::class, 'edit'])  ->name('profile.edit');
    Route::put('/profile',       [ProfileController::class, 'update'])->name('profile.update');

    // Changement de mot de passe forcé — première connexion
    Route::get('/password/first-change',  [PasswordChangeController::class, 'show'])  ->name('password.first-change');
    Route::put('/password/first-change',  [PasswordChangeController::class, 'update'])->name('password.first-change.update');
});


/*
|--------------------------------------------------------------------------
| Entreprises — auth requis, accès commun à tous les rôles
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/entreprises',                       [CompanyController::class, 'index'])  ->name('entreprises.index');
    Route::get('/entreprises/create',                [CompanyController::class, 'create']) ->name('entreprises.create');
    Route::post('/entreprises',                      [CompanyController::class, 'store'])  ->name('entreprises.store');
    Route::get('/entreprises/{entreprise}',          [CompanyController::class, 'show'])   ->name('entreprises.show');
    Route::put('/entreprises/{entreprise}',          [CompanyController::class, 'update']) ->name('entreprises.update');

    Route::get('/entreprises/import',  [CompanyController::class, 'importForm'])->name('entreprises.import.form');
    Route::post('/entreprises/import', [CompanyController::class, 'import'])    ->name('entreprises.import');
});


/*
|--------------------------------------------------------------------------
| API SIRET — auth requis
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::post('/companies/import-siret', [CompanyController::class, 'importSiret']);
    Route::post('/companies',              [CompanyController::class, 'store']);
});


/*
|--------------------------------------------------------------------------
| Employés / Contacts — auth requis, accès commun à tous les rôles
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/entreprises/{entreprise}/employes/create', [EmployeController::class, 'create'])
        ->name('employes.create');
    Route::post('/entreprises/{entreprise}/employes', [EmployeController::class, 'store'])
        ->name('employes.store');
    Route::resource('employes', EmployeController::class);
});


/*
|--------------------------------------------------------------------------
| Stages — auth requis, accès fin géré par StagePolicy
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/entreprises/{entreprise}/stages/create', [StageController::class, 'create'])
        ->name('stages.create');
    Route::resource('stages', StageController::class)->except(['create']);
});


/*
|--------------------------------------------------------------------------
| Espace Étudiant
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Etudiant'])->group(function () {

    Route::get('/etudiant', function () {
        return view('dashboards.etudiant');
    })->name('etudiant.dashboard');

    Route::get('/etudiant/stages', [StageController::class, 'mesStages'])
        ->name('etudiant.stages.index');

    Route::get('/etudiant/conventions', [StageController::class, 'mesConventions'])
        ->name('etudiant.conventions.index');

    // Journal de bord — UC_JDB
    Route::get('/stages/{stage}/journal',                   [JournalController::class, 'index'])  ->name('stages.journal.index');
    Route::get('/stages/{stage}/journal/create',            [JournalController::class, 'create']) ->name('stages.journal.create');
    Route::post('/stages/{stage}/journal',                  [JournalController::class, 'store'])  ->name('stages.journal.store');
    Route::get('/stages/{stage}/journal/{entry}/edit',      [JournalController::class, 'edit'])   ->name('stages.journal.edit');
    Route::put('/stages/{stage}/journal/{entry}',           [JournalController::class, 'update']) ->name('stages.journal.update');
    Route::delete('/stages/{stage}/journal/{entry}',        [JournalController::class, 'destroy'])->name('stages.journal.destroy');
});


/*
|--------------------------------------------------------------------------
| Espace Professeur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Professeur'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboards.professeur');
    })->name('professeur.dashboard');
});

// Gestion des étudiants — Prof ET Admin
Route::middleware(['auth', 'role:Professeur|Administrateur'])->group(function () {

    Route::get('/admin/users',                        [AdminUserController::class, 'index'])       ->name('admin.users.index');
    Route::get('/admin/users/{user}/edit',            [AdminUserController::class, 'edit'])        ->name('admin.users.edit');
    Route::put('/admin/users/{user}',                 [AdminUserController::class, 'update'])      ->name('admin.users.update');
    Route::patch('/admin/users/{user}/statut',        [AdminUserController::class, 'updateStatut'])->name('admin.users.statut');
    Route::patch('/admin/users/{user}/assign-tuteur', [AdminUserController::class, 'assignTuteur'])->name('admin.users.assign-tuteur');
});

// Stages + Paramètres année scolaire — Prof ET Admin
Route::middleware(['auth', 'role:Professeur|Administrateur'])->group(function () {
    Route::get('/admin/stages',                       [AdminStageController::class, 'index'])  ->name('admin.stages.index');
    Route::put('/admin/stages/{stage}/assign',        [AdminStageController::class, 'assign']) ->name('admin.stages.assign');
    Route::patch('/admin/stages/{stage}/valider',     [AdminStageController::class, 'valider'])->name('admin.stages.valider');
    Route::patch('/admin/stages/{stage}/rejeter',     [AdminStageController::class, 'rejeter'])->name('admin.stages.rejeter');

    Route::get('/admin/parametres',  [AdminParametreController::class, 'index'])       ->name('admin.parametres.index');
    Route::put('/admin/parametres',  [AdminParametreController::class, 'update'])      ->name('admin.parametres.update');
    Route::post('/admin/parametres/nouvelle-annee', [AdminParametreController::class, 'nouvelleAnnee'])->name('admin.parametres.nouvelle-annee');
    Route::post('/admin/parametres/set-active',     [AdminParametreController::class, 'setActive'])    ->name('admin.parametres.set-active');
});

// Bascule SPE — Admin uniquement
Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::post('/admin/parametres/toggle-spe', [AdminParametreController::class, 'toggleSpe'])->name('admin.parametres.toggle-spe');
});

// Import Pronote + Spécialités — Prof ET Admin
Route::middleware(['auth', 'role:Professeur|Administrateur'])->group(function () {

    Route::get('/imports/pronote',          [AdminImportController::class, 'pronoteForm'])   ->name('imports.pronote.form');
    Route::post('/imports/pronote/preview', [AdminImportController::class, 'pronotePreview'])->name('imports.pronote.preview');
    Route::post('/imports/pronote/confirm', [AdminImportController::class, 'pronoteConfirm'])->name('imports.pronote.confirm');

    Route::get('/spe',                       [AdminSpeController::class, 'index'])       ->name('spe.index');
    Route::get('/spe/{classe}',              [AdminSpeController::class, 'editClasse'])  ->name('spe.edit-classe');
    Route::post('/spe/{classe}',             [AdminSpeController::class, 'updateClasse'])->name('spe.update-classe');
});

// Bascule SPE déplacée dans AdminParametreController (admin.parametres.toggle-spe)


/*
|--------------------------------------------------------------------------
| PDF — auth requis
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/stages/{stage}/pdf/convention',  [PdfController::class, 'convention']) ->name('pdf.convention');
    Route::get('/stages/{stage}/pdf/attestation', [PdfController::class, 'attestation'])->name('pdf.attestation');
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


    // Opérations destructives — Admin uniquement
    Route::delete('/admin/users/{user}',          [AdminUserController::class, 'destroy'])  ->name('admin.users.destroy');
    Route::patch('/admin/users/{user}/anonymize', [AdminUserController::class, 'anonymize'])->name('admin.users.anonymize');

    // Import Pronote — UC_PRONOTE
    // Import Pronote accessible via /imports/pronote (Prof|Admin)

    // Journal d'actions — UC_AUDIT
    Route::get('/admin/audit', [AdminAuditController::class, 'index'])->name('admin.audit.index');

    // Journal d'actions — UC_AUDIT
    // Bascule SPE et journal restent admin uniquement
});

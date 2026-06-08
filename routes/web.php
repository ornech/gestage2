<?php

use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminCommunicationController;
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

/*
|--------------------------------------------------------------------------
| RGPD — suppression email maître de stage (lien signé, public)
|--------------------------------------------------------------------------
*/
Route::get('/rgpd/employe/{employe}/supprimer-email', [EmployeController::class, 'supprimerEmailRgpd'])
    ->name('rgpd.employe.supprimer-email')
    ->middleware('signed');
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
| Entreprises — lecture publique (auth), écriture réservée Professeur|Admin
|--------------------------------------------------------------------------
*/

// Routes fixes AVANT le wildcard {entreprise} pour éviter l'interception
// Écriture : Professeur et Administrateur uniquement
Route::middleware(['auth', 'role:Professeur|Administrateur'])->group(function () {
    Route::get('/entreprises/create',            [CompanyController::class, 'create'])    ->name('entreprises.create');
    Route::get('/entreprises/import',            [CompanyController::class, 'importForm'])->name('entreprises.import.form');
    Route::post('/entreprises/import',           [CompanyController::class, 'import'])    ->name('entreprises.import');
    Route::post('/entreprises',                  [CompanyController::class, 'store'])     ->name('entreprises.store');
    Route::put('/entreprises/{entreprise}',      [CompanyController::class, 'update'])    ->name('entreprises.update');
});

// Lecture seule : tous les utilisateurs authentifiés (étudiants inclus)
// {entreprise} en DERNIER pour ne pas intercepter /create et /import
Route::middleware(['auth'])->group(function () {
    Route::get('/entreprises',             [CompanyController::class, 'index']) ->name('entreprises.index');
    Route::get('/entreprises/{entreprise}',[CompanyController::class, 'show'])  ->name('entreprises.show');
});


/*
|--------------------------------------------------------------------------
| Employés / Contacts — lecture limitée (auth), écriture réservée Prof|Admin
|--------------------------------------------------------------------------
*/

// Vue fiche contact + édition : tous les auth, accès fin géré par EmployePolicy
// (Professeur, Administrateur, ou l'étudiant qui a créé ce maître de stage)
Route::middleware(['auth'])->group(function () {
    Route::get('/employes/{employe}',      [EmployeController::class, 'show']) ->name('employes.show');
    Route::get('/employes/{employe}/edit', [EmployeController::class, 'edit']) ->name('employes.edit');
    Route::put('/employes/{employe}',      [EmployeController::class, 'update'])->name('employes.update');
});

// CRUD complet : Professeur et Administrateur uniquement
Route::middleware(['auth', 'role:Professeur|Administrateur'])->group(function () {
    Route::get('/employes',                                          [EmployeController::class, 'index'])  ->name('employes.index');
    Route::get('/entreprises/{entreprise}/employes/create',          [EmployeController::class, 'create']) ->name('employes.create');
    Route::post('/entreprises/{entreprise}/employes',                [EmployeController::class, 'store'])  ->name('employes.store');
});


/*
|--------------------------------------------------------------------------
| Stages — auth requis, accès fin géré par StagePolicy
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::resource('stages', StageController::class)->except(['create', 'destroy']);

    // Journal de stage — student_has_stage laisse passer les profs/admins, bloque les étudiants sans stage
    Route::middleware('student_has_stage')->group(function () {
        Route::get('/stages/{stage}/journal',              [JournalController::class, 'index'])  ->name('stages.journal.index');
        Route::get('/stages/{stage}/journal/create',       [JournalController::class, 'create']) ->name('stages.journal.create');
        Route::post('/stages/{stage}/journal',             [JournalController::class, 'store'])  ->name('stages.journal.store');
        Route::get('/stages/{stage}/journal/{entry}/edit', [JournalController::class, 'edit'])   ->name('stages.journal.edit');
        Route::put('/stages/{stage}/journal/{entry}',      [JournalController::class, 'update']) ->name('stages.journal.update');
        Route::delete('/stages/{stage}/journal/{entry}',   [JournalController::class, 'destroy'])->name('stages.journal.destroy');
    });
});


/*
|--------------------------------------------------------------------------
| Espace Étudiant
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Etudiant'])->group(function () {

    Route::get('/etudiant', function () {
        $user   = auth()->user();
        $stages = $user->stages()->with(['entreprise', 'maitreDeStage', 'professeur'])->withCount('journalEntries')->latest()->get();
        $convPapier = $user->conventionPapier;
        return view('dashboards.etudiant', compact('user', 'stages', 'convPapier'));
    })->name('etudiant.dashboard');

    Route::get('/etudiant/stages', [StageController::class, 'mesStages'])
        ->name('etudiant.stages.index');

    Route::get('/etudiant/stage/nouveau', [StageController::class, 'etudiantNouveau'])
        ->name('etudiant.stage.nouveau');

    Route::get('/etudiant/stage/recherche-siret', [StageController::class, 'rechercheSiret'])
        ->name('etudiant.stage.recherche-siret');

    Route::post('/etudiant/stage/maitre-de-stage', [StageController::class, 'ajouterMaitreDeStage'])
        ->name('etudiant.stage.maitre-de-stage.store');

    Route::get('/etudiant/conventions', [StageController::class, 'mesConventions'])
        ->name('etudiant.conventions.index');

});


/*
|--------------------------------------------------------------------------
| Espace Professeur
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Professeur'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\ProfesseurController::class, 'dashboard'])
        ->name('professeur.dashboard');
});

// Gestion des étudiants — Prof ET Admin
Route::middleware(['auth', 'role:Professeur|Administrateur'])->group(function () {

    Route::get('/admin/users',                        [AdminUserController::class, 'index'])       ->name('admin.users.index');
    Route::get('/admin/users/create',                 [AdminUserController::class, 'create'])      ->name('admin.users.create');
    Route::post('/admin/users',                       [AdminUserController::class, 'store'])       ->name('admin.users.store');
    Route::get('/admin/users/{user}/edit',            [AdminUserController::class, 'edit'])        ->name('admin.users.edit');
    Route::put('/admin/users/{user}',                 [AdminUserController::class, 'update'])      ->name('admin.users.update');
    Route::patch('/admin/users/{user}/statut',        [AdminUserController::class, 'updateStatut'])->name('admin.users.statut');
    Route::patch('/admin/users/{user}/redoubler',     [AdminUserController::class, 'redoubler'])   ->name('admin.users.redoubler');
    Route::patch('/admin/users/{user}/assign-tuteur', [AdminUserController::class, 'assignTuteur'])->name('admin.users.assign-tuteur');
});

// Stages + Paramètres année scolaire — Prof ET Admin
Route::middleware(['auth', 'role:Professeur|Administrateur'])->group(function () {
    Route::get('/admin/stages',                       [AdminStageController::class, 'index'])  ->name('admin.stages.index');
    Route::put('/admin/stages/{stage}/assign',        [AdminStageController::class, 'assign']) ->name('admin.stages.assign');
    Route::patch('/admin/stages/{stage}/valider',             [AdminStageController::class, 'valider'])          ->name('admin.stages.valider');
    Route::patch('/admin/stages/{stage}/rejeter',             [AdminStageController::class, 'rejeter'])          ->name('admin.stages.rejeter');
    Route::patch('/admin/stages/{stage}/convention/{statut}', [AdminStageController::class, 'updateConvention'])  ->name('admin.stages.convention');
    Route::patch('/admin/stages/hors-appli/{user}',                    [AdminStageController::class, 'marquerHorsAppli'])          ->name('admin.stages.hors-appli');
    Route::patch('/admin/conventions-papier/{convention}/avancer',     [AdminStageController::class, 'avancerConventionPapier'])    ->name('admin.conventions-papier.avancer');
    Route::delete('/admin/conventions-papier/{convention}/revert',     [AdminStageController::class, 'revertConventionPapier'])     ->name('admin.conventions-papier.revert');
    Route::delete('/admin/stages/{stage}/revert',                      [AdminStageController::class, 'revertConvention'])           ->name('admin.stages.revert');

    Route::get('/admin/parametres',  [AdminParametreController::class, 'index'])            ->name('admin.parametres.index');
    Route::put('/admin/parametres',  [AdminParametreController::class, 'update'])           ->name('admin.parametres.update');
    Route::get('/admin/parametres/convention',  [AdminParametreController::class, 'convention'])      ->name('admin.parametres.convention');
    Route::put('/admin/parametres/convention',  [AdminParametreController::class, 'updateConvention'])->name('admin.parametres.convention.update');
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

    Route::get('/admin', [\App\Http\Controllers\AdminDashboardController::class, 'dashboard'])
        ->name('admin.dashboard');


    // Opérations destructives — Admin uniquement
    Route::get('/admin/professeurs',                  [AdminUserController::class, 'professeurs'])   ->name('admin.professeurs.index');
    Route::patch('/admin/users/{user}/toggle-admin',  [AdminUserController::class, 'toggleAdmin'])   ->name('admin.users.toggle-admin');
    Route::get('/admin/reset-password',               [AdminUserController::class, 'resetPasswordForm']) ->name('admin.reset-password');
    Route::post('/admin/reset-password',              [AdminUserController::class, 'resetPassword'])     ->name('admin.reset-password.do');
    Route::delete('/admin/users/{user}',          [AdminUserController::class, 'destroy'])  ->name('admin.users.destroy');
    Route::patch('/admin/users/{user}/anonymize', [AdminUserController::class, 'anonymize'])->name('admin.users.anonymize');

    // Import Pronote — UC_PRONOTE
    // Import Pronote accessible via /imports/pronote (Prof|Admin)

    // Journal d'actions — UC_AUDIT (conservé, non affiché en nav)
    Route::get('/admin/audit', [AdminAuditController::class, 'index'])->name('admin.audit.index');

    // Nettoyage comptes — comptes @import.local et doublons
    Route::get('/admin/comptes/nettoyage',              [AdminUserController::class, 'nettoyage'])      ->name('admin.comptes.nettoyage');
    Route::post('/admin/comptes/{user}/update-email',   [AdminUserController::class, 'updateEmail'])    ->name('admin.comptes.update-email');
    Route::post('/admin/comptes/fusionner',             [AdminUserController::class, 'fusionner'])      ->name('admin.comptes.fusionner');

    // Communication — envoi, templates, suivi RGPD
    Route::get('/admin/communication',                    [AdminCommunicationController::class, 'index'])          ->name('admin.communication.index');
    Route::post('/admin/communication/envoyer',           [AdminCommunicationController::class, 'envoyer'])        ->name('admin.communication.envoyer');
    Route::put('/admin/communication/template',           [AdminCommunicationController::class, 'updateTemplate']) ->name('admin.communication.template');
    Route::get('/admin/communication/preview/bienvenue',  [AdminCommunicationController::class, 'previewBienvenue'])->name('admin.communication.preview.bienvenue');
});

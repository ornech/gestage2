<?php
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedirectController; // Importer le nouveau contrôleur
use Illuminate\Support\Facades\Route; // <- Import très important
use App\Http\Controllers\EmployeController; // Importer le contrôleur Employe
use App\Http\Controllers\StageController; // Importer le contrôleur Stage
use App\Http\Controllers\ContactController; // Importer le contrôleur Contact

// --- L'AIGUILLEUR PRINCIPAL (Racine du site) ---
Route::get('/', [RedirectController::class, 'index']);
// --- Route de test pour vérifier que l'application fonctionne ---
Route::post('/companies', [CompanyController::class, 'store']);
// --- Routes pour les étudiants ---
Route::get('/etudiant/stages', [StageController::class, 'mesStages'])
    ->name('etudiant.stages.index');
    // --- Route pour l'import de SIRET (utilisée dans le test) ---
Route::post('/companies/import-siret', [CompanyController::class, 'importSiret']);

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



//ajout de la route pour les employes
Route::resource('employes', EmployeController::class);
//ajout de la route pour les stages 
Route::resource('stages', StageController::class);
// Routes pour les contacts d'une entreprise
Route::prefix('companies/{company}')->group(function () {
    Route::get('/contacts', [ContactController::class, 'index'])->name('companies.contacts.index');
    Route::post('/contacts', [ContactController::class, 'store'])->name('companies.contacts.store');
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('companies.contacts.show');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('companies.contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('companies.contacts.destroy');
});
// Espace administrateur 
Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::get('/admin', function () {
        return view('dashboards.admin');
    })->name('admin.dashboard');

    //  Nouvelle route pour la console admin des stages
    Route::get('/admin/stages', [\App\Http\Controllers\AdminStageController::class, 'index'])
        ->name('admin.stages.index');

// Assignation du tuteur
    Route::put('/admin/stages/{stage}/assign', [\App\Http\Controllers\AdminStageController::class, 'assign'])
        ->name('admin.stages.assign');
});


<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder; // Import manquant ajouté ici
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Données structurelles (S'exécutent partout)
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Cloisonnement : Données de test uniquement pour le développement
        if (app()->environment('local', 'testing')) {
            $this->command->info('Environnement de dev détecté : Création des données de test...');

            // Création d'un étudiant spécifique
            User::factory()->create([
                'name' => 'Étudiant Test',
                'email' => 'etudiant@test.com',
                'role' => 'etudiant',
                'password' => Hash::make('etudiant'),
            ]);

            // Création d'un professeur spécifique
            User::factory()->create([
                'name' => 'Professeur Test',
                'email' => 'prof@test.com',
                'role' => 'professeur',
                'password' => Hash::make('professeur'),
            ]);

            // Création de l'administrateur
            User::factory()->create([
                'name' => 'Admin Système',
                'email' => 'admin@test.com',
                'role' => 'admin',
                'password' => Hash::make('admin'),
            ]);

            // Crée 10 entreprises (et 10 utilisateurs associés automatiquement)
            Entreprise::factory(10)->create();
        }
    }
}

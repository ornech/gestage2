<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
        // IL FAUT DÉCOMMENTER CECI pour que les rôles existent en base !
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Cloisonnement : Données de test uniquement pour le développement
        if (app()->environment('local', 'testing')) {
            $this->command->info('Environnement de dev détecté : Création des données de test...');

            // Création d'un étudiant de test et assignation de son rôle
            $etudiant = User::factory()->create([
                'nom' => 'DEMO',
                'prenom' => 'Étudiant',
                'email' => 'etudiant@test.com',
                'password' => Hash::make('etudiant'),
            ]);
            $etudiant->assignRole('Etudiant'); // <- L'assignation magique

            // Création d'un professeur spécifique et assignation de son rôle
            $professeur = User::factory()->create([
                'nom' => 'DEMO',
                'prenom' => 'professeur',
                'email' => 'professeur@test.com',
                'password' => Hash::make('professeur'),
            ]);
            $professeur->assignRole('Professeur'); // <- L'assignation magique

            // Création de l'admin d'abord et assignation de son rôle
            $admin = User::factory()->create([
                'nom' => 'ADMIN',
                'prenom' => 'Système',
                'email' => 'admin@test.com',
                'password' => Hash::make('admin'),
            ]);
            $admin->assignRole('Administrateur'); // <- L'assignation magique (Attention à l'orthographe exacte !)

            // On crée 10 entreprises liées à cet admin précis
            Entreprise::factory(10)->create([
                'user_id' => $admin->id,
            ]);
        }
    }
}

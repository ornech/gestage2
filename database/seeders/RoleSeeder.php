<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // Import du modèle de Spatie

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Utilisation de firstOrCreate pour garantir l'idempotence
        Role::firstOrCreate(['name' => 'Administrateur']);
        Role::firstOrCreate(['name' => 'Professeur']);
        Role::firstOrCreate(['name' => 'Etudiant']);
    }
}

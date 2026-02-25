<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EntrepriseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // On crée des entreprises (et les users liés automatiquement par la factory)
        \App\Models\Entreprise::factory(10)->create();
    }
}

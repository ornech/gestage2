<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir les éventuels hors_appli résiduels
        DB::statement("UPDATE stages SET statut_convention = 'a_faire_signer' WHERE statut_convention = 'hors_appli'");

        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant')
            NOT NULL DEFAULT 'a_faire_signer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant','hors_appli')
            NOT NULL DEFAULT 'a_faire_signer'");
    }
};

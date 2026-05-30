<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : élargir l'enum pour accepter les deux nomenclatures
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant','en_attente','validee')
            NOT NULL DEFAULT 'a_faire_signer'");

        DB::statement("ALTER TABLE conventions_papier MODIFY COLUMN statut
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant','en_attente','validee')
            NOT NULL DEFAULT 'a_faire_signer'");

        // Étape 2 : migrer les données
        DB::statement("UPDATE stages SET statut_convention = 'en_attente' WHERE statut_convention = 'en_attente_signature'");
        DB::statement("UPDATE stages SET statut_convention = 'validee'    WHERE statut_convention = 'remise_etudiant'");
        DB::statement("UPDATE conventions_papier SET statut = 'en_attente' WHERE statut = 'en_attente_signature'");
        DB::statement("UPDATE conventions_papier SET statut = 'validee'    WHERE statut = 'remise_etudiant'");

        // Étape 3 : réduire aux nouvelles valeurs
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente','validee')
            NOT NULL DEFAULT 'a_faire_signer'");

        DB::statement("ALTER TABLE conventions_papier MODIFY COLUMN statut
            ENUM('a_faire_signer','en_attente','validee')
            NOT NULL DEFAULT 'a_faire_signer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente','validee','en_attente_signature','remise_etudiant')
            NOT NULL DEFAULT 'a_faire_signer'");
        DB::statement("ALTER TABLE conventions_papier MODIFY COLUMN statut
            ENUM('a_faire_signer','en_attente','validee','en_attente_signature','remise_etudiant')
            NOT NULL DEFAULT 'a_faire_signer'");

        DB::statement("UPDATE stages SET statut_convention = 'en_attente_signature' WHERE statut_convention = 'en_attente'");
        DB::statement("UPDATE stages SET statut_convention = 'remise_etudiant'      WHERE statut_convention = 'validee'");
        DB::statement("UPDATE conventions_papier SET statut = 'en_attente_signature' WHERE statut = 'en_attente'");
        DB::statement("UPDATE conventions_papier SET statut = 'remise_etudiant'      WHERE statut = 'validee'");

        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant')
            NOT NULL DEFAULT 'a_faire_signer'");
        DB::statement("ALTER TABLE conventions_papier MODIFY COLUMN statut
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant')
            NOT NULL DEFAULT 'a_faire_signer'");
    }
};

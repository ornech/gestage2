<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : élargir l'enum pour accepter les deux nomenclatures
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('aucune','remise','signee','signee_hors_appli',
                 'a_faire_signer','en_attente_signature','remise_etudiant','hors_appli')
            NOT NULL DEFAULT 'a_faire_signer'");

        // Étape 2 : migrer les anciennes valeurs
        DB::statement("UPDATE stages SET statut_convention = 'a_faire_signer'       WHERE statut_convention = 'aucune'");
        DB::statement("UPDATE stages SET statut_convention = 'en_attente_signature' WHERE statut_convention = 'remise'");
        DB::statement("UPDATE stages SET statut_convention = 'remise_etudiant'      WHERE statut_convention = 'signee'");
        DB::statement("UPDATE stages SET statut_convention = 'hors_appli'           WHERE statut_convention = 'signee_hors_appli'");

        // Étape 3 : réduire aux nouvelles valeurs uniquement
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant','hors_appli')
            NOT NULL DEFAULT 'a_faire_signer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('a_faire_signer','en_attente_signature','remise_etudiant','hors_appli',
                 'aucune','remise','signee','signee_hors_appli')
            NOT NULL DEFAULT 'aucune'");

        DB::statement("UPDATE stages SET statut_convention = 'aucune'          WHERE statut_convention = 'a_faire_signer'");
        DB::statement("UPDATE stages SET statut_convention = 'remise'          WHERE statut_convention = 'en_attente_signature'");
        DB::statement("UPDATE stages SET statut_convention = 'signee'          WHERE statut_convention = 'remise_etudiant'");
        DB::statement("UPDATE stages SET statut_convention = 'signee_hors_appli' WHERE statut_convention = 'hors_appli'");

        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('aucune','remise','signee','signee_hors_appli')
            NOT NULL DEFAULT 'aucune'");
    }
};

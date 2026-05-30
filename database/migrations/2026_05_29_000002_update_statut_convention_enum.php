<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : Élargir l'enum pour accepter toutes les valeurs (anciennes + nouvelles)
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('non_remise','remise_direction','signee_rendue','aucune','remise','signee','signee_hors_appli')
            NOT NULL DEFAULT 'aucune'");

        // Étape 2 : Migrer les données vers les nouvelles valeurs
        DB::statement("UPDATE stages SET statut_convention = 'aucune'  WHERE statut_convention = 'non_remise'");
        DB::statement("UPDATE stages SET statut_convention = 'remise'  WHERE statut_convention = 'remise_direction'");
        DB::statement("UPDATE stages SET statut_convention = 'signee'  WHERE statut_convention = 'signee_rendue'");

        // Étape 3 : Réduire l'enum aux nouvelles valeurs uniquement
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('aucune','remise','signee','signee_hors_appli')
            NOT NULL DEFAULT 'aucune'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('aucune','remise','signee','signee_hors_appli','non_remise','remise_direction','signee_rendue')
            NOT NULL DEFAULT 'non_remise'");

        DB::statement("UPDATE stages SET statut_convention = 'non_remise'       WHERE statut_convention = 'aucune'");
        DB::statement("UPDATE stages SET statut_convention = 'remise_direction' WHERE statut_convention = 'remise'");
        DB::statement("UPDATE stages SET statut_convention = 'signee_rendue'    WHERE statut_convention = 'signee'");

        DB::statement("ALTER TABLE stages MODIFY COLUMN statut_convention
            ENUM('non_remise','remise_direction','signee_rendue')
            NOT NULL DEFAULT 'non_remise'");
    }
};

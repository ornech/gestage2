<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : élargir l'enum pour accepter le nouveau statut initial
        DB::statement("ALTER TABLE conventions_papier MODIFY COLUMN statut
            ENUM('hors_app','a_faire_signer','en_attente','validee')
            NOT NULL DEFAULT 'hors_app'");

        // Étape 2 : les conventions remises directement par l'étudiant (signées)
        // démarrent désormais à 'hors_app' au lieu de 'en_attente'
        DB::statement("UPDATE conventions_papier SET statut = 'hors_app' WHERE statut = 'en_attente'");
    }

    public function down(): void
    {
        DB::statement("UPDATE conventions_papier SET statut = 'en_attente' WHERE statut = 'hors_app'");

        DB::statement("ALTER TABLE conventions_papier MODIFY COLUMN statut
            ENUM('a_faire_signer','en_attente','validee')
            NOT NULL DEFAULT 'a_faire_signer'");
    }
};

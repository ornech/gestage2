<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deux statuts de sortie du cycle, distincts de « diplômé » (qui reste une validation explicite) :
 * - archive : ancien dont le résultat n'est pas connu (reprise de l'ancienne base)
 * - echec   : BTS non obtenu
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN statut
            ENUM('actif','demissionnaire','diplome','archive','echec') NOT NULL DEFAULT 'actif'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET statut = 'actif' WHERE statut IN ('archive','echec')");
        DB::statement("ALTER TABLE users MODIFY COLUMN statut
            ENUM('actif','demissionnaire','diplome') NOT NULL DEFAULT 'actif'");
    }
};

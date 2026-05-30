<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Un redoublant reste actif, sa promo est incrémentée via le bouton "Redoubler"
        DB::statement("UPDATE users SET statut = 'actif' WHERE statut = 'redoublant'");

        DB::statement("ALTER TABLE users MODIFY COLUMN statut
            ENUM('actif','demissionnaire') NOT NULL DEFAULT 'actif'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN statut
            ENUM('actif','redoublant','demissionnaire') NOT NULL DEFAULT 'actif'");
    }
};

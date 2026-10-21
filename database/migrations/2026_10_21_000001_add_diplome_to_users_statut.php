<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le statut « diplômé » n'est plus déduit de la promo : un professeur le valide
 * explicitement. On trace la date et l'auteur de la validation.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN statut
            ENUM('actif','demissionnaire','diplome') NOT NULL DEFAULT 'actif'");

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('diplome_at')->nullable()->after('date_sortie');
            $table->foreignId('diplome_par')->nullable()->after('diplome_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diplome_par');
            $table->dropColumn('diplome_at');
        });

        DB::statement("UPDATE users SET statut = 'actif' WHERE statut = 'diplome'");
        DB::statement("ALTER TABLE users MODIFY COLUMN statut
            ENUM('actif','demissionnaire') NOT NULL DEFAULT 'actif'");
    }
};

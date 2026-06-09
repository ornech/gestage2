<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->string('annee_scolaire', 9)->nullable()->after('classe');
        });

        // Backfill depuis date_debut : mois >= 9 → même année, sinon année précédente
        DB::statement("
            UPDATE stages
            SET annee_scolaire = CASE
                WHEN MONTH(date_debut) >= 9
                    THEN CONCAT(YEAR(date_debut), '-', YEAR(date_debut) + 1)
                ELSE
                    CONCAT(YEAR(date_debut) - 1, '-', YEAR(date_debut))
            END
            WHERE date_debut IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn('annee_scolaire');
        });
    }
};

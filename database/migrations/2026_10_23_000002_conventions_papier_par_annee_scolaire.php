<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Une convention papier est celle d'un stage, donc d'une année scolaire :
 * un étudiant (redoublant compris) en a au plus une par année.
 */
return new class extends Migration
{
    public function up(): void
    {
        $active = DB::table('parametres')->where('cle', 'annee_scolaire')->value('valeur')
            ?? date('Y').'-'.(date('Y') + 1);

        Schema::table('conventions_papier', function (Blueprint $table) {
            $table->string('annee_scolaire', 9)->nullable()->after('etudiant_id');
        });

        DB::table('conventions_papier')->update(['annee_scolaire' => $active]);

        Schema::table('conventions_papier', function (Blueprint $table) {
            $table->string('annee_scolaire', 9)->nullable(false)->change();
            // Le nouvel index doit exister avant de retirer l'ancien (clé étrangère sur etudiant_id)
            $table->unique(['etudiant_id', 'annee_scolaire'], 'conventions_papier_etudiant_annee_unique');
            $table->dropUnique(['etudiant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('conventions_papier', function (Blueprint $table) {
            $table->unique('etudiant_id');
            $table->dropUnique('conventions_papier_etudiant_annee_unique');
            $table->dropColumn('annee_scolaire');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->enum('statut_convention', [
                'non_remise',        // Convention pas encore déposée
                'remise_direction',  // Déposée pour signature
                'signee_rendue',     // Signée et rendue à l'étudiant
            ])->default('non_remise')->after('statut_validation');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn('statut_convention');
        });
    }
};

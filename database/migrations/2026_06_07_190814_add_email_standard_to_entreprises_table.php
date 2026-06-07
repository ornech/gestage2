<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            // Contact générique de l'entreprise (standard téléphonique / accueil) :
            // reste accessible aux étudiants même si un maître de stage demande
            // l'anonymisation de ses coordonnées personnelles (RGPD).
            $table->string('email')->nullable()->after('telephone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};

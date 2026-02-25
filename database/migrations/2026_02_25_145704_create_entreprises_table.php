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
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('raison_sociale');
            $table->string('siret', 14)->nullable()->unique();
            $table->string('code_naf', 5)->nullable();

            // Coordonnées
            $table->string('adresse');
            $table->string('complement_adresse')->nullable();
            $table->string('code_postal', 5);
            $table->string('ville');
            $table->string('departement_code', 3)->nullable();
            $table->string('telephone', 20)->nullable();

            // Métadonnées
            $table->string('type')->nullable(); // SA, SARL...
            $table->unsignedInteger('effectif')->nullable();
            $table->boolean('est_valide')->default(false);

            // Clé étrangère (Ancien Created_UserID)
            // Crée la colonne user_id et la lie à la table users
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Timestamps (Created_Date & Updated_Date)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};

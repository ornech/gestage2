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
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
             $table->string('titre');
            $table->text('description')->nullable();
           $table->date('date_debut');
            $table->date('date_fin');
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('maitre_de_stage_id')->constrained('employes')->onDelete('cascade');
            $table->foreignId('etudiant_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('professeur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('classe')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};

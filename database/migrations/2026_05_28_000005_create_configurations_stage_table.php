<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configurations_stage', function (Blueprint $table) {
            $table->id();
            $table->string('annee_scolaire');
            $table->enum('classe', ['SIO1', 'SIO2']);
            $table->foreignId('prof_principal_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('stage_date_debut')->nullable();
            $table->date('stage_date_fin')->nullable();
            $table->timestamps();

            $table->unique(['annee_scolaire', 'classe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configurations_stage');
    }
};

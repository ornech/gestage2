<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conventions_papier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('users')->onDelete('cascade');
            $table->enum('statut', ['a_faire_signer', 'en_attente_signature', 'remise_etudiant'])
                  ->default('a_faire_signer');
            $table->timestamps();

            $table->unique('etudiant_id'); // une seule convention papier en cours par étudiant
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conventions_papier');
    }
};

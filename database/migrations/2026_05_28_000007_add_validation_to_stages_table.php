<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->enum('statut_validation', ['en_attente', 'valide', 'rejete'])
                  ->default('en_attente')
                  ->after('classe');
            $table->text('note_rejet')->nullable()->after('statut_validation');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn(['statut_validation', 'note_rejet']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('statut', ['actif', 'demissionnaire', 'redoublant'])
                  ->default('actif')
                  ->after('classe');
            $table->date('date_sortie')->nullable()->after('statut');
            $table->boolean('force_password_change')->default(false)->after('date_sortie');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['statut', 'date_sortie', 'force_password_change']);
        });
    }
};

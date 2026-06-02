<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->string('code_postal', 20)->nullable()->change();
            $table->string('code_naf', 10)->nullable()->change();
            $table->string('departement_code', 5)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->string('code_postal', 5)->nullable()->change();
            $table->string('code_naf', 5)->nullable()->change();
            $table->string('departement_code', 3)->nullable()->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->foreignId('entreprise_id')->nullable()->change();
            $table->foreignId('maitre_de_stage_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->foreignId('entreprise_id')->nullable(false)->change();
            $table->foreignId('maitre_de_stage_id')->nullable(false)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres', function (Blueprint $table) {
            $table->string('cle')->primary();
            $table->text('valeur')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('parametres')->insert([
            [
                'cle'         => 'spe_assignments_open',
                'valeur'      => '0',
                'description' => 'Ouverture de l\'affectation SLAM/SISR (second semestre)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'cle'         => 'annee_scolaire',
                'valeur'      => '2025-2026',
                'description' => 'Année scolaire courante (format YYYY-YYYY)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};

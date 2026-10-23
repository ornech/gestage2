<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** L'année scolaire est désormais calculée (bascule le 1er septembre) : plus de valeur stockée. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('parametres')->where('cle', 'annee_scolaire')->delete();
    }

    public function down(): void
    {
        DB::table('parametres')->insert([
            'cle'    => 'annee_scolaire',
            'valeur' => date('Y').'-'.(date('Y') + 1),
            'description' => 'Année scolaire courante (format YYYY-YYYY)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};

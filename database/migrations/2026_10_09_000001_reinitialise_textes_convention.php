<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Supprime les articles de convention enregistrés : les textes validés
    // (valeurs par défaut de PdfController) s'appliquent alors.
    public function up(): void
    {
        $cles = [];
        foreach (array_merge(
            array_map(fn ($n) => "conv_art{$n}", range(1, 12)),
            ['conv_part1', 'conv_part2']
        ) as $base) {
            $cles[] = $base.'_titre';
            $cles[] = $base.'_corps';
        }

        DB::table('parametres')->whereIn('cle', $cles)->delete();
    }

    public function down(): void
    {
        // Les anciens textes ne sont pas restaurés.
    }
};

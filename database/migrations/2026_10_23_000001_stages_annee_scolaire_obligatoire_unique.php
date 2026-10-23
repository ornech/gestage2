<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un stage appartient à une année scolaire (jamais à une promo) et
 * un étudiant a au plus un stage par année scolaire.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Les stages restés sans année (sans date de début) reçoivent l'année active
        $active = DB::table('parametres')->where('cle', 'annee_scolaire')->value('valeur')
            ?? date('Y').'-'.(date('Y') + 1);

        DB::table('stages')->whereNull('annee_scolaire')->update(['annee_scolaire' => $active]);

        $doublons = DB::table('stages')
            ->select('etudiant_id', 'annee_scolaire', DB::raw('COUNT(*) as n'), DB::raw('GROUP_CONCAT(id) as ids'))
            ->whereNotNull('etudiant_id')
            ->groupBy('etudiant_id', 'annee_scolaire')
            ->having('n', '>', 1)
            ->get();

        if ($doublons->isNotEmpty()) {
            $detail = $doublons->map(fn ($d) => "étudiant {$d->etudiant_id} / {$d->annee_scolaire} : stages {$d->ids}")->implode(' ; ');
            throw new RuntimeException("Plusieurs stages pour un même étudiant et une même année, à corriger avant de migrer : {$detail}");
        }

        Schema::table('stages', function (Blueprint $table) {
            $table->string('annee_scolaire', 9)->nullable(false)->change();
            $table->unique(['etudiant_id', 'annee_scolaire'], 'stages_etudiant_annee_unique');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropUnique('stages_etudiant_annee_unique');
            $table->string('annee_scolaire', 9)->nullable()->change();
        });
    }
};

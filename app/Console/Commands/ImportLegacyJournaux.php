<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Importe les réalisations de journal depuis btssio17_legacy.journaux vers journal_entries.
 *
 * Idempotent : chaque entrée legacy est identifiée par son legacy_id.
 * Relancer la commande n'insère que les nouvelles entrées.
 *
 * Usage :
 *   php artisan journals:import-legacy            → import réel
 *   php artisan journals:import-legacy --dry-run  → simulation sans écriture
 *   php artisan journals:import-legacy --force    → réimporte tout (remplace les existants)
 */
class ImportLegacyJournaux extends Command
{
    protected $signature = 'journals:import-legacy
                            {--dry-run : Simule l\'import sans écrire en base}
                            {--force   : Réimporte toutes les entrées (remplace les existantes)}';

    protected $description = 'Importe les journaux de stage depuis btssio17_legacy vers gestage2';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $isForce  = $this->option('force');

        if ($isDryRun) {
            $this->warn('MODE DRY-RUN — aucune écriture en base.');
        }

        // Récupère toutes les entrées legacy avec leur stage gestage2 pour calculer la date
        $rows = DB::connection('legacy')
            ->table('journaux as j')
            ->join('gestage2.stages as gs', 'gs.id', '=', 'j.idStageEtu')
            ->select(
                'j.id        as legacy_id',
                'j.idEtu     as user_id',
                'j.idStageEtu as stage_id',
                'j.semaine',
                'j.titre',
                'j.description',
                'j.competences',
                'gs.date_debut as stage_date_debut'
            )
            ->get();

        $stats = ['inserted' => 0, 'skipped' => 0, 'replaced' => 0, 'errors' => 0];

        foreach ($rows as $row) {
            try {
                $existing = DB::table('journal_entries')
                    ->where('legacy_id', $row->legacy_id)
                    ->first();

                if ($existing && !$isForce) {
                    $stats['skipped']++;
                    continue;
                }

                // Conversion latin1 → UTF-8
                $titre       = $this->toUtf8($row->titre);
                $activites   = $this->toUtf8($row->description);

                // Fallback sur les valeurs vides/placeholder legacy
                $titre     = ($titre === null || $titre === '' || $titre === '-') ? 'Sans titre' : $titre;
                $activites = ($activites === null || $activites === '' || $activites === '-') ? '(Aucune description)' : $activites;

                // Calcul de la date de début de semaine depuis la date de début du stage
                $dateDebut = $row->stage_date_debut
                    ? Carbon::parse($row->stage_date_debut)->addDays(((int) $row->semaine - 1) * 7)->toDateString()
                    : now()->toDateString();

                $data = [
                    'legacy_id'          => $row->legacy_id,
                    'stage_id'           => $row->stage_id,
                    'user_id'            => $row->user_id,
                    'semaine'            => (int) $row->semaine,
                    'date_debut_semaine' => $dateDebut,
                    'titre'              => $titre,
                    'activites'          => $activites,
                    'competences'        => ($row->competences > 0) ? (int) $row->competences : null,
                    'updated_at'         => now(),
                ];

                if (!$isDryRun) {
                    if ($existing && $isForce) {
                        DB::table('journal_entries')->where('legacy_id', $row->legacy_id)->update($data);
                        $stats['replaced']++;
                    } else {
                        $data['created_at'] = now();
                        DB::table('journal_entries')->insert($data);
                        $stats['inserted']++;
                    }
                } else {
                    // En dry-run on simule le comptage
                    $existing ? $stats['replaced']++ : $stats['inserted']++;
                }

            } catch (\Throwable $e) {
                $this->error("  Erreur legacy_id={$row->legacy_id} : {$e->getMessage()}");
                $stats['errors']++;
            }
        }

        $prefix = $isDryRun ? '[DRY-RUN] ' : '';
        $this->info("{$prefix}Import terminé :");
        $this->table(
            ['Action', 'Nombre'],
            [
                ['Insérées',  $stats['inserted']],
                ['Remplacées', $stats['replaced']],
                ['Ignorées (déjà présentes)', $stats['skipped']],
                ['Erreurs',   $stats['errors']],
                ['Total traité', count($rows)],
            ]
        );

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** Convertit une chaîne latin1 en UTF-8 propre. */
    private function toUtf8(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Si déjà valide en UTF-8, on ne touche pas
        if (mb_detect_encoding($value, 'UTF-8', true)) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
    }
}

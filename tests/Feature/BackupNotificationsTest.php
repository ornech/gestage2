<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Env;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Tests\TestCase;

class BackupNotificationsTest extends TestCase
{
    /** Spatie lève une erreur pour tout événement sans notification déclarée dans config/backup.php. */
    public function test_chaque_evenement_de_sauvegarde_a_une_notification_declaree(): void
    {
        $declarees = collect(array_keys(config('backup.notifications.notifications')))
            ->map(fn ($classe) => class_basename($classe))
            ->all();

        foreach ([BackupHasFailed::class, BackupWasSuccessful::class, CleanupHasFailed::class,
            CleanupWasSuccessful::class, HealthyBackupWasFound::class, UnhealthyBackupWasFound::class] as $evenement) {
            $this->assertContains(class_basename($evenement).'Notification', $declarees, class_basename($evenement));
        }
    }

    public function test_les_alertes_d_echec_partent_par_mail_et_les_evenements_normaux_ne_font_rien(): void
    {
        $config = collect(config('backup.notifications.notifications'))
            ->mapWithKeys(fn ($canaux, $classe) => [class_basename($classe) => $canaux]);

        foreach (['BackupHasFailedNotification', 'CleanupHasFailedNotification', 'UnhealthyBackupWasFoundNotification'] as $alerte) {
            $this->assertSame(['mail'], $config[$alerte], $alerte);
        }
        foreach (['CleanupWasSuccessfulNotification', 'HealthyBackupWasFoundNotification'] as $silencieux) {
            $this->assertSame([], $config[$silencieux], $silencieux);
        }
    }

    public function test_le_controle_des_sauvegardes_est_planifie_apres_la_sauvegarde(): void
    {
        $commandes = collect(app(Schedule::class)->events())->map(fn ($e) => $e->command.' @ '.$e->expression);

        $this->assertTrue($commandes->contains(fn ($c) => str_contains($c, 'backup:monitor') && str_ends_with($c, '0 3 * * *')), $commandes->implode("\n"));
        $this->assertTrue($commandes->contains(fn ($c) => str_contains($c, 'backup:run') && str_ends_with($c, '0 2 * * *')));
    }

    /**
     * `composer install` (post-autoload-dump → package:discover) charge cette config AVANT que le .env existe,
     * par exemple en CI. Spatie refuse alors une adresse nulle et fait échouer toute l'installation.
     */
    public function test_l_adresse_des_alertes_est_valide_meme_sans_fichier_env(): void
    {
        $repo = Env::getRepository();
        $avant = ['BACKUP_MAIL_TO' => $repo->get('BACKUP_MAIL_TO'), 'MAIL_FROM_ADDRESS' => $repo->get('MAIL_FROM_ADDRESS')];

        foreach (array_keys($avant) as $variable) {
            $repo->clear($variable);
        }

        try {
            $config = require config_path('backup.php');
        } finally {
            foreach ($avant as $variable => $valeur) {
                if ($valeur !== null) {
                    $repo->set($variable, $valeur);
                }
            }
        }

        $this->assertNotFalse(filter_var($config['notifications']['mail']['to'], FILTER_VALIDATE_EMAIL));
        $this->assertNotFalse(filter_var($config['notifications']['mail']['from']['address'], FILTER_VALIDATE_EMAIL));
    }
}

<?php

use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

return [

    'backup' => [
        'name' => env('APP_NAME', 'gestage'),

        'source' => [
            // Le code source est dans git — on ne sauvegarde que la base
            'files' => [
                'include' => [],
                'exclude' => [],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],

            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        // Dump compressé en gzip
        'database_dump_compressor' => Spatie\DbDumper\Compressors\GzipCompressor::class,
        'database_dump_file_timestamp_format' => 'Y-m-d-H-i-s',
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'filename_prefix'    => '',
            'disks'              => ['local'],
            'continue_on_failure'=> false,
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password'   => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'default',
        'verify_backup' => false,
        'tries'         => 2,
        'retry_delay'   => 60,
    ],

    'notifications' => [
        'notifications' => [
            // Seulement succès et échec — pas de bruit sur le monitoring
            BackupWasSuccessfulNotification::class   => ['mail'],
            BackupHasFailedNotification::class       => ['mail'],
            CleanupHasFailedNotification::class      => ['mail'],
            UnhealthyBackupWasFoundNotification::class => ['mail'],
        ],

        'notifiable' => Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_MAIL_TO', 'jean-francois.ornech@ac-poitiers.fr'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'gestage@btssio17.fr'),
                'name'    => env('MAIL_FROM_NAME', 'Gestage'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',
            'channel'     => null,
            'username'    => null,
            'icon'        => null,
        ],
        'discord' => ['webhook_url' => '', 'username' => '', 'avatar_url' => ''],
        'webhook' => ['url' => ''],
    ],

    'log_channel' => null,

    'monitor_backups' => [
        [
            'name'  => env('APP_NAME', 'gestage'),
            'disks' => ['local'],
            'health_checks' => [
                MaximumAgeInDays::class          => 2,   // alerte si pas de backup depuis 2 jours
                MaximumStorageInMegabytes::class => 500, // alerte si > 500 Mo de backups
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days'      => 7,   // 1 semaine : tous les backups
            'keep_daily_backups_for_days'    => 30,  // 1 mois   : 1 par jour
            'keep_weekly_backups_for_weeks'  => 12,  // 3 mois   : 1 par semaine
            'keep_monthly_backups_for_months'=> 12,  // 1 an     : 1 par mois
            'keep_yearly_backups_for_years'  => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 400,
        ],
    ],

];

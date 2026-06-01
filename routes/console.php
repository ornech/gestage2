<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rappel hebdomadaire aux étudiants sans stage saisi — tous les lundis à 8h
Schedule::command('stages:rappel')->weeklyOn(1, '08:00');

// Traitement de la file d'attente des mails — toutes les minutes
// Nécessaire pour les communications envoyées en masse via le module Communication
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();

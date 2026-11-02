<?php

namespace Tests\Feature;

use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommandesDestructivesTest extends TestCase
{
    /** Garde-fou de AppServiceProvider : en production, migrate:fresh et db:wipe sont interdits (ils effaceraient la base restaurée). */
    public function test_les_commandes_destructives_sont_interdites_en_production(): void
    {
        $this->app['env'] = 'production';
        $this->app->getProvider(\App\Providers\AppServiceProvider::class)->boot();

        $this->assertTrue($this->interdite(FreshCommand::class));
        $this->assertTrue($this->interdite(WipeCommand::class));
    }

    public function test_elles_restent_autorisees_hors_production(): void
    {
        $this->app['env'] = 'local';
        $this->app->getProvider(\App\Providers\AppServiceProvider::class)->boot();

        $this->assertFalse($this->interdite(FreshCommand::class));
    }

    private function interdite(string $commande): bool
    {
        return (new \ReflectionClass($commande))->getProperty('prohibitedFromRunning')->getValue();
    }

    protected function tearDown(): void
    {
        DB::prohibitDestructiveCommands(false);
        parent::tearDown();
    }
}

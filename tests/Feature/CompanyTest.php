<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Entreprise;
use App\Services\SireneClient;

class CompanyTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
  public function test_manual_creation_sets_status_pending()
{
    
    // 1) On simule une requête POST pour créer une entreprise
    $response = $this->post('/companies', [
        'raison_sociale' => 'Entreprise Test',
        'siret' => '12345678901234',
    ]);

    // 2) On vérifie que la base contient bien status = pending
    $this->assertDatabaseHas('entreprises', [
        'raison_sociale' => 'Entreprise Test',
        'est_valide' => 0,
    ]);
}
public function test_import_siret_creates_company_if_not_exists()
{
    // Fake du client Sirene
    $this->mock(\App\Services\SireneClient::class, function ($mock) {
        $mock->shouldReceive('getBySiret')->andReturn([
            'etablissement' => [
                'uniteLegale' => [
                    'denominationUniteLegale' => 'ENTREPRISE IMPORTÉE'
                ],
                'adresseEtablissement' => [
                    'libelleVoieEtablissement' => '10 RUE TEST',
                    'codePostalEtablissement' => '33000',
                    'libelleCommuneEtablissement' => 'BORDEAUX'
                ]
            ]
        ]);
    });

    $response = $this->post('/companies/import-siret', [
        'siret' => '12345678901234'
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('entreprises', [
        'siret' => '12345678901234',
        'raison_sociale' => 'ENTREPRISE IMPORTÉE',
        'est_valide' => 1,
    ]);
}
public function test_import_siret_updates_company_if_exists()
{
    // Entreprise existante
    Entreprise::create([
        'raison_sociale' => 'ANCIEN NOM',
        'siret' => '12345678901234',
        'est_valide' => 0,
    ]);

    // Fake API
    $this->mock(\App\Services\SireneClient::class, function ($mock) {
        $mock->shouldReceive('getBySiret')->andReturn([
            'etablissement' => [
                'uniteLegale' => [
                    'denominationUniteLegale' => 'NOUVEAU NOM'
                ],
                'adresseEtablissement' => [
                    'libelleVoieEtablissement' => '20 AVENUE TEST',
                    'codePostalEtablissement' => '75000',
                    'libelleCommuneEtablissement' => 'PARIS'
                ]
            ]
        ]);
    });

    $response = $this->post('/companies/import-siret', [
        'siret' => '12345678901234'
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('entreprises', [
        'siret' => '12345678901234',
        'raison_sociale' => 'NOUVEAU NOM',
        'est_valide' => 1,
    ]);
}

}

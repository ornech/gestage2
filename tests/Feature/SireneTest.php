<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
class SireneTest extends TestCase
{
    /** @test */
    public function it_returns_company_data_on_valid_siret()
    {
        // 1) On simule la réponse de l'API INSEE
    Http::fake([
        'https://api.insee.fr/*' => Http::response([
            'etablissement' => [
                'siret' => '12345678901234',
                'uniteLegale' => [
                    'denominationUniteLegale' => 'Entreprise Test'
                ]
            ]
        ], 200)
    ]);

    // 2) On appelle notre futur service (il n'existe pas encore)
    $client = new \App\Services\SireneClient();
    $data = $client->getBySiret('12345678901234');

    // 3) On vérifie que les données sont bien retournées
    $this->assertEquals('Entreprise Test', $data['etablissement']['uniteLegale']['denominationUniteLegale']);
    }
      /** @test */
    public function it_handles_not_found_siret()
    {
          // 1) On simule une réponse 404 de l'API INSEE
    Http::fake([
        'https://api.insee.fr/*' => Http::response([], 404)
    ]);

    // 2) On appelle notre futur service
    $client = new \App\Services\SireneClient();
    $data = $client->getBySiret('00000000000000');

    // 3) On vérifie que le service renvoie null
    $this->assertNull($data);
    }
}

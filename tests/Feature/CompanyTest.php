<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    /**
     * A basic feature test example.
     */
  public function test_manual_creation_sets_status_pending()
{
    // 1) On simule une requête POST pour créer une entreprise
    $response = $this->post('/companies', [
        'name' => 'Entreprise Test',
        'siret' => '12345678901234',
    ]);

    // 2) On vérifie que la base contient bien status = pending
    $this->assertDatabaseHas('entreprises', [
        'name' => 'Entreprise Test',
        'status' => 'pending',
    ]);
}
}

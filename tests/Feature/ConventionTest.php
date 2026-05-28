<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stage;
use App\Models\Entreprise;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConventionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Désactive les policies pour ce test PDF
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class);
    }

    public function test_convention_fails_without_siret()
    {
        $this->actingAs(User::factory()->create());

        $stage = Stage::factory()->create([
            'entreprise_id' => Entreprise::factory()->create([
                'siret' => null
            ])->id,
        ]);

        $response = $this->get("/stages/{$stage->id}/pdf/convention");

        $response->assertStatus(422);
    }

    public function test_convention_pdf_generates_successfully()
    {
        $this->actingAs(User::factory()->create());

        $stage = Stage::factory()->create([
            'entreprise_id' => Entreprise::factory()->create([
                'siret' => '12345678900011'
            ])->id,
        ]);

        $response = $this->get("/stages/{$stage->id}/pdf/convention");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}

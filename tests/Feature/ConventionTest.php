<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConventionTest extends TestCase
{
    use RefreshDatabase;

    private function stageDe(User $etudiant): Stage
    {
        return Stage::factory()->create([
            'etudiant_id'   => $etudiant->id,
            'classe'        => 'SIO1',
            'entreprise_id' => Entreprise::factory()->create(['siret' => '12345678900011'])->id,
        ]);
    }

    public function test_l_etudiant_telecharge_la_convention_de_son_stage(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = $this->stageDe($etudiant);

        $response = $this->actingAs($etudiant)->get("/stages/{$stage->id}/pdf/convention");

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_un_etudiant_ne_peut_pas_telecharger_la_convention_d_un_autre(): void
    {
        $stage = $this->stageDe(User::factory()->etudiant()->create());

        $this->actingAs(User::factory()->etudiant()->create())
            ->get("/stages/{$stage->id}/pdf/convention")
            ->assertForbidden();
    }

    public function test_un_professeur_peut_telecharger_n_importe_quelle_convention(): void
    {
        $stage = $this->stageDe(User::factory()->etudiant()->create());

        $this->actingAs(User::factory()->professeur()->create())
            ->get("/stages/{$stage->id}/pdf/convention")
            ->assertOk();
    }

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $stage = $this->stageDe(User::factory()->etudiant()->create());

        $this->get("/stages/{$stage->id}/pdf/convention")->assertRedirect('/login');
    }
}

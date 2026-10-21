<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_recherche_siret_est_limitee_a_30_requetes_par_minute(): void
    {
        Http::fake(['*' => Http::response([], 404)]);
        $etudiant = User::factory()->etudiant()->create();

        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($etudiant)
                ->get(route('etudiant.stage.recherche-siret', ['siret' => '12345678901234']))
                ->assertSuccessful();
        }

        $this->actingAs($etudiant)
            ->get(route('etudiant.stage.recherche-siret', ['siret' => '12345678901234']))
            ->assertStatus(429);
    }

    public function test_la_limite_siret_est_par_utilisateur(): void
    {
        Http::fake(['*' => Http::response([], 404)]);
        $a = User::factory()->etudiant()->create();
        $b = User::factory()->etudiant()->create();

        for ($i = 0; $i < 31; $i++) {
            $this->actingAs($a)->get(route('etudiant.stage.recherche-siret', ['siret' => '12345678901234']));
        }

        $this->actingAs($b)
            ->get(route('etudiant.stage.recherche-siret', ['siret' => '12345678901234']))
            ->assertSuccessful();
    }

    public function test_la_connexion_est_limitee_a_5_tentatives_par_minute(): void
    {
        $user = User::factory()->etudiant()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'mauvais'])
                ->assertSessionHasErrors();
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'mauvais'])
            ->assertStatus(429);
    }
}

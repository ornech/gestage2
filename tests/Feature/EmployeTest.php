<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Employe;
use App\Models\Entreprise;

class EmployeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_displays_employes()
    {
        $entreprise = Entreprise::factory()->create();
        $employe1 = Employe::factory()->create(['entreprise_id' => $entreprise->id]);
        $employe2 = Employe::factory()->create(['entreprise_id' => $entreprise->id]);

        $response = $this->get('/employes');

        $response->assertStatus(200);
        $response->assertSee($employe1->nom);
        $response->assertSee($employe2->nom);
    }

    /** @test */
    public function store_creates_an_employe()
    {
        $entreprise = Entreprise::factory()->create();

        $data = [
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'email' => 'marie@example.com',
            'telephone' => '0600000000',
            'entreprise_id' => $entreprise->id,
        ];

        $response = $this->post('/employes', $data);

        $response->assertRedirect('/employes');

        $this->assertDatabaseHas('employes', [
            'nom' => 'Dupont',
            'email' => 'marie@example.com',
        ]);
    }

    /** @test */
    public function show_displays_employe_details()
    {
        $employe = Employe::factory()->create();

        $response = $this->get("/employes/{$employe->id}");

        $response->assertStatus(200);
        $response->assertSee($employe->nom);
        $response->assertSee($employe->email);
    }

    /** @test */
    public function update_modifies_employe()
    {
        $employe = Employe::factory()->create();

        $response = $this->put("/employes/{$employe->id}", [
            'nom' => 'NouveauNom',
            'prenom' => $employe->prenom,
            'email' => $employe->email,
            'telephone' => $employe->telephone,
            'entreprise_id' => $employe->entreprise_id,
        ]);

        $response->assertRedirect('/employes');

        $this->assertDatabaseHas('employes', [
            'id' => $employe->id,
            'nom' => 'NouveauNom',
        ]);
    }

    /** @test */
    public function destroy_deletes_employe()
    {
        $employe = Employe::factory()->create();

        $response = $this->delete("/employes/{$employe->id}");

        $response->assertRedirect('/employes');

        $this->assertDatabaseMissing('employes', [
            'id' => $employe->id,
        ]);
    }
}

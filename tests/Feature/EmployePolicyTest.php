<?php

namespace Tests\Feature;

use App\Models\Employe;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployePolicyTest extends TestCase
{
    use RefreshDatabase;

    private function maitreCreePar(User $etudiant): Employe
    {
        return Employe::factory()->create(['creator_id' => $etudiant->id]);
    }

    public function test_createur_modifie_sa_fiche_tant_qu_elle_ne_sert_qu_a_lui(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $employe = $this->maitreCreePar($etudiant);
        $this->assertTrue($etudiant->can('update', $employe));

        Stage::factory()->create(['etudiant_id' => $etudiant->id, 'maitre_de_stage_id' => $employe->id]);
        $this->assertTrue($etudiant->can('update', $employe));
    }

    public function test_createur_perd_la_main_des_qu_un_autre_etudiant_s_y_rattache(): void
    {
        $createur = User::factory()->etudiant()->create();
        $autre = User::factory()->etudiant()->create();
        $employe = $this->maitreCreePar($createur);

        Stage::factory()->create(['etudiant_id' => $autre->id, 'maitre_de_stage_id' => $employe->id]);

        $this->assertFalse($createur->can('update', $employe));
        $this->assertFalse($autre->can('update', $employe));
    }

    public function test_le_staff_modifie_toujours(): void
    {
        $createur = User::factory()->etudiant()->create();
        $autre = User::factory()->etudiant()->create();
        $employe = $this->maitreCreePar($createur);
        Stage::factory()->create(['etudiant_id' => $autre->id, 'maitre_de_stage_id' => $employe->id]);

        $this->assertTrue(User::factory()->professeur()->create()->can('update', $employe));
        $this->assertTrue(User::factory()->administrateur()->create()->can('update', $employe));
    }

    public function test_email_du_maitre_masque_aux_etudiants_sauf_le_leur(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $autre = User::factory()->etudiant()->create();
        $employe = Employe::factory()->create(['email' => 'secret@entreprise.test']);
        Stage::factory()->create(['etudiant_id' => $autre->id, 'maitre_de_stage_id' => $employe->id, 'entreprise_id' => $employe->entreprise_id]);

        $this->actingAs($etudiant)->get(route('employes.show', $employe))->assertOk()->assertDontSee('secret@entreprise.test');
        $this->actingAs($etudiant)->get(route('entreprises.show', $employe->entreprise_id))->assertOk()->assertDontSee('secret@entreprise.test');
        $this->actingAs($autre)->get(route('employes.show', $employe))->assertSee('secret@entreprise.test');
        $this->actingAs(User::factory()->professeur()->create())->get(route('employes.show', $employe))->assertSee('secret@entreprise.test');
    }

    public function test_fiche_entreprise_adaptee_au_role(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $autre = User::factory()->etudiant()->create();
        $employe = Employe::factory()->create();
        Stage::factory()->create(['etudiant_id' => $autre->id, 'maitre_de_stage_id' => $employe->id, 'entreprise_id' => $employe->entreprise_id]);
        $url = route('entreprises.show', $employe->entreprise_id);

        $this->actingAs(User::factory()->professeur()->create())->get($url)
            ->assertOk()->assertSee('Modifier le standard')->assertSee('Ajouter un maître de stage')->assertSee($autre->nom);

        $this->actingAs($etudiant)->get($url)
            ->assertOk()->assertDontSee('Modifier le standard')->assertDontSee('Ajouter un maître de stage')
            ->assertDontSee($autre->nom)->assertSee('Un autre étudiant')->assertSee('Réservé');
    }
}

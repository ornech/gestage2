<?php

namespace Tests\Feature;

use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageValidationUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-01');
    }

    private function stage(array $attrs = []): Stage
    {
        $etudiant = User::factory()->etudiant()->create(['statut' => 'actif', 'promo' => 2027, 'classe' => 'SIO1']);

        return Stage::factory()->create($attrs + [
            'etudiant_id' => $etudiant->id, 'classe' => 'SIO1', 'annee_scolaire' => '2026-2027',
        ]);
    }

    public function test_la_liste_propose_valider_et_rejeter_pour_un_stage_en_attente(): void
    {
        $stage = $this->stage(['statut_validation' => 'en_attente', 'statut_convention' => 'a_faire_signer']);

        $this->actingAs(User::factory()->professeur()->create())
            ->get(route('admin.stages.index'))
            ->assertOk()
            ->assertSee(route('admin.stages.valider', $stage), false)
            ->assertSee(route('admin.stages.rejeter', $stage), false);
    }

    public function test_un_stage_valide_n_offre_plus_que_le_rejet_et_un_stage_rejete_que_la_validation(): void
    {
        $valide = $this->stage(['statut_validation' => 'valide', 'statut_convention' => 'a_faire_signer']);
        $rejete = $this->stage(['statut_validation' => 'rejete', 'statut_convention' => 'a_faire_signer', 'note_rejet' => 'SIRET manquant']);

        $page = $this->actingAs(User::factory()->professeur()->create())->get(route('admin.stages.index'));

        $page->assertSee(route('admin.stages.rejeter', $valide), false)
            ->assertDontSee(route('admin.stages.valider', $valide), false)
            ->assertSee(route('admin.stages.valider', $rejete), false)
            ->assertDontSee(route('admin.stages.rejeter', $rejete), false)
            ->assertSee('SIRET manquant');
    }

    public function test_un_stage_dont_la_convention_est_validee_ne_peut_plus_etre_rejete_depuis_la_liste(): void
    {
        $stage = $this->stage(['statut_validation' => 'valide', 'statut_convention' => 'validee']);

        $this->actingAs(User::factory()->professeur()->create())
            ->get(route('admin.stages.index'))
            ->assertDontSee(route('admin.stages.rejeter', $stage), false);
    }

    public function test_le_rejet_par_un_professeur_est_visible_par_l_etudiant_et_le_rend_modifiable(): void
    {
        $stage = $this->stage(['statut_validation' => 'en_attente']);
        $prof  = User::factory()->professeur()->create();

        $this->actingAs($prof)->patch(route('admin.stages.rejeter', $stage), ['note_rejet' => 'Dates incohérentes'])
            ->assertSessionHas('success');

        $etudiant = $stage->etudiant;
        $this->assertTrue($etudiant->can('update', $stage->fresh()));
        $this->actingAs($etudiant)->get(route('etudiant.dashboard'))->assertOk()->assertSee('Dates incohérentes');
    }

    public function test_un_etudiant_ne_peut_pas_valider_ni_rejeter(): void
    {
        $stage = $this->stage(['statut_validation' => 'en_attente']);

        $this->actingAs($stage->etudiant)->patch(route('admin.stages.valider', $stage));
        $this->actingAs($stage->etudiant)->patch(route('admin.stages.rejeter', $stage), ['note_rejet' => 'x']);

        $this->assertSame('en_attente', $stage->fresh()->statut_validation);
    }
}

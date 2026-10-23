<?php

namespace Tests\Feature;

use App\Enums\StatutConvention;
use App\Models\ConventionPapier;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConventionCycleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->administrateur()->create();
    }

    public function test_convention_papier_hors_app_donne_un_stage_en_attente(): void
    {
        $papier = new ConventionPapier(['statut' => 'hors_app']);

        $this->assertSame(StatutConvention::EnAttente, StatutConvention::depuisConventionPapier($papier));
        $this->assertSame(StatutConvention::AFaireSigner, StatutConvention::depuisConventionPapier(null));
        $this->assertSame(StatutConvention::Validee, StatutConvention::depuisConventionPapier(new ConventionPapier(['statut' => 'validee'])));
    }

    public function test_convention_papier_avance_puis_recule(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $papier   = ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => Parametre::anneeActive(), 'statut' => 'hors_app']);
        $admin    = $this->admin();

        $this->actingAs($admin)->patch(route('admin.conventions-papier.avancer', $papier));
        $this->assertSame('en_attente', $papier->fresh()->statut);

        $this->actingAs($admin)->patch(route('admin.conventions-papier.avancer', $papier));
        $this->assertSame('validee', $papier->fresh()->statut);

        $this->actingAs($admin)->patch(route('admin.conventions-papier.avancer', $papier));
        $this->assertSame('validee', $papier->fresh()->statut, 'pas d\'état après validee');

        $this->actingAs($admin)->delete(route('admin.conventions-papier.revert', $papier));
        $this->assertSame('en_attente', $papier->fresh()->statut);

        $this->actingAs($admin)->delete(route('admin.conventions-papier.revert', $papier));
        $this->assertSame('hors_app', $papier->fresh()->statut);

        $this->actingAs($admin)->delete(route('admin.conventions-papier.revert', $papier));
        $this->assertNull(ConventionPapier::find($papier->id), 'retour depuis le 1er statut = suppression');
    }

    public function test_deposer_la_convention_valide_implicitement_le_stage(): void
    {
        $stage = Stage::factory()->create(['statut_validation' => 'en_attente', 'statut_convention' => 'a_faire_signer', 'note_rejet' => 'x']);

        $this->actingAs($this->admin())->patch(route('admin.stages.convention', [$stage, 'en_attente']));

        $stage->refresh();
        $this->assertSame('en_attente', $stage->statut_convention);
        $this->assertSame('valide', $stage->statut_validation);
        $this->assertNull($stage->note_rejet);
    }

    public function test_statut_de_convention_inconnu_est_refuse(): void
    {
        $stage = Stage::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.stages.convention', [$stage, 'aucune']))->assertStatus(422);
    }

    public function test_revert_depuis_en_attente_remet_le_stage_en_validation(): void
    {
        $stage = Stage::factory()->create(['statut_validation' => 'valide', 'statut_convention' => 'en_attente']);

        $this->actingAs($this->admin())->delete(route('admin.stages.revert', $stage));

        $stage->refresh();
        $this->assertSame('a_faire_signer', $stage->statut_convention);
        $this->assertSame('en_attente', $stage->statut_validation);
    }

    public function test_valider_et_rejeter_un_stage(): void
    {
        $admin = $this->admin();
        $stage = Stage::factory()->create(['statut_validation' => 'en_attente']);

        $this->actingAs($admin)->patch(route('admin.stages.rejeter', $stage), ['note_rejet' => 'SIRET manquant']);
        $this->assertSame('rejete', $stage->fresh()->statut_validation);
        $this->assertSame('SIRET manquant', $stage->fresh()->note_rejet);

        $this->actingAs($admin)->patch(route('admin.stages.valider', $stage));
        $this->assertSame('valide', $stage->fresh()->statut_validation);
        $this->assertNull($stage->fresh()->note_rejet);
    }

    public function test_le_rejet_exige_un_motif(): void
    {
        $stage = Stage::factory()->create();

        $this->actingAs($this->admin())->patch(route('admin.stages.rejeter', $stage), [])
            ->assertSessionHasErrors('note_rejet');
    }
}

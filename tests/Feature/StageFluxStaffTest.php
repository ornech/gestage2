<?php

namespace Tests\Feature;

use App\Mail\RappelConventionHorsApp;
use App\Mail\RappelSaisieStage;
use App\Models\ConventionPapier;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StageFluxStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-23');
    }

    private function saisie(User $etudiant): array
    {
        $entreprise = Entreprise::factory()->create();
        $maitre     = Employe::factory()->create(['entreprise_id' => $entreprise->id]);

        return [
            'etudiant_id' => $etudiant->id, 'entreprise_id' => $entreprise->id,
            'maitre_de_stage_id' => $maitre->id, 'date_debut' => '2026-11-02', 'duree' => 6,
        ];
    }

    public function test_le_staff_cree_le_stage_de_l_annee_avec_la_classe_de_l_etudiant(): void
    {
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);

        $this->actingAs(User::factory()->professeur()->create())
            ->post(route('admin.stages.creer'), $this->saisie($etudiant))->assertSessionHasNoErrors();

        $stage = $etudiant->stages()->sole();
        $this->assertSame('2026-2027', $stage->annee_scolaire);
        $this->assertSame('SIO2', $stage->classe);
    }

    public function test_le_staff_ne_cree_pas_de_stage_sans_classe_ni_en_double(): void
    {
        $staff    = User::factory()->administrateur()->create();
        $orphelin = User::factory()->etudiant()->create(['promo' => 2026]);
        $this->actingAs($staff)->post(route('admin.stages.creer'), $this->saisie($orphelin))->assertSessionHasErrors();
        $this->assertSame(0, $orphelin->stages()->count());

        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);
        $this->actingAs($staff)->post(route('admin.stages.creer'), $this->saisie($etudiant))->assertSessionHasNoErrors();
        $this->actingAs($staff)->post(route('admin.stages.creer'), $this->saisie($etudiant))->assertSessionHasErrors();
        $this->assertSame(1, $etudiant->stages()->count());
    }

    public function test_convention_hors_app_enregistree_pour_l_annee_demandee(): void
    {
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);
        $staff    = User::factory()->administrateur()->create();

        $this->actingAs($staff)->patch(route('admin.stages.hors-appli', $etudiant));
        $this->actingAs($staff)->patch(route('admin.stages.hors-appli', $etudiant));
        $this->actingAs($staff)->patch(route('admin.stages.hors-appli', $etudiant), ['annee' => '2025-2026']);

        $this->assertSame(
            ['2025-2026', '2026-2027'],
            $etudiant->conventionsPapier()->orderBy('annee_scolaire')->pluck('annee_scolaire')->all()
        );
    }

    public function test_le_rappel_vise_ceux_qui_n_ont_pas_de_stage_cette_annee(): void
    {
        Mail::fake();
        $sans     = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);
        $ancien   = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);
        $papier   = User::factory()->etudiant()->create(['promo' => 2028, 'statut' => 'actif']);
        $complet  = User::factory()->etudiant()->create(['promo' => 2028, 'statut' => 'actif']);
        $diplome  = User::factory()->etudiant()->create(['promo' => 2026, 'statut' => 'actif']);

        Stage::factory()->create(['etudiant_id' => $ancien->id, 'annee_scolaire' => '2025-2026']);
        Stage::factory()->create(['etudiant_id' => $complet->id, 'annee_scolaire' => '2026-2027']);
        ConventionPapier::create(['etudiant_id' => $papier->id, 'annee_scolaire' => '2026-2027', 'statut' => 'validee']);

        $this->artisan('stages:rappel')->assertSuccessful();

        Mail::assertSent(RappelSaisieStage::class, fn($m) => $m->hasTo($sans->email));
        Mail::assertSent(RappelSaisieStage::class, fn($m) => $m->hasTo($ancien->email));
        Mail::assertSent(RappelConventionHorsApp::class, fn($m) => $m->hasTo($papier->email));
        Mail::assertNotSent(RappelSaisieStage::class, fn($m) => $m->hasTo($complet->email));
        Mail::assertNotSent(RappelSaisieStage::class, fn($m) => $m->hasTo($diplome->email));
    }

    public function test_la_fusion_deplace_les_stages_et_garde_une_convention_papier_par_annee(): void
    {
        $garder    = User::factory()->etudiant()->create();
        $supprimer = User::factory()->etudiant()->create();
        Stage::factory()->create(['etudiant_id' => $garder->id, 'annee_scolaire' => '2025-2026']);
        $stage = Stage::factory()->create(['etudiant_id' => $supprimer->id, 'annee_scolaire' => '2026-2027']);
        ConventionPapier::create(['etudiant_id' => $garder->id, 'annee_scolaire' => '2025-2026', 'statut' => 'validee']);
        ConventionPapier::create(['etudiant_id' => $supprimer->id, 'annee_scolaire' => '2025-2026', 'statut' => 'hors_app']);
        ConventionPapier::create(['etudiant_id' => $supprimer->id, 'annee_scolaire' => '2026-2027', 'statut' => 'hors_app']);

        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.comptes.fusionner'), ['garder_id' => $garder->id, 'supprimer_id' => $supprimer->id])
            ->assertSessionHasNoErrors();

        $this->assertSame($garder->id, $stage->fresh()->etudiant_id);
        $this->assertSame(2, $garder->stages()->count());
        $this->assertSame('validee', $garder->conventionsPapier()->where('annee_scolaire', '2025-2026')->value('statut'), 'celle du compte conservé prime');
        $this->assertSame(2, $garder->conventionsPapier()->count());
        $this->assertNull(User::find($supprimer->id));
    }

    public function test_la_convention_papier_validee_previent_le_maitre_de_stage_a_la_creation_du_stage(): void
    {
        Mail::fake();
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);
        ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027', 'statut' => 'validee']);
        $donnees  = $this->saisie($etudiant);
        $maitre   = Employe::findOrFail($donnees['maitre_de_stage_id']);
        $maitre->update(['email' => 'maitre@example.org']);

        app(\App\Services\CreationStage::class)->creer(
            $etudiant, $donnees['entreprise_id'], $maitre->id, $donnees['date_debut'], $donnees['duree']
        );

        Mail::assertSent(\App\Mail\BienvenueMaitreDeStage::class, fn($m) => $m->hasTo('maitre@example.org'));
        $this->assertNotNull($etudiant->stages()->sole()->mail_bienvenue_envoye_at);
    }

    public function test_le_mail_de_bienvenue_n_est_envoye_qu_une_fois_a_la_validation(): void
    {
        Mail::fake();
        $maitre = Employe::factory()->create(['email' => 'maitre@example.org']);
        $stage  = Stage::factory()->create(['maitre_de_stage_id' => $maitre->id, 'etudiant_id' => User::factory()->etudiant()->create()->id, 'statut_convention' => 'en_attente']);
        $staff  = User::factory()->administrateur()->create();

        $this->actingAs($staff)->patch(route('admin.stages.convention', [$stage, 'validee']));
        $this->actingAs($staff)->patch(route('admin.stages.convention', [$stage, 'validee']));

        Mail::assertSent(\App\Mail\BienvenueMaitreDeStage::class, 1);
    }

    public function test_changer_l_email_du_maitre_ne_relance_que_les_stages_de_l_annee(): void
    {
        Mail::fake();
        $maitre = Employe::factory()->create(['email' => 'ancien@example.org']);
        $etudiant = User::factory()->etudiant()->create();
        Stage::factory()->create(['maitre_de_stage_id' => $maitre->id, 'etudiant_id' => $etudiant->id, 'annee_scolaire' => '2025-2026', 'statut_convention' => 'validee']);
        Stage::factory()->create(['maitre_de_stage_id' => $maitre->id, 'etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027', 'statut_convention' => 'validee']);

        $this->actingAs(User::factory()->administrateur()->create())
            ->put(route('employes.update', $maitre), ['nom' => $maitre->nom, 'prenom' => $maitre->prenom, 'email' => 'nouveau@example.org']);

        Mail::assertSent(\App\Mail\BienvenueMaitreDeStage::class, 1);
    }

    public function test_le_staff_ne_peut_assigner_qu_un_maitre_de_l_entreprise_du_stage(): void
    {
        $stage    = Stage::factory()->create();
        $etranger = Employe::factory()->create();   // autre entreprise
        $staff    = User::factory()->administrateur()->create();

        $this->actingAs($staff)->put(route('admin.stages.assign', $stage), ['maitre_de_stage_id' => $etranger->id])
            ->assertSessionHasErrors('maitre_de_stage_id');

        $interne = Employe::factory()->create(['entreprise_id' => $stage->entreprise_id]);
        $this->actingAs($staff)->put(route('admin.stages.assign', $stage), ['maitre_de_stage_id' => $interne->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($interne->id, $stage->fresh()->maitre_de_stage_id);
    }

    public function test_une_annee_passee_garde_les_redoublants_et_les_diplomes(): void
    {
        $this->travelTo('2026-10-23');
        // Redoublant : SIO1 en 2025-2026 (promo 2027), promo passée à 2028 depuis
        $redoublant = User::factory()->etudiant()->create(['promo' => 2028, 'statut' => 'actif']);
        Stage::factory()->create(['etudiant_id' => $redoublant->id, 'classe' => 'SIO1', 'annee_scolaire' => '2025-2026']);
        // Diplômé en juin 2026 : SIO2 en 2025-2026
        $diplome = User::factory()->etudiant()->create(['promo' => 2026, 'statut' => 'diplome']);
        Stage::factory()->create(['etudiant_id' => $diplome->id, 'classe' => 'SIO2', 'annee_scolaire' => '2025-2026']);

        $admin = User::factory()->administrateur()->create();
        $sio1  = $this->actingAs($admin)->get(route('admin.stages.index', ['annee' => '2025-2026', 'classe' => 'sio1']))->viewData('etudiants');
        $sio2  = $this->actingAs($admin)->get(route('admin.stages.index', ['annee' => '2025-2026', 'classe' => 'sio2']))->viewData('etudiants');

        $this->assertTrue($sio1->contains($redoublant));
        $this->assertFalse($sio1->contains($diplome));
        $this->assertTrue($sio2->contains($diplome));
        $this->assertFalse($sio2->contains($redoublant));
    }

    public function test_deux_creations_simultanees_donnent_une_erreur_claire(): void
    {
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);
        $donnees  = $this->saisie($etudiant);
        Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(\App\Services\CreationStage::class)->creer(
            $etudiant, $donnees['entreprise_id'], $donnees['maitre_de_stage_id'], $donnees['date_debut'], $donnees['duree']
        );
    }

    public function test_la_suppression_d_un_etudiant_garde_ses_stages_sans_nom(): void
    {
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);
        $stage    = Stage::factory()->create(['etudiant_id' => $etudiant->id, 'classe' => 'SIO2', 'annee_scolaire' => '2026-2027']);
        $admin    = User::factory()->administrateur()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $etudiant));

        $stage = $stage->fresh();
        $this->assertNotNull($stage, 'le stage est conservé');
        $this->assertNull($stage->etudiant_id);
        $this->assertSame('2026-2027', $stage->annee_scolaire);

        $this->get(route('stages.show', $stage))->assertOk();
        $this->get(route('pdf.convention', $stage))->assertNotFound();
        $this->get(route('entreprises.show', $stage->entreprise_id))->assertOk();
        $this->patch(route('admin.stages.convention', [$stage, 'validee']))->assertRedirect();
    }
}

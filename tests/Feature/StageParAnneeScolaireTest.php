<?php

namespace Tests\Feature;

use App\Models\ConventionPapier;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use App\Services\CartesClasses;
use App\Services\CreationStage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageParAnneeScolaireTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_seul_stage_par_etudiant_et_par_annee(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027']);

        $this->expectException(UniqueConstraintViolationException::class);
        Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027']);
    }

    public function test_un_etudiant_peut_avoir_un_stage_par_annee_differente(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2025-2026']);
        Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027']);

        $this->assertSame(2, $etudiant->stages()->count());
    }

    public function test_l_annee_scolaire_d_un_stage_est_obligatoire(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Stage::factory()->create(['annee_scolaire' => null]);
    }

    public function test_la_liste_admin_n_affiche_pas_les_stages_d_une_autre_annee(): void
    {
        $this->travelTo('2026-10-01');

        // SIO2 en 2026-2027 = promo 2027 ; son stage de SIO1 date de 2025-2026
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);
        Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2025-2026']);

        $admin = User::factory()->administrateur()->create();

        $reponse = $this->actingAs($admin)->get(route('admin.stages.index', ['classe' => 'sio2']));

        $reponse->assertOk();
        $this->assertCount(0, $reponse->viewData('etudiants')->first()->stages);
        $this->assertSame(1, $reponse->viewData('compteurs')['sans_stage']);
    }

    public function test_un_redoublant_a_un_stage_par_annee_dans_la_meme_classe(): void
    {
        $redoublant = User::factory()->etudiant()->create(['promo' => 2028]);

        // SIO1 en 2025-2026, puis SIO1 de nouveau en 2026-2027 (promo décalée d'un an)
        Stage::factory()->create(['etudiant_id' => $redoublant->id, 'classe' => 'SIO1', 'annee_scolaire' => '2025-2026']);
        Stage::factory()->create(['etudiant_id' => $redoublant->id, 'classe' => 'SIO1', 'annee_scolaire' => '2026-2027']);

        $this->assertSame(2, $redoublant->stages()->where('classe', 'SIO1')->count());
    }

    public function test_une_convention_papier_par_etudiant_et_par_annee(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2025-2026']);
        ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027']);

        $this->assertSame(2, $etudiant->conventionsPapier()->count());

        $this->expectException(UniqueConstraintViolationException::class);
        ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027']);
    }

    public function test_la_convention_papier_active_est_celle_de_l_annee_active(): void
    {
        $this->travelTo('2026-10-01');
        $etudiant = User::factory()->etudiant()->create();
        ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2025-2026', 'statut' => 'validee']);

        $this->assertNull($etudiant->fresh()->conventionPapier);

        ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027', 'statut' => 'hors_app']);

        $this->assertSame('hors_app', $etudiant->fresh()->conventionPapier->statut);
    }

    public function test_la_creation_du_stage_reprend_la_convention_papier_de_l_annee_seulement(): void
    {
        $this->travelTo('2026-10-01');
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);
        $ancienne = ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2025-2026', 'statut' => 'validee']);
        $courante = ConventionPapier::create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027', 'statut' => 'en_attente']);
        $entreprise = Entreprise::factory()->create();
        $maitre     = Employe::factory()->create(['entreprise_id' => $entreprise->id]);

        $stage = app(CreationStage::class)->creer($etudiant, $entreprise->id, $maitre->id, '2026-11-02', 6);

        $this->assertSame('2026-2027', $stage->annee_scolaire);
        $this->assertSame('en_attente', $stage->statut_convention);
        $this->assertNull(ConventionPapier::find($courante->id), 'la convention de l\'année est reprise par le stage');
        $this->assertNotNull(ConventionPapier::find($ancienne->id), 'celle d\'une autre année reste');
    }

    public function test_le_journal_exige_un_stage_de_l_annee_active(): void
    {
        $this->travelTo('2026-10-01');
        $etudiant = User::factory()->etudiant()->create();
        $ancien   = Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2025-2026']);

        $this->assertFalse($etudiant->aUnStageCetteAnnee());
        $this->actingAs($etudiant)->get(route('stages.journal.index', $ancien))
            ->assertRedirect(route('etudiant.stages.index'));

        $courant = Stage::factory()->create(['etudiant_id' => $etudiant->id, 'annee_scolaire' => '2026-2027']);

        $this->assertTrue($etudiant->fresh()->aUnStageCetteAnnee());
        $this->actingAs($etudiant)->get(route('stages.journal.index', $courant))->assertOk();
    }

    public function test_les_cartes_des_tableaux_de_bord_ne_comptent_que_l_annee_active(): void
    {
        $this->travelTo('2026-10-01');
        $ancien = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);
        Stage::factory()->create(['etudiant_id' => $ancien->id, 'annee_scolaire' => '2025-2026', 'statut_convention' => 'validee']);
        $actuel = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);
        Stage::factory()->create(['etudiant_id' => $actuel->id, 'annee_scolaire' => '2026-2027', 'statut_convention' => 'a_faire_signer']);

        $cartes = app(CartesClasses::class)->pourAnneeActive()['SIO2'];

        $this->assertSame(0, $cartes['remis']);
        $this->assertSame(1, $cartes['a_faire_signer']);
        $this->assertSame(1, $cartes['sans_stage']);
    }

    public function test_la_fusion_est_refusee_si_les_deux_comptes_ont_un_stage_la_meme_annee(): void
    {
        $garder    = User::factory()->etudiant()->create();
        $supprimer = User::factory()->etudiant()->create();
        Stage::factory()->create(['etudiant_id' => $garder->id, 'annee_scolaire' => '2026-2027']);
        Stage::factory()->create(['etudiant_id' => $supprimer->id, 'annee_scolaire' => '2026-2027']);

        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.comptes.fusionner'), ['garder_id' => $garder->id, 'supprimer_id' => $supprimer->id])
            ->assertSessionHasErrors();

        $this->assertNotNull($supprimer->fresh());
    }
}

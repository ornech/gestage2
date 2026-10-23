<?php

namespace Tests\Feature;

use App\Models\ConfigurationStage;
use App\Models\ConventionPapier;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Les écrans qui lisent stages et conventions s'affichent, avec un jeu de données réaliste sur deux années. */
class EcransStagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $prof;
    private User $sio2Avec;     // SIO2 avec son stage 2026-2027 et celui de SIO1 de l'an dernier
    private User $sio2Papier;   // SIO2 avec convention papier cette année, sans stage
    private User $sio1Sans;
    private User $orphelin;
    private Stage $stageCourant;
    private Stage $stageAncien;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-10-23');

        $this->admin = User::factory()->administrateur()->create();
        $this->prof  = User::factory()->professeur()->create();

        foreach (['SIO1', 'SIO2'] as $classe) {
            ConfigurationStage::create([
                'annee_scolaire' => '2026-2027', 'classe' => $classe, 'prof_principal_id' => $this->prof->id,
                'stage_date_debut' => '2026-11-02', 'stage_date_fin' => '2026-12-11',
            ]);
        }

        $entreprise = Entreprise::factory()->create();
        $maitre     = Employe::factory()->create(['entreprise_id' => $entreprise->id]);
        $commun     = ['entreprise_id' => $entreprise->id, 'maitre_de_stage_id' => $maitre->id];

        $this->sio2Avec = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);
        $this->stageAncien  = Stage::factory()->create($commun + [
            'etudiant_id' => $this->sio2Avec->id, 'classe' => 'SIO1', 'annee_scolaire' => '2025-2026', 'statut_convention' => 'validee',
        ]);
        $this->stageCourant = Stage::factory()->create($commun + [
            'etudiant_id' => $this->sio2Avec->id, 'classe' => 'SIO2', 'annee_scolaire' => '2026-2027', 'statut_convention' => 'a_faire_signer',
        ]);

        $this->sio2Papier = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);
        ConventionPapier::create(['etudiant_id' => $this->sio2Papier->id, 'annee_scolaire' => '2025-2026', 'statut' => 'validee']);
        ConventionPapier::create(['etudiant_id' => $this->sio2Papier->id, 'annee_scolaire' => '2026-2027', 'statut' => 'hors_app']);

        $this->sio1Sans = User::factory()->etudiant()->create(['promo' => 2028, 'statut' => 'actif']);
        $this->orphelin = User::factory()->etudiant()->create(['promo' => 2026, 'statut' => 'actif']);
    }

    public function test_ecran_admin_des_stages_par_annee_et_classe(): void
    {
        $this->actingAs($this->admin);

        $page = $this->get(route('admin.stages.index', ['classe' => 'sio2']))->assertOk();
        $page->assertSee($this->sio2Avec->nom)->assertSee($this->sio2Papier->nom)
             ->assertDontSee($this->sio1Sans->nom)
             ->assertSee(route('admin.stages.destroy', $this->stageCourant), false)
             ->assertDontSee(route('admin.stages.destroy', $this->stageAncien), false);

        // La convention papier affichée est celle de l'année (hors app), pas celle de 2025-2026 (validée)
        $page->assertSee('Déposée direction');

        $this->get(route('admin.stages.index', ['classe' => 'sio1']))->assertOk()->assertSee($this->sio1Sans->nom);
        // En 2025-2026 la promo 2027 était en SIO1 : sa convention de cette année-là (validée) n'est pas celle de 2026-2027
        $ancienne = $this->get(route('admin.stages.index', ['annee' => '2025-2026', 'classe' => 'sio1']))->assertOk();
        $this->assertSame('validee', $ancienne->viewData('etudiants')->firstWhere('id', $this->sio2Papier->id)->conventionsPapier->first()->statut);
        $this->assertSame('validee', $ancienne->viewData('etudiants')->firstWhere('id', $this->sio2Avec->id)->stages->first()->statut_convention);

        foreach (['sans_stage', 'hors_app', 'a_faire_signer', 'en_attente', 'validee'] as $filtre) {
            $this->get(route('admin.stages.index', ['filtre' => $filtre]))->assertOk();
        }
    }

    public function test_les_compteurs_de_l_ecran_admin(): void
    {
        $this->actingAs($this->admin);
        $c = $this->get(route('admin.stages.index'))->viewData('compteurs');

        $this->assertSame(3, $c['tous']);            // 2 SIO2 + 1 SIO1 actifs (l'orphelin n'a pas de classe)
        $this->assertSame(1, $c['sans_stage']);      // sio1Sans
        $this->assertSame(1, $c['hors_app']);        // sio2Papier
        $this->assertSame(1, $c['a_faire_signer']);  // stage courant (le papier « hors app » a son propre compteur)
        $this->assertSame(0, $c['validee']);         // le stage de l'an dernier ne compte pas
    }

    public function test_tableaux_de_bord_admin_et_professeur(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee($this->orphelin->nom)->assertSee('sans classe');
        $this->actingAs($this->prof)->get(route('professeur.dashboard'))->assertOk()->assertSee($this->orphelin->nom);
    }

    public function test_fiche_etudiant_admin_historique_par_annee(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.edit', $this->sio2Avec))
            ->assertOk()->assertSee('2025-2026')->assertSee('2026-2027');
        $this->actingAs($this->admin)->get(route('admin.users.edit', $this->sio2Papier))
            ->assertOk()->assertSee('Convention hors app');
        $this->actingAs($this->admin)->get(route('admin.users.edit', $this->orphelin))->assertOk();
    }

    public function test_autres_ecrans_staff(): void
    {
        $this->actingAs($this->admin);

        foreach ([
            route('admin.stages.creer.form'),
            route('admin.parametres.index'),
            route('admin.parametres.index', ['annee' => '2025-2026']),
            route('admin.users.index'),
            route('stages.show', $this->stageCourant),
            route('stages.edit', $this->stageCourant),
            route('stages.journal.index', $this->stageCourant),
            route('entreprises.show', $this->stageCourant->entreprise_id),
            route('admin.stages.export-maitres'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_ecrans_etudiant(): void
    {
        $this->actingAs($this->sio2Avec);
        $this->get(route('etudiant.dashboard'))->assertOk()->assertSee('2026');
        $this->get(route('etudiant.stages.index'))->assertOk();
        $this->get(route('etudiant.conventions.index'))->assertOk();
        $this->get(route('stages.journal.index', $this->stageCourant))->assertOk();

        // Étudiant avec convention papier et sans stage : bannière d'alerte + formulaire de saisie ouvert
        $this->actingAs($this->sio2Papier);
        $this->get(route('etudiant.dashboard'))->assertOk()->assertSee('hors app', false);
        $this->get(route('etudiant.stage.nouveau'))->assertOk();

        // Orphelin : saisie fermée, message affiché sur le tableau de bord
        $this->actingAs($this->orphelin);
        $this->get(route('etudiant.stage.nouveau'))->assertRedirect(route('etudiant.dashboard'));
        $this->followingRedirects()->get(route('etudiant.stage.nouveau'))->assertOk()->assertSee('aucune classe');
    }

    public function test_pdf_de_la_convention_utilise_l_annee_du_stage(): void
    {
        $this->actingAs($this->admin)->get(route('pdf.convention', $this->stageAncien))->assertOk();
    }
}

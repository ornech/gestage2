<?php

namespace Tests\Feature;

use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiddlewareChainTest extends TestCase
{
    use RefreshDatabase;

    private function etudiant(array $attrs = []): User
    {
        return User::factory()->etudiant()->create($attrs + ['statut' => 'actif']);
    }

    // ForcePasswordChange ---------------------------------------------------

    public function test_force_password_change_redirige_vers_le_changement_de_mot_de_passe(): void
    {
        $user = $this->etudiant(['force_password_change' => true]);

        $this->actingAs($user)->get('/etudiant')->assertRedirect(route('password.first-change'));
    }

    public function test_force_password_change_laisse_acceder_a_la_page_de_changement(): void
    {
        $user = $this->etudiant(['force_password_change' => true]);

        $this->actingAs($user)->get(route('password.first-change'))->assertOk();
    }

    // EnsureCguAccepted -----------------------------------------------------

    public function test_cgu_non_acceptees_redirige_vers_les_cgu(): void
    {
        $user = $this->etudiant(['cgu_accepted_at' => null]);

        $this->actingAs($user)->get('/etudiant')->assertRedirect(route('cgu.show'));
    }

    public function test_cgu_acceptees_laisse_passer(): void
    {
        $this->actingAs($this->etudiant())->get('/etudiant')->assertOk();
    }

    // EnsureStudentActive ---------------------------------------------------

    public function test_etudiant_demissionnaire_est_deconnecte(): void
    {
        $user = $this->etudiant(['statut' => 'demissionnaire']);

        $this->actingAs($user)->get('/etudiant')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_etudiant_diplome_valide_par_un_professeur_est_deconnecte(): void
    {
        $user = $this->etudiant(['statut' => 'diplome']);

        $this->actingAs($user)->get('/etudiant')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_la_fin_de_promo_ne_bloque_pas_sans_validation_du_diplome(): void
    {
        // Promo 2026 en 2026-2027 : formation terminée, mais le diplôme n'est pas encore validé
        $this->travelTo('2026-10-01');
        $user = $this->etudiant(['promo' => 2026]);

        $this->actingAs($user)->get('/etudiant')->assertOk();
    }

    public function test_etudiant_en_cours_de_formation_n_est_pas_bloque(): void
    {
        $this->travelTo('2026-10-01');
        $user = $this->etudiant(['promo' => 2027]);

        $this->actingAs($user)->get('/etudiant')->assertOk();
    }

    public function test_un_professeur_n_est_jamais_bloque_par_le_statut_etudiant(): void
    {
        Role::findOrCreate('Etudiant'); // le dashboard liste les étudiants
        $prof = User::factory()->professeur()->create(['statut' => 'demissionnaire']);

        $this->actingAs($prof)->get('/dashboard')->assertOk();
    }

    // EnsureStudentHasStage -------------------------------------------------

    public function test_journal_inaccessible_sans_stage(): void
    {
        $etudiant = $this->etudiant();
        $stage    = Stage::factory()->create(); // stage d'un autre : l'étudiant n'en a aucun

        $this->actingAs($etudiant)->get(route('stages.journal.index', $stage))
            ->assertRedirect(route('etudiant.stages.index'))
            ->assertSessionHasErrors('stage');
    }

    public function test_journal_accessible_avec_un_stage(): void
    {
        $etudiant = $this->etudiant();
        $stage    = Stage::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($etudiant)->get(route('stages.journal.index', $stage))->assertOk();
    }

    public function test_journal_non_bloque_pour_un_professeur_sans_stage(): void
    {
        $prof  = User::factory()->professeur()->create();
        $stage = Stage::factory()->create();

        $this->actingAs($prof)->get(route('stages.journal.index', $stage))->assertOk();
    }

    // Visiteur --------------------------------------------------------------

    public function test_visiteur_non_connecte_redirige_vers_login(): void
    {
        $this->get('/etudiant')->assertRedirect('/login');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Parametre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiplomeTest extends TestCase
{
    use RefreshDatabase;

    private function etudiant(array $attrs = []): User
    {
        return User::factory()->etudiant()->create($attrs + ['statut' => 'actif', 'promo' => 2026]);
    }

    public function test_un_professeur_valide_le_diplome_et_la_trace_est_conservee(): void
    {
        $prof     = User::factory()->professeur()->create();
        $etudiant = $this->etudiant();

        $this->actingAs($prof)
            ->patch(route('admin.users.statut', $etudiant), ['statut' => 'diplome'])
            ->assertSessionHasNoErrors();

        $etudiant->refresh();
        $this->assertTrue($etudiant->estDiplome());
        $this->assertNotNull($etudiant->diplome_at);
        $this->assertSame($prof->id, $etudiant->diplome_par);
    }

    public function test_annuler_le_diplome_efface_la_trace(): void
    {
        $prof     = User::factory()->professeur()->create();
        $etudiant = $this->etudiant(['statut' => 'diplome', 'diplome_at' => now(), 'diplome_par' => $prof->id]);

        $this->actingAs($prof)->patch(route('admin.users.statut', $etudiant), ['statut' => 'actif']);

        $etudiant->refresh();
        $this->assertSame('actif', $etudiant->statut);
        $this->assertNull($etudiant->diplome_at);
        $this->assertNull($etudiant->diplome_par);
    }

    public function test_un_etudiant_ne_peut_pas_se_declarer_diplome(): void
    {
        $etudiant = $this->etudiant();

        $this->actingAs($etudiant)->patch(route('admin.users.statut', $etudiant), ['statut' => 'diplome']);

        $this->assertSame('actif', $etudiant->fresh()->statut);
    }

    public function test_un_statut_inconnu_est_refuse(): void
    {
        $prof     = User::factory()->professeur()->create();
        $etudiant = $this->etudiant();

        $this->actingAs($prof)->patch(route('admin.users.statut', $etudiant), ['statut' => 'nimporte'])
            ->assertSessionHasErrors('statut');
    }

    public function test_la_liste_des_anciens_propose_de_valider_les_diplomes_en_attente(): void
    {
        $this->travelTo('2026-10-01');
        $this->etudiant(['nom' => 'ATTENTE', 'promo' => 2026]);
        $this->etudiant(['nom' => 'VALIDE', 'promo' => 2026, 'statut' => 'diplome']);

        $this->actingAs(User::factory()->professeur()->create())
            ->get(route('admin.users.index', ['filtre' => 'anciens']))
            ->assertOk()
            ->assertSee('Diplôme à valider')
            ->assertSee('Valider le diplôme')
            ->assertSee('Diplômé 2026');
    }

    public function test_un_diplome_valide_apparait_dans_l_historique_de_son_annee(): void
    {
        $this->travelTo('2026-10-01');
        $this->etudiant(['nom' => 'DIPLOMEE', 'promo' => 2026, 'statut' => 'diplome']);

        // Année 2025-2026 : promo 2026 = SIO2
        $this->actingAs(User::factory()->professeur()->create())
            ->get(route('admin.users.index', ['annee' => '2025-2026']))
            ->assertOk()
            ->assertSee('DIPLOMEE');
    }

    public function test_un_role_inexistant_donne_une_liste_vide_au_lieu_d_une_erreur(): void
    {
        // Base fraîche sans RoleSeeder : aucun rôle n'existe
        Role::query()->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertCount(0, User::role('Etudiant')->get());
        $this->assertCount(0, User::role('Professeur')->get());

        $user = User::factory()->create();
        $this->assertTrue(User::role('Etudiant', null, true)->whereKey($user->id)->exists(), 'sans le rôle : tout le monde');
    }
}

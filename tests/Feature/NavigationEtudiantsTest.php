<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationEtudiantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fiche_etudiant_propose_precedent_et_suivant_de_la_meme_promo(): void
    {
        $admin = User::factory()->administrateur()->create();
        $a = User::factory()->etudiant()->create(['nom' => 'AAA', 'prenom' => 'Zoe', 'promo' => 2027, 'statut' => 'actif']);
        $b = User::factory()->etudiant()->create(['nom' => 'BBB', 'prenom' => 'Zoe', 'promo' => 2027, 'statut' => 'actif']);
        $c = User::factory()->etudiant()->create(['nom' => 'CCC', 'prenom' => 'Zoe', 'promo' => 2027, 'statut' => 'actif']);
        User::factory()->etudiant()->create(['nom' => 'BBC', 'prenom' => 'Autre', 'promo' => 2028, 'statut' => 'actif']);

        $this->actingAs($admin)->get(route('admin.users.edit', $b))
            ->assertOk()
            ->assertViewHas('precedent', fn ($u) => $u->is($a))
            ->assertViewHas('suivant', fn ($u) => $u->is($c));

        $this->actingAs($admin)->get(route('admin.users.edit', $a))
            ->assertViewHas('precedent', null)
            ->assertViewHas('suivant', fn ($u) => $u->is($b));

        $this->actingAs($admin)->get(route('admin.users.edit', $c))
            ->assertViewHas('suivant', null);
    }

    public function test_pagination_utilise_le_gabarit_bulma_sans_svg(): void
    {
        $admin = User::factory()->administrateur()->create();
        User::factory()->etudiant()->count(35)->create(['promo' => 2027, 'statut' => 'actif']);

        $this->actingAs($admin)->get(route('admin.users.index', ['classe' => 'SIO2']))
            ->assertOk()
            ->assertSee('pagination-link', false)
            ->assertDontSee('<svg', false);
    }

    public function test_fiche_etudiant_affiche_les_acces_stage_et_journal_et_la_specialite_se_change_seule(): void
    {
        $admin = User::factory()->administrateur()->create();
        $etu = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif', 'spe' => null]);

        $this->actingAs($admin)->get(route('admin.users.edit', $etu))
            ->assertOk()
            ->assertSee('Aucun stage', false)
            ->assertSee('data-toggle-edition', false);

        $this->actingAs($admin)->patch(route('admin.users.spe', $etu), ['spe' => 'SISR'])->assertRedirect();
        $this->assertSame('SISR', $etu->fresh()->spe);

        $this->actingAs($admin)->patch(route('admin.users.spe', $etu), ['spe' => 'XXX'])->assertSessionHasErrors('spe');
    }
}

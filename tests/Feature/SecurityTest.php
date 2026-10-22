<?php

namespace Tests\Feature;

use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_etudiant_ne_peut_pas_ouvrir_le_formulaire_de_modification_du_stage_d_un_autre(): void
    {
        $stage = Stage::factory()->create(['etudiant_id' => User::factory()->etudiant()->create()->id]);

        $this->actingAs(User::factory()->etudiant()->create())
            ->get(route('stages.edit', $stage))
            ->assertForbidden();
    }

    public function test_un_etudiant_ne_peut_pas_ouvrir_le_formulaire_d_un_stage_deja_valide(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = Stage::factory()->create(['etudiant_id' => $etudiant->id, 'statut_validation' => 'valide']);

        $this->actingAs($etudiant)->get(route('stages.edit', $stage))->assertForbidden();
    }

    public function test_un_etudiant_peut_modifier_son_stage_en_attente_et_le_staff_tous(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = Stage::factory()->create(['etudiant_id' => $etudiant->id, 'statut_validation' => 'en_attente']);

        $this->actingAs($etudiant)->get(route('stages.edit', $stage))->assertOk();
        $this->actingAs(User::factory()->professeur()->create())->get(route('stages.edit', $stage))->assertOk();
    }

    public function test_un_professeur_ne_peut_pas_modifier_un_compte_administrateur(): void
    {
        $admin = User::factory()->administrateur()->create();

        $this->actingAs(User::factory()->professeur()->create())
            ->put(route('admin.users.update', $admin), ['nom' => 'X', 'prenom' => 'Y', 'email' => 'pirate@example.fr'])
            ->assertNotFound();

        $this->assertNotSame('pirate@example.fr', $admin->fresh()->email);
    }

    public function test_un_professeur_ne_peut_pas_creer_d_administrateur(): void
    {
        $this->actingAs(User::factory()->professeur()->create())
            ->post(route('admin.users.store'), ['nom' => 'A', 'prenom' => 'B', 'email' => 'a@example.fr', 'role' => 'Administrateur'])
            ->assertSessionHasErrors('role');
    }

    public function test_l_inscription_publique_est_desactivee(): void
    {
        $this->post('/register', ['nom' => 'A', 'email' => 'a@example.fr', 'password' => 'motdepasse1', 'password_confirmation' => 'motdepasse1'])
            ->assertNotFound();

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * La CSP (script-src avec nonce) interdit les gestionnaires inline : un `onclick="return confirm(…)"`
     * serait ignoré par le navigateur et l'action partirait SANS confirmation.
     * Utiliser data-confirm / data-prompt / data-autosubmit (voir layouts/app.blade.php).
     */
    public function test_aucune_vue_n_utilise_de_gestionnaire_inline_interdit_par_la_csp(): void
    {
        $fautes = [];

        foreach ((new Finder)->files()->in(resource_path('views'))->name('*.php') as $fichier) {
            if (preg_match_all('/\son(?:click|submit|change|input|keyup|keydown|load|focus|blur|mouseover)\s*=|href\s*=\s*["\']javascript:/i', $fichier->getContents(), $m)) {
                $fautes[] = $fichier->getRelativePathname().' ('.count($m[0]).')';
            }
        }

        $this->assertSame([], $fautes);
    }

    public function test_la_csp_n_autorise_pas_les_scripts_inline_sans_nonce(): void
    {
        $csp = $this->get('/login')->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[a-f0-9]+';/", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
    }

    public function test_le_layout_fournit_les_attributs_data_utilises_par_les_vues(): void
    {
        $page = $this->actingAs(User::factory()->administrateur()->create())->get(route('admin.dashboard'));

        foreach (['data-confirm', 'data-prompt-field', 'data-autosubmit', 'data-reload'] as $attr) {
            $page->assertSee($attr, false);
        }
    }
}

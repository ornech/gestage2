<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\StatistiquesPromos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatistiquesPromosTest extends TestCase
{
    use RefreshDatabase;

    private function etudiants(): void
    {
        User::factory()->etudiant()->count(3)->create(['promo' => 2025, 'statut' => 'diplome', 'spe' => 'SLAM']);
        User::factory()->etudiant()->create(['promo' => 2025, 'statut' => 'demissionnaire', 'spe' => 'SISR']);
        User::factory()->etudiant()->count(2)->create(['promo' => 2026, 'statut' => 'actif', 'spe' => null]);
    }

    public function test_calcul_par_promo(): void
    {
        $this->etudiants();

        $lignes = app(StatistiquesPromos::class)->calculer([2025, 2026]);

        $this->assertSame([2026, 2025], $lignes->pluck('promo')->all());
        $p2025 = $lignes->firstWhere('promo', 2025);
        $this->assertSame(4, $p2025['total']);
        $this->assertSame(3, $p2025['statuts']['diplome']);
        $this->assertSame(75.0, $p2025['taux_diplomes']);
        $this->assertSame(25.0, $p2025['taux_demission']);
        $this->assertSame(['SLAM' => 3, 'SISR' => 1, 'aucune' => 0], $p2025['specialites']);

        $filtre = app(StatistiquesPromos::class)->calculer([2025, 2026], 'aucune');
        $this->assertSame(2, $filtre->sum('total'));
    }

    public function test_page_accessible_au_staff_et_pas_a_un_etudiant(): void
    {
        $this->etudiants();
        $prof = User::factory()->professeur()->create();

        $this->actingAs($prof)->get(route('admin.statistiques', ['promos' => [2025]]))
            ->assertOk()
            ->assertSee('Statistiques des promos')
            ->assertSee('75 %')
            ->assertSee('stats-data', false)
            ->assertSee('Spécialité dominante : SLAM');

        $etu = User::factory()->etudiant()->create(['promo' => 2026]);
        $this->actingAs($etu)->get(route('admin.statistiques'))->assertRedirect();

        // Le lien du menu n'est affiché qu'au staff
        $this->actingAs($etu)->get(route('etudiant.dashboard'))->assertDontSee(route('admin.statistiques'), false);
        $this->actingAs($prof)->get(route('professeur.dashboard'))->assertSee(route('admin.statistiques'), false);
    }

    public function test_parametres_invalides_refuses(): void
    {
        $admin = User::factory()->administrateur()->create();

        $this->actingAs($admin)->get(route('admin.statistiques', ['spe' => 'XXX']))->assertSessionHasErrors('spe');
    }

    public function test_export_csv_et_clic_vers_la_liste_filtree_par_statut(): void
    {
        $this->etudiants();
        $prof = User::factory()->professeur()->create();

        $csv = $this->actingAs($prof)->get(route('admin.statistiques.csv', ['promos' => [2025]]));
        $csv->assertOk()->assertDownload('statistiques-promos.csv');

        $this->actingAs($prof)->get(route('admin.users.index', ['filtre' => 'anciens', 'promo' => 2025, 'statut' => 'demissionnaire']))
            ->assertOk()
            ->assertViewHas('users', fn ($u) => $u->total() === 1);
    }
}

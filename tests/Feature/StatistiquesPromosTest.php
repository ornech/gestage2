<?php

namespace Tests\Feature;

use App\Models\Stage;
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
        $this->assertSame(3, $p2025['base_diplomes']);
        $this->assertSame(100.0, $p2025['taux_diplomes']); // démissionnaire exclu de la base
        $this->assertSame(25.0, $p2025['taux_demission']);
        $this->assertSame(['SLAM' => 3, 'SISR' => 1, 'aucune' => 0], $p2025['specialites']);

        $filtre = app(StatistiquesPromos::class)->calculer([2025, 2026], 'aucune');
        $this->assertSame(2, $filtre->sum('total'));
    }

    public function test_stages_promo_terminee_et_promo_en_cours(): void
    {
        $annee = \App\Models\Parametre::anneeActive();
        $enCours = \App\Models\Parametre::promos($annee)['SIO2'];

        // Promo terminée : stage au cours du cursus, démissionnaire et archivé exclus de la base
        $diplome = User::factory()->etudiant()->create(['promo' => 2020, 'statut' => 'diplome']);
        User::factory()->etudiant()->create(['promo' => 2020, 'statut' => 'diplome']);
        User::factory()->etudiant()->create(['promo' => 2020, 'statut' => 'demissionnaire']);
        User::factory()->etudiant()->create(['promo' => 2020, 'statut' => 'archive']);
        Stage::factory()->create(['etudiant_id' => $diplome->id, 'annee_scolaire' => '2018-2019']);

        // Promo en cours : seul le stage de l'année active compte
        $avec = User::factory()->etudiant()->create(['promo' => $enCours, 'statut' => 'actif']);
        $ancien = User::factory()->etudiant()->create(['promo' => $enCours, 'statut' => 'actif']);
        User::factory()->etudiant()->create(['promo' => $enCours, 'statut' => 'demissionnaire']);
        Stage::factory()->create(['etudiant_id' => $avec->id, 'annee_scolaire' => $annee]);
        Stage::factory()->create(['etudiant_id' => $ancien->id, 'annee_scolaire' => '2000-2001']);

        $lignes = app(StatistiquesPromos::class)->calculer([2020, $enCours]);

        $t = $lignes->firstWhere('promo', 2020);
        $this->assertTrue($t['terminee']);
        $this->assertSame(2, $t['base_stage']);
        $this->assertSame(1, $t['avec_stage']);

        $c = $lignes->firstWhere('promo', $enCours);
        $this->assertFalse($c['terminee']);
        $this->assertNull($c['taux_diplomes']);
        $this->assertSame(2, $c['base_stage']);
        $this->assertSame(1, $c['avec_stage']);
        $this->assertSame(1, $c['actifs_sans_stage']);
    }

    public function test_page_accessible_au_staff_et_pas_a_un_etudiant(): void
    {
        $this->etudiants();
        $prof = User::factory()->professeur()->create();

        $this->actingAs($prof)->get(route('admin.statistiques', ['promos' => [2025]]))
            ->assertOk()
            ->assertSee('Statistiques des promos')
            ->assertSee('100 %')
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

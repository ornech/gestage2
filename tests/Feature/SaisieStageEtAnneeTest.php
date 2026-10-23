<?php

namespace Tests\Feature;

use App\Models\ConfigurationStage;
use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\JournalEntry;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaisieStageEtAnneeTest extends TestCase
{
    use RefreshDatabase;

    public function test_l_annee_bascule_d_elle_meme_le_1er_septembre(): void
    {
        $this->travelTo('2027-08-31');
        $this->assertSame('2026-2027', Parametre::anneeActive());

        $this->travelTo('2027-09-01');
        $this->assertSame('2027-2028', Parametre::anneeActive());
    }

    private function donnees(User $etudiant): array
    {
        $entreprise = Entreprise::factory()->create();
        $maitre     = Employe::factory()->create(['entreprise_id' => $entreprise->id]);

        return [
            'entreprise_id' => $entreprise->id, 'maitre_de_stage_id' => $maitre->id,
            'date_debut' => '2026-11-02', 'duree' => 6,
        ];
    }

    private function ouvrirLaConfig(string $classe = 'SIO2'): void
    {
        ConfigurationStage::create([
            'annee_scolaire' => Parametre::anneeActive(), 'classe' => $classe,
            'stage_date_debut' => '2026-11-02', 'stage_date_fin' => '2026-12-11',
        ]);
    }

    public function test_saisie_fermee_sans_dates_de_stage_pour_l_annee(): void
    {
        $this->travelTo('2026-10-01');
        $etudiant = User::factory()->etudiant()->create(['promo' => 2027]);

        $this->actingAs($etudiant)->post(route('stages.store'), $this->donnees($etudiant))->assertSessionHasErrors();
        $this->assertSame(0, $etudiant->stages()->count());

        $this->ouvrirLaConfig('SIO2');
        $this->actingAs($etudiant)->post(route('stages.store'), $this->donnees($etudiant))->assertSessionHasNoErrors();
        $this->assertSame('2026-2027', $etudiant->stages()->first()->annee_scolaire);
    }

    public function test_saisie_fermee_pour_un_etudiant_sans_classe(): void
    {
        $this->travelTo('2026-10-01');
        $this->ouvrirLaConfig('SIO1');
        $this->ouvrirLaConfig('SIO2');
        $diplome = User::factory()->etudiant()->create(['promo' => 2026]);

        $this->actingAs($diplome)->post(route('stages.store'), $this->donnees($diplome))->assertSessionHasErrors();
        $this->assertSame(0, $diplome->stages()->count());
    }

    public function test_le_tableau_de_bord_signale_les_etudiants_sans_classe(): void
    {
        $this->travelTo('2026-10-01');
        $orphelin = User::factory()->etudiant()->create(['promo' => 2026, 'statut' => 'actif']);
        $normal   = User::factory()->etudiant()->create(['promo' => 2027, 'statut' => 'actif']);

        $reponse = $this->actingAs(User::factory()->professeur()->create())->get(route('professeur.dashboard'));

        $reponse->assertOk()->assertSee($orphelin->nom)->assertSee('sans classe');
        $this->assertTrue($reponse->viewData('orphelins')->contains($orphelin));
        $this->assertFalse($reponse->viewData('orphelins')->contains($normal));
    }

    public function test_le_staff_supprime_un_stage_mal_saisi_puis_la_saisie_redevient_possible(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = Stage::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs(User::factory()->professeur()->create())
            ->delete(route('admin.stages.destroy', $stage))->assertSessionHasNoErrors();

        $this->assertNull(Stage::find($stage->id));
        $this->assertFalse($etudiant->aUnStageCetteAnnee());
    }

    public function test_un_stage_avec_journal_ne_se_supprime_pas_et_un_etudiant_ne_peut_pas_supprimer(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = Stage::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($etudiant)->delete(route('admin.stages.destroy', $stage));
        $this->assertNotNull(Stage::find($stage->id), 'un étudiant ne supprime pas de stage');

        JournalEntry::unguarded(fn() => JournalEntry::create([
            'stage_id' => $stage->id, 'user_id' => $etudiant->id, 'titre' => 't', 'semaine' => 1, 'activites' => 'x',
        ]));

        $this->actingAs(User::factory()->administrateur()->create())
            ->delete(route('admin.stages.destroy', $stage))->assertSessionHasErrors();
        $this->assertNotNull(Stage::find($stage->id));
    }
}

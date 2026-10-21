<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_professeur_cree_une_entreprise_validee(): void
    {
        $prof = User::factory()->professeur()->create();

        $response = $this->actingAs($prof)->post(route('entreprises.store'), [
            'raison_sociale' => 'Entreprise Test',
            'ville'          => 'La Rochelle',
            'siret'          => '12345678901234',
            'code_postal'    => '17000',
        ]);

        $entreprise = Entreprise::where('siret', '12345678901234')->firstOrFail();
        $response->assertRedirect(route('entreprises.show', $entreprise));
        $this->assertSame('ENTREPRISE TEST', $entreprise->raison_sociale);
        $this->assertSame('LA ROCHELLE', $entreprise->ville);
        $this->assertSame('17', $entreprise->departement_code);
        $this->assertTrue((bool) $entreprise->est_valide);
        $this->assertSame($prof->id, $entreprise->user_id);
    }

    public function test_creation_refuse_un_siret_invalide(): void
    {
        $prof = User::factory()->professeur()->create();

        $this->actingAs($prof)->post(route('entreprises.store'), [
            'raison_sociale' => 'Entreprise Test',
            'ville'          => 'Paris',
            'siret'          => '123',
        ])->assertSessionHasErrors('siret');

        $this->assertDatabaseCount('entreprises', 0);
    }

    public function test_un_etudiant_ne_peut_pas_creer_d_entreprise_via_le_crud(): void
    {
        $etudiant = User::factory()->etudiant()->create();

        $this->actingAs($etudiant)->post(route('entreprises.store'), [
            'raison_sociale' => 'Entreprise Test',
            'ville'          => 'Paris',
        ]);

        $this->assertDatabaseCount('entreprises', 0);
    }

    public function test_import_siret_affiche_les_donnees_sirene(): void
    {
        Http::fake([
            'https://api.insee.fr/*' => Http::response([
                'etablissement' => [
                    'uniteLegale'          => ['denominationUniteLegale' => 'ENTREPRISE IMPORTÉE'],
                    'adresseEtablissement' => [
                        'libelleVoieEtablissement'    => 'RUE TEST',
                        'codePostalEtablissement'     => '33000',
                        'libelleCommuneEtablissement' => 'BORDEAUX',
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs(User::factory()->professeur()->create())
            ->post(route('entreprises.import'), ['siret' => '12345678901234'])
            ->assertOk()
            ->assertSee('ENTREPRISE IMPORTÉE');
    }

    public function test_import_siret_inconnu_affiche_la_page_introuvable(): void
    {
        Http::fake(['https://api.insee.fr/*' => Http::response([], 404)]);

        $this->actingAs(User::factory()->professeur()->create())
            ->post(route('entreprises.import'), ['siret' => '12345678901234'])
            ->assertOk()
            ->assertViewIs('entreprises.import-not-found');
    }

    public function test_import_siret_exige_14_chiffres(): void
    {
        $this->actingAs(User::factory()->professeur()->create())
            ->post(route('entreprises.import'), ['siret' => 'abc'])
            ->assertSessionHasErrors('siret');
    }
}

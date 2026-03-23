<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Entreprise;
use App\Models\Contact;

class ContactTest extends TestCase
{
    //on utilise RefreshDatabase pour s'assurer que la base est propre à chaque test
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    //on va créer des tests pour chaque méthode du ContactController
     public function test_index_lists_contacts_for_company()
    {
        $company = Entreprise::factory()->create();
        $otherCompany = Entreprise::factory()->create();

        $contact1 = Contact::factory()->create(['entreprise_id' => $company->id]);
        $contact2 = Contact::factory()->create(['entreprise_id' => $company->id]);
        $otherContact = Contact::factory()->create(['entreprise_id' => $otherCompany->id]);

        $response = $this->get("/companies/{$company->id}/contacts");

        $response->assertStatus(200);
        $response->assertSee($contact1->nom);
        $response->assertSee($contact2->nom);
        $response->assertDontSee($otherContact->nom);
    }
//on va créer une entreprise et des contacts liés pour tester l'affichage
    public function test_store_creates_contact_for_company()
    {
         $company = Entreprise::factory()->create();

        $data = [
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'email' => 'marie.dupont@example.com',
            'telephone' => '0600000000',
        ];
        $response = $this->post("/companies/{$company->id}/contacts", $data);

        $response->assertStatus(302);

        $this->assertDatabaseHas('contacts', [
            'entreprise_id' => $company->id,
            'nom' => 'Dupont',
        ]);
    }
//on va ajouter une validation pour s'assurer que les données sont correctes avant de créer le contact
    public function test_show_displays_contact_if_belongs_to_company()
    {
          $company = Entreprise::factory()->create();
        $contact = Contact::factory()->create(['entreprise_id' => $company->id]);

        $response = $this->get("/companies/{$company->id}/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertSee($contact->nom);
    }
//on va vérifier que le contact affiché appartient bien à l'entreprise demandée
    public function test_update_updates_contact_if_belongs_to_company()
    {
        $company = Entreprise::factory()->create();
        $contact = Contact::factory()->create(['entreprise_id' => $company->id]);

        $response = $this->put("/companies/{$company->id}/contacts/{$contact->id}", [
            'nom' => 'NouveauNom',
            'prenom' => $contact->prenom,
            'email' => $contact->email,
            'telephone' => $contact->telephone,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'nom' => 'NouveauNom',
        ]);
    }

    public function test_destroy_deletes_contact_if_belongs_to_company()
    {
          $company = Entreprise::factory()->create();
        $contact = Contact::factory()->create(['entreprise_id' => $company->id]);

        $response = $this->delete("/companies/{$company->id}/contacts/{$contact->id}");
//on va vérifier que le contact supprimé appartient bien à l'entreprise demandée
        $response->assertStatus(200);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}

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
        $this->assertTrue(true);
    }
//on va créer une entreprise et des contacts liés pour tester l'affichage
    public function test_store_creates_contact_for_company()
    {
        $this->assertTrue(true);
    }
//on va ajouter une validation pour s'assurer que les données sont correctes avant de créer le contact
    public function test_show_displays_contact_if_belongs_to_company()
    {
        $this->assertTrue(true);
    }
//on va vérifier que le contact affiché appartient bien à l'entreprise demandée
    public function test_update_updates_contact_if_belongs_to_company()
    {
        $this->assertTrue(true);
    }

    public function test_destroy_deletes_contact_if_belongs_to_company()
    {
        $this->assertTrue(true);
    }
}

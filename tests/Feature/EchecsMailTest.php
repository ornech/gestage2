<?php

namespace Tests\Feature;

use App\Models\EchecMail;
use App\Models\User;
use App\Services\EchecsMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EchecsMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerte_sur_liste_et_fiche_puis_resolue(): void
    {
        $admin    = User::factory()->administrateur()->create();
        $etudiant = User::factory()->etudiant()->create(['email' => 'Eleve@example.org', 'statut' => 'actif', 'promo' => (int) substr(\App\Models\Parametre::anneeActive(), 0, 4) + 1]);

        app(EchecsMail::class)->enregistrer(['eleve@example.org'], 'InvitationCompte', '550 5.1.1 User unknown');

        $this->actingAs($admin)->get(route('admin.users.edit', $etudiant))
            ->assertOk()
            ->assertSee('ne sont pas remis')
            ->assertSee('550 5.1.1 User unknown')
            ->assertSee("n'existe pas");

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('fa-exclamation-triangle', false);

        app(EchecsMail::class)->resoudre('ELEVE@example.org');

        $this->assertSame(0, EchecMail::nonResolu()->count());
        $this->actingAs($admin)->get(route('admin.users.edit', $etudiant))->assertDontSee('ne sont pas remis');
    }
}

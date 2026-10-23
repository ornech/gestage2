<?php

namespace Tests\Feature;

use App\Mail\InvitationCompte;
use App\Models\Parametre;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Etudiant', 'Professeur', 'Administrateur'] as $role) {
            Role::findOrCreate($role);
        }
        $this->travelTo('2025-10-01');
        Mail::fake();
    }

    private function lienDeLInvitation(): string
    {
        $lien = null;
        Mail::assertQueued(InvitationCompte::class, function (InvitationCompte $m) use (&$lien) {
            $lien = $m->lien;

            return true;
        });

        return $lien;
    }

    // Plus aucun mot de passe connu -----------------------------------------

    public function test_le_mot_de_passe_provisoire_n_existe_plus_dans_le_code(): void
    {
        $fautes = [];
        foreach ((new Finder)->files()->in([app_path(), resource_path('views'), base_path('routes')])->name('*.php') as $f) {
            if (preg_match('/[\'"`]achanger[\'"`]/', $f->getContents()) && ! str_contains($f->getFilename(), 'InviterMotDePasseProvisoire')) {
                $fautes[] = $f->getRelativePathname();
            }
        }

        $this->assertSame([], $fautes, 'Aucun mot de passe en dur ne doit être posé sur un compte');
    }

    public function test_un_compte_cree_par_un_admin_ne_peut_pas_se_connecter_avec_achanger(): void
    {
        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.users.store'), ['nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@example.fr', 'role' => 'Etudiant']);

        $user = User::where('email', 'jean@example.fr')->firstOrFail();
        $this->assertFalse(Hash::check('achanger', $user->password));
        $this->assertTrue((bool) $user->force_password_change);

        auth()->logout();
        $this->post('/login', ['email' => 'jean@example.fr', 'password' => 'achanger'])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_deux_comptes_n_ont_jamais_le_meme_mot_de_passe(): void
    {
        $service = app(InvitationService::class);
        $a = User::factory()->etudiant()->create();
        $b = User::factory()->etudiant()->create();

        $service->inviter($a);
        $service->inviter($b);

        $this->assertNotSame($a->fresh()->password, $b->fresh()->password);
    }

    // Envoi du mail + parcours complet ---------------------------------------

    public function test_la_creation_d_un_compte_envoie_un_mail_avec_un_lien_qui_permet_de_choisir_son_mot_de_passe(): void
    {
        $this->actingAs(User::factory()->professeur()->create())
            ->post(route('admin.users.store'), ['nom' => 'Martin', 'prenom' => 'Lea', 'email' => 'lea@example.fr', 'role' => 'Etudiant'])
            ->assertSessionHas('success');

        Mail::assertQueued(InvitationCompte::class, 1);
        Mail::assertQueued(InvitationCompte::class, fn ($m) => $m->hasTo('lea@example.fr'));

        $lien = $this->lienDeLInvitation();
        auth()->logout();

        // Le lien affiche le formulaire (c'était une erreur 500 : la vue n'existait pas)
        $this->get($lien)->assertOk()->assertSee('Choisissez votre mot de passe');

        parse_str(parse_url($lien, PHP_URL_QUERY), $q);
        $token = basename(parse_url($lien, PHP_URL_PATH));

        $this->post(route('password.update'), [
            'token' => $token, 'email' => 'lea@example.fr',
            'password' => 'Un-mot-de-passe-solide-42', 'password_confirmation' => 'Un-mot-de-passe-solide-42',
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'lea@example.fr')->first();
        $this->assertFalse((bool) $user->force_password_change, 'le changement forcé est levé une fois le mot de passe choisi');

        $this->post('/login', ['email' => 'lea@example.fr', 'password' => 'Un-mot-de-passe-solide-42']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_le_lien_est_a_usage_unique(): void
    {
        $user = User::factory()->etudiant()->create();
        app(InvitationService::class)->inviter($user);
        $token = basename(parse_url($this->lienDeLInvitation(), PHP_URL_PATH));
        $donnees = ['token' => $token, 'email' => $user->email, 'password' => 'Un-mot-de-passe-solide-42', 'password_confirmation' => 'Un-mot-de-passe-solide-42'];

        $this->post(route('password.update'), $donnees)->assertRedirect(route('login'));
        $this->post(route('password.update'), ['password' => 'Autre-mot-de-passe-77', 'password_confirmation' => 'Autre-mot-de-passe-77'] + $donnees)
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('Un-mot-de-passe-solide-42', $user->fresh()->password));
    }

    public function test_le_lien_expire_apres_sept_jours(): void
    {
        $user = User::factory()->etudiant()->create();
        app(InvitationService::class)->inviter($user);
        $token = basename(parse_url($this->lienDeLInvitation(), PHP_URL_PATH));
        $donnees = ['token' => $token, 'email' => $user->email, 'password' => 'Un-mot-de-passe-solide-42', 'password_confirmation' => 'Un-mot-de-passe-solide-42'];

        $this->travel(6)->days();
        $this->post(route('password.update'), $donnees)->assertRedirect(route('login')); // encore valide

        // nouveau lien, puis on dépasse 7 jours
        app(InvitationService::class)->inviter($user->fresh());
        $token2 = basename(parse_url($this->lienDeLInvitation(), PHP_URL_PATH));
        $this->travel(8)->days();

        $this->post(route('password.update'), ['token' => $token2] + $donnees)->assertSessionHasErrors('email');
    }

    public function test_un_lien_ne_fonctionne_pas_avec_l_email_d_un_autre_compte(): void
    {
        $a = User::factory()->etudiant()->create();
        $b = User::factory()->etudiant()->create();
        app(InvitationService::class)->inviter($a);
        $token = basename(parse_url($this->lienDeLInvitation(), PHP_URL_PATH));

        $this->post(route('password.update'), [
            'token' => $token, 'email' => $b->email,
            'password' => 'Un-mot-de-passe-solide-42', 'password_confirmation' => 'Un-mot-de-passe-solide-42',
        ])->assertSessionHasErrors('email');
    }

    // Adresse non distribuable ------------------------------------------------

    public function test_un_compte_sans_adresse_distribuable_n_envoie_pas_de_mail_et_donne_le_lien_a_l_admin(): void
    {
        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.users.store'), ['nom' => 'Sans', 'prenom' => 'Mail', 'email' => 'sans.mail@import.local', 'role' => 'Etudiant'])
            ->assertSessionHas('lien_invitation', fn ($l) => str_contains($l['lien'], '/reset-password/') && $l['email'] === 'sans.mail@import.local');

        Mail::assertNothingQueued();
    }

    // Mode lien (messagerie académique injoignable) --------------------------------------

    public function test_les_adresses_academiques_ne_recoivent_pas_de_mail_mais_un_lien_s_affiche(): void
    {
        $prof = User::factory()->professeur()->create(['email' => 'prof.dupont@ac-poitiers.fr']);

        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.reset-password.do'), ['user_id' => $prof->id])
            ->assertSessionHas('lien_invitation', fn ($l) => $l['email'] === 'prof.dupont@ac-poitiers.fr'
                && str_contains($l['raison'], 'ac-poitiers.fr'));

        Mail::assertNothingQueued();
    }

    public function test_les_sous_domaines_academiques_sont_aussi_concernes(): void
    {
        $service = app(InvitationService::class);

        $this->assertNotNull($service->raisonSansMail(User::factory()->make(['email' => 'x@ac-poitiers.fr'])));
        $this->assertNotNull($service->raisonSansMail(User::factory()->make(['email' => 'x@mail.education.gouv.fr'])));
        $this->assertNull($service->raisonSansMail(User::factory()->make(['email' => 'x@gmail.com'])));
        $this->assertNull($service->raisonSansMail(User::factory()->make(['email' => 'x@pasac-poitiers.fr'])), 'pas un faux positif sur le suffixe');
    }

    public function test_la_liste_des_domaines_sans_mail_est_configurable(): void
    {
        config(['invitations.domaines_sans_mail' => ['lycee-test.fr']]);

        $service = app(InvitationService::class);
        $this->assertNotNull($service->raisonSansMail(User::factory()->make(['email' => 'x@lycee-test.fr'])));
        $this->assertNull($service->raisonSansMail(User::factory()->make(['email' => 'x@ac-poitiers.fr'])));
    }

    public function test_la_case_sans_mail_affiche_le_lien_pour_une_adresse_pourtant_joignable(): void
    {
        $cible = User::factory()->etudiant()->create(['email' => 'eleve@gmail.com']);

        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.reset-password.do'), ['user_id' => $cible->id, 'sans_mail' => '1'])
            ->assertSessionHas('lien_invitation');

        Mail::assertNothingQueued();
    }

    public function test_le_lien_affiche_a_l_admin_permet_de_choisir_le_mot_de_passe(): void
    {
        $prof = User::factory()->professeur()->create(['email' => 'prof@ac-poitiers.fr', 'password' => Hash::make('ancien-mot-de-passe')]);

        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.reset-password.do'), ['user_id' => $prof->id]);

        $lien = session('lien_invitation')['lien'];
        auth()->logout();

        $this->get($lien)->assertOk();
        $this->post(route('password.update'), [
            'token' => basename(parse_url($lien, PHP_URL_PATH)), 'email' => 'prof@ac-poitiers.fr',
            'password' => 'Un-mot-de-passe-solide-42', 'password_confirmation' => 'Un-mot-de-passe-solide-42',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Un-mot-de-passe-solide-42', $prof->fresh()->password));
        $this->assertFalse(Hash::check('ancien-mot-de-passe', $prof->fresh()->password));
    }

    public function test_la_page_affiche_le_lien_et_le_bouton_copier(): void
    {
        $prof = User::factory()->professeur()->create(['email' => 'prof@ac-poitiers.fr']);
        $admin = User::factory()->administrateur()->create();

        $this->actingAs($admin)->post(route('admin.reset-password.do'), ['user_id' => $prof->id]);
        $lien = session('lien_invitation')['lien'];

        $this->actingAs($admin)->followingRedirects()
            ->withSession(['lien_invitation' => session('lien_invitation')])
            ->get(route('admin.reset-password'))
            ->assertOk()
            ->assertSee($lien, false)
            ->assertSee('data-copy', false);
    }

    public function test_un_non_admin_ne_peut_pas_obtenir_de_lien(): void
    {
        $cible = User::factory()->etudiant()->create();

        $this->actingAs(User::factory()->etudiant()->create())
            ->post(route('admin.reset-password.do'), ['user_id' => $cible->id]);

        $this->assertNull(session('lien_invitation'));
        Mail::assertNothingQueued();
    }

    public function test_l_import_en_mode_lien_n_envoie_aucun_mail_et_liste_les_liens(): void
    {
        $this->actingAs(User::factory()->administrateur()->create());
        $csv = "Élève;Classe;Régime;Mail;Date d'entrée;Date de sortie\r\n"
            ."DUPONT Jean;SIO1;Externe;jean@example.fr;01/09/2025;\r\n"
            ."MARTIN Lea;SIO1;Externe;lea@ac-poitiers.fr;01/09/2025;\r\n";

        $this->post(route('imports.pronote.preview'), [
            'fichier' => UploadedFile::fake()->createWithContent('p.csv', "\xEF\xBB\xBF".$csv), 'classe_forcee' => 'SIO1',
        ]);
        $this->post(route('imports.pronote.confirm'), ['sans_mail' => '1'])
            ->assertOk()
            ->assertSee('/reset-password/', false)
            ->assertSee('jean@example.fr', false);

        Mail::assertNothingQueued();
    }

    public function test_l_import_envoie_les_mails_sauf_aux_adresses_academiques(): void
    {
        $this->actingAs(User::factory()->administrateur()->create());
        $csv = "Élève;Classe;Régime;Mail;Date d'entrée;Date de sortie\r\n"
            ."DUPONT Jean;SIO1;Externe;jean@example.fr;01/09/2025;\r\n"
            ."MARTIN Lea;SIO1;Externe;lea@ac-poitiers.fr;01/09/2025;\r\n";

        $this->post(route('imports.pronote.preview'), [
            'fichier' => UploadedFile::fake()->createWithContent('p.csv', "\xEF\xBB\xBF".$csv), 'classe_forcee' => 'SIO1',
        ]);
        $this->post(route('imports.pronote.confirm'))->assertOk()->assertSee('lea@ac-poitiers.fr', false);

        Mail::assertQueued(InvitationCompte::class, 1);
        Mail::assertQueued(InvitationCompte::class, fn ($m) => $m->hasTo('jean@example.fr'));
    }

    public function test_le_mail_s_affiche_correctement(): void
    {
        $user = User::factory()->etudiant()->create(['prenom' => 'Lea', 'nom' => 'Martin']);

        $html = (new InvitationCompte($user, 'https://exemple.test/reset-password/abc?email=x', 7))->render();

        $this->assertStringContainsString('Lea Martin', $html);
        $this->assertStringContainsString('https://exemple.test/reset-password/abc?email=x', $html);
        $this->assertStringContainsString('7 jour(s)', $html);
    }

    // Réinitialisation par l'admin -------------------------------------------

    public function test_la_reinitialisation_invalide_l_ancien_mot_de_passe_et_envoie_un_mail(): void
    {
        $cible = User::factory()->etudiant()->create(['password' => Hash::make('ancien-mot-de-passe')]);

        $this->actingAs(User::factory()->administrateur()->create())
            ->post(route('admin.reset-password.do'), ['user_id' => $cible->id])
            ->assertSessionHas('success');

        $this->assertFalse(Hash::check('ancien-mot-de-passe', $cible->fresh()->password));
        Mail::assertQueued(InvitationCompte::class, fn ($m) => $m->hasTo($cible->email));
    }

    // Import Pronote -------------------------------------------------------------

    public function test_l_import_pronote_envoie_une_invitation_par_nouveau_compte(): void
    {
        $this->actingAs(User::factory()->administrateur()->create());
        $csv = "Élève;Classe;Régime;Mail;Date d'entrée;Date de sortie\r\n"
            ."DUPONT Jean;SIO1;Externe;jean@example.fr;01/09/2025;\r\n"
            ."MARTIN Lea;SIO1;Externe;lea@example.fr;01/09/2025;\r\n";

        $this->post(route('imports.pronote.preview'), [
            'fichier' => UploadedFile::fake()->createWithContent('p.csv', "\xEF\xBB\xBF".$csv), 'classe_forcee' => 'SIO1',
        ]);
        $this->post(route('imports.pronote.confirm'))->assertOk()->assertSee('2 envoi');

        Mail::assertQueued(InvitationCompte::class, 2);
        foreach (['jean@example.fr', 'lea@example.fr'] as $email) {
            $this->assertFalse(Hash::check('achanger', User::where('email', $email)->first()->password));
        }
    }

    // Mot de passe oublié ----------------------------------------------------------

    public function test_la_page_mot_de_passe_oublie_s_affiche_avec_un_retour_utilisateur(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Mot de passe oublié');
        $this->get('/login')->assertOk()->assertSee('Mot de passe oublié');
    }

    // Commande pour les comptes existants -------------------------------------------

    public function test_la_commande_en_mode_sans_mail_n_envoie_rien(): void
    {
        $legacy = User::factory()->etudiant()->create(['password' => Hash::make('achanger')]);

        $this->artisan('comptes:invitations-mdp-provisoire', ['--sans-mail' => true])->assertSuccessful();

        $this->assertFalse(Hash::check('achanger', $legacy->fresh()->password));
        Mail::assertNothingQueued();
    }

    public function test_la_commande_repere_les_comptes_achanger_sans_toucher_aux_autres(): void
    {
        $legacy = User::factory()->etudiant()->create(['password' => Hash::make('achanger')]);
        $sain   = User::factory()->etudiant()->create(['password' => Hash::make('mot-de-passe-perso')]);
        $hashSain = $sain->password;

        $this->artisan('comptes:invitations-mdp-provisoire', ['--dry-run' => true])->assertSuccessful();
        $this->assertTrue(Hash::check('achanger', $legacy->fresh()->password), 'dry-run : rien ne change');
        Mail::assertNothingQueued();

        $this->artisan('comptes:invitations-mdp-provisoire')->assertSuccessful();

        $this->assertFalse(Hash::check('achanger', $legacy->fresh()->password));
        $this->assertSame($hashSain, $sain->fresh()->password);
        Mail::assertQueued(InvitationCompte::class, 1);
        Mail::assertQueued(InvitationCompte::class, fn ($m) => $m->hasTo($legacy->email));
    }
}

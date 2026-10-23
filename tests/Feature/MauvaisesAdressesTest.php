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
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MauvaisesAdressesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Etudiant', 'Professeur', 'Administrateur'] as $role) {
            Role::findOrCreate($role);
        }
        Parametre::set('annee_scolaire', '2025-2026');
        Mail::fake();
    }

    private function admin(): User
    {
        return User::factory()->administrateur()->create();
    }

    private function eleveEnAttente(string $email, array $attrs = []): User
    {
        return User::factory()->etudiant()->create($attrs + ['email' => $email, 'force_password_change' => true, 'statut' => 'actif']);
    }

    // Détection -------------------------------------------------------------------

    /** @dataProvider fautesDeFrappe */
    public function test_les_fautes_de_frappe_sur_un_domaine_courant_sont_detectees(string $adresse, string $attendu): void
    {
        $anomalie = app(InvitationService::class)->anomalieAdresse($adresse);

        $this->assertNotNull($anomalie, $adresse);
        $this->assertStringContainsString($attendu, $anomalie);
    }

    public static function fautesDeFrappe(): array
    {
        return [
            'gmial'      => ['jean@gmial.com', 'gmail.com'],
            'gmai'       => ['jean@gmai.com', 'gmail.com'],
            'gamil'      => ['jean@gamil.com', 'gmail.com'],
            'gmail.con'  => ['jean@gmail.con', 'gmail.com'],
            'hotmial'    => ['jean@hotmial.fr', 'hotmail.fr'],
            'outlok'     => ['jean@outlok.com', 'outlook.com'],
            'yahou'      => ['jean@yahou.fr', 'yahoo.fr'],
            'ornge'      => ['jean@ornge.fr', 'orange.fr'],
            'wanadoo'    => ['jean@wanadou.fr', 'wanadoo.fr'],
            'protonmail' => ['jean@protonmial.com', 'protonmail.com'],
        ];
    }

    /** @dataProvider adressesCorrectes */
    public function test_les_adresses_correctes_ne_sont_pas_signalees(string $adresse): void
    {
        $this->assertNull(app(InvitationService::class)->anomalieAdresse($adresse), $adresse);
    }

    public static function adressesCorrectes(): array
    {
        return [
            ['jean@gmail.com'], ['jean@hotmail.fr'], ['jean@orange.fr'], ['jean@free.fr'], ['jean@laposte.net'],
            ['jean@ac-poitiers.fr'], ['jean@lycee-exemple.fr'], ['jean.dupont@entreprise-xyz.com'], ['jean@gmx.fr'],
        ];
    }

    public function test_une_adresse_sans_serveur_de_messagerie_est_signalee_quand_la_verification_dns_est_active(): void
    {
        config(['invitations.verifier_dns' => true]);

        $this->assertStringContainsString(
            'pas de serveur de messagerie',
            app(InvitationService::class)->anomalieAdresse('jean@domaine-qui-n-existe-pas.invalid')
        );
    }

    public function test_une_adresse_mal_formee_est_invalide(): void
    {
        $this->assertSame('adresse email invalide', app(InvitationService::class)->anomalieAdresse('jean@@gmail'));
    }

    // Invitation ----------------------------------------------------------------------

    public function test_une_adresse_avec_faute_de_frappe_ne_recoit_aucun_mail_et_le_lien_est_affiche(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), ['nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'jean@gmial.com', 'role' => 'Etudiant'])
            ->assertSessionHas('lien_invitation', fn ($l) => str_contains($l['raison'], 'gmail.com'));

        Mail::assertNothingQueued(); // sinon un inconnu pourrait recevoir un lien donnant la main sur le compte
    }

    // Correction de l'adresse ---------------------------------------------------------------

    public function test_corriger_l_adresse_d_un_compte_non_active_renvoie_l_invitation_a_la_bonne_adresse(): void
    {
        $eleve = $this->eleveEnAttente('jean@gmial.com');

        $this->actingAs($this->admin())
            ->post(route('admin.comptes.update-email', $eleve), ['email' => 'jean@gmail.com'])
            ->assertSessionHas('success');

        $this->assertSame('jean@gmail.com', $eleve->fresh()->email);
        Mail::assertQueued(InvitationCompte::class, 1);
        Mail::assertQueued(InvitationCompte::class, fn ($m) => $m->hasTo('jean@gmail.com'));
    }

    public function test_corriger_une_adresse_factice_affiche_le_lien_si_la_nouvelle_adresse_est_academique(): void
    {
        $eleve = $this->eleveEnAttente('jean@import.local');

        $this->actingAs($this->admin())
            ->post(route('admin.comptes.update-email', $eleve), ['email' => 'jean@ac-poitiers.fr'])
            ->assertSessionHas('lien_invitation');

        Mail::assertNothingQueued();
    }

    public function test_l_ancien_lien_ne_fonctionne_plus_apres_correction_de_l_adresse(): void
    {
        $eleve = $this->eleveEnAttente('jean@gmial.com');
        app(InvitationService::class)->inviter($eleve, 0, false);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'jean@gmial.com']);

        $this->actingAs($this->admin())->post(route('admin.comptes.update-email', $eleve), ['email' => 'jean@gmail.com']);

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'jean@gmial.com']);
    }

    public function test_corriger_l_adresse_d_un_compte_actif_ne_touche_pas_a_son_mot_de_passe(): void
    {
        $actif = User::factory()->etudiant()->create(['email' => 'a@gmail.com', 'password' => Hash::make('mon-mot-de-passe'), 'force_password_change' => false]);

        $this->actingAs($this->admin())->post(route('admin.comptes.update-email', $actif), ['email' => 'b@gmail.com']);

        $this->assertSame('b@gmail.com', $actif->fresh()->email);
        $this->assertTrue(Hash::check('mon-mot-de-passe', $actif->fresh()->password));
        Mail::assertNothingQueued();
    }

    public function test_une_adresse_deja_utilisee_est_refusee(): void
    {
        User::factory()->create(['email' => 'pris@gmail.com']);
        $eleve = $this->eleveEnAttente('jean@gmial.com');

        $this->actingAs($this->admin())->post(route('admin.comptes.update-email', $eleve), ['email' => 'pris@gmail.com'])
            ->assertSessionHasErrors('email');
    }

    // Page de suivi -------------------------------------------------------------------------

    public function test_la_page_liste_les_comptes_en_attente_avec_leur_diagnostic(): void
    {
        $this->eleveEnAttente('typo@gmial.com', ['nom' => 'TYPO']);
        $this->eleveEnAttente('ok@gmail.com', ['nom' => 'CORRECT']);
        $this->eleveEnAttente('x@import.local', ['nom' => 'FACTICE']);
        User::factory()->etudiant()->create(['nom' => 'ACTIVE', 'force_password_change' => false, 'statut' => 'actif']);

        $this->actingAs($this->admin())->get(route('admin.comptes.nettoyage'))
            ->assertOk()
            ->assertSee('Comptes en attente d')
            ->assertSee('TYPO')->assertSee('gmail.com', false)
            ->assertSee('CORRECT')->assertSee('Adresse plausible')
            ->assertSee('FACTICE')->assertSee('absente ou factice')
            ->assertDontSee('ACTIVE');
    }

    public function test_renvoyer_l_invitation_par_mail_et_afficher_le_lien(): void
    {
        $eleve = $this->eleveEnAttente('ok@gmail.com');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.comptes.inviter', $eleve))->assertSessionHas('success');
        Mail::assertQueued(InvitationCompte::class, 1);

        $this->actingAs($admin)->post(route('admin.comptes.inviter', $eleve), ['sans_mail' => '1'])
            ->assertSessionHas('lien_invitation');
        Mail::assertQueued(InvitationCompte::class, 1); // pas de second envoi
    }

    public function test_on_ne_peut_pas_reinviter_un_compte_deja_active(): void
    {
        $actif = User::factory()->etudiant()->create(['password' => Hash::make('mon-mot-de-passe'), 'force_password_change' => false]);

        $this->actingAs($this->admin())->post(route('admin.comptes.inviter', $actif))->assertSessionHasErrors();

        $this->assertTrue(Hash::check('mon-mot-de-passe', $actif->fresh()->password), 'un compte actif ne doit pas être invalidé par ce bouton');
        Mail::assertNothingQueued();
    }

    public function test_un_professeur_ne_peut_pas_utiliser_ces_actions(): void
    {
        $eleve = $this->eleveEnAttente('ok@gmail.com');
        $prof  = User::factory()->professeur()->create();

        $this->actingAs($prof)->post(route('admin.comptes.inviter', $eleve), ['sans_mail' => '1']);
        $this->actingAs($prof)->post(route('admin.comptes.update-email', $eleve), ['email' => 'autre@gmail.com']);

        $this->assertSame('ok@gmail.com', $eleve->fresh()->email);
        $this->assertNull(session('lien_invitation'));
    }

    public function test_la_page_est_accessible_depuis_le_menu_admin(): void
    {
        $this->actingAs($this->admin())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.comptes.nettoyage'), false);
    }

    // Import Pronote ---------------------------------------------------------------------------

    public function test_l_apercu_de_l_import_signale_une_adresse_suspecte(): void
    {
        $this->actingAs($this->admin());
        $csv = "Élève;Classe;Régime;Mail;Date d'entrée;Date de sortie\r\n"
            ."DUPONT Jean;SIO1;Externe;jean@gmial.com;01/09/2025;\r\n"
            ."MARTIN Lea;SIO1;Externe;lea@gmail.com;01/09/2025;\r\n";

        $this->post(route('imports.pronote.preview'), [
            'fichier' => UploadedFile::fake()->createWithContent('p.csv', "\xEF\xBB\xBF".$csv), 'classe_forcee' => 'SIO1',
        ])->assertOk()->assertSee('vouliez-vous écrire', false);

        $apercu = session('pronote_preview');
        $this->assertNotNull($apercu[0]['alerte_email']);
        $this->assertNull($apercu[1]['alerte_email']);

        // L'import crée quand même les comptes, mais sans envoyer d'invitation à l'adresse suspecte
        $this->post(route('imports.pronote.confirm'))->assertOk()->assertSee('jean@gmial.com', false);
        Mail::assertQueued(InvitationCompte::class, 1);
        Mail::assertQueued(InvitationCompte::class, fn ($m) => $m->hasTo('lea@gmail.com'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Parametre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PronoteImportTest extends TestCase
{
    use RefreshDatabase;

    private const HEADER = "Élève;Classe;Régime;Mail;Date d'entrée;Date de sortie";

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Etudiant', 'Professeur', 'Administrateur'] as $role) {
            Role::findOrCreate($role);
        }

        $this->travelTo('2025-10-01');
        $this->actingAs(User::factory()->administrateur()->create());
    }

    /** Construit un CSV Pronote (UTF-8 BOM, délimiteur ';') à partir de lignes [élève, email, entrée, sortie]. */
    private function csv(array $lignes, bool $bom = true): UploadedFile
    {
        $out = [self::HEADER];
        foreach ($lignes as [$eleve, $email, $entree, $sortie]) {
            $out[] = "$eleve;SIO1;Externe;$email;$entree;$sortie";
        }

        return UploadedFile::fake()->createWithContent(
            'pronote.csv',
            ($bom ? "\xEF\xBB\xBF" : '').implode("\r\n", $out)
        );
    }

    /** Preview puis retourne le contenu de session analysé. */
    private function preview(array $lignes, string $classe = 'SIO1', bool $bom = true): array
    {
        $this->post(route('imports.pronote.preview'), [
            'fichier'       => $this->csv($lignes, $bom),
            'classe_forcee' => $classe,
        ])->assertOk();

        return session('pronote_preview');
    }

    private function etudiant(array $attrs = []): User
    {
        return User::factory()->etudiant()->create($attrs + ['statut' => 'actif', 'classe' => 'SIO1']);
    }

    // Preview ---------------------------------------------------------------

    public function test_nouvel_eleve_est_marque_create_avec_la_bonne_promo(): void
    {
        $sio1 = $this->preview([['DUPONT Jean', 'jean.dupont@example.fr', '01/09/2025', '']], 'SIO1');
        $sio2 = $this->preview([['DUPONT Jean', 'jean.dupont@example.fr', '01/09/2025', '']], 'SIO2');

        $this->assertSame('create', $sio1[0]['action']);
        $this->assertSame(2027, $sio1[0]['promo']);   // 2025 + (3 - 1)
        $this->assertSame(2026, $sio2[0]['promo']);   // 2025 + (3 - 2)
        $this->assertSame('actif', $sio1[0]['statut']);
        $this->assertSame('2025-09-01', $sio1[0]['date_entree']);
    }

    public function test_bom_et_noms_composes_pronote_sont_geres(): void
    {
        $preview = $this->preview([['BARBAT--PATINAUD Marie Claire', 'marie@example.fr', '01/09/2025', '']]);

        $this->assertSame('BARBAT PATINAUD', $preview[0]['nom']);
        $this->assertSame('Marie Claire', $preview[0]['prenom']);
    }

    public function test_noms_a_particule_sont_separes_du_prenom(): void
    {
        $preview = $this->preview([['DE LA FONTAINE Jean', 'jf@example.fr', '01/09/2025', '']]);

        $this->assertSame('DE LA FONTAINE', $preview[0]['nom']);
        $this->assertSame('Jean', $preview[0]['prenom']);
    }

    public function test_lignes_sans_email_valide_sont_ignorees(): void
    {
        $preview = $this->preview([
            ['AAA Bob', 'pas-un-email', '01/09/2025', ''],
            ['BBB Alice', '', '01/09/2025', ''],
            ['CCC Eve', 'eve@example.fr', '01/09/2025', ''],
        ]);

        $this->assertCount(1, $preview);
        $this->assertSame('eve@example.fr', $preview[0]['email']);
    }

    public function test_email_est_normalise_en_minuscules(): void
    {
        $preview = $this->preview([['DUPONT Jean', 'Jean.DUPONT@Example.FR', '01/09/2025', '']]);

        $this->assertSame('jean.dupont@example.fr', $preview[0]['email']);
    }

    public function test_correspondance_par_email_donne_update(): void
    {
        $existant = $this->etudiant(['email' => 'jean@example.fr', 'date_entree' => '2025-09-01']);

        $preview = $this->preview([['AUTRE Nom', 'jean@example.fr', '01/09/2025', '']]);

        $this->assertSame('update', $preview[0]['action']);
        $this->assertSame($existant->id, $preview[0]['existing_id']);
    }

    public function test_correspondance_par_nom_normalise_quand_email_different(): void
    {
        $existant = $this->etudiant([
            'nom' => 'DE  LA FONTAINE', // double espace en base
            'prenom' => 'Jean',
            'email' => 'ancien@import.local',
            'date_entree' => '2025-09-01',
        ]);

        $preview = $this->preview([['DE LA FONTAINE Jean', 'nouveau@example.fr', '01/09/2025', '']]);

        $this->assertSame('update', $preview[0]['action']);
        $this->assertSame($existant->id, $preview[0]['existing_id']);
    }

    public function test_correspondance_par_nom_ignore_les_non_etudiants(): void
    {
        User::factory()->professeur()->create(['nom' => 'DUPONT', 'prenom' => 'Jean']);

        $preview = $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', '']]);

        $this->assertSame('create', $preview[0]['action']);
    }

    public function test_meme_classe_et_date_entree_differente_donne_redoublant(): void
    {
        $this->etudiant(['email' => 'jean@example.fr', 'classe' => 'SIO1', 'date_entree' => '2024-09-02']);

        $preview = $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', '']], 'SIO1');

        $this->assertSame('redoublant', $preview[0]['action']);
    }

    public function test_changement_de_classe_n_est_pas_un_redoublement(): void
    {
        $this->etudiant(['email' => 'jean@example.fr', 'classe' => 'SIO1', 'date_entree' => '2024-09-02']);

        $preview = $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', '']], 'SIO2');

        $this->assertSame('update', $preview[0]['action']);
    }

    public function test_date_de_sortie_passee_donne_demissionnaire(): void
    {
        $this->etudiant(['email' => 'jean@example.fr', 'date_entree' => '2025-09-01']);
        $passee = Carbon::now()->subWeek()->format('d/m/Y');

        $preview = $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', $passee]]);

        $this->assertSame('demissionnaire', $preview[0]['action']);
        $this->assertSame('demissionnaire', $preview[0]['statut']);
    }

    public function test_date_de_sortie_future_reste_actif(): void
    {
        $future = Carbon::now()->addMonths(3)->format('d/m/Y');

        $preview = $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', $future]]);

        $this->assertSame('actif', $preview[0]['statut']);
    }

    public function test_classe_forcee_est_obligatoire_et_limitee(): void
    {
        $this->post(route('imports.pronote.preview'), ['fichier' => $this->csv([])])
            ->assertSessionHasErrors('classe_forcee');
        $this->post(route('imports.pronote.preview'), ['fichier' => $this->csv([]), 'classe_forcee' => 'SIO3'])
            ->assertSessionHasErrors('classe_forcee');
    }

    // Confirm ---------------------------------------------------------------

    public function test_preview_n_ecrit_rien_en_base(): void
    {
        $avant = User::count();

        $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', '']]);

        $this->assertSame($avant, User::count());
    }

    public function test_confirm_sans_preview_redirige_vers_le_formulaire(): void
    {
        $this->post(route('imports.pronote.confirm'))
            ->assertRedirect(route('imports.pronote.form'))
            ->assertSessionHasErrors();
    }

    public function test_confirm_cree_le_compte_etudiant(): void
    {
        $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', '']], 'SIO1');

        $this->post(route('imports.pronote.confirm'))->assertOk();

        $user = User::where('email', 'jean@example.fr')->firstOrFail();
        $this->assertTrue($user->hasRole('Etudiant'));
        $this->assertTrue((bool) $user->force_password_change);
        $this->assertSame('jean@example.fr', $user->email_pronote);
        $this->assertSame('SIO1', $user->classe);
        $this->assertSame(2027, (int) $user->promo);
        $this->assertSame('DUPONT', $user->nom);
        $this->assertSame('Jean', $user->prenom);
        $this->assertNull(session('pronote_preview'), 'la preview doit être purgée après confirmation');
    }

    public function test_confirm_redoublant_incremente_la_promo(): void
    {
        $user = $this->etudiant(['email' => 'jean@example.fr', 'classe' => 'SIO1', 'promo' => 2027, 'date_entree' => '2024-09-02']);

        $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', '']], 'SIO1');
        $this->post(route('imports.pronote.confirm'));

        $user->refresh();
        $this->assertSame(2028, (int) $user->promo);
        $this->assertSame('2025-09-01', $user->date_entree->format('Y-m-d'));
        $this->assertSame('actif', $user->statut);
    }

    public function test_confirm_update_remplace_l_email_placeholder_import_local(): void
    {
        $user = $this->etudiant(['nom' => 'DUPONT', 'prenom' => 'Jean', 'email' => 'dupont@import.local', 'date_entree' => '2025-09-01']);

        $this->preview([['DUPONT Jean', 'vrai@example.fr', '01/09/2025', '']]);
        $this->post(route('imports.pronote.confirm'));

        $user->refresh();
        $this->assertSame('vrai@example.fr', $user->email);
        $this->assertSame('vrai@example.fr', $user->email_pronote);
    }

    public function test_confirm_update_conserve_un_email_reel(): void
    {
        $user = $this->etudiant(['nom' => 'DUPONT', 'prenom' => 'Jean', 'email' => 'perso@example.fr', 'date_entree' => '2025-09-01']);

        $this->preview([['DUPONT Jean', 'pronote@example.fr', '01/09/2025', '']]);
        $this->post(route('imports.pronote.confirm'));

        $this->assertSame('perso@example.fr', $user->fresh()->email);
    }

    public function test_confirm_demissionnaire_enregistre_statut_et_date_de_sortie(): void
    {
        $user   = $this->etudiant(['email' => 'jean@example.fr', 'date_entree' => '2025-09-01']);
        $sortie = Carbon::now()->subWeek();

        $this->preview([['DUPONT Jean', 'jean@example.fr', '01/09/2025', $sortie->format('d/m/Y')]]);
        $this->post(route('imports.pronote.confirm'));

        $user->refresh();
        $this->assertSame('demissionnaire', $user->statut);
        $this->assertSame($sortie->format('Y-m-d'), $user->date_sortie->format('Y-m-d'));
    }

    public function test_reimporter_le_meme_fichier_ne_cree_pas_de_doublon(): void
    {
        $lignes = [['DUPONT Jean', 'jean@example.fr', '01/09/2025', '']];

        $this->preview($lignes);
        $this->post(route('imports.pronote.confirm'));
        $this->preview($lignes);
        $this->post(route('imports.pronote.confirm'));

        $this->assertSame(1, User::where('email', 'jean@example.fr')->count());
    }

    public function test_une_erreur_en_cours_d_import_annule_tout(): void
    {
        $this->preview([
            ['DUPONT Jean', 'jean@example.fr', '01/09/2025', ''],
            ['MARTIN Paul', 'paul@example.fr', '01/09/2025', ''],
        ]);

        // Le second élève fait échouer l'invitation : le premier ne doit pas rester en base
        $this->mock(\App\Services\InvitationService::class, function ($mock) {
            $mock->shouldReceive('inviter')->once()->andReturnUsing(fn ($u) => ['lien' => 'x', 'mail_envoye' => true, 'raison' => null]);
            $mock->shouldReceive('inviter')->once()->andThrow(new \RuntimeException('panne'));
        });

        $this->withoutExceptionHandling();
        try {
            $this->post(route('imports.pronote.confirm'));
            $this->fail('exception attendue');
        } catch (\RuntimeException) {
        }

        $this->assertSame(0, User::whereIn('email', ['jean@example.fr', 'paul@example.fr'])->count());
        $this->assertNotNull(session('pronote_preview'), 'la prévisualisation reste disponible pour relancer');
    }

    // Accès -----------------------------------------------------------------

    public function test_un_etudiant_ne_peut_pas_importer(): void
    {
        $this->actingAs($this->etudiant());

        $this->post(route('imports.pronote.preview'), [
            'fichier' => $this->csv([]), 'classe_forcee' => 'SIO1',
        ])->assertRedirect(); // UnauthorizedException Spatie → redirection

        $this->assertNull(session('pronote_preview'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Entreprise;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConventionTest extends TestCase
{
    use RefreshDatabase;

    private function stageDe(User $etudiant): Stage
    {
        return Stage::factory()->create([
            'etudiant_id'   => $etudiant->id,
            'classe'        => 'SIO1',
            'entreprise_id' => Entreprise::factory()->create(['siret' => '12345678900011'])->id,
        ]);
    }

    public function test_l_etudiant_telecharge_la_convention_de_son_stage(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = $this->stageDe($etudiant);

        $response = $this->actingAs($etudiant)->get("/stages/{$stage->id}/pdf/convention");

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_un_etudiant_ne_peut_pas_telecharger_la_convention_d_un_autre(): void
    {
        $stage = $this->stageDe(User::factory()->etudiant()->create());

        $this->actingAs(User::factory()->etudiant()->create())
            ->get("/stages/{$stage->id}/pdf/convention")
            ->assertForbidden();
    }

    public function test_un_professeur_peut_telecharger_n_importe_quelle_convention(): void
    {
        $stage = $this->stageDe(User::factory()->etudiant()->create());

        $this->actingAs(User::factory()->professeur()->create())
            ->get("/stages/{$stage->id}/pdf/convention")
            ->assertOk();
    }

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $stage = $this->stageDe(User::factory()->etudiant()->create());

        $this->get("/stages/{$stage->id}/pdf/convention")->assertRedirect('/login');
    }

    public function test_voir_en_ligne_ou_telecharger(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = $this->stageDe($etudiant);

        $this->actingAs($etudiant);

        $this->assertStringStartsWith('inline', $this->get(route('pdf.convention', $stage))->headers->get('Content-Disposition'));

        $telechargement = $this->get(route('pdf.convention', [$stage, 'telecharger' => 1]));
        $this->assertStringStartsWith('attachment', $telechargement->headers->get('Content-Disposition'));
        $this->assertMatchesRegularExpression('/filename="[a-z0-9\-]+\.pdf"/', $telechargement->headers->get('Content-Disposition'));
    }

    public function test_l_etudiant_n_obtient_pas_la_convention_d_un_stage_rejete_mais_le_staff_oui(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $stage    = $this->stageDe($etudiant);
        $stage->update(['statut_validation' => 'rejete', 'note_rejet' => 'x']);

        $this->actingAs($etudiant)->get(route('pdf.convention', $stage))->assertForbidden();
        $this->actingAs(User::factory()->professeur()->create())->get(route('pdf.convention', $stage))->assertOk();
    }

    public function test_le_pdf_contient_les_informations_du_stage(): void
    {
        if (!trim((string) shell_exec('command -v pdftotext'))) {
            $this->markTestSkipped('pdftotext (poppler-utils) indisponible.');
        }

        $etudiant = User::factory()->etudiant()->create(['nom' => 'DUPONTEL', 'prenom' => 'Camille', 'promo' => 2027]);
        $entreprise = Entreprise::factory()->create(['raison_sociale' => 'ACME INFORMATIQUE', 'siret' => '12345678900011']);
        $stage = Stage::factory()->create([
            'etudiant_id' => $etudiant->id, 'entreprise_id' => $entreprise->id, 'classe' => 'SIO2',
            'annee_scolaire' => '2026-2027', 'date_debut' => '2026-11-02', 'date_fin' => '2026-12-11',
        ]);

        $pdf = tempnam(sys_get_temp_dir(), 'conv');
        file_put_contents($pdf, $this->actingAs($etudiant)->get(route('pdf.convention', $stage))->getContent());
        // Espaces et retours à la ligne du PDF normalisés : seul le contenu compte
        $texte = preg_replace('/\s+/', ' ', (string) shell_exec('pdftotext -layout '.escapeshellarg($pdf).' - 2>/dev/null'));
        unlink($pdf);

        foreach (['DUPONTEL', 'Camille', 'ACME INFORMATIQUE', 'lundi 2 novembre 2026', 'vendredi 11 décembre 2026', '2026/ 2027'] as $attendu) {
            $this->assertStringContainsString($attendu, $texte);
        }
    }
}

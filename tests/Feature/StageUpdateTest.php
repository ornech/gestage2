<?php

namespace Tests\Feature;

use App\Models\Employe;
use App\Models\Entreprise;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_modifier_un_stage_le_remet_en_attente_de_validation_et_de_signature(): void
    {
        $etudiant   = User::factory()->etudiant()->create();
        $entreprise = Entreprise::factory()->create();
        $maitre     = Employe::factory()->create(['entreprise_id' => $entreprise->id]);
        $stage      = Stage::factory()->create([
            'etudiant_id'        => $etudiant->id,
            'entreprise_id'      => $entreprise->id,
            'maitre_de_stage_id' => $maitre->id,
            'statut_validation'  => 'rejete',
            'statut_convention'  => 'validee',
        ]);

        $this->actingAs($etudiant)->put(route('stages.update', $stage), [
            'date_debut'         => '2026-11-02',
            'duree'              => 5,
            'maitre_de_stage_id' => $maitre->id,
        ])->assertRedirect(route('stages.index'));

        $stage->refresh();
        $this->assertSame('en_attente', $stage->statut_validation);
        $this->assertSame('a_faire_signer', $stage->statut_convention);
        $this->assertSame('2026-12-07', $stage->date_fin->format('Y-m-d'));
    }

    public function test_un_etudiant_ne_peut_pas_modifier_un_stage_valide(): void
    {
        $etudiant   = User::factory()->etudiant()->create();
        $entreprise = Entreprise::factory()->create();
        $maitre     = Employe::factory()->create(['entreprise_id' => $entreprise->id]);
        $stage      = Stage::factory()->create([
            'etudiant_id' => $etudiant->id, 'entreprise_id' => $entreprise->id,
            'maitre_de_stage_id' => $maitre->id, 'statut_validation' => 'valide',
        ]);

        $this->actingAs($etudiant)->put(route('stages.update', $stage), [
            'date_debut' => '2026-11-02', 'duree' => 5, 'maitre_de_stage_id' => $maitre->id,
        ])->assertForbidden();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StagePolicyTest extends TestCase
{
    use RefreshDatabase;

    private function stageDe(User $etudiant, string $statut): Stage
    {
        return Stage::factory()->create([
            'etudiant_id'       => $etudiant->id,
            'statut_validation' => $statut,
        ]);
    }

    public function test_etudiant_peut_modifier_son_stage_en_attente_ou_rejete(): void
    {
        $etudiant = User::factory()->etudiant()->create();

        foreach (['en_attente', 'rejete'] as $statut) {
            $this->assertTrue($etudiant->can('update', $this->stageDe($etudiant, $statut)), $statut);
        }
    }

    public function test_etudiant_ne_peut_pas_modifier_un_stage_valide(): void
    {
        $etudiant = User::factory()->etudiant()->create();

        $this->assertFalse($etudiant->can('update', $this->stageDe($etudiant, 'valide')));
    }

    public function test_etudiant_ne_peut_ni_voir_ni_modifier_le_stage_d_un_autre(): void
    {
        $etudiant = User::factory()->etudiant()->create();
        $autre    = User::factory()->etudiant()->create();
        $stage    = $this->stageDe($autre, 'en_attente');

        $this->assertFalse($etudiant->can('view', $stage));
        $this->assertFalse($etudiant->can('update', $stage));
        $this->assertFalse($etudiant->can('manageJournal', $stage));
    }

    public function test_etudiant_gere_le_journal_de_son_propre_stage(): void
    {
        $etudiant = User::factory()->etudiant()->create();

        $this->assertTrue($etudiant->can('manageJournal', $this->stageDe($etudiant, 'valide')));
    }

    public function test_professeur_et_admin_peuvent_tout_voir_et_modifier(): void
    {
        $stage = $this->stageDe(User::factory()->etudiant()->create(), 'valide');

        foreach ([User::factory()->professeur()->create(), User::factory()->administrateur()->create()] as $staff) {
            $this->assertTrue($staff->can('view', $stage));
            $this->assertTrue($staff->can('update', $stage));
            $this->assertTrue($staff->can('manageJournal', $stage));
            $this->assertTrue($staff->can('validate', Stage::class));
        }
    }

    public function test_etudiant_ne_peut_pas_valider(): void
    {
        $this->assertFalse(User::factory()->etudiant()->create()->can('validate', Stage::class));
    }

    public function test_seul_un_etudiant_peut_creer_un_stage(): void
    {
        $this->assertTrue(User::factory()->etudiant()->create()->can('create', Stage::class));
        $this->assertFalse(User::factory()->professeur()->create()->can('create', Stage::class));
        $this->assertFalse(User::factory()->administrateur()->create()->can('create', Stage::class));
    }

    public function test_restore_et_force_delete_sont_interdits_a_tous(): void
    {
        $admin = User::factory()->administrateur()->create();
        $stage = $this->stageDe(User::factory()->etudiant()->create(), 'en_attente');

        $this->assertFalse($admin->can('restore', $stage));
        $this->assertFalse($admin->can('forceDelete', $stage));
    }
}

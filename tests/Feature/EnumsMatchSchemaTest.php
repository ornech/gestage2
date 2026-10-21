<?php

namespace Tests\Feature;

use App\Enums\StatutConvention;
use App\Enums\StatutConventionPapier;
use App\Enums\StatutEtudiant;
use App\Enums\StatutValidation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verrou anti-dérive : les enums PHP doivent rester identiques aux ENUM MySQL.
 * (Une valeur écrite en base mais absente de l'ENUM provoque « Data truncated » en production.)
 */
class EnumsMatchSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function valeursEnumMysql(string $table, string $colonne): array
    {
        $type = DB::selectOne("SHOW COLUMNS FROM `$table` WHERE Field = ?", [$colonne])->Type;
        preg_match_all("/'([^']+)'/", $type, $m);

        return $m[1];
    }

    private function valeurs(string $enum): array
    {
        return array_map(fn ($c) => $c->value, $enum::cases());
    }

    public function test_statut_validation(): void
    {
        $this->assertEqualsCanonicalizing(
            $this->valeursEnumMysql('stages', 'statut_validation'),
            $this->valeurs(StatutValidation::class)
        );
    }

    public function test_statut_convention_du_stage(): void
    {
        $this->assertEqualsCanonicalizing(
            $this->valeursEnumMysql('stages', 'statut_convention'),
            $this->valeurs(StatutConvention::class)
        );
    }

    public function test_statut_convention_papier(): void
    {
        $this->assertEqualsCanonicalizing(
            $this->valeursEnumMysql('conventions_papier', 'statut'),
            $this->valeurs(StatutConventionPapier::class)
        );
    }

    public function test_statut_etudiant(): void
    {
        $this->assertEqualsCanonicalizing(
            $this->valeursEnumMysql('users', 'statut'),
            $this->valeurs(StatutEtudiant::class)
        );
    }
}

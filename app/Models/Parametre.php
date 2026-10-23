<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $primaryKey = 'cle';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['cle', 'valeur', 'description'];

    public static function get(string $cle, mixed $default = null): mixed
    {
        return static::find($cle)?->valeur ?? $default;
    }

    /**
     * Année scolaire en cours, au format « 2026-2027 » : elle bascule d'elle-même le 1er septembre.
     * Aucune valeur n'est stockée, donc rien à penser à changer à la rentrée.
     */
    public static function anneeActive(): string
    {
        return Stage::anneeScolaireDu(now());
    }

    /** Promos présentes dans l'année scolaire donnée : ['SIO1' => promo, 'SIO2' => promo]. */
    public static function promos(string $annee): array
    {
        $debut = (int) explode('-', $annee)[0];

        return ['SIO1' => $debut + 2, 'SIO2' => $debut + 1];
    }

    public static function set(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }

    public static function isOpen(string $cle): bool
    {
        return static::get($cle, '0') === '1';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationStage extends Model
{
    protected $table = 'configurations_stage';

    protected $fillable = [
        'annee_scolaire',
        'classe',
        'prof_principal_id',
        'stage_date_debut',
        'stage_date_fin',
    ];

    protected $casts = [
        'stage_date_debut' => 'date',
        'stage_date_fin'   => 'date',
    ];

    public function profPrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prof_principal_id');
    }

    public function getDureeEnSemainesAttribute(): ?int
    {
        if (!$this->stage_date_debut || !$this->stage_date_fin) return null;
        // La fin est stockée comme début + N×7 - 3j (lundi→vendredi)
        // donc (diffInDays + 3) / 7 = N exactement
        return (int) (($this->stage_date_debut->diffInDays($this->stage_date_fin) + 3) / 7);
    }

    public static function forAnnee(string $annee): \Illuminate\Support\Collection
    {
        return static::where('annee_scolaire', $annee)
            ->with('profPrincipal')
            ->get()
            ->keyBy('classe');
    }

    public static function toutesLesAnnees(): \Illuminate\Support\Collection
    {
        return static::distinct()
            ->orderByDesc('annee_scolaire')
            ->pluck('annee_scolaire')
            ->unique();
    }
}

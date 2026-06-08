<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entreprise extends Model
{
    /** @use HasFactory<\Database\Factories\EntrepriseFactory> */
    use HasFactory;

    // Champs autorisés à être remplis (Mass Assignment)
    protected $fillable = [
        'raison_sociale',
        'siret',
        'code_naf',
        'adresse',
        'complement_adresse',
        'code_postal',
        'ville',
        'departement_code',
        'telephone',
        'email',
        'type',
        'effectif',
        'est_valide',
        'user_id',
    ];

    // Conversion automatique des types
    protected $casts = [
        'est_valide' => 'boolean',
        'effectif' => 'integer',
    ];

    // Relation : Une entreprise appartient à un utilisateur (créateur)
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employes(): HasMany
    {
        return $this->hasMany(Employe::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class);
    }

}

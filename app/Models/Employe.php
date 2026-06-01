<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employe extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'entreprise_id',
        'creator_id',
        'nom',
        'prenom',
        'email',
        'email_supprime_at',
        'telephone',
        'service',
        'fonction',
        'contact_valide',
        'newsletter',
        'jury',
    ];

    protected $casts = [
        'email_supprime_at' => 'datetime',
        'newsletter'        => 'boolean',
        'jury'              => 'boolean',
        'contact_valide'    => 'boolean',
    ];
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class, 'maitre_de_stage_id');
    }
}
  

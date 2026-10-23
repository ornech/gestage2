<?php

namespace App\Models;

use App\Enums\StatutConventionPapier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConventionPapier extends Model
{
    protected $table    = 'conventions_papier';
    protected $fillable = ['etudiant_id', 'annee_scolaire', 'statut'];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function statutSuivant(): ?string
    {
        return StatutConventionPapier::tryFrom($this->statut)?->suivant()?->value;
    }

    public function statutPrecedent(): ?string
    {
        return StatutConventionPapier::tryFrom($this->statut)?->precedent()?->value;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConventionPapier extends Model
{
    protected $table    = 'conventions_papier';
    protected $fillable = ['etudiant_id', 'statut'];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function statutSuivant(): ?string
    {
        return match($this->statut) {
            'hors_app'   => 'en_attente',
            'en_attente' => 'validee',
            default      => null,
        };
    }

    public function statutPrecedent(): ?string
    {
        return match($this->statut) {
            'en_attente' => 'hors_app',
            'validee'    => 'en_attente',
            default      => null,
        };
    }
}

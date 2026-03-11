<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'entreprise_id',
        'maitre_de_stage_id',
        'etudiant_id',
        'professeur_id',
        'classe',

    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function maitreDeStage(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

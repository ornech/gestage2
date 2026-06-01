<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'statut_validation',
        'note_rejet',
        'statut_convention',
        'mail_bienvenue_envoye_at',
    ];

    protected $casts = [
        'date_debut'               => 'date',
        'date_fin'                 => 'date',
        'mail_bienvenue_envoye_at' => 'datetime',
    ];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function maitreDeStage(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'maitre_de_stage_id');
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}

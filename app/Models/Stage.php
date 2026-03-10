<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

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
        'date_fin' => 'date'
        ];

    public function entreprise(): BelongsTo
     { 
        return $this->belongsTo(Entreprise::class, 'entreprise_id'); 
     }

    public function maitreDeStage()
    {
        return $this->belongsTo(Employe::class, 'maitre_de_stage_id');
    }

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function professeur()
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'employe_id',
        'maitre_de_stage_id',
        'etudiant_id',
        'professeur_id',
        'classe',

    ];
    protected $casts = ['date_debut' => 'date', 'date_fin' => 'date'];

    public function entreprise(): BelongsTo { return $this->belongsTo(Entreprise::class); }

    public function maitreDeStage()
    {
        return $this->belongsTo(Employe::class, 'idMaitreDeStage');
    }

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'idEtudiant');
    }

    public function professeur()
    {
        return $this->belongsTo(User::class, 'idProfesseur');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'dateDebut',
        'dateFin',
        'idEmploye',
         'idMaitreDeStage',
        'idEtudiant',
        'idProfesseur',
        'classe',
    
    ];
       public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'idEntreprise');
    }

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

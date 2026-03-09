<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    protected $fillable = [
        'idEntreprise',
        'nom',
        'prenom',
        'email',
        'telephone',
        'service',
        'fonction',
        'created_userid',
        'created_date',
        'contact_valide',
        'newsletter',
        'jury',
    ];
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'idEntreprise');
    }

    public function stages()
    {
        return $this->hasMany(Stage::class, 'idMaitreDeStage');
    }
}

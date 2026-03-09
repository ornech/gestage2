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
        'idEmploye',
        'idUser',
    ];
    // Un stage appartient à un employé
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
    // Un stage peut être associé à un utilisateur (étudiant)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

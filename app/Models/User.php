<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;


protected $fillable = [
    'nom',
    'prenom',
    'email',
    'password',
    'date_entree',
    'telephone',
    'spe',
    'classe',
    'promo',
    'idTuteur',
    'idClasse',
    'role',
];
protected $hidden = [
    'password',
    'remember_token',
];


    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_entree' => 'date',
        'dateFirstConn' => 'datetime',
        'inactif' => 'boolean',
        'deleted' => 'boolean',
    ];

    public function isProfesseur(): bool
    {
        return $this->role === 'Professeur';
    }

    public function isEtudiant(): bool
    {
        return $this->role === 'Etudiant';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Administrateur';
    }
}

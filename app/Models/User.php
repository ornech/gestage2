<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // 1. L'import doit être présent
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

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
        'tuteur_id',
        'classe_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_entree' => 'date',
    ];
    // Un utilisateur (étudiant) peut avoir plusieurs stages
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }
    // On ajoute une méthode pour vérifier si l'utilisateur est un étudiant (basé sur son rôle ou d'autres critères)
    public function isEtudiant()
    {
    return $this->prenom === 'Étudiant' || $this->email === 'etudiant@test.com';
    }

}


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
        'idTuteur',
        'idClasse',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_entree' => 'date',
        'dateFirstConn' => 'datetime',
        'inactif' => 'boolean',
        'deleted' => 'boolean',
    ];
}


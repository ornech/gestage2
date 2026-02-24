<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';

    protected $fillable = [
        'idTuteur',
        'idClasse',
        'nom',
        'prenom',
        'email',
        'date_entree',
        'telephone',
        'spe',
        'classe',
        'promo',
        'login',
        'password',
        'password_reset',
        'statut',
        'inactif',
        'dateFirstConn',
        'isDeleted',
    ];

    public $timestamps = true;
}

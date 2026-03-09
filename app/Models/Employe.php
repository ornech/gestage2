<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'entreprise',
        'service',
        'fonction',
    ];
    //un employé peut avoir plusieurs stages
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
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

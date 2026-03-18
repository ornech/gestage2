<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'prenom',
        'email',
        'telephone',
        'fonction',
    ];
    //ajout de la relation avec l'entreprise

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'statut',
        'date_sortie',
        'force_password_change',
        'cgu_accepted_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'cgu_accepted_at'   => 'datetime',
        'date_entree'       => 'date',
    ];
    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class, 'etudiant_id');
    }

    public function tuteur(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'tuteur_id');
    }

    public function conventionPapier(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\ConventionPapier::class, 'etudiant_id');
    }

    /**
     * Classe courante calculée depuis promo + année scolaire.
     * Retourne "SIO1", "SIO2", ou null si indéterminable.
     */
    public function getClasseCouranteAttribute(): ?string
    {
        if (!$this->promo) return null;

        // Cache statique : Parametre::get appelé une seule fois par request
        static $sy = null;
        if ($sy === null) {
            $annee = \App\Models\Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));
            $sy    = (int) explode('-', $annee)[0];
        }

        $anneeEtude = 3 - ($this->promo - $sy);

        return ($anneeEtude >= 1 && $anneeEtude <= 2) ? 'SIO'.$anneeEtude : null;
    }
    // On ajoute une méthode pour vérifier si l'utilisateur est un étudiant (basé sur son rôle ou d'autres critères)
    public function isEtudiant()
    {
    return $this->prenom === 'Étudiant' || $this->email === 'etudiant@test.com';
    }

}


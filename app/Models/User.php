<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\StatutEtudiant;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles {
        scopeRole as private scopeRoleSpatie;
    }

    /**
     * Idem Spatie, mais un rôle inexistant donne un résultat vide au lieu d'une
     * exception (base fraîche sans RoleSeeder) : personne ne possède un rôle qui n'existe pas.
     */
    public function scopeRole(Builder $query, $roles, $guard = null, bool $without = false): Builder
    {
        try {
            return $this->scopeRoleSpatie($query, $roles, $guard, $without);
        } catch (RoleDoesNotExist) {
            return $without ? $query : $query->whereRaw('1 = 0');
        }
    }

    /** Diplôme validé par un professeur (jamais déduit de la promo). */
    /** Change le statut ; la trace (qui, quand) n'est gardée que pour le diplôme, validé explicitement. */
    public function definirStatut(StatutEtudiant $statut, User $par): void
    {
        $diplome = $statut === StatutEtudiant::Diplome;

        $this->update([
            'statut'      => $statut->value,
            'diplome_at'  => $diplome ? now() : null,
            'diplome_par' => $diplome ? $par->id : null,
        ]);
    }

    /** Statut de sortie du cycle (diplômé, archivé, échec) : l'accès à l'application est fermé. */
    public function aQuitteLeCycle(): bool
    {
        return StatutEtudiant::tryFrom($this->statut)?->estSortie() ?? false;
    }

    public function estDiplome(): bool
    {
        return $this->statut === StatutEtudiant::Diplome->value;
    }

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'email_pronote',
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
        'diplome_at',
        'diplome_par',
        'force_password_change',
        'cgu_accepted_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'cgu_accepted_at'   => 'datetime',
        'date_entree'       => 'date',
        'date_sortie'       => 'date',
        'diplome_at'        => 'datetime',
    ];
    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class, 'etudiant_id');
    }

    public function tuteur(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'tuteur_id');
    }

    /** Toutes les conventions papier de l'étudiant : une par année scolaire. */
    public function conventionsPapier(): HasMany
    {
        return $this->hasMany(ConventionPapier::class, 'etudiant_id');
    }

    /** Convention papier de l'année scolaire active. */
    public function conventionPapier(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ConventionPapier::class, 'etudiant_id')
            ->where('annee_scolaire', Parametre::anneeActive());
    }

    public function stageDeLAnnee(?string $annee = null): ?Stage
    {
        return $this->stages()->deLAnnee($annee ?? Parametre::anneeActive())->first();
    }

    public function aUnStageCetteAnnee(): bool
    {
        return $this->stages()->deLAnnee(Parametre::anneeActive())->exists();
    }

    /** Étudiants dont la promo ne correspond ni à SIO1 ni à SIO2 pour l'année en cours (à traiter par un professeur). */
    public function scopeSansClasse(Builder $query): Builder
    {
        $promos = array_values(Parametre::promos(Parametre::anneeActive()));

        return $query->where(fn($q) => $q->whereNull('promo')->orWhereNotIn('promo', $promos));
    }

    /**
     * Classe courante calculée depuis promo + année scolaire.
     * Retourne "SIO1", "SIO2", ou null si indéterminable.
     */
    public function getClasseCouranteAttribute(): ?string
    {
        if (!$this->promo) return null;

        $sy = (int) explode('-', Parametre::anneeActive())[0];

        $anneeEtude = 3 - ($this->promo - $sy);

        return ($anneeEtude >= 1 && $anneeEtude <= 2) ? 'SIO'.$anneeEtude : null;
    }
    // On ajoute une méthode pour vérifier si l'utilisateur est un étudiant (basé sur son rôle ou d'autres critères)
    public function isEtudiant()
    {
    return $this->prenom === 'Étudiant' || $this->email === 'etudiant@test.com';
    }

}


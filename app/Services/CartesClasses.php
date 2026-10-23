<?php

namespace App\Services;

use App\Models\ConfigurationStage;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Chiffres des cartes SIO1 / SIO2 des tableaux de bord (administrateur et professeur),
 * pour l'année scolaire active : les stages et conventions papier d'autres années ne comptent pas.
 */
class CartesClasses
{
    /** Étudiants actifs sans classe cette année (diplômés non marqués, redoublants non traités, promo vide…). */
    public function orphelins(): Collection
    {
        return User::role('Etudiant')->where('statut', 'actif')->sansClasse()->orderBy('nom')->orderBy('prenom')->get();
    }

    public function pourAnneeActive(): array
    {
        $annee = Parametre::anneeActive();
        $cartes = [];

        foreach (Parametre::promos($annee) as $classe => $promo) {
            $etudiants = User::role('Etudiant')
                ->where('promo', $promo)
                ->whereIn('statut', ['actif', 'demissionnaire'])
                ->with(['conventionPapier', 'stages' => fn($q) => $q->deLAnnee($annee)])
                ->get();

            $actifs  = $etudiants->where('statut', 'actif');
            $stages  = $etudiants->flatMap->stages;
            // Convention papier remise, stage pas encore saisi dans l'application
            $papiers = $etudiants->filter(fn($u) => $u->stages->isEmpty() && $u->conventionPapier)
                ->map->conventionPapier;

            $nbConventions = fn(string $stageStatut, string $papierStatut) =>
                $stages->where('statut_convention', $stageStatut)->count()
                + $papiers->where('statut', $papierStatut)->count();

            $cartes[$classe] = [
                'config'          => ConfigurationStage::where('annee_scolaire', $annee)
                                        ->where('classe', $classe)
                                        ->with('profPrincipal')
                                        ->first(),
                'total'           => $actifs->count(),
                'slam'            => $actifs->where('spe', 'SLAM')->count(),
                'sisr'            => $actifs->where('spe', 'SISR')->count(),
                'actifs'          => $actifs->count(),
                'demissionnaires' => $etudiants->where('statut', 'demissionnaire')->count(),
                'a_faire_signer'  => $nbConventions('a_faire_signer', 'hors_app'),
                'en_attente'      => $nbConventions('en_attente', 'en_attente'),
                'remis'           => $nbConventions('validee', 'validee'),
                'sans_stage'      => $actifs->filter(fn($u) => $u->stages->isEmpty() && !$u->conventionPapier)->count(),
                'papier_pending'  => $actifs->filter(fn($u) => $u->stages->isEmpty() && $u->conventionPapier)->count(),
            ];
        }

        return $cartes;
    }
}

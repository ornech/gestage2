<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationStage;
use App\Models\ConventionPapier;
use App\Models\Parametre;
use App\Models\Stage;
use App\Models\User;

class ProfesseurController extends Controller
{
    public function dashboard()
    {
        $annee  = Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));
        $syInt  = (int) explode('-', $annee)[0];

        $promoSio1 = $syInt + 2;
        $promoSio2 = $syInt + 1;

        $aValider = Stage::whereHas('etudiant',
            fn($q) => $q->whereIn('promo', [$promoSio1, $promoSio2])
        )->where('statut_validation', 'en_attente')->count();

        // ── Données par classe ───────────────────────────────────────────
        $cartesSio = [];

        foreach (['SIO1' => $promoSio1, 'SIO2' => $promoSio2] as $classe => $promo) {

            $etudiants = User::role('Etudiant')
                ->where('promo', $promo)
                ->whereIn('statut', ['actif', 'demissionnaire'])
                ->with('conventionPapier')
                ->get();

            $actifs        = $etudiants->whereIn('statut', ['actif']);
            
            $demissionnaires = $etudiants->where('statut', 'demissionnaire');

            // Conventions numériques
            $baseStages = Stage::whereHas('etudiant', fn($q) => $q->where('promo', $promo));

            // Conventions papier sans stage
            $idEtudiants = $etudiants->pluck('id');
            $papierFn    = fn($statut) => ConventionPapier::whereIn('etudiant_id', $idEtudiants)
                ->where('statut', $statut)
                ->whereDoesntHave('etudiant.stages')
                ->count();

            $cartesSio[$classe] = [
                'config'           => ConfigurationStage::where('annee_scolaire', $annee)
                                        ->where('classe', $classe)
                                        ->with('profPrincipal')
                                        ->first(),
                // Effectifs
                'total'            => $etudiants->whereIn('statut', ['actif'])->count(),
                'slam'             => $etudiants->where('spe', 'SLAM')->whereIn('statut', ['actif'])->count(),
                'sisr'             => $etudiants->where('spe', 'SISR')->whereIn('statut', ['actif'])->count(),
                // Statuts administratifs
                'actifs'           => $actifs->count(),
                
                'demissionnaires'  => $demissionnaires->count(),
                // Conventions (numérique + papier)
                'a_faire_signer'   => (clone $baseStages)->where('statut_convention', 'a_faire_signer')->count()
                                      + $papierFn('hors_app'),
                'en_attente'       => (clone $baseStages)->where('statut_convention', 'en_attente')->count()
                                      + $papierFn('en_attente'),
                'remis'            => (clone $baseStages)->where('statut_convention', 'validee')->count()
                                      + $papierFn('validee'),
                // Stages manquants
                'sans_stage'       => $etudiants->whereIn('statut', ['actif'])
                                        ->filter(fn($u) => !Stage::where('etudiant_id', $u->id)->exists() && !$u->conventionPapier)
                                        ->count(),
                'papier_pending'   => $etudiants->whereIn('statut', ['actif'])
                                        ->filter(fn($u) => !Stage::where('etudiant_id', $u->id)->exists() && $u->conventionPapier)
                                        ->count(),
            ];
        }

        return view('dashboards.professeur', compact('annee', 'aValider', 'cartesSio'));
    }
}

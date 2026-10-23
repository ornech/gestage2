<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use App\Models\Stage;
use App\Services\CartesClasses;

class AdminDashboardController extends Controller
{
    public function dashboard(CartesClasses $cartes)
    {
        $annee = Parametre::anneeActive();

        // Conventions à faire signer par l'employeur (action directe du prof)
        $aValider = Stage::deLAnnee($annee)
            ->whereHas('etudiant', fn($q) => $q->whereIn('promo', Parametre::promos($annee)))
            ->where('statut_convention', 'a_faire_signer')
            ->count();

        return view('dashboards.admin', [
            'annee'     => $annee,
            'aValider'  => $aValider,
            'cartesSio' => $cartes->pourAnneeActive(),
            'orphelins' => $cartes->orphelins(),
        ]);
    }
}

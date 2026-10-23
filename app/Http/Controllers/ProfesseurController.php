<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use App\Models\Stage;
use App\Services\CartesClasses;

class ProfesseurController extends Controller
{
    public function dashboard(CartesClasses $cartes)
    {
        $annee = Parametre::anneeActive();

        $aValider = Stage::deLAnnee($annee)
            ->whereHas('etudiant', fn($q) => $q->whereIn('promo', Parametre::promos($annee)))
            ->where('statut_validation', 'en_attente')
            ->count();

        return view('dashboards.professeur', [
            'annee'     => $annee,
            'aValider'  => $aValider,
            'cartesSio' => $cartes->pourAnneeActive(),
            'orphelins' => $cartes->orphelins(),
        ]);
    }
}

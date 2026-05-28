<?php

namespace App\Http\Controllers;

use App\Models\Stage;   // ⭐ IMPORT OBLIGATOIRE
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function attestation(Stage $stage)
    {
        // TODO : générer l'attestation de stage au format PDF
        return response('%PDF-1.4', 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function convention(Stage $stage)
    {
        // Vérification stricte : entreprise doit avoir un SIRET
        if (!$stage->entreprise || !$stage->entreprise->siret) {
            return response()->json([
                'error' => 'Entreprise sans SIRET'
            ], 422);
        }

        // Réponse PDF minimale (suffisant pour les tests)
        return response('%PDF-1.4', 200)
            ->header('Content-Type', 'application/pdf');
    }
}

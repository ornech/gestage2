<?php

namespace App\Http\Controllers;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    //
     public function store(Request $request)
    {
        Entreprise::create([
          'raison_sociale' => $request->raison_sociale,
         'siret' => $request->siret,
            'est_valide' => 0,
        ]);

        return response()->json(['message' => 'ok']);
    }
        public function importSiret(Request $request, SireneClient $sirene)
    {
        $siret = $request->siret;

        $data = $sirene->getBySiret($siret);

        if (!$data || !isset($data['etablissement'])) {
            return response()->json(['error' => 'SIRET introuvable'], 404);
        }

        $etab = $data['etablissement'];

        $entreprise = Entreprise::updateOrCreate(
            ['siret' => $siret],
            [
                'raison_sociale' => $etab['uniteLegale']['denominationUniteLegale'] ?? null,
                'adresse' => $etab['adresseEtablissement']['libelleVoieEtablissement'] ?? null,
                'code_postal' => $etab['adresseEtablissement']['codePostalEtablissement'] ?? null,
                'ville' => $etab['adresseEtablissement']['libelleCommuneEtablissement'] ?? null,
                'est_valide' => true,
            ]
        );

        return response()->json([
            'message' => 'Entreprise importée',
            'entreprise' => $entreprise
        ]);
    }
}

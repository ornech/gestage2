<?php

namespace App\Http\Controllers;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Services\SireneClient;
use App\Models\Stage;
use App\Models\Employe;


class CompanyController extends Controller
{
public function index()
{
    $entreprises = Entreprise::all();
    $nbEntreprises = Entreprise::count();
    $nbStages = Stage::count();
    $nbContacts = Employe::count(); // tes "contacts" = employés

    return view('entreprises.index', compact(
        'entreprises',
        'nbEntreprises',
        'nbStages',
        'nbContacts'
    ));
}


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
    public function show(Entreprise $entreprise)
{
    $entreprise->load([
        'employes',              // contacts
        'stages.etudiant',       // étudiant du stage
        'stages.maitreDeStage'   // maître de stage
    ]);

    return view('entreprises.show', compact('entreprise'));
}

}

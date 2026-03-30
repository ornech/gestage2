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
        $entreprises = Entreprise::paginate(15);

        $nbEntreprises = Entreprise::count();
        $nbStages = Stage::count();
        $nbContacts = Employe::count();

        return view('entreprises.index', compact(
            'entreprises',
            'nbEntreprises',
            'nbStages',
            'nbContacts'
        ));
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $entreprise->update([
            'raison_sociale' => $request->raison_socialiale,
            'adresse' => $request->adresse,
            'code_postal' => $request->code_postal,
            'ville' => $request->ville,
            'siret' => $request->siret,
        ]);

        return redirect()->route('entreprises.show', $entreprise)
                         ->with('success', 'Entreprise mise à jour.');
    }

    public function store(Request $request)
    {
        Entreprise::create([
            'raison_sociale' => $request->raison_sociale,
            'adresse' => $request->adresse,
            'code_postal' => $request->code_postal,
            'ville' => $request->ville,
            'siret' => $request->siret,
            'est_valide' => false,
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

        $normalized = [
            'nom' => $etab['uniteLegale']['denominationUniteLegale'] ?? null,
            'adresse' => $etab['adresseEtablissement']['libelleVoieEtablissement'] ?? null,
            'cp' => $etab['adresseEtablissement']['codePostalEtablissement'] ?? null,
            'ville' => $etab['adresseEtablissement']['libelleCommuneEtablissement'] ?? null,
            'siret' => $siret,
        ];

        $entreprise = Entreprise::where('siret', $siret)->first();

        return response()->json([
            'message' => 'Entreprise importée',
            'data' => $normalized,
            'entreprise' => $entreprise
        ]);
    }

    public function show(Entreprise $entreprise)
    {
        $entreprise->load([
            'employes',
            'stages.etudiant',
            'stages.maitreDeStage'
        ]);

        return view('entreprises.show', compact('entreprise'));
    }

    public function importForm()
    {
        return view('entreprises.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'siret' => 'required|digits:14'
        ]);

        $client = new SireneClient();
        $data = $client->getBySiret($request->siret);

        if (!$data || !isset($data['etablissement'])) {
            return back()->withErrors(['siret' => 'Aucune entreprise trouvée pour ce SIRET.']);
        }

        $etab = $data['etablissement'];

        $normalized = [
            'nom' => $etab['uniteLegale']['denominationUniteLegale'] ?? null,
            'adresse' => $etab['adresseEtablissement']['libelleVoieEtablissement'] ?? null,
            'cp' => $etab['adresseEtablissement']['codePostalEtablissement'] ?? null,
            'ville' => $etab['adresseEtablissement']['libelleCommuneEtablissement'] ?? null,
            'siret' => $request->siret,
        ];

        $entreprise = Entreprise::where('siret', $request->siret)->first();

        return view('entreprises.import-result', [
            'data' => $normalized,
            'entreprise' => $entreprise
        ]);
    }
}

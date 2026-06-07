<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Services\SireneClient;
use App\Models\Stage;
use App\Models\Employe;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        // Toutes les entreprises chargées d'un coup — filtrage en temps réel côté client
        $entreprises = Entreprise::withCount(['stages', 'employes'])
            ->orderBy('raison_sociale')
            ->get();

        $nbEntreprises = $entreprises->count();
        $nbStages      = Stage::count();
        $nbContacts    = Employe::count();

        return view('entreprises.index', compact(
            'entreprises', 'nbEntreprises', 'nbStages', 'nbContacts'
        ));
    }



    public function update(Request $request, Entreprise $entreprise)
    {
        $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'adresse'        => 'nullable|string|max:255',
            'code_postal'    => 'nullable|string|max:20',
            'ville'          => 'required|string|max:100',
            'siret'          => 'nullable|digits:14',
            'telephone'      => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
        ]);

        $entreprise->update([
            'raison_sociale' => $request->raison_sociale,
            'adresse' => $request->adresse,
            'code_postal' => $request->code_postal,
            'ville' => $request->ville,
            'siret' => $request->siret,
            'telephone' => $request->telephone,
            'email' => $request->email,
        ]);

        return redirect()->route('entreprises.show', $entreprise)
                         ->with('success', 'Entreprise mise à jour.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'ville'          => 'required|string|max:100',
            'siret'          => 'nullable|digits:14',
            'code_postal'    => 'nullable|string|max:20',
            'telephone'      => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'code_naf'       => 'nullable|string|max:10',
        ]);

        $entreprise = Entreprise::create([
            'raison_sociale'  => strtoupper($request->raison_sociale),
            'adresse'         => $request->adresse,
            'code_postal'     => $request->code_postal,
            'ville'           => strtoupper($request->ville),
            'siret'           => $request->siret ?: null,
            'telephone'       => $request->telephone,
            'email'           => $request->email,
            'code_naf'        => $request->code_naf,
            'departement_code'=> $request->code_postal ? substr($request->code_postal, 0, 2) : null,
            'est_valide'      => true,
            'user_id'         => auth()->id(),
        ]);

        return redirect()
            ->route('entreprises.show', $entreprise)
            ->with('success', 'Entreprise créée avec succès.');
    }

    public function importSiret(Request $request, SireneClient $sirene)
    {
        $siret = trim($request->siret);

        $data = $sirene->getBySiret($request->siret);


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

        // Téléphones des contacts : visibles par le staff, et par l'étudiant pour son propre maître de stage
        $monMaitreDeStageIds = auth()->user()->hasRole('Etudiant')
            ? auth()->user()->stages()->whereNotNull('maitre_de_stage_id')->pluck('maitre_de_stage_id')->all()
            : [];

        return view('entreprises.show', compact('entreprise', 'monMaitreDeStageIds'));
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

    // NORMALISATION DU SIRET
    $siret = trim($request->siret);

    $client = new SireneClient();
    $data = $client->getBySiret($siret);

    if (!$data || !isset($data['etablissement'])) {
    return view('entreprises.import-not-found', [
        'siret' => $request->siret
    ]);
}


    $etab = $data['etablissement'];

    $normalized = [
        'nom' => $etab['uniteLegale']['denominationUniteLegale'] ?? null,
        'adresse' => $etab['adresseEtablissement']['libelleVoieEtablissement'] ?? null,
        'cp' => $etab['adresseEtablissement']['codePostalEtablissement'] ?? null,
        'ville' => $etab['adresseEtablissement']['libelleCommuneEtablissement'] ?? null,
        'siret' => $siret,
    ];

    // RECHERCHE CORRECTE
    $entreprise = Entreprise::where('siret', $siret)->first();

    return view('entreprises.import-result', [
        'data' => $normalized,
        'entreprise' => $entreprise
    ]);
}

public function create()
{
    return view('entreprises.create');
}

}

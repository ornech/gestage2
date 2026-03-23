<?php

namespace App\Http\Controllers;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Services\SireneClient;


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
        $companies_count = Entreprise::count();
        $stages_count = Stage::count();
        $contacts_count = Contact::count();

        return view('index', compact('companies_count', 'stages_count', 'contacts_count'));

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
    public function index(Request $request)
{
    // Compteurs
    $companies_count = Entreprise::count();
    $stages_count = Stage::count();
    $contacts_count = Contact::count();

    // Recherche simple
    $search = $request->input('search');

    $entreprises = Entreprise::query()
        ->when($search, function ($query, $search) {
            $query->where('raison_sociale', 'like', "%{$search}%");
        })
        ->paginate(10);

    return view('index', compact(
        'companies_count',
        'stages_count',
        'contacts_count',
        'entreprises'
    ));
}

    public function show($id)
{
    $entreprise = Entreprise::findOrFail($id);
    return view('show', compact('entreprise'));
}

}

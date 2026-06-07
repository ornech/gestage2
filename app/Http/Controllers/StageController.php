<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Employe;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Entreprise;

class StageController extends Controller
{
    /**
     * Affiche la liste des stages
     */
    public function index()
    {
        return redirect()->route('admin.stages.index');
    }

    /**
     * Enregistrement d'un stage
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Vérifier que l'étudiant est encore en BTS (promo null = compte de test, autorisé)
        if ($user->promo && $user->promo < date('Y')) {
            abort(403, "Vous n'êtes plus autorisé à ajouter un stage.");
        }

        // Vérifier qu'il n'a pas déjà un stage pour sa classe
        // (classe_courante = champ calculé fiable, cohérent avec etudiantNouveau() — voir le bug de transition SIO1→SIO2)
        $existe = Stage::where('etudiant_id', $user->id)
                       ->where('classe', $user->classe_courante)
                       ->exists();

        if ($existe) {
            return back()->withErrors("Vous avez déjà ajouté un stage pour votre année.");
        }

        // Validation
        $request->validate([
            'entreprise_id'      => 'required|exists:entreprises,id',
            'maitre_de_stage_id' => [
                'required',
                Rule::exists('employes', 'id')->where('entreprise_id', $request->entreprise_id),
            ],
            'date_debut'         => 'required|date',
            'duree'              => 'required|integer|min:1',
        ]);

        // Calcul de la date de fin
        $date_debut = Carbon::parse($request->date_debut);
        $date_fin = $date_debut->copy()->addWeeks((int) $request->duree);

        $entreprise = \App\Models\Entreprise::find($request->entreprise_id);

        // Transférer le statut de la convention hors app si elle existe
        $convPapier = \App\Models\ConventionPapier::where('etudiant_id', $user->id)->first();

        // "hors_app" est un statut propre à la convention hors application :
        // l'employeur a déjà signé → côté stage, l'équivalent est "en_attente" (déposée à la direction)
        $statutConvention = match ($convPapier?->statut) {
            'hors_app' => 'en_attente',
            null       => 'a_faire_signer',
            default    => $convPapier->statut,
        };

        $stage = Stage::create([
            'titre'              => "Stage chez {$entreprise->raison_sociale}",
            'entreprise_id'      => $request->entreprise_id,
            'maitre_de_stage_id' => $request->maitre_de_stage_id,
            'etudiant_id'        => $user->id,
            'classe'             => $user->classe_courante ?? $request->classe,
            'date_debut'         => $date_debut,
            'date_fin'           => $date_fin,
            'statut_convention'  => $statutConvention,
            'statut_validation'  => $convPapier ? 'valide' : 'en_attente',
        ]);

        // Supprimer la convention papier maintenant que le stage est saisi
        $convPapier?->delete();

        return redirect()->route('entreprises.show', $request->entreprise_id)
                         ->with('success', 'Stage ajouté avec succès.');
    }
    // Affiche les conventions de l’étudiant connecté
public function mesConventions()
{
    $stages = auth()->user()->stages; // les stages de l’étudiant connecté

    return view('etudiant.conventions', compact('stages'));
}

    public function show(Stage $stage)
    {
        $this->authorize('view', $stage);

        $stage->load(['entreprise', 'maitreDeStage', 'etudiant', 'professeur', 'journalEntries']);

        return view('stages.show', compact('stage'));
    }

    /**
     * Formulaire d'édition
     */
public function edit(Stage $stage)
{
    $stage->load(['entreprise.employes', 'etudiant', 'maitreDeStage']);

    // Employés de l'entreprise du stage uniquement
    $employes = $stage->entreprise?->employes ?? collect();
    $duree    = ($stage->date_debut && $stage->date_fin)
        ? (int) $stage->date_debut->diffInWeeks($stage->date_fin)
        : 6;

    return view('stages.edit', compact('stage', 'employes', 'duree'));
}

    /**
     * Mise à jour d'un stage
     */
    public function update(Request $request, Stage $stage)
    {
        // Autoriser un étudiant à modifier son stage
        $this->authorize('update', $stage);

        $request->validate([
            'date_debut'         => 'required|date',
            'duree'              => 'required|integer|min:1',
            'maitre_de_stage_id' => [
                'required',
                Rule::exists('employes', 'id')->where('entreprise_id', $stage->entreprise_id),
            ],
        ]);

        // Recalcul de la date de fin
        $date_debut = Carbon::parse($request->date_debut);
        $date_fin = $date_debut->copy()->addWeeks((int) $request->duree);

        $stage->update([
            'date_debut'         => $date_debut,
            'date_fin'           => $date_fin,
            'maitre_de_stage_id' => $request->maitre_de_stage_id,
            'statut_convention'  => 'aucune',   // Toute modification remet en cycle de signature
            'statut_validation'  => 'en_attente',
        ]);

        return redirect()->route('stages.index')->with('success', 'Stage mis à jour.');
    }

    /**
     * Affiche les stages de l'étudiant connecté
     */
    public function mesStages()
    {
        $stages = Stage::with(['entreprise', 'maitreDeStage'])
            ->where('etudiant_id', auth()->id())
            ->get();

        return view('etudiant.stages.index', compact('stages'));
    }

    /**
     * Formulaire unifié de saisie d'un stage (étudiant)
     */
    public function etudiantNouveau()
    {
        $user = auth()->user();

        if ($user->classe_courante && Stage::where('etudiant_id', $user->id)
                ->where('classe', $user->classe_courante)->exists()) {
            return redirect()->route('etudiant.dashboard')
                ->withErrors("Tu as déjà un stage enregistré pour cette année.");
        }

        $annee  = \App\Models\Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));
        $config = \App\Models\ConfigurationStage::where('annee_scolaire', $annee)
            ->where('classe', $user->classe_courante)
            ->first();

        return view('etudiant.stage.nouveau', compact('user', 'config'));
    }

    /**
     * Recherche une entreprise par SIRET :
     * 1) dans la base locale
     * 2) sinon via l'API INSEE Sirene — crée la fiche automatiquement si trouvée
     */
    public function rechercheSiret(Request $request)
    {
        $siret = preg_replace('/\D/', '', $request->get('siret', ''));

        if (strlen($siret) !== 14) {
            return response()->json(['found' => false, 'error' => 'SIRET invalide (14 chiffres attendus).']);
        }

        // ── 1. Recherche dans la base locale ────────────────────────
        $entreprise = Entreprise::where('siret', $siret)->with('employes')->first();

        if ($entreprise) {
            return $this->entrepriseJson($entreprise);
        }

        // ── 2. Appel API INSEE Sirene ────────────────────────────────
        $apiKey = config('services.sirene.key');
        $url    = config('services.sirene.url') . $siret;

        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders([
                'Accept'                       => 'application/json',
                'X-INSEE-Api-Key-Integration'  => $apiKey,
            ])
            ->get($url);

        if ($response->failed()) {
            $status = $response->status();
            $msg = match(true) {
                $status === 404 => 'SIRET introuvable dans la base INSEE.',
                $status === 403 => 'Accès API refusé — vérifiez la clé.',
                default         => "Erreur INSEE ({$status}).",
            };
            return response()->json(['found' => false, 'error' => $msg]);
        }

        // ── 3. Parsing de la réponse INSEE ───────────────────────────
        $etab    = $response->json('etablissement');
        $unite   = $etab['uniteLegale']           ?? [];
        $adr     = $etab['adresseEtablissement']  ?? [];
        $periode = ($etab['periodesEtablissement'] ?? [[]])[0] ?? [];

        $raisonSociale = $unite['denominationUniteLegale']
            ?? trim(($unite['nomUniteLegale'] ?? '') . ' ' . ($unite['prénomUsuelUniteLegale'] ?? ''));

        $numVoie   = trim(($adr['numeroVoieEtablissement'] ?? '') . ' ' . ($adr['indiceRepetitionEtablissement'] ?? ''));
        $adresseLigne = trim("{$numVoie} " . ($adr['typeVoieEtablissement'] ?? '') . ' ' . ($adr['libelleVoieEtablissement'] ?? ''));
        $codePostal = $adr['codePostalEtablissement']     ?? '';
        $ville      = $adr['libelleCommuneEtablissement'] ?? '';
        $codeNaf    = $periode['activitePrincipaleEtablissement']
            ?? ($unite['activitePrincipaleUniteLegale'] ?? '');

        // ── 4. Création automatique de la fiche entreprise ───────────
        $entreprise = Entreprise::create([
            'raison_sociale'  => strtoupper($raisonSociale),
            'siret'           => $siret,
            'code_naf'        => $codeNaf,
            'adresse'         => $adresseLigne,
            'code_postal'     => $codePostal,
            'ville'           => strtoupper($ville),
            'departement_code'=> substr($codePostal, 0, 2),
            'est_valide'      => true,
            'user_id'         => auth()->id(),
        ]);

        return $this->entrepriseJson($entreprise->load('employes'), created: true);
    }

    private function entrepriseJson(Entreprise $e, bool $created = false): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'found'   => true,
            'created' => $created,
            'id'      => $e->id,
            'siret'   => $e->siret,
            'nom'     => $e->raison_sociale,
            'adresse' => trim("{$e->adresse} {$e->code_postal} {$e->ville}"),
            'contacts'=> $e->employes->map(fn($emp) => [
                'id'    => $emp->id,
                'label' => "{$emp->prenom} {$emp->nom}" . ($emp->fonction ? " — {$emp->fonction}" : ''),
            ])->values(),
        ]);
    }
}

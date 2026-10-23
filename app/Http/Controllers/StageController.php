<?php

namespace App\Http\Controllers;

use App\Enums\StatutConvention;
use App\Enums\StatutValidation;
use Carbon\Carbon;
use App\Http\Controllers\Concerns\RechercheSiretTrait;
use App\Models\Employe;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Parametre;
use App\Services\CreationStage;

class StageController extends Controller
{
    use RechercheSiretTrait;

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
    public function store(Request $request, CreationStage $creation)
    {
        $this->authorize('create', Stage::class);

        $user = auth()->user();

        if ($raison = $creation->raisonFermeture($user)) {
            return back()->withErrors($raison);
        }

        if ($user->aUnStageCetteAnnee()) {
            return back()->withErrors("Vous avez déjà ajouté un stage pour votre année.");
        }

        $request->validate([
            'entreprise_id'      => 'required|exists:entreprises,id',
            'maitre_de_stage_id' => [
                'required',
                Rule::exists('employes', 'id')->where('entreprise_id', $request->entreprise_id),
            ],
            'date_debut'         => 'required|date',
            'duree'              => 'required|integer|min:1',
        ]);

        $creation->creer(
            $user,
            (int) $request->entreprise_id,
            (int) $request->maitre_de_stage_id,
            $request->date_debut,
            (int) $request->duree,
        );

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
    $this->authorize('update', $stage);

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
            'statut_convention'  => StatutConvention::AFaireSigner->value,   // Toute modification remet en cycle de signature
            'statut_validation'  => StatutValidation::EnAttente->value,
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
            ->orderByDesc('annee_scolaire')
            ->get();

        return view('etudiant.stages.index', compact('stages'));
    }

    /**
     * Formulaire unifié de saisie d'un stage (étudiant)
     */
    public function etudiantNouveau(CreationStage $creation)
    {
        $user = auth()->user();

        if ($raison = $creation->raisonFermeture($user)) {
            return redirect()->route('etudiant.dashboard')->withErrors($raison);
        }

        $annee = Parametre::anneeActive();

        if ($user->aUnStageCetteAnnee()) {
            return redirect()->route('etudiant.dashboard')
                ->withErrors("Tu as déjà un stage enregistré pour cette année.");
        }

        $config = \App\Models\ConfigurationStage::where('annee_scolaire', $annee)
            ->where('classe', $user->classe_courante)
            ->first();

        return view('etudiant.stage.nouveau', compact('user', 'config'));
    }

    /**
     * Ajout en AJAX d'un nouveau maître de stage depuis le formulaire unifié
     * de saisie de stage (étape 2). Reste dans le point d'entrée unique
     * étudiant — évite de renvoyer vers la fiche entreprise (réservée au staff).
     */
    public function ajouterMaitreDeStage(Request $request)
    {
        $validated = $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
            'nom'           => 'required|string|max:255',
            'prenom'        => 'required|string|max:255',
            'email'         => 'nullable|email|unique:employes,email',
            'telephone'     => 'nullable|string|max:30',
        ]);

        $employe = Employe::create($validated + ['creator_id' => auth()->id()]);

        return response()->json([
            'id'    => $employe->id,
            'label' => "{$employe->prenom} {$employe->nom}",
        ]);
    }

    /**
     * Recherche une entreprise par SIRET :
     * 1) dans la base locale
     * 2) sinon via l'API INSEE Sirene — crée la fiche automatiquement si trouvée
     */
    public function rechercheSiret(Request $request)
    {
        return $this->rechercherEntrepriseParSiret($request);
    }
}

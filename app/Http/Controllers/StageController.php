<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Http\Controllers\Concerns\RechercheSiretTrait;
use App\Models\Employe;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Entreprise;

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
    public function store(Request $request)
    {
        $this->authorize('create', Stage::class);

        $user = auth()->user();

        // Vérifier que l'étudiant est encore en BTS (promo null = compte de test, autorisé)
        if ($user->promo && $user->promo < date('Y')) {
            abort(403, "Vous n'êtes plus autorisé à ajouter un stage.");
        }

        $annee  = \App\Models\Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));

        $existe = Stage::where('etudiant_id', $user->id)
                       ->where('annee_scolaire', $annee)
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
            'annee_scolaire'     => $annee,
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

        $annee  = \App\Models\Parametre::get('annee_scolaire', date('Y').'-'.(date('Y') + 1));

        if (Stage::where('etudiant_id', $user->id)
                ->where('annee_scolaire', $annee)
                ->exists()) {
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

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Employe;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Entreprise;

class StageController extends Controller
{
    /**
     * Affiche la liste des stages
     */
    public function index()
    {
        // Autoriser uniquement les étudiants à voir la liste des stages
        $this->authorize('viewAny', Stage::class);

        $stages = Stage::with(['entreprise', 'maitreDeStage'])->paginate(10);

        return view('stages.index', compact('stages'));
    }

    /**
     * Formulaire de création (étudiant)
     */
    public function create(Entreprise $entreprise)
    {
        $this->authorize('create', Stage::class);

        return view('stages.create', [
            'entreprise' => $entreprise,
            'employes' => $entreprise->employes, // uniquement les employés de CETTE entreprise
        ]);
    }

    /**
     * Enregistrement d'un stage
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Vérifier que l'étudiant est encore en BTS
        if ($user->promo < date('Y')) {
            abort(403, "Vous n'êtes plus autorisé à ajouter un stage.");
        }

        // Vérifier qu'il n'a pas déjà un stage pour sa classe
        $existe = Stage::where('etudiant_id', $user->id)
                       ->where('classe', $user->classe)
                       ->exists();

        if ($existe) {
            return back()->withErrors("Vous avez déjà ajouté un stage pour votre année.");
        }

        // Validation
        $request->validate([
            'entreprise_id'      => 'required|exists:entreprises,id',
            'maitre_de_stage_id' => 'required|exists:employes,id',
            'date_debut'         => 'required|date',
            'duree'              => 'required|integer|min:1',
        ]);

        // Calcul de la date de fin
        $date_debut = Carbon::parse($request->date_debut);
        $date_fin = $date_debut->copy()->addWeeks($request->duree);

        // Création du stage
        Stage::create([
            'entreprise_id'      => $request->entreprise_id,
            'maitre_de_stage_id' => $request->maitre_de_stage_id,
            'etudiant_id'        => $user->id,
            'classe'             => $user->classe,
            'date_debut'         => $date_debut,
            'date_fin'           => $date_fin,
        ]);

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
        $stage->load(['entreprise', 'maitreDeStage', 'etudiant', 'professeur', 'journalEntries']);

        return view('stages.show', compact('stage'));
    }

    /**
     * Formulaire d'édition
     */
public function edit(Stage $stage)
{
    $entreprises = \App\Models\Entreprise::all();
    $tuteurs = \App\Models\Employe::all();
    $etudiants = \App\Models\User::role('Etudiant')->get();
    $duree = $stage->date_debut->diffInWeeks($stage->date_fin);

    return view('stages.edit', compact('stage', 'entreprises', 'tuteurs', 'etudiants', 'duree'));
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
            'maitre_de_stage_id' => 'required|exists:employes,id',
        ]);

        // Recalcul de la date de fin
        $date_debut = Carbon::parse($request->date_debut);
        $date_fin = $date_debut->copy()->addWeeks($request->duree);

        $stage->update([
            'date_debut'         => $date_debut,
            'date_fin'           => $date_fin,
            'maitre_de_stage_id' => $request->maitre_de_stage_id,
        ]);

        return redirect()->route('stages.index')->with('success', 'Stage mis à jour.');
    }

    /**
     * Suppression d'un stage
     */
    public function destroy(Stage $stage)
    {
        // Autoriser un étudiant à supprimer son stage
        $this->authorize('delete', $stage);

        $stage->delete();

        return redirect()->route('stages.index')->with('success', 'Stage supprimé.');
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
}

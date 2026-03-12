<?php

namespace App\Http\Controllers;

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
        //autoriser uniquement les étudiants à voir la liste des stages
        $this->authorize('viewAny', Stage::class);

        $stages = Stage::with(['entreprise', 'maitreDeStage'])->paginate(10);

        return view('stages.index', compact('stages'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        //autorisation pour créer un stage
        $this->authorize('create', Stage::class);

        $employes = Employe::all();
        $users = User::all(); // Étudiants potentiels
        $entreprises = Entreprise::all();

        return view('stages.create', compact('employes', 'users', 'entreprises'));
    }

    /**
     * Enregistrement d'un stage
     */
    public function store(Request $request)
    {
        
            //autorisation pour créer un stage
            $this->authorize('create', Stage::class);
        
            $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'entreprise_id' => 'required|exists:entreprises,id',
            'maitre_de_stage_id' => 'required|exists:employes,id',
            'etudiant_id' => 'nullable|exists:users,id',
            ]);

        Stage::create($request->validated());

        return redirect()->route('stages.index')->with('success', 'Stage créé avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Stage $stage)
    {
        //autoriser un étudiant à modifier son stage seulement si c'est le sien
        $this->authorize('update', $stage);

        $employes = Employe::all();
        $users = User::all();
        $entreprises = Entreprise::all();

        return view('stages.edit', compact('stage', 'employes', 'users', 'entreprises'));
    }

    /**
     * Mise à jour d'un stage
     */
    public function update(Request $request, Stage $stage)
    {
        //autoriser un étudiant à modifier son stage
        $this->authorize('update', $stage);

        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'entreprise_id' => 'required|exists:entreprises,id',
            'maitre_de_stage_id' => 'required|exists:employes,id',
       
         'etudiant_id' => 'nullable|exists:users,id',
        ]);

        $stage->update($request->validated());

        return redirect()->route('stages.index')->with('success', 'Stage mis à jour.');
    }

    /**
     * Suppression d'un stage
     */
    public function destroy(Stage $stage)
    {
        //autoriser un étudiant à supprimer son stage
        $this->authorize('delete', $stage);

        $stage->delete();

        return redirect()->route('stages.index')->with('success', 'Stage supprimé.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Affiche la liste des stages
     */
    public function index()
    {
        $stages = Stage::with('employe')->get();
        return view('stages.index', compact('stages'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $employes = Employe::all();
        $users = User::all(); // Étudiants potentiels
        return view('stages.create', compact('employes', 'users'));
    }

    /**
     * Enregistrement d'un stage
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'employe_id' => 'required|exists:employes,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Stage::create($request->all());

        return redirect()->route('stages.index')->with('success', 'Stage créé avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Stage $stage)
    {
        $employes = Employe::all();
        $users = User::all();
        return view('stages.edit', compact('stage', 'employes', 'users'));
    }

    /**
     * Mise à jour d'un stage
     */
    public function update(Request $request, Stage $stage)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'employe_id' => 'required|exists:employes,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $stage->update($request->all());

        return redirect()->route('stages.index')->with('success', 'Stage mis à jour.');
    }

    /**
     * Suppression d'un stage
     */
    public function destroy(Stage $stage)
    {
        $stage->delete();
        return redirect()->route('stages.index')->with('success', 'Stage supprimé.');
    }
}

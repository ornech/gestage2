<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employe;

class EmployeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer tous les employés et les passer à la vue
        $employes = Employe::all();
        return view('employes.index', compact('employes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Afficher le formulaire de création d'un nouvel employé
        return view('employes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valider les données du formulaire
         $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|email|unique:employes,email',
            'telephone' => 'nullable',
            'entreprise_id' => 'required|exists:entreprises,id',
            ]);
            // Créer un nouvel employé avec les données validées
            Employe::create($request->all());
        // Rediriger vers la liste des employés avec un message de succès
        return redirect()->route('employes.index')
                         ->with('success', 'Employé ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    // Afficher les détails d'un employé spécifique
    public function show(Employe $employe)
    {
        return view('employes.show', compact('employe'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employe $employe)
    {
        // Afficher le formulaire d'édition pour l'employé sélectionné
        return view('employes.edit', compact('employe'));
    }
   

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employe $employe)
     {
        // Valider les données du formulaire
         $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|email|unique:employes,email,' . $employe->id,
            'telephone' => 'nullable',
            'entreprise_id' => 'required|exists:entreprises,id',
            ]);
            // Mettre à jour l'employé avec les données validées
            $employe->update($request->all());
        // Rediriger vers la liste des employés avec un message de succès
        return redirect()->route('employes.index')
                         ->with('success', 'Employé mis à jour avec succès.');
     }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employe $employe)
    {
        // Supprimer l'employé
        $employe->delete();

        return redirect()->route('employes.index')
                         ->with('success', 'Employé supprimé avec succès.');
    }
    
}

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
        $employes = Employe::with('entreprise')->paginate(10);
        return view('employes.index', compact('employes'));
    }

    /**
     * Show the form for creating a new resource.
     */
   public function create($entrepriseId)
{
    return view('employes.create', [
        'entreprise_id' => $entrepriseId
    ]);
}


    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $request->validate([
        'nom' => 'required',
        'prenom' => 'required',
        'email' => 'required|email|unique:employes,email',
        'telephone' => 'nullable',
        'entreprise_id' => 'required|exists:entreprises,id',
    ]);

    Employe::create([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'email' => $request->email,
        'telephone' => $request->telephone,
        'entreprise_id' => $request->entreprise_id,
        'is_maitre_de_stage' => true,
    ]);

    return redirect()
        ->route('entreprises.show', $request->entreprise_id)
        ->with('success', 'Maître de stage ajouté avec succès.');
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
            $employe->update($request->validated());
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

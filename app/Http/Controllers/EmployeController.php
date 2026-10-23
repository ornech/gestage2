<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employe;
use App\Models\Parametre;
use App\Services\MailBienvenueStage;

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

    $employe = Employe::create([
        'nom'           => $request->nom,
        'prenom'        => $request->prenom,
        'email'         => $request->email,
        'telephone'     => $request->telephone,
        'entreprise_id' => $request->entreprise_id,
        'creator_id'    => auth()->id(),
    ]);

    return redirect()
        ->route('entreprises.show', $request->entreprise_id)
        ->with('success', 'Maître de stage ajouté avec succès.');
}


    /**
     * Display the specified resource.
     */
    public function show(Employe $employe)
    {
        $employe->load('entreprise');

        // Téléphone : visible par le staff, et par l'étudiant si c'est son propre maître de stage
        $estMonMaitreDeStage = auth()->user()->hasRole('Etudiant')
            && auth()->user()->stages()->where('maitre_de_stage_id', $employe->id)->exists();

        return view('employes.show', compact('employe', 'estMonMaitreDeStage'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employe $employe)
    {
        $this->authorize('update', $employe);

        $employe->load('entreprise');

        return view('employes.edit', compact('employe'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employe $employe, MailBienvenueStage $mails)
     {
        $this->authorize('update', $employe);

        // Valider les données du formulaire — l'entreprise n'est pas modifiable ici
        // (elle est affichée en lecture seule, voir employes/edit.blade.php)
         $validated = $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'nullable|email|unique:employes,email,' . $employe->id,
            'telephone' => 'nullable',
            ]);
            // Mettre à jour l'employé avec les données validées
            $emailChange = $employe->email !== $request->email;
            $employe->update($validated);

            // Si l'email change, renvoyer le mail sur les stages validés de l'année en cours
            // (les maîtres de stage des années passées n'ont pas à être sollicités de nouveau)
            if ($emailChange && $request->email) {
                $stagesValides = $employe->stages()
                    ->deLAnnee(Parametre::anneeActive())
                    ->where('statut_convention', 'validee')
                    ->get();

                foreach ($stagesValides as $stage) {
                    $mails->envoyer($stage, $request->email, renvoi: true);
                }
            }

        return redirect()->route('employes.show', $employe)
                         ->with('success', 'Maître de stage mis à jour avec succès.');
     }

    public function supprimerEmailRgpd(Employe $employe)
    {
        $employe->update([
            'email'             => null,
            'telephone'         => null,
            'email_supprime_at' => now(),
        ]);

        return view('mail.rgpd-confirmation');
    }
}

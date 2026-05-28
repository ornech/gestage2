<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Employe;
use Illuminate\Http\Request;

class AdminStageController extends Controller
{
    /**
     * Affiche la liste complète des stages pour l'admin/prof
     */
   public function index()
{
    $query = Stage::with(['entreprise', 'maitreDeStage', 'etudiant']);

    // Filtre promo
    if (request('promo')) {
        $query->whereHas('etudiant', function ($q) {
            $q->where('classe', request('promo'));
        });
    }

    $stages = $query->paginate(10);

    $tuteurs = Employe::all();

    return view('admin.stages.index', compact('stages', 'tuteurs'));
}

    public function assign(Request $request, Stage $stage)
{
    // Validation : le tuteur doit exister dans la table employes
    $request->validate([
        'maitre_de_stage_id' => 'nullable|exists:employes,id',
    ]);

    // Mise à jour du tuteur
    $stage->maitre_de_stage_id = $request->maitre_de_stage_id;
    $stage->save();

    return back()->with('success', 'Tuteur assigné avec succès.');
}

}

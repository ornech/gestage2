<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Employe;
class AdminStageController extends Controller
{
    /**
     * Affiche la liste complète des stages pour l'admin/prof
     */
    public function index()
    {
        
          // Charger tous les stages avec leurs relations
     $stages = Stage::with(['entreprise', 'maitreDeStage', 'etudiant'])->paginate(10);
        // Charger tous les employés (tuteurs potentiels)
     $tuteurs = Employe::all();
     return view('admin.stages.index', compact('stages', 'tuteurs'));
    }
}

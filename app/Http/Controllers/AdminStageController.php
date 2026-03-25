<?php

namespace App\Http\Controllers;

use App\Models\Stage;

class AdminStageController extends Controller
{
    /**
     * Affiche la liste complète des stages pour l'admin/prof
     */
    public function index()
    {
        
          // Charger tous les stages avec leurs relations
     $stages = Stage::with(['entreprise', 'maitreDeStage', 'etudiant'])->paginate(10);

     return view('admin.stages.index', compact('stages'));
    }
}

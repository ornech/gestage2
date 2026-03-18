<?php

namespace App\Http\Controllers;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    //
     public function store(Request $request)
    {
        Entreprise::create([
          'raison_sociale' => $request->raison_sociale,
         'siret' => $request->siret,
            'est_valide' => 0,
        ]);

        return response()->json(['message' => 'ok']);
    }
}

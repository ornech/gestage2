<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminSpeController extends Controller
{
    public function index()
    {
        $classes = User::role('Etudiant')
            ->whereIn('statut', ['actif'])
            ->whereNotNull('classe')
            ->distinct()
            ->orderBy('classe')
            ->pluck('classe');

        return view('admin.spe.index', compact('classes'));
    }

    public function editClasse(string $classe)
    {
        $etudiants = User::role('Etudiant')
            ->where('classe', $classe)
            ->whereIn('statut', ['actif'])
            ->orderBy('nom')
            ->get();

        return view('admin.spe.edit-classe', compact('classe', 'etudiants'));
    }

    public function updateClasse(Request $request, string $classe)
    {
        $request->validate([
            'spe'   => 'nullable|array',
            'spe.*' => 'nullable|in:SLAM,SISR,',  // chaîne vide autorisée (= non défini)
        ]);

        foreach ($request->spe as $userId => $spe) {
            User::where('id', $userId)->update(['spe' => $spe ?: null]);
        }

        return redirect()->route('spe.index')
            ->with('success', "Spécialités de {$classe} enregistrées.");
    }
}

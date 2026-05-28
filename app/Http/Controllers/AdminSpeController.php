<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use App\Models\User;
use Illuminate\Http\Request;

class AdminSpeController extends Controller
{
    public function index()
    {
        $isOpen = Parametre::isOpen('spe_assignments_open');

        $classes = User::role('Etudiant')
            ->where('statut', 'actif')
            ->whereNotNull('classe')
            ->distinct()
            ->orderBy('classe')
            ->pluck('classe');

        return view('admin.spe.index', compact('isOpen', 'classes'));
    }

    public function editClasse(string $classe)
    {
        abort_unless(Parametre::isOpen('spe_assignments_open'), 403,
            "L'affectation des spécialités n'est pas encore ouverte.");

        $etudiants = User::role('Etudiant')
            ->where('classe', $classe)
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get();

        return view('admin.spe.edit-classe', compact('classe', 'etudiants'));
    }

    public function updateClasse(Request $request, string $classe)
    {
        abort_unless(Parametre::isOpen('spe_assignments_open'), 403,
            "L'affectation des spécialités n'est pas encore ouverte.");

        $request->validate([
            'spe'   => 'required|array',
            'spe.*' => 'nullable|in:SLAM,SISR',
        ]);

        foreach ($request->spe as $userId => $spe) {
            User::where('id', $userId)->update(['spe' => $spe ?: null]);
        }

        return redirect()->route('spe.index')
            ->with('success', "Spécialités de {$classe} enregistrées.");
    }

    public function toggle()
    {
        $current = Parametre::get('spe_assignments_open', '0');
        Parametre::set('spe_assignments_open', $current === '1' ? '0' : '1');

        $etat = $current === '1' ? 'fermée' : 'ouverte';
        return back()->with('success', "Affectation des spécialités {$etat}.");
    }
}

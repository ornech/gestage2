<?php

namespace App\Http\Controllers;

use App\Enums\StatutEtudiant;
use App\Services\StatistiquesPromos;
use Illuminate\Http\Request;

class AdminStatistiqueController extends Controller
{
    public function index(Request $request, StatistiquesPromos $stats)
    {
        $request->validate([
            'promos' => ['nullable', 'array'],
            'promos.*' => ['integer'],
            'spe' => ['nullable', 'in:SLAM,SISR,aucune'],
            'mode' => ['nullable', 'in:effectifs,pourcentages'],
        ]);

        $disponibles = $stats->promosDisponibles();

        // Sans sélection, toutes les promos ; une promo inconnue est ignorée
        $choisies = collect($request->input('promos', []))->map(fn ($p) => (int) $p)->intersect($disponibles)->values();
        if ($choisies->isEmpty()) {
            $choisies = $disponibles;
        }

        $spe = $request->input('spe') ?: null;
        $mode = $request->input('mode', 'effectifs');

        $lignes = $stats->calculer($choisies->all(), $spe);

        $statutsLibelles = collect(StatutEtudiant::cases())
            ->mapWithKeys(fn ($s) => [$s->value => $s->libelle()])
            ->all();

        return view('admin.statistiques.index', [
            'lignes' => $lignes,
            'disponibles' => $disponibles,
            'choisies' => $choisies,
            'filtreTouche' => $request->filled('promos'),
            'spe' => $spe,
            'mode' => $mode,
            'statutsLibelles' => $statutsLibelles,
            'maxTotal' => max(1, (int) $lignes->max('total')),
        ]);
    }
}

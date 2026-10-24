<?php

namespace App\Http\Controllers;

use App\Enums\StatutEtudiant;
use App\Models\Parametre;
use App\Services\StatistiquesPromos;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AdminStatistiqueController extends Controller
{
    public function index(Request $request, StatistiquesPromos $stats)
    {
        [$disponibles, $choisies, $spe] = $this->filtres($request, $stats);
        $lignes = $stats->calculer($choisies->all(), $spe);

        $statutsLibelles = collect(StatutEtudiant::cases())
            ->mapWithKeys(fn ($s) => [$s->value => $s->libelle()])
            ->all();

        // Données des graphes : ordre chronologique (promo la plus ancienne à gauche)
        $chrono = $lignes->sortBy('promo')->values();
        $donnees = [
            'statuts' => $statutsLibelles,
            'promos' => $chrono->map(fn ($l) => [
                'promo' => $l['promo'],
                'total' => $l['total'],
                'statuts' => $l['statuts'],
                'specialites' => $l['specialites'],
                'avec_stage' => $l['avec_stage'],
                'convention_validee' => $l['convention_validee'],
                'liens' => $this->liensStatuts($l['promo']),
            ])->all(),
        ];

        return view('admin.statistiques.index', [
            'lignes' => $lignes,
            'disponibles' => $disponibles,
            'choisies' => $choisies,
            'filtreTouche' => $request->filled('promos'),
            'spe' => $spe,
            'donnees' => $donnees,
            'insights' => $this->insights($chrono),
            'exportUrl' => route('admin.statistiques.csv', $request->only('promos', 'spe')),
        ]);
    }

    public function csv(Request $request, StatistiquesPromos $stats)
    {
        [, $choisies, $spe] = $this->filtres($request, $stats);
        $lignes = $stats->calculer($choisies->all(), $spe);

        return response()->streamDownload(function () use ($lignes) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($out, ['Promo', 'Total', 'Actifs', 'Diplômés', 'Échecs', 'Archivés', 'Démissionnaires', 'SLAM', 'SISR', 'Spécialité non définie', 'Avec stage', 'Convention validée', '% diplômés', '% démissions'], ';');
            foreach ($lignes as $l) {
                fputcsv($out, [
                    $l['promo'], $l['total'],
                    $l['statuts']['actif'], $l['statuts']['diplome'], $l['statuts']['echec'], $l['statuts']['archive'], $l['statuts']['demissionnaire'],
                    $l['specialites']['SLAM'], $l['specialites']['SISR'], $l['specialites']['aucune'],
                    $l['avec_stage'], $l['convention_validee'], $l['taux_diplomes'], $l['taux_demission'],
                ], ';');
            }
            fclose($out);
        }, 'statistiques-promos.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: Collection, 1: Collection, 2: ?string} promos disponibles, promos choisies, spécialité */
    private function filtres(Request $request, StatistiquesPromos $stats): array
    {
        $request->validate([
            'promos' => ['nullable', 'array'],
            'promos.*' => ['integer'],
            'spe' => ['nullable', 'in:SLAM,SISR,aucune'],
        ]);

        $disponibles = $stats->promosDisponibles();

        // Sans sélection, toutes les promos ; une promo inconnue est ignorée
        $choisies = collect($request->input('promos', []))->map(fn ($p) => (int) $p)->intersect($disponibles)->values();
        if ($choisies->isEmpty()) {
            $choisies = $disponibles;
        }

        return [$disponibles, $choisies, $request->input('spe') ?: null];
    }

    /**
     * Lien de la liste d'étudiants correspondant à un statut d'une promo (clic sur un graphe).
     * Les actifs n'ont de liste que pour les deux promos en cours (via la classe).
     *
     * @return array<string, ?string>
     */
    private function liensStatuts(int $promo): array
    {
        $sy = (int) explode('-', Parametre::anneeActive())[0];
        $classe = match ($promo) {
            $sy + 2 => 'SIO1',
            $sy + 1 => 'SIO2',
            default => null,
        };

        $liens = [];
        foreach (StatutEtudiant::cases() as $statut) {
            $liens[$statut->value] = $statut === StatutEtudiant::Actif
                ? ($classe ? route('admin.users.index', ['classe' => $classe]) : null)
                : route('admin.users.index', ['filtre' => 'anciens', 'promo' => $promo, 'statut' => $statut->value]);
        }

        return $liens;
    }

    /**
     * Constats rédigés à partir des chiffres (promos de moins de 5 étudiants ignorées : trop petites pour conclure).
     *
     * @return array<int, array{icone: string, couleur: string, texte: string}>
     */
    private function insights(Collection $chrono): array
    {
        $insights = [];
        $assezGrandes = $chrono->filter(fn ($l) => $l['total'] >= 5)->values();

        $terminees = $assezGrandes->filter(fn ($l) => $l['statuts']['actif'] === 0)->values();
        if ($terminees->count() >= 1) {
            $meilleure = $terminees->sortByDesc('taux_diplomes')->first();
            $insights[] = ['icone' => 'fa-graduation-cap', 'couleur' => 'has-text-link',
                'texte' => "Meilleur taux de diplômés : promo {$meilleure['promo']} ({$meilleure['taux_diplomes']} %, {$meilleure['statuts']['diplome']} sur {$meilleure['total']})."];
        }

        if ($assezGrandes->isNotEmpty()) {
            $pire = $assezGrandes->sortByDesc('taux_demission')->first();
            if ($pire['taux_demission'] > 0) {
                $insights[] = ['icone' => 'fa-user-minus', 'couleur' => 'has-text-danger',
                    'texte' => "Plus forte démission : promo {$pire['promo']} ({$pire['taux_demission']} %, {$pire['statuts']['demissionnaire']} sur {$pire['total']})."];
            }
        }

        if ($assezGrandes->count() >= 2) {
            $derniere = $assezGrandes->last();
            $precedente = $assezGrandes->get($assezGrandes->count() - 2);
            $ecart = round($derniere['taux_demission'] - $precedente['taux_demission'], 1);
            if ($ecart != 0) {
                $sens = $ecart > 0 ? 'hausse' : 'baisse';
                $insights[] = ['icone' => $ecart > 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down', 'couleur' => $ecart > 0 ? 'has-text-danger' : 'has-text-success',
                    'texte' => 'Démissions en '.$sens.' de '.abs($ecart)." points entre la promo {$precedente['promo']} et la promo {$derniere['promo']}."];
            }
        }

        $slam = $chrono->sum(fn ($l) => $l['specialites']['SLAM']);
        $sisr = $chrono->sum(fn ($l) => $l['specialites']['SISR']);
        if ($slam + $sisr > 0) {
            $dominante = $slam >= $sisr ? 'SLAM' : 'SISR';
            $part = round(max($slam, $sisr) * 100 / ($slam + $sisr));
            $insights[] = ['icone' => 'fa-code-branch', 'couleur' => 'has-text-info',
                'texte' => "Spécialité dominante : {$dominante} ({$part} % des étudiants qui ont choisi)."];
        }

        $sansStage = $chrono->sum('actifs_sans_stage');
        if ($sansStage > 0) {
            $insights[] = ['icone' => 'fa-briefcase', 'couleur' => 'has-text-warning-dark',
                'texte' => "{$sansStage} étudiant(s) actif(s) n'ont encore aucun stage."];
        }

        return $insights;
    }
}

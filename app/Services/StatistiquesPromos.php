<?php

namespace App\Services;

use App\Enums\StatutEtudiant;
use App\Models\Parametre;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Statistiques par promo : devenir des étudiants, spécialités, stages.
 * Calculées à la volée (quelques centaines d'étudiants) : aucune table dédiée.
 */
class StatistiquesPromos
{
    /** Promos présentes en base, de la plus récente à la plus ancienne. */
    public function promosDisponibles(): Collection
    {
        return User::role('Etudiant')
            ->whereNotNull('promo')
            ->distinct()
            ->orderByDesc('promo')
            ->pluck('promo')
            ->map(fn ($p) => (int) $p)
            ->values();
    }

    /**
     * @param  array<int>  $promos
     * @param  ?string  $spe  SLAM, SISR, aucune ou null (toutes)
     * @return Collection<int, array<string, mixed>> une ligne par promo, de la plus récente à la plus ancienne
     */
    public function calculer(array $promos, ?string $spe = null): Collection
    {
        $annee = Parametre::anneeActive();
        $enCours = array_values(Parametre::promos($annee));

        $etudiants = User::role('Etudiant')
            ->whereIn('promo', $promos)
            ->when($spe === 'aucune', fn ($q) => $q->where(fn ($w) => $w->whereNull('spe')->orWhere('spe', '')))
            ->when(in_array($spe, ['SLAM', 'SISR'], true), fn ($q) => $q->where('spe', $spe))
            ->withExists([
                'stages as a_stage',
                'stages as a_convention_validee' => fn ($q) => $q->where('statut_convention', 'validee'),
                'stages as a_stage_annee' => fn ($q) => $q->deLAnnee($annee),
                'stages as a_convention_annee' => fn ($q) => $q->deLAnnee($annee)->where('statut_convention', 'validee'),
            ])
            ->get(['id', 'promo', 'statut', 'spe']);

        return $etudiants->groupBy('promo')
            ->map(fn (Collection $groupe, $promo) => $this->ligne((int) $promo, $groupe, in_array((int) $promo, $enCours, true)))
            ->sortByDesc('promo')
            ->values();
    }

    /**
     * Une promo en cours (SIO1/SIO2 de l'année active) n'a pas encore de résultats : ses taux de diplômés
     * et ses stages ne se comparent pas à ceux d'une promo terminée. Les indicateurs de stage portent donc
     * sur les actifs et l'année active pour une promo en cours, et sur tout le cursus (hors démissionnaires
     * et archivés, qui n'ont pas de stage exploitable) pour une promo terminée.
     */
    private function ligne(int $promo, Collection $groupe, bool $enCours): array
    {
        $total = $groupe->count();
        $statuts = [];
        foreach (StatutEtudiant::cases() as $cas) {
            $statuts[$cas->value] = $groupe->where('statut', $cas->value)->count();
        }

        $baseDiplomes = $total - $statuts['demissionnaire'];

        if ($enCours) {
            $baseStage = $groupe->where('statut', StatutEtudiant::Actif->value);
            $avecStage = $baseStage->where('a_stage_annee', true)->count();
            $conventionValidee = $baseStage->where('a_convention_annee', true)->count();
        } else {
            $baseStage = $groupe->whereNotIn('statut', [StatutEtudiant::Demissionnaire->value, StatutEtudiant::Archive->value]);
            $avecStage = $baseStage->where('a_stage', true)->count();
            $conventionValidee = $baseStage->where('a_convention_validee', true)->count();
        }

        $slam = $groupe->where('spe', 'SLAM')->count();
        $sisr = $groupe->where('spe', 'SISR')->count();

        return [
            'promo' => $promo,
            'total' => $total,
            'statuts' => $statuts,
            'specialites' => ['SLAM' => $slam, 'SISR' => $sisr, 'aucune' => $total - $slam - $sisr],
            'terminee' => ! $enCours,
            'base_stage' => $baseStage->count(),
            'avec_stage' => $avecStage,
            'convention_validee' => $conventionValidee,
            'actifs_sans_stage' => $enCours ? $baseStage->count() - $avecStage : 0,
            // Base des diplômés : la promo hors démissionnaires (ils ne se présentent pas à l'examen).
            'base_diplomes' => $baseDiplomes,
            'taux_diplomes' => $enCours ? null : $this->pourcent($statuts['diplome'], $baseDiplomes),
            'taux_demission' => $this->pourcent($statuts['demissionnaire'], $total),
        ];
    }

    private function pourcent(int $partie, int $total): float
    {
        return $total > 0 ? round($partie * 100 / $total, 1) : 0.0;
    }
}

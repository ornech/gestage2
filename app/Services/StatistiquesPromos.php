<?php

namespace App\Services;

use App\Enums\StatutEtudiant;
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
        $etudiants = User::role('Etudiant')
            ->whereIn('promo', $promos)
            ->when($spe === 'aucune', fn ($q) => $q->where(fn ($w) => $w->whereNull('spe')->orWhere('spe', '')))
            ->when(in_array($spe, ['SLAM', 'SISR'], true), fn ($q) => $q->where('spe', $spe))
            ->withExists([
                'stages as a_stage',
                'stages as a_convention_validee' => fn ($q) => $q->where('statut_convention', 'validee'),
            ])
            ->get(['id', 'promo', 'statut', 'spe']);

        return $etudiants->groupBy('promo')
            ->map(fn (Collection $groupe, $promo) => $this->ligne((int) $promo, $groupe))
            ->sortByDesc('promo')
            ->values();
    }

    private function ligne(int $promo, Collection $groupe): array
    {
        $total = $groupe->count();
        $statuts = [];
        foreach (StatutEtudiant::cases() as $cas) {
            $statuts[$cas->value] = $groupe->where('statut', $cas->value)->count();
        }

        $slam = $groupe->where('spe', 'SLAM')->count();
        $sisr = $groupe->where('spe', 'SISR')->count();

        return [
            'promo' => $promo,
            'total' => $total,
            'statuts' => $statuts,
            'specialites' => ['SLAM' => $slam, 'SISR' => $sisr, 'aucune' => $total - $slam - $sisr],
            'avec_stage' => $groupe->where('a_stage', true)->count(),
            'convention_validee' => $groupe->where('a_convention_validee', true)->count(),
            'taux_diplomes' => $this->pourcent($statuts['diplome'], $total),
            'taux_demission' => $this->pourcent($statuts['demissionnaire'], $total),
        ];
    }

    private function pourcent(int $partie, int $total): float
    {
        return $total > 0 ? round($partie * 100 / $total, 1) : 0.0;
    }
}

@extends('layouts.app')

@section('content')
@php
    $couleursStatut = ['actif' => '#48c78e', 'diplome' => '#485fc7', 'echec' => '#f59f00', 'archive' => '#7a7a7a', 'demissionnaire' => '#f14668'];
    $couleursSpe = ['SLAM' => '#3273dc', 'SISR' => '#7957d5', 'aucune' => '#b5b5b5'];
    $libellesSpe = ['SLAM' => 'SLAM', 'SISR' => 'SISR', 'aucune' => 'Non définie'];
    $pourcent = $mode === 'pourcentages';

    $totalEtudiants = $lignes->sum('total');
    $totalDiplomes = $lignes->sum(fn ($l) => $l['statuts']['diplome']);
    $totalDemissions = $lignes->sum(fn ($l) => $l['statuts']['demissionnaire']);
    $totalAvecStage = $lignes->sum('avec_stage');
    $pct = fn ($a, $b) => $b > 0 ? round($a * 100 / $b) : 0;

    // Barre empilée : $valeurs = [clé => nombre], $couleurs/$libelles = [clé => ...]
    $barre = function (array $valeurs, int $total, array $couleurs, array $libelles) use ($pourcent, $maxTotal) {
        $largeur = $pourcent ? 100 : round($total * 100 / $maxTotal, 1);
        $html = '<div style="width:'.$largeur.'%; min-width:2rem; display:flex; height:28px; border-radius:4px; overflow:hidden; background:#f0f0f0;">';
        foreach ($valeurs as $cle => $n) {
            if ($n <= 0) { continue; }
            $part = $total > 0 ? $n * 100 / $total : 0;
            $texte = $pourcent ? round($part).' %' : $n;
            $html .= '<div title="'.e($libelles[$cle]).' : '.$n.' ('.round($part).' %)" style="flex:'.$n.'; background:'.$couleurs[$cle].'; color:#fff; font-size:.75rem; font-weight:600; display:flex; align-items:center; justify-content:center; min-width:0; overflow:hidden;">'.($part >= 7 ? $texte : '').'</div>';
        }
        return $html.'</div>';
    };
@endphp

<div class="container mt-4">

    <div class="mb-4">
        <h1 class="title is-4 mb-0"><i class="fas fa-chart-bar mr-2 has-text-info"></i> Statistiques des promos</h1>
        <p class="is-size-7 has-text-grey">Devenir des étudiants, spécialités et stages, promo par promo.</p>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('admin.statistiques') }}" class="box mb-4">
        <div class="columns is-vcentered is-multiline mb-0">
            <div class="column is-12-tablet is-6-desktop">
                <p class="heading mb-1">Promos</p>
                <div class="field is-grouped is-grouped-multiline mb-0">
                    @foreach($disponibles as $p)
                        <label class="checkbox mr-3">
                            <input type="checkbox" name="promos[]" value="{{ $p }}"
                                   {{ !$filtreTouche || $choisies->contains($p) ? 'checked' : '' }}>
                            {{ $p }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="column is-6-tablet is-2-desktop">
                <p class="heading mb-1">Spécialité</p>
                <div class="select is-small">
                    <select name="spe">
                        <option value="">Toutes</option>
                        <option value="SLAM" {{ $spe === 'SLAM' ? 'selected' : '' }}>SLAM</option>
                        <option value="SISR" {{ $spe === 'SISR' ? 'selected' : '' }}>SISR</option>
                        <option value="aucune" {{ $spe === 'aucune' ? 'selected' : '' }}>Non définie</option>
                    </select>
                </div>
            </div>
            <div class="column is-6-tablet is-2-desktop">
                <p class="heading mb-1">Affichage</p>
                <div class="select is-small">
                    <select name="mode">
                        <option value="effectifs" {{ !$pourcent ? 'selected' : '' }}>Effectifs</option>
                        <option value="pourcentages" {{ $pourcent ? 'selected' : '' }}>Pourcentages</option>
                    </select>
                </div>
            </div>
            <div class="column is-6-tablet is-2-desktop has-text-right">
                <a href="{{ route('admin.statistiques') }}" class="button is-light is-small">Réinitialiser</a>
                <button type="submit" class="button is-info is-small">
                    <i class="fas fa-sync-alt mr-1"></i> Appliquer
                </button>
            </div>
        </div>
    </form>

    @if($lignes->isEmpty())
        <div class="notification is-light">Aucun étudiant pour ces critères.</div>
    @else

    {{-- Chiffres clés --}}
    <div class="columns is-multiline is-mobile mb-2">
        <div class="column is-6-mobile is-3-desktop">
            <div class="box p-3 has-text-centered mb-0">
                <p class="heading">Étudiants</p>
                <p class="title is-4 mb-0">{{ $totalEtudiants }}</p>
                <p class="is-size-7 has-text-grey">{{ $lignes->count() }} promo(s)</p>
            </div>
        </div>
        <div class="column is-6-mobile is-3-desktop">
            <div class="box p-3 has-text-centered mb-0">
                <p class="heading">Diplômés</p>
                <p class="title is-4 mb-0 has-text-link">{{ $pct($totalDiplomes, $totalEtudiants) }} %</p>
                <p class="is-size-7 has-text-grey">{{ $totalDiplomes }} sur {{ $totalEtudiants }}</p>
            </div>
        </div>
        <div class="column is-6-mobile is-3-desktop">
            <div class="box p-3 has-text-centered mb-0">
                <p class="heading">Démissionnaires</p>
                <p class="title is-4 mb-0 has-text-danger">{{ $pct($totalDemissions, $totalEtudiants) }} %</p>
                <p class="is-size-7 has-text-grey">{{ $totalDemissions }} sur {{ $totalEtudiants }}</p>
            </div>
        </div>
        <div class="column is-6-mobile is-3-desktop">
            <div class="box p-3 has-text-centered mb-0">
                <p class="heading">Ont un stage</p>
                <p class="title is-4 mb-0 has-text-success">{{ $pct($totalAvecStage, $totalEtudiants) }} %</p>
                <p class="is-size-7 has-text-grey">{{ $totalAvecStage }} sur {{ $totalEtudiants }}</p>
            </div>
        </div>
    </div>

    {{-- Devenir des étudiants --}}
    <div class="box mt-4">
        <p class="menu-label">Devenir des étudiants par promo</p>
        @foreach($lignes as $l)
            <div class="is-flex is-align-items-center mb-2">
                <span class="has-text-weight-semibold mr-3" style="width:3.5rem;">{{ $l['promo'] }}</span>
                <div style="flex:1;">{!! $barre($l['statuts'], $l['total'], $couleursStatut, $statutsLibelles) !!}</div>
                <span class="is-size-7 has-text-grey ml-3" style="width:3.5rem; text-align:right;">{{ $l['total'] }} ét.</span>
            </div>
        @endforeach
        <div class="is-flex is-flex-wrap-wrap mt-3 is-size-7">
            @foreach($statutsLibelles as $cle => $libelle)
                <span class="mr-4"><span style="display:inline-block; width:.8rem; height:.8rem; border-radius:2px; background:{{ $couleursStatut[$cle] }}; vertical-align:middle;"></span> {{ $libelle }}</span>
            @endforeach
        </div>
    </div>

    {{-- Spécialités --}}
    <div class="box">
        <p class="menu-label">Spécialités par promo</p>
        @foreach($lignes as $l)
            <div class="is-flex is-align-items-center mb-2">
                <span class="has-text-weight-semibold mr-3" style="width:3.5rem;">{{ $l['promo'] }}</span>
                <div style="flex:1;">{!! $barre($l['specialites'], $l['total'], $couleursSpe, $libellesSpe) !!}</div>
                <span class="is-size-7 has-text-grey ml-3" style="width:3.5rem; text-align:right;">{{ $l['total'] }} ét.</span>
            </div>
        @endforeach
        <div class="is-flex is-flex-wrap-wrap mt-3 is-size-7">
            @foreach($libellesSpe as $cle => $libelle)
                <span class="mr-4"><span style="display:inline-block; width:.8rem; height:.8rem; border-radius:2px; background:{{ $couleursSpe[$cle] }}; vertical-align:middle;"></span> {{ $libelle }}</span>
            @endforeach
        </div>
    </div>

    {{-- Stages --}}
    <div class="box">
        <p class="menu-label">Stages par promo (part des étudiants de la promo)</p>
        @foreach($lignes as $l)
            @php
                $pStage = $pct($l['avec_stage'], $l['total']);
                $pConv = $pct($l['convention_validee'], $l['total']);
            @endphp
            <div class="mb-3">
                <span class="has-text-weight-semibold">{{ $l['promo'] }}</span>
                <div class="is-flex is-align-items-center">
                    <span class="is-size-7 has-text-grey" style="width:9rem;">Ont un stage</span>
                    <progress class="progress is-success is-small mb-0" value="{{ $pStage }}" max="100" style="flex:1;"></progress>
                    <span class="is-size-7 ml-3" style="width:6.5rem; text-align:right;">{{ $l['avec_stage'] }}/{{ $l['total'] }} · {{ $pStage }} %</span>
                </div>
                <div class="is-flex is-align-items-center">
                    <span class="is-size-7 has-text-grey" style="width:9rem;">Convention validée</span>
                    <progress class="progress is-info is-small mb-0" value="{{ $pConv }}" max="100" style="flex:1;"></progress>
                    <span class="is-size-7 ml-3" style="width:6.5rem; text-align:right;">{{ $l['convention_validee'] }}/{{ $l['total'] }} · {{ $pConv }} %</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Détail chiffré --}}
    <div class="box">
        <p class="menu-label">Détail</p>
        <div class="table-container">
        <table class="table is-fullwidth is-narrow is-hoverable">
            <thead>
                <tr>
                    <th>Promo</th><th class="has-text-right">Total</th>
                    @foreach($statutsLibelles as $libelle)<th class="has-text-right">{{ $libelle }}</th>@endforeach
                    <th class="has-text-right">SLAM</th><th class="has-text-right">SISR</th>
                    <th class="has-text-right">% diplômés</th><th class="has-text-right">% démiss.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $l)
                <tr>
                    <td class="has-text-weight-semibold">{{ $l['promo'] }}</td>
                    <td class="has-text-right">{{ $l['total'] }}</td>
                    @foreach($statutsLibelles as $cle => $libelle)<td class="has-text-right">{{ $l['statuts'][$cle] }}</td>@endforeach
                    <td class="has-text-right">{{ $l['specialites']['SLAM'] }}</td>
                    <td class="has-text-right">{{ $l['specialites']['SISR'] }}</td>
                    <td class="has-text-right">{{ $l['taux_diplomes'] }} %</td>
                    <td class="has-text-right">{{ $l['taux_demission'] }} %</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <p class="help">Les promos encore en cours comptent surtout des étudiants actifs : les taux de diplômés et de démission ne sont significatifs que pour les promos terminées.</p>
    </div>

    @endif
</div>
@endsection

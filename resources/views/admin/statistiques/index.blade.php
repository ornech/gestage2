@extends('layouts.app')

@section('content')
@php
    $totalEtudiants = $lignes->sum('total');
    $totalDiplomes = $lignes->sum(fn ($l) => $l['statuts']['diplome']);
    $totalDemissions = $lignes->sum(fn ($l) => $l['statuts']['demissionnaire']);
    $totalAvecStage = $lignes->sum('avec_stage');
    $pct = fn ($a, $b) => $b > 0 ? round($a * 100 / $b) : 0;
    $statutsLibelles = $donnees['statuts'];
@endphp

<div class="container mt-4">

    <div class="level mb-3">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0"><i class="fas fa-chart-bar mr-2 has-text-info"></i> Statistiques des promos</h1>
                <p class="is-size-7 has-text-grey">Cliquez sur une barre pour ouvrir la liste des étudiants correspondants.</p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ $exportUrl }}" class="button is-light is-small">
                <i class="fas fa-file-csv mr-1"></i> Exporter en CSV
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('admin.statistiques') }}" class="box p-3 mb-4">
        <div class="columns is-vcentered is-multiline mb-0">
            <div class="column is-12-tablet is-7-desktop">
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
            <div class="column is-6-tablet is-3-desktop has-text-right">
                <a href="{{ route('admin.statistiques') }}" class="button is-light is-small">Réinitialiser</a>
                <button type="submit" class="button is-info is-small"><i class="fas fa-sync-alt mr-1"></i> Appliquer</button>
            </div>
        </div>
    </form>

    @if($lignes->isEmpty())
        <div class="notification is-light">Aucun étudiant pour ces critères.</div>
    @else

    {{-- Chiffres clés --}}
    <div class="columns is-multiline is-mobile mb-0">
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

    {{-- Constats automatiques --}}
    @if(count($insights))
    <div class="box mt-4 p-4" style="background:#f7fafd;">
        <p class="menu-label"><i class="fas fa-lightbulb mr-1 has-text-warning"></i> À retenir</p>
        <ul>
            @foreach($insights as $i)
                <li class="mb-1"><i class="fas {{ $i['icone'] }} {{ $i['couleur'] }} mr-2" style="width:1.2rem; text-align:center;"></i>{{ $i['texte'] }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Bascule effectifs / pourcentages (agit sur tous les graphes sans recharger) --}}
    <div class="buttons has-addons mt-4 mb-2" id="bascule-mode">
        <button type="button" class="button is-small is-info" data-mode="effectifs">Effectifs</button>
        <button type="button" class="button is-small" data-mode="pourcentages">Pourcentages</button>
    </div>

    <div class="box">
        <p class="menu-label">Devenir des étudiants par promo</p>
        <div style="position:relative; height:{{ max(180, $lignes->count() * 44 + 80) }}px;"><canvas id="graphe-devenir"></canvas></div>
    </div>

    <div class="columns">
        <div class="column is-7">
            <div class="box" style="height:100%;">
                <p class="menu-label">Évolution d'une promo à l'autre (en %)</p>
                <div style="position:relative; height:320px;"><canvas id="graphe-tendance"></canvas></div>
            </div>
        </div>
        <div class="column is-5">
            <div class="box" style="height:100%;">
                <p class="menu-label">Stages et conventions</p>
                <div style="position:relative; height:320px;"><canvas id="graphe-stages"></canvas></div>
            </div>
        </div>
    </div>

    <div class="columns">
        <div class="column is-7">
            <div class="box" style="height:100%;">
                <p class="menu-label">Spécialités par promo</p>
                <div style="position:relative; height:300px;"><canvas id="graphe-spe"></canvas></div>
            </div>
        </div>
        <div class="column is-5">
            <div class="box" style="height:100%;">
                <p class="menu-label">Spécialités, toutes promos</p>
                <div style="position:relative; height:300px;"><canvas id="graphe-spe-total"></canvas></div>
            </div>
        </div>
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

    <script type="application/json" id="stats-data">@json($donnees)</script>
    @endif
</div>
@endsection

@push('scripts')
@if($lignes->isNotEmpty())
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script nonce="{{ $cspNonce ?? '' }}">
(function () {
    var data = JSON.parse(document.getElementById('stats-data').textContent);
    var promos = data.promos;                       // ordre chronologique
    var recents = promos.slice().reverse();         // barres horizontales : la plus récente en haut
    var mode = 'effectifs';
    var charts = {};

    var C = { actif: '#48c78e', diplome: '#485fc7', echec: '#f59f00', archive: '#9b9b9b', demissionnaire: '#f14668',
              SLAM: '#3273dc', SISR: '#7957d5', aucune: '#c8c8c8', stage: '#48c78e', convention: '#3e8ed0' };
    var SPE = { SLAM: 'SLAM', SISR: 'SISR', aucune: 'Non définie' };

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 12;

    function pct(n, total) { return total > 0 ? Math.round(n * 1000 / total) / 10 : 0; }
    function valeur(n, total) { return mode === 'pourcentages' ? pct(n, total) : n; }
    function suffixe() { return mode === 'pourcentages' ? ' %' : ''; }

    function recreer(id, config) {
        if (charts[id]) { charts[id].destroy(); }
        charts[id] = new Chart(document.getElementById(id), config);
    }

    function empile(horizontal, total) {
        var axeValeur = { stacked: true, beginAtZero: true, max: mode === 'pourcentages' ? 100 : undefined,
                          ticks: { callback: function (v) { return v + suffixe(); } } };
        var axeCategorie = { stacked: true, grid: { display: false } };
        return {
            indexAxis: horizontal ? 'y' : 'x',
            responsive: true, maintainAspectRatio: false,
            scales: horizontal ? { x: axeValeur, y: axeCategorie } : { y: axeValeur, x: axeCategorie },
            plugins: { legend: { position: 'bottom' } }
        };
    }

    function devenir() {
        var datasets = Object.keys(data.statuts).map(function (cle) {
            return {
                label: data.statuts[cle], cle: cle, backgroundColor: C[cle],
                data: recents.map(function (p) { return valeur(p.statuts[cle], p.total); })
            };
        });
        var options = empile(true);
        options.plugins.tooltip = { callbacks: { label: function (ctx) {
            var p = recents[ctx.dataIndex], n = p.statuts[ctx.dataset.cle];
            return ctx.dataset.label + ' : ' + n + ' (' + pct(n, p.total) + ' %)';
        } } };
        options.onHover = function (e, els) {
            var el = els[0];
            var lien = el && recents[el.index].liens[datasets[el.datasetIndex].cle];
            e.native.target.style.cursor = lien ? 'pointer' : 'default';
        };
        options.onClick = function (e, els) {
            if (!els.length) { return; }
            var lien = recents[els[0].index].liens[datasets[els[0].datasetIndex].cle];
            if (lien) { window.location.href = lien; }
        };
        recreer('graphe-devenir', { type: 'bar', data: { labels: recents.map(function (p) { return 'Promo ' + p.promo + ' (' + p.total + ')'; }), datasets: datasets }, options: options });
    }

    function tendance() {
        var serie = function (label, couleur, f) {
            return { label: label, borderColor: couleur, backgroundColor: couleur, tension: .25, pointRadius: 4, pointHoverRadius: 6,
                     data: promos.map(function (p) { return pct(f(p), p.total); }) };
        };
        recreer('graphe-tendance', { type: 'line',
            data: { labels: promos.map(function (p) { return p.promo; }), datasets: [
                serie('Diplômés', C.diplome, function (p) { return p.statuts.diplome; }),
                serie('Démissionnaires', C.demissionnaire, function (p) { return p.statuts.demissionnaire; }),
                serie('Ont un stage', C.stage, function (p) { return p.avec_stage; }),
                serie('Convention validée', C.convention, function (p) { return p.convention_validee; })
            ] },
            options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                scales: { y: { min: 0, max: 100, ticks: { callback: function (v) { return v + ' %'; } } } },
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function (c) { return c.dataset.label + ' : ' + c.parsed.y + ' %'; } } } } } });
    }

    function stages() {
        var jeu = function (label, couleur, cle) {
            return { label: label, backgroundColor: couleur, data: promos.map(function (p) { return valeur(p[cle], p.total); }), cle: cle };
        };
        recreer('graphe-stages', { type: 'bar',
            data: { labels: promos.map(function (p) { return p.promo; }), datasets: [
                jeu('Ont un stage', C.stage, 'avec_stage'), jeu('Convention validée', C.convention, 'convention_validee')
            ] },
            options: { responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, max: mode === 'pourcentages' ? 100 : undefined, ticks: { callback: function (v) { return v + suffixe(); } } }, x: { grid: { display: false } } },
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function (c) {
                    var p = promos[c.dataIndex], n = p[c.dataset.cle];
                    return c.dataset.label + ' : ' + n + '/' + p.total + ' (' + pct(n, p.total) + ' %)';
                } } } } } });
    }

    function specialites() {
        var cles = ['SLAM', 'SISR', 'aucune'];
        var options = empile(false);
        options.plugins.tooltip = { callbacks: { label: function (c) {
            var p = promos[c.dataIndex], n = p.specialites[c.dataset.cle];
            return c.dataset.label + ' : ' + n + ' (' + pct(n, p.total) + ' %)';
        } } };
        recreer('graphe-spe', { type: 'bar',
            data: { labels: promos.map(function (p) { return p.promo; }), datasets: cles.map(function (cle) {
                return { label: SPE[cle], cle: cle, backgroundColor: C[cle], data: promos.map(function (p) { return valeur(p.specialites[cle], p.total); }) };
            }) }, options: options });

        var totaux = cles.map(function (cle) { return promos.reduce(function (s, p) { return s + p.specialites[cle]; }, 0); });
        var somme = totaux.reduce(function (a, b) { return a + b; }, 0);
        recreer('graphe-spe-total', { type: 'doughnut',
            data: { labels: cles.map(function (c) { return SPE[c]; }), datasets: [{ data: totaux, backgroundColor: cles.map(function (c) { return C[c]; }) }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom' },
                tooltip: { callbacks: { label: function (c) { return c.label + ' : ' + c.parsed + ' (' + pct(c.parsed, somme) + ' %)'; } } } } } });
    }

    function tout() { devenir(); stages(); specialites(); }

    document.querySelectorAll('#bascule-mode [data-mode]').forEach(function (b) {
        b.addEventListener('click', function () {
            mode = b.dataset.mode;
            document.querySelectorAll('#bascule-mode [data-mode]').forEach(function (x) {
                x.classList.toggle('is-info', x === b);
            });
            tout();
        });
    });

    tendance();
    tout();
})();
</script>
@endif
@endpush

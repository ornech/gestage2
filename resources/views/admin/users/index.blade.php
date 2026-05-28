@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
    <div class="level">
        <div class="level-left">
            <h1 class="title mb-0">Étudiants</h1>
        </div>
        <div class="level-right">
            <a href="{{ route('imports.pronote.form') }}" class="button is-primary">
                <i class="fas fa-file-import mr-2"></i> Import Pronote
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

    {{-- ── Sélecteur d'années ──────────────────────────────────────────── --}}
    <div class="box p-3 mb-4">
        <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
            <span class="has-text-grey is-size-7 mr-2">Année :</span>
            @foreach($annees as $annee)
            @php $syAnnee = (int) explode('-', $annee)[0]; $isFuture = $syAnnee > $syActif; @endphp
                <a href="{{ route('admin.users.index', array_merge(request()->except('annee','page'), ['annee' => $annee])) }}"
                   class="button is-small {{ $annee === $anneeSelectionnee ? ($isFuture ? 'is-warning' : 'is-link') : 'is-light' }}"
                   title="{{ $isFuture ? 'Anticipation — rentrée de septembre' : '' }}">
                    {{ $annee }}
                    @if($annee === $anneeActive)
                        <span class="tag is-success ml-1" style="font-size:0.6rem; padding:2px 4px;">●</span>
                    @elseif($isFuture)
                        <span class="tag is-warning is-light ml-1" style="font-size:0.6rem; padding:2px 4px;">à venir</span>
                    @endif
                </a>
            @endforeach
        </div>
        @if((int) explode('-', $anneeSelectionnee)[0] > $syActif)
        <p class="is-size-7 has-text-warning mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            Anticipation : affichage des classes telles qu'elles seront à la rentrée {{ $anneeSelectionnee }}.
        </p>
        @endif
    </div>

    {{-- ── Statistiques ────────────────────────────────────────────────── --}}
    <div class="columns is-mobile mb-4">
        <div class="column">
            <a href="{{ route('admin.users.index', ['annee' => $anneeSelectionnee, 'filtre' => 'sio1']) }}"
               class="box has-text-centered" style="border-bottom: 3px solid #3273dc;">
                <p class="heading">SIO1</p>
                <p class="title is-4">{{ $stats['sio1'] }}</p>
                <p class="is-size-7 has-text-grey">actifs</p>
            </a>
        </div>
        <div class="column">
            <a href="{{ route('admin.users.index', ['annee' => $anneeSelectionnee, 'filtre' => 'sio2']) }}"
               class="box has-text-centered" style="border-bottom: 3px solid #00d1b2;">
                <p class="heading">SIO2</p>
                <p class="title is-4">{{ $stats['sio2'] }}</p>
                <p class="is-size-7 has-text-grey">actifs</p>
            </a>
        </div>
        <div class="column">
            <div class="box has-text-centered" style="border-bottom: 3px solid #ffdd57;">
                <p class="heading">Redoublants</p>
                <p class="title is-4">{{ $stats['redoublants'] }}</p>
                <p class="is-size-7 has-text-grey">cette année</p>
            </div>
        </div>
        <div class="column">
            <a href="{{ route('admin.users.index', ['annee' => $anneeSelectionnee, 'filtre' => 'anciens']) }}"
               class="box has-text-centered" style="border-bottom: 3px solid #f14668;">
                <p class="heading">Démissionnaires</p>
                <p class="title is-4">{{ $stats['demissionnaires'] }}</p>
                <p class="is-size-7 has-text-grey">total</p>
            </a>
        </div>
    </div>

    {{-- ── Filtres ─────────────────────────────────────────────────────── --}}
    <form method="GET" class="mb-3">
        <input type="hidden" name="annee" value="{{ $anneeSelectionnee }}">
        <div class="columns is-vcentered is-mobile">
            <div class="column">
                <div class="control has-icons-left">
                    <input class="input is-small" type="text" name="search"
                           placeholder="Nom, prénom ou email…"
                           value="{{ request('search') }}">
                    <span class="icon is-left is-small"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="buttons has-addons are-small">
                    @foreach(['sio1' => 'SIO1', 'sio2' => 'SIO2', 'tout' => 'Tout afficher'] as $val => $label)
                        <a href="{{ route('admin.users.index', array_merge(request()->except('filtre','search','page'), ['annee' => $anneeSelectionnee, 'filtre' => $val])) }}"
                           class="button {{ $filtre === $val ? 'is-link' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
            @if(request('search'))
            <div class="column is-narrow">
                <a href="{{ route('admin.users.index', ['annee' => $anneeSelectionnee, 'filtre' => $filtre]) }}"
                   class="button is-small is-light">✕</a>
            </div>
            @endif
        </div>
    </form>

    {{-- Indicateur recherche globale --}}
    @if(request('search'))
        <div class="notification is-info is-light py-2 mb-3">
            <i class="fas fa-search mr-2"></i>
            Recherche dans <strong>tous les étudiants</strong> pour
            « {{ request('search') }} » —
            <a href="{{ route('admin.users.index', ['annee' => $anneeSelectionnee, 'filtre' => $filtre]) }}">
                Effacer la recherche
            </a>
        </div>
    @endif

    {{-- ── Tableau ─────────────────────────────────────────────────────── --}}
    <table class="table is-striped is-fullwidth is-hoverable is-size-7">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Classe</th>
                <th>Spé</th>
                <th>Promo</th>
                <th>Statut</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php
                $anneeEtude = $user->promo ? (3 - ($user->promo - $syInt)) : null;
                $classeAnnee = ($anneeEtude >= 1 && $anneeEtude <= 2) ? 'SIO'.$anneeEtude : null;

                $statutColor = match($user->statut) {
                    'actif'          => 'is-success is-light',
                    'redoublant'     => 'is-warning is-light',
                    'demissionnaire' => 'is-danger is-light',
                    default          => 'is-light',
                };
                $statutLabel = match($user->statut) {
                    'actif'          => 'Actif',
                    'redoublant'     => 'Redoublant',
                    'demissionnaire' => 'Démiss.',
                    default          => '—',
                };
            @endphp
            <tr>
                <td>{{ $user->nom }}</td>
                <td>{{ $user->prenom }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($classeAnnee)
                        <span class="tag is-info is-light">{{ $classeAnnee }}</span>
                    @elseif($user->promo && $user->promo <= $syInt)
                        <span class="tag is-success is-light">Diplômé {{ $user->promo }}</span>
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>
                <td>{{ $user->spe ?? '—' }}</td>
                <td>{{ $user->promo ?? '—' }}</td>
                <td><span class="tag {{ $statutColor }}">{{ $statutLabel }}</span></td>
                <td>
                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="button is-small is-warning" title="Modifier">
                        <i class="fas fa-pen"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="has-text-centered has-text-grey py-5">
                    Aucun étudiant pour ce filtre.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $users->withQueryString()->links() }}

</div>
@endsection

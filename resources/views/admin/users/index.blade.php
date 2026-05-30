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

    {{-- ── Années + stats + recherche sur une seule ligne ───────────── --}}
    <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;" class="mb-3">

        {{-- Sélecteur d'années --}}
        <span class="has-text-grey is-size-7">Année :</span>
        <div class="buttons has-addons are-small mb-0">
            @foreach($annees as $annee)
                <a href="{{ route('admin.users.index', array_merge(request()->except('annee','page'), ['annee' => $annee])) }}"
                   class="button is-small {{ $annee === $anneeSelectionnee ? 'is-link' : 'is-light' }}">
                    {{ $annee }}
                    @if($annee === $anneeActive)
                        <span style="font-size:0.55rem; margin-left:3px;">●</span>
                    @endif
                </a>
            @endforeach
        </div>

        <span class="has-text-grey">|</span>

        {{-- Stats compactes --}}
        <span class="tag is-info is-light is-medium">
            {{ $stats['actifs'] }} actif(s)
            @if($classeParam && $classeParam !== 'tous') ({{ $classeParam }}) @endif
        </span>
        <span class="tag is-danger is-light is-medium">{{ $stats['demissionnaires'] }} démiss.</span>

        <span class="has-text-grey">|</span>

        {{-- Recherche --}}
        <form method="GET" style="flex:1; min-width:200px;">
            <input type="hidden" name="annee"  value="{{ $anneeSelectionnee }}">
            <input type="hidden" name="classe" value="{{ $classeParam }}">
            <div class="control has-icons-left" style="display:flex; gap:4px;">
                <input class="input is-small" type="text" name="search"
                       placeholder="Nom, prénom ou email…"
                       value="{{ request('search') }}">
                <span class="icon is-left is-small"><i class="fas fa-search"></i></span>
                @if(request('search'))
                    <a href="{{ route('admin.users.index', ['annee' => $anneeSelectionnee, 'classe' => $classeParam]) }}"
                       class="button is-small is-light">✕</a>
                @endif
            </div>
        </form>

    </div>

    {{-- ── Tableau ─────────────────────────────────────────────────────── --}}
    <div class="table-scroll"><table class="table is-striped is-fullwidth is-hoverable is-size-7">
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
                    'demissionnaire' => 'is-danger is-light',
                    default          => 'is-light',
                };
                $statutLabel = match($user->statut) {
                    'actif'          => 'Actif',
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
    </table></div>

    {{ $users->withQueryString()->links() }}

</div>
@endsection

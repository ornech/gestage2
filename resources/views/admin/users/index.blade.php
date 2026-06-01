@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
    <div class="level">
        <div class="level-left">
            <h1 class="title mb-0">
                Étudiants
                @if(isset($classeParam) && $classeParam && $classeParam !== 'tous')
                    <span class="tag {{ $classeParam === 'SIO1' ? 'is-info' : 'is-primary' }} is-large ml-2" style="vertical-align:middle;">{{ $classeParam }}</span><span class="tag is-large" style="vertical-align:middle; background:#e0e0e0; color:#555;">{{ $classeParam === 'SIO1' ? 'Première année' : 'Deuxième année' }}</span>
                @endif
            </h1>
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

    {{-- ── Stats (gauche) + Année dropdown (droite) ──────────────────── --}}
    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;" class="mb-3">

        {{-- Stats gauche --}}
        <div style="display:flex; gap:8px; align-items:center;">
            <span class="tag {{ $classeParam === 'SIO2' ? 'is-primary' : 'is-info' }} is-light is-medium">
                {{ $stats['actifs'] }} actif(s)
                @if($classeParam && $classeParam !== 'tous') — {{ $classeParam }} @endif
            </span>
            <span class="tag is-danger is-light is-medium">{{ $stats['demissionnaires'] }} démiss.</span>
        </div>

        {{-- Année dropdown droite --}}
        <form method="GET">
            <input type="hidden" name="classe" value="{{ $classeParam }}">
            <div class="select is-small">
                <select name="annee" onchange="this.form.submit()">
                    @foreach($annees as $annee)
                        <option value="{{ $annee }}" {{ $annee === $anneeSelectionnee ? 'selected' : '' }}>
                            {{ $annee }}{{ $annee === $anneeActive ? ' ●' : '' }}
                        </option>
                    @endforeach
                </select>
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
                        <span class="tag {{ $classeAnnee === 'SIO1' ? 'is-info' : 'is-primary' }}">{{ $classeAnnee }}</span>
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
                    @role('Administrateur')
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="button is-small is-warning" title="Modifier">
                            <i class="fas fa-pen"></i>
                        </a>
                    @else
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="button is-small is-light" title="Voir le profil">
                            <i class="fas fa-eye"></i>
                        </a>
                    @endrole
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

@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0"><i class="fas fa-broom mr-2"></i> Nettoyage des comptes</h1>
                <p class="is-size-7 has-text-grey mt-1">Comptes à email provisoire et doublons détectés</p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.dashboard') }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light py-2 mb-4">
            <i class="fas fa-check mr-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Doublons ──────────────────────────────────────────────── --}}
    <div class="box mb-5">
        <p class="menu-label mb-3">
            Doublons détectés
            @if($doublons->isNotEmpty())
                <span class="tag is-danger ml-2">{{ $doublons->count() }}</span>
            @else
                <span class="tag is-success is-light ml-2">Aucun</span>
            @endif
        </p>

        @if($doublons->isEmpty())
            <p class="has-text-grey is-italic is-size-7">Aucun doublon détecté.</p>
        @else
        @foreach($doublons as $groupe)
        <div class="notification is-warning is-light py-3 mb-3">
            <p class="has-text-weight-semibold mb-2">
                <i class="fas fa-copy mr-1"></i>
                {{ preg_replace('/\s+/', ' ', trim($groupe->first()->nom)) }}
                {{ $groupe->first()->prenom }}
                — promo {{ $groupe->first()->promo }}
            </p>
            <table class="table is-size-7 is-fullwidth mb-3">
                <thead><tr><th>ID</th><th>Email</th><th>Statut</th><th>Stages</th></tr></thead>
                <tbody>
                @foreach($groupe as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>
                        {{ $u->email }}
                        @if(str_ends_with($u->email, '@import.local'))
                            <span class="tag is-warning is-light is-small ml-1">provisoire</span>
                        @endif
                    </td>
                    <td><span class="tag is-{{ $u->statut === 'actif' ? 'success' : 'danger' }} is-light is-small">{{ $u->statut }}</span></td>
                    <td>{{ $u->stages()->count() }} stage(s)</td>
                </tr>
                @endforeach
                </tbody>
            </table>

            @php
                // Suggérer : garder celui avec le vrai email, supprimer l'import.local
                $aGarder    = $groupe->first(fn($u) => !str_ends_with($u->email, '@import.local')) ?? $groupe->first();
                $aSupprimer = $groupe->first(fn($u) => $u->id !== $aGarder->id);
            @endphp

            <form action="{{ route('admin.comptes.fusionner') }}" method="POST"
                  onsubmit="return confirm('Fusionner ces deux comptes ? L\'action est irréversible.')">
                @csrf
                <div class="field has-addons">
                    <div class="control">
                        <span class="button is-static is-small">Garder ID</span>
                    </div>
                    <div class="control">
                        <div class="select is-small">
                            <select name="garder_id">
                                @foreach($groupe as $u)
                                    <option value="{{ $u->id }}" {{ $u->id === $aGarder->id ? 'selected' : '' }}>
                                        ID {{ $u->id }} — {{ $u->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="control">
                        <span class="button is-static is-small">Supprimer ID</span>
                    </div>
                    <div class="control">
                        <div class="select is-small">
                            <select name="supprimer_id">
                                @foreach($groupe as $u)
                                    <option value="{{ $u->id }}" {{ $u->id === $aSupprimer?->id ? 'selected' : '' }}>
                                        ID {{ $u->id }} — {{ $u->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="control">
                        <button type="submit" class="button is-danger is-small">
                            <i class="fas fa-compress-alt mr-1"></i> Fusionner
                        </button>
                    </div>
                </div>
                <p class="help">Les stages du compte supprimé seront transférés vers le compte conservé.</p>
            </form>
        </div>
        @endforeach
        @endif
    </div>

    {{-- ── Comptes @import.local ─────────────────────────────────── --}}
    <div class="box">
        <p class="menu-label mb-1">
            Comptes avec email provisoire (@import.local)
            <span class="tag is-warning ml-2">{{ $importLocal->count() }}</span>
        </p>
        <p class="is-size-7 has-text-grey mb-3">
            Ces comptes n'ont pas encore d'email réel. Importez le CSV Pronote correspondant
            pour les mettre à jour automatiquement, ou saisissez l'email manuellement.
        </p>

        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable is-size-7">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email provisoire</th>
                        <th>Promo</th>
                        <th>Statut</th>
                        <th>Mettre à jour l'email</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($importLocal as $u)
                <tr>
                    <td>{{ $u->nom }}</td>
                    <td>{{ $u->prenom }}</td>
                    <td class="has-text-grey-light">{{ $u->email }}</td>
                    <td>{{ $u->promo ?? '—' }}</td>
                    <td>
                        <span class="tag is-{{ $u->statut === 'actif' ? 'success' : 'danger' }} is-light is-small">
                            {{ $u->statut }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('admin.comptes.update-email', $u) }}" method="POST"
                              style="display:flex; gap:4px;">
                            @csrf
                            <input class="input is-small" type="email" name="email"
                                   placeholder="prenom.nom@domaine.fr"
                                   style="max-width:220px;" required>
                            <button type="submit" class="button is-primary is-small">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="notification is-info is-light py-2 mt-3" style="font-size:.82rem;">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Tip :</strong> Un import Pronote (SIO1 ou SIO2) mettra à jour automatiquement
            les emails provisoires des étudiants trouvés par nom/prénom.
            <a href="{{ route('imports.pronote.form') }}" class="ml-1">→ Lancer un import</a>
        </div>
    </div>

</div>
@endsection

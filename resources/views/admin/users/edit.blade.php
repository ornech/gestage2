@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:900px;">

    <h1 class="title">{{ $user->prenom }} {{ $user->nom }}</h1>
    <p class="subtitle has-text-grey">{{ $user->email }}</p>

    @if($errors->any())
        <div class="notification is-danger is-light">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="columns">

            {{-- Colonne gauche : identité --}}
            <div class="column">
                <div class="box">
                    <p class="menu-label">Identité</p>

                    <div class="field">
                        <label class="label is-small">Nom</label>
                        <input class="input" type="text" name="nom"
                               value="{{ old('nom', $user->nom) }}" required>
                    </div>
                    <div class="field">
                        <label class="label is-small">Prénom</label>
                        <input class="input" type="text" name="prenom"
                               value="{{ old('prenom', $user->prenom) }}" required>
                    </div>
                    <div class="field">
                        <label class="label is-small">Email</label>
                        <input class="input" type="email" name="email"
                               value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>
            </div>

            {{-- Colonne droite : scolarité --}}
            <div class="column">
                <div class="box">
                    <p class="menu-label">Scolarité</p>

                    <div class="field">
                        <label class="label is-small">Classe</label>
                        <div class="select is-fullwidth">
                            <select name="classe" id="classe-select" onchange="updatePromo()">
                                <option value="">— Non définie —</option>
                                @foreach(['SIO1', 'SIO2'] as $c)
                                    @php
                                        $current = $user->classe_courante ?? $user->classe;
                                    @endphp
                                    <option value="{{ $c }}"
                                        {{ old('classe', $current) === $c ? 'selected' : '' }}>
                                        {{ $c }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label is-small">
                            Promotion
                            <span class="has-text-grey is-size-7">(calculée automatiquement)</span>
                        </label>
                        <input class="input" type="number" name="promo" id="promo-input"
                               value="{{ old('promo', $user->promo) }}" readonly>
                    </div>

                    <div class="field">
                        <label class="label is-small">
                            Spécialité
                            @if(!$isOpen)
                                <span class="tag is-warning is-light ml-1">Disponible au second semestre</span>
                            @endif
                        </label>
                        <div class="select is-fullwidth">
                            <select name="spe" {{ !$isOpen ? 'disabled' : '' }}>
                                <option value="">— Non définie —</option>
                                <option value="SLAM" {{ old('spe', $user->spe) === 'SLAM' ? 'selected' : '' }}>SLAM</option>
                                <option value="SISR" {{ old('spe', $user->spe) === 'SISR' ? 'selected' : '' }}>SISR</option>
                            </select>
                        </div>
                        @if(!$isOpen && $user->spe)
                            <p class="help">Spécialité actuelle : <strong>{{ $user->spe }}</strong></p>
                        @endif
                    </div>

                    <div class="field">
                        <label class="label is-small">Tuteur référent</label>
                        <div class="select is-fullwidth">
                            <select name="tuteur_id">
                                <option value="">— Aucun —</option>
                                @foreach($tuteurs as $tuteur)
                                    <option value="{{ $tuteur->id }}"
                                        {{ old('tuteur_id', $user->tuteur_id) == $tuteur->id ? 'selected' : '' }}>
                                        {{ $tuteur->prenom }} {{ $tuteur->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="field is-grouped mt-2">
            <div class="control">
                <button type="submit" class="button is-primary">Enregistrer</button>
            </div>
            <div class="control">
                <a href="{{ route('admin.users.index') }}" class="button is-light">Annuler</a>
            </div>
        </div>
    </form>

    {{-- Statut administratif — réservé aux profs et admins --}}
    @role('Professeur|Administrateur')
    <div class="box mt-5">
        <p class="menu-label">Statut administratif</p>
        <p class="is-size-7 has-text-grey mb-3">
            Seuls les professeurs et administrateurs peuvent modifier ce statut.
        </p>

        <div class="buttons">
            @foreach(['actif' => ['is-success','Actif'], 'redoublant' => ['is-warning','Redoublant'], 'demissionnaire' => ['is-danger','Démissionnaire']] as $val => [$color, $label])
                @if($user->statut === $val)
                    <span class="button {{ $color }}">
                        <i class="fas fa-check mr-2"></i> {{ $label }}
                    </span>
                @else
                    <form action="{{ route('admin.users.statut', $user) }}" method="POST" style="display:inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="statut" value="{{ $val }}">
                        <button type="submit" class="button is-light"
                                onclick="return confirm('Changer le statut en {{ $label }} ?')">
                            {{ $label }}
                        </button>
                    </form>
                @endif
            @endforeach
        </div>

        @if($user->statut === 'demissionnaire')
            <p class="help is-danger">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Cet étudiant ne peut plus se connecter à l'application.
            </p>
        @endif
    </div>
    @endrole
</div>

<script nonce="{{ $cspNonce ?? '' }}">
const promos = { 'SIO1': {{ $currentYear + 2 }}, 'SIO2': {{ $currentYear + 1 }} };

function updatePromo() {
    const sel   = document.getElementById('classe-select');
    const input = document.getElementById('promo-input');
    input.value = promos[sel.value] ?? '';
}
</script>
@endsection

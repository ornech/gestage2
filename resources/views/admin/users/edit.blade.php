@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:900px;">

    {{-- En-tête --}}
    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title mb-0">{{ $user->prenom }} {{ $user->nom }}</h1>
                <p class="is-size-7 has-text-grey">{{ $user->email }}</p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.users.index') }}" class="button is-light is-small">
                ← Retour à la liste
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="notification is-danger is-light">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="columns">

            {{-- ── Colonne gauche : Identité + Statut ──────────────────── --}}
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

                {{-- Statut administratif (action immédiate, hors formulaire principal) --}}
                @role('Professeur|Administrateur')
                <div class="box">
                    <p class="menu-label">Statut administratif</p>

                    <div class="buttons">
                        @foreach([
                            'actif'          => ['is-success', 'Actif'],
                            'redoublant'      => ['is-warning', 'Redoublant'],
                            'demissionnaire'  => ['is-danger',  'Démissionnaire'],
                        ] as $val => [$color, $label])
                            @if($user->statut === $val)
                                <span class="button {{ $color }}">
                                    <i class="fas fa-check mr-2"></i> {{ $label }}
                                </span>
                            @else
                                <form action="{{ route('admin.users.statut', $user) }}"
                                      method="POST" style="display:inline">
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
                        <p class="help is-danger mt-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Cet étudiant ne peut plus se connecter à l'application.
                        </p>
                    @endif
                </div>
                @endrole

            </div>

            {{-- ── Colonne droite : Scolarité ──────────────────────────── --}}
            <div class="column">
                <div class="box">
                    <p class="menu-label">Scolarité</p>

                    <div class="field">
                        <label class="label is-small">Classe</label>
                        <div class="select is-fullwidth">
                            <select name="classe" id="classe-select" onchange="updatePromo()">
                                <option value="">— Non définie —</option>
                                @foreach(['SIO1', 'SIO2'] as $c)
                                @php $current = $user->classe_courante ?? $user->classe; @endphp
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
                        <label class="label is-small">Spécialité</label>
                        <div class="select is-fullwidth">
                            <select name="spe">
                                <option value="">— Non définie —</option>
                                <option value="SLAM" {{ old('spe', $user->spe) === 'SLAM' ? 'selected' : '' }}>SLAM</option>
                                <option value="SISR" {{ old('spe', $user->spe) === 'SISR' ? 'selected' : '' }}>SISR</option>
                            </select>
                        </div>
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

        {{-- Boutons enregistrement --}}
        <div class="field is-grouped">
            <div class="control">
                <button type="submit" class="button is-primary">
                    <i class="fas fa-save mr-2"></i> Enregistrer
                </button>
            </div>
            <div class="control">
                <a href="{{ route('admin.users.index') }}" class="button is-light">Annuler</a>
            </div>
        </div>
    </form>

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

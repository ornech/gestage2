@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width: 640px;">

    <div class="level mb-4">
        <div class="level-left">
            <h1 class="title is-4 mb-0">Créer un compte utilisateur</h1>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.dashboard') }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="field">
                <label class="label is-small">Rôle</label>
                <div class="select is-fullwidth">
                    <select name="role" id="role-select" required onchange="toggleEtudiantFields()">
                        <option value="">— Choisir —</option>
                        <option value="Etudiant"       {{ old('role') === 'Etudiant'       ? 'selected' : '' }}>Étudiant</option>
                        <option value="Professeur"     {{ old('role') === 'Professeur'     ? 'selected' : '' }}>Professeur</option>
                        <option value="Administrateur" {{ old('role') === 'Administrateur' ? 'selected' : '' }}>Administrateur</option>
                    </select>
                </div>
            </div>

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Nom</label>
                        <input class="input" type="text" name="nom"
                               value="{{ old('nom') }}" placeholder="DUPONT" required>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Prénom</label>
                        <input class="input" type="text" name="prenom"
                               value="{{ old('prenom') }}" placeholder="Marie" required>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label is-small">Email</label>
                <input class="input" type="email" name="email"
                       value="{{ old('email') }}" placeholder="marie.dupont@lycee.fr" required>
                <p class="help">Le mot de passe initial sera <strong>achanger</strong>. L'utilisateur devra le modifier à sa première connexion.</p>
            </div>

            {{-- Champs spécifiques Étudiant --}}
            <div id="etudiant-fields">
                <hr>
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Classe</label>
                            <div class="select is-fullwidth">
                                <select name="classe" id="classe-select" onchange="updatePromo()">
                                    <option value="">—</option>
                                    <option value="SIO1" {{ old('classe') === 'SIO1' ? 'selected' : '' }}>SIO1</option>
                                    <option value="SIO2" {{ old('classe') === 'SIO2' ? 'selected' : '' }}>SIO2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Promo</label>
                            <input class="input" type="number" name="promo" id="promo-input"
                                   value="{{ old('promo') }}" min="2020" max="2040">
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Spécialité</label>
                            <div class="select is-fullwidth">
                                <select name="spe">
                                    <option value="">—</option>
                                    <option value="SLAM" {{ old('spe') === 'SLAM' ? 'selected' : '' }}>SLAM</option>
                                    <option value="SISR" {{ old('spe') === 'SISR' ? 'selected' : '' }}>SISR</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label is-small">Tuteur référent</label>
                    <div class="select is-fullwidth">
                        <select name="tuteur_id">
                            <option value="">— Aucun —</option>
                            @foreach($tuteurs as $tuteur)
                                <option value="{{ $tuteur->id }}" {{ old('tuteur_id') == $tuteur->id ? 'selected' : '' }}>
                                    {{ $tuteur->prenom }} {{ $tuteur->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary">
                        <i class="fas fa-user-plus mr-1"></i> Créer le compte
                    </button>
                </div>
                <div class="control">
                    <a href="{{ route('admin.dashboard') }}" class="button is-light">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
const promos = { SIO1: {{ $currentYear + 2 }}, SIO2: {{ $currentYear + 1 }} };

function toggleEtudiantFields() {
    const role   = document.getElementById('role-select').value;
    const fields = document.getElementById('etudiant-fields');
    fields.style.display = role === 'Etudiant' ? '' : 'none';
}

function updatePromo() {
    const sel = document.getElementById('classe-select');
    const inp = document.getElementById('promo-input');
    if (sel.value && promos[sel.value]) inp.value = promos[sel.value];
}

toggleEtudiantFields();
</script>
@endsection

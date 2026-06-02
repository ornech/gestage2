@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width:640px;">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">Ajouter une entreprise</h1>
                <p class="is-size-7 has-text-grey mt-1">
                    Saisie manuelle — pour les entreprises étrangères ou non trouvées via SIRET
                </p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('entreprises.index') }}" class="button is-light is-small">← Annuler</a>
        </div>
    </div>

    @if($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('entreprises.store') }}" method="POST">
            @csrf

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Raison sociale <span class="has-text-danger">*</span></label>
                        <input class="input is-small" type="text" name="raison_sociale"
                               value="{{ old('raison_sociale') }}" required autofocus>
                    </div>
                </div>
                <div class="column is-one-third">
                    <div class="field">
                        <label class="label is-small">
                            SIRET
                            <span class="has-text-grey is-size-7">(optionnel)</span>
                        </label>
                        <input class="input is-small" type="text" name="siret"
                               value="{{ old('siret') }}" maxlength="17"
                               placeholder="— entreprise étrangère —">
                        <p class="help">Laisser vide si entreprise hors France</p>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label is-small">Adresse</label>
                <input class="input is-small" type="text" name="adresse"
                       value="{{ old('adresse') }}">
            </div>

            <div class="columns">
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">Code postal</label>
                        <input class="input is-small" type="text" name="code_postal"
                               value="{{ old('code_postal') }}">
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Ville <span class="has-text-danger">*</span></label>
                        <input class="input is-small" type="text" name="ville"
                               value="{{ old('ville') }}" required>
                    </div>
                </div>
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label is-small">Pays</label>
                        <input class="input is-small" type="text" name="pays"
                               value="{{ old('pays', 'France') }}">
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Téléphone</label>
                        <input class="input is-small" type="text" name="telephone"
                               value="{{ old('telephone') }}">
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Code NAF</label>
                        <input class="input is-small" type="text" name="code_naf"
                               value="{{ old('code_naf') }}" placeholder="ex : 6201Z">
                    </div>
                </div>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary is-small">
                        <i class="fas fa-save mr-1"></i> Créer l'entreprise
                    </button>
                </div>
                <div class="control">
                    <a href="{{ route('entreprises.index') }}" class="button is-light is-small">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

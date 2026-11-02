@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:640px;">

    <div class="level mb-4">
        <div class="level-left">
            <h1 class="title is-4 mb-0">Ajouter un employé</h1>
        </div>
        <div class="level-right">
            <a href="{{ route('entreprises.show', $entreprise_id) }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('employes.store', $entreprise_id) }}" method="POST">
            @csrf
            <input type="hidden" name="entreprise_id" value="{{ $entreprise_id }}">

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label is-small" for="nom">Nom</label>
                        <input class="input is-small" type="text" name="nom" id="nom" value="{{ old('nom', request('nom')) }}" required>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small" for="prenom">Prénom</label>
                        <input class="input is-small" type="text" name="prenom" id="prenom" value="{{ old('prenom') }}" required>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label is-small" for="email">Email</label>
                <input class="input is-small" type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label class="label is-small" for="telephone">Téléphone</label>
                <input class="input is-small" type="text" name="telephone" id="telephone" value="{{ old('telephone') }}">
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary is-small">Enregistrer</button>
                </div>
                <div class="control">
                    <a href="{{ route('entreprises.show', $entreprise_id) }}" class="button is-light is-small">Annuler</a>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mt-6">

    <h1 class="title has-text-centered">Importer une entreprise</h1>

    <div class="box content">

        <h2 class="subtitle">Depuis la base de données SIRENE</h2>

        <p>
            Cette fonctionnalité importe automatiquement les dernières informations disponibles sur une entreprise
            depuis la base de données SIRENE. Cela permet d’éviter les informations erronées ou obsolètes.
        </p>

        <h3 class="subtitle is-5">Où trouver un numéro de SIRET ?</h3>
        <ul>
            <li>Depuis la page <strong>Recherche</strong> de l’application</li>
            <li>Sur le site officiel : <a href="https://annuaire-entreprises.data.gouv.fr/" target="_blank">annuaire-entreprises.data.gouv.fr</a></li>
        </ul>

        <p>
            Importez les données relatives à une entreprise à partir de son numéro de SIRET.
            <strong>Renseignez un numéro de SIRET (14 caractères)</strong>.
        </p>

        <hr>

        @if ($errors->any())
            <div class="notification is-danger">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('entreprises.import') }}" method="POST">
            @csrf

            <div class="field">
                <label class="label">Numéro de SIRET</label>
                <div class="control">
                    <input class="input" type="text" name="siret" maxlength="14" placeholder="Ex : 41816609600051" required>
                </div>
            </div>

            <button class="button is-link">Importer depuis SIRENE</button>
        </form>

        <hr>

        <p class="is-size-7 has-text-grey">
            Gestage contributeurs : Logan Gaillard, Astrik Manukyan, Jean‑François Ornech<br>
            Code source sous licence CC BY NC SA 4.0
        </p>

    </div>

</div>
@endsection

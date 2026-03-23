@extends('layouts.app')

@section('content')
<div class="container">

    <h1 class="title is-3 mb-4">{{ $entreprise->raison_sociale }}</h1>

    <div class="columns">
        <div class="column is-two-thirds">

            <div class="box">
                <p><strong>Adresse :</strong> {{ $entreprise->adresse }}</p>

                @if($entreprise->complement_adresse)
                    <p><strong>Complément :</strong> {{ $entreprise->complement_adresse }}</p>
                @endif

                <p><strong>Ville :</strong> {{ $entreprise->code_postal }} {{ $entreprise->ville }}</p>

                <p><strong>Code NAF :</strong> {{ $entreprise->code_naf }}</p>

                <p><strong>Type :</strong> {{ $entreprise->type }}</p>

                <p><strong>Effectif :</strong> {{ $entreprise->effectif }}</p>

                <p><strong>SIRET :</strong> {{ $entreprise->siret }}</p>

                <p><strong>Statut :</strong>
                    @if($entreprise->est_valide)
                        <span class="tag is-success">Validée</span>
                    @else
                        <span class="tag is-warning">En attente</span>
                    @endif
                </p>
            </div>

            <hr>

            <h2 class="title is-5">Contacts</h2>
            <p class="has-text-grey"><i>En attente de validation du professeur</i></p>

            <hr>

            <h2 class="title is-5">Stages</h2>
            <p class="has-text-grey"><i>En attente de validation du professeur</i></p>

        </div>

        <div class="column">
            <div class="box has-text-centered">
                <p class="has-text-grey">Carte à intégrer plus tard</p>
            </div>
        </div>
    </div>

</div>
@endsection

@extends('layouts.app')

@section('content')

<div class="container mt-5">

    <h1 class="title is-3">Annuaire entreprises</h1>
    <p class="subtitle is-6">Liste des entreprises approchées ou ayant accueilli des stagiaires</p>

    {{-- Compteurs --}}
    <div class="columns mt-4">

        <div class="column is-narrow">
            <div class="tags has-addons is-medium">
                <span class="tag is-dark">Entreprises :</span>
                <span class="tag is-link"><b>{{ $nbEntreprises }}</b></span>
            </div>
        </div>

        <div class="column is-narrow">
            <div class="tags has-addons is-medium">
                <span class="tag is-dark">Stages :</span>
                <span class="tag is-success"><b>{{ $nbStages }}</b></span>
            </div>
        </div>

        <div class="column is-narrow">
            <div class="tags has-addons is-medium">
                <span class="tag is-dark">Contacts :</span>
                <span class="tag is-warning"><b>{{ $nbContacts }}</b></span>
            </div>
        </div>

    </div>

    {{-- Filtres --}}
    <div class="box mt-4">
        <form method="GET" action="">
            <div class="columns is-multiline">

                <div class="column is-3">
                    <label class="label">Nom entreprise</label>
                    <input type="text" name="nom" class="input" placeholder="Rechercher..." value="{{ request('nom') }}">
                </div>

                <div class="column is-3">
                    <label class="label">Adresse</label>
                    <input type="text" name="adresse" class="input" value="{{ request('adresse') }}">
                </div>

                <div class="column is-2">
                    <label class="label">Ville</label>
                    <input type="text" name="ville" class="input" value="{{ request('ville') }}">
                </div>

                <div class="column is-2">
                    <label class="label">Code postal</label>
                    <input type="text" name="cp" class="input" value="{{ request('cp') }}">
                </div>

                <div class="column is-2">
                    <label class="label">NAF</label>
                    <input type="text" name="naf" class="input" value="{{ request('naf') }}">
                </div>

            </div>

            <button class="button is-link mt-2">Filtrer</button>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="table-container mt-5">
        <table class="table is-striped is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th>Nom entreprise</th>
                    <th>Adresse</th>
                    <th>Ville</th>
                    <th>NAF</th>
                    <th>Code postal</th>
                    <th>Stage</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($entreprises as $entreprise)
                    <tr>
                       <td>
                        <a href="{{ route('entreprises.show', $entreprise) }}">
                            {{ $entreprise->raison_sociale }}
                        </a>
                    </td>

                        <td>{{ $entreprise->adresse }}</td>
                        <td>{{ $entreprise->ville }}</td>
                        <td>{{ $entreprise->code_naf }}</td>
                        <td>{{ $entreprise->code_postal }}</td>
                        <td>
                            {{ $entreprise->stages->count() > 0 ? $entreprise->stages->count() : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>

@endsection

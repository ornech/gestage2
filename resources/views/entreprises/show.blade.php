@extends('layouts.app')

@section('content')

<div class="container mt-5">

    <h1 class="title is-3">{{ $entreprise->raison_sociale }}</h1>

    <p class="subtitle">
        {{ $entreprise->adresse }}<br>
        {{ $entreprise->code_postal }} {{ $entreprise->ville }}
    </p>

    <p><strong>Activité :</strong> {{ $entreprise->code_naf }}</p>
    <p><strong>Type :</strong> {{ $entreprise->type }}</p>
    <p><strong>Effectif :</strong> {{ $entreprise->effectif }}</p>
    <p><strong>SIRET :</strong> {{ $entreprise->siret }}</p>

    <hr>

    {{-- Contacts --}}
    <h2 class="title is-4">Contacts</h2>

    @foreach ($entreprise->employes as $employe)
        <p>{{ $employe->nom }} {{ $employe->prenom }} | {{ $employe->telephone ?? 'Non défini' }}</p>
    @endforeach

    <hr>

    {{-- Stages --}}
    <h2 class="title is-4">Stages</h2>

    <table class="table is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Classe</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Maître de stage</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($entreprise->stages as $stage)
                <tr>
                    <td>{{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}</td>
                    <td>{{ $stage->etudiant->classe }}</td>
                    <td>{{ $stage->date_debut }}</td>
                    <td>{{ $stage->date_fin }}</td>
                    <td>{{ $stage->maitreDeStage->nom ?? 'Non défini' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection

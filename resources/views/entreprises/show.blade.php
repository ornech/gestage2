@extends('layouts.app')

@section('content')

<div class="container mt-5">

    <h1 class="title is-3">{{ $entreprise->raison_sociale }}</h1>

    <p class="subtitle">
        {{ $entreprise->adresse }}<br>
        {{ $entreprise->code_postal }} {{ $entreprise->ville }}
    </p>

    <p><strong>Activité :</strong> {{ $entreprise->code_naf ?? 'Non défini' }}</p>
    <p><strong>Type :</strong> {{ $entreprise->type ?? 'Non défini' }}</p>
    <p><strong>Effectif :</strong> {{ $entreprise->effectif ?? 'Non défini' }}</p>
    <p><strong>SIRET :</strong> {{ $entreprise->siret ?? 'Non défini' }}</p>

    <hr>

   

    {{-- Stages --}}
  <h2 class="title is-4">Contacts</h2>

<a href="{{ route('employes.create', $entreprise->id) }}" class="button is-primary mb-3">
    Ajouter un contact
</a>

@if($entreprise->employes->isEmpty())
    <p>Aucun employé pour cette entreprise.</p>
@else
<table class="table is-striped is-fullwidth">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Rôle</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($entreprise->employes as $employe)
        <tr>
            <td>{{ $employe->prenom }} {{ $employe->nom }}</td>
            <td>{{ $employe->email }}</td>
            <td>{{ $employe->telephone ?? '—' }}</td>
            <td>
                @if($employe->is_maitre_de_stage)
                    Maître de stage
                @else
                    Employé
                @endif
            </td>
            <td>
                <a href="{{ route('employes.show', $employe->id) }}" class="button is-small is-link">
                    Voir
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif


<hr>

<h2 class="title is-4">Stages</h2>

<a href="{{ route('stages.create', $entreprise->id) }}" class="button is-link mb-3">
    Ajouter un stage
</a>

<p class="has-text-grey">
    Les stages sont visibles par les étudiants et les professeurs.
</p>

@if($entreprise->stages->isEmpty())
    <p>Aucun stage enregistré pour cette entreprise.</p>
@else
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
        @foreach($entreprise->stages as $stage)
        <tr>
            <td>{{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}</td>
            <td>{{ $stage->etudiant->classe }}</td>
            <td>{{ $stage->date_debut }}</td>
            <td>{{ $stage->date_fin }}</td>
            <td>
    @if($stage->employe)
        {{ $stage->employe->prenom }} {{ $stage->employe->nom }}
    @else
        <span class="has-text-grey">Non défini</span>
    @endif
</td>

        </tr>
        @endforeach
    </tbody>
</table>
@endif

@endsection

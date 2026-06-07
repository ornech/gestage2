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

    {{-- Contact générique de l'entreprise (standard) : reste accessible même si
         le maître de stage demande l'anonymisation de ses coordonnées (RGPD) --}}
    <div class="box mt-4 mb-4">
        <p class="menu-label mb-2">Contact générique (standard)</p>
        @if($entreprise->telephone || $entreprise->email)
            <p>
                <i class="fas fa-phone mr-2"></i>
                {{ $entreprise->telephone ?? '—' }}
            </p>
            <p>
                <i class="fas fa-envelope mr-2"></i>
                {{ $entreprise->email ?? '—' }}
            </p>
        @else
            <p class="has-text-grey">Aucun contact générique renseigné pour cette entreprise.</p>
        @endif

        @hasanyrole('Professeur|Administrateur')
            <details class="mt-3">
                <summary class="is-size-7 has-text-link" style="cursor:pointer;">Modifier le contact générique</summary>
                <form action="{{ route('entreprises.update', $entreprise) }}" method="POST" class="mt-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="raison_sociale" value="{{ $entreprise->raison_sociale }}">
                    <input type="hidden" name="adresse" value="{{ $entreprise->adresse }}">
                    <input type="hidden" name="code_postal" value="{{ $entreprise->code_postal }}">
                    <input type="hidden" name="ville" value="{{ $entreprise->ville }}">
                    <input type="hidden" name="siret" value="{{ $entreprise->siret }}">
                    <div class="columns">
                        <div class="column">
                            <div class="field">
                                <label class="label is-small">Téléphone standard</label>
                                <input class="input is-small" type="text" name="telephone" value="{{ $entreprise->telephone }}">
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label is-small">Email standard</label>
                                <input class="input is-small" type="email" name="email" value="{{ $entreprise->email }}">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="button is-link is-small">Enregistrer</button>
                </form>
            </details>
        @endhasanyrole
    </div>

    <hr>

   

    {{-- Stages --}}
  <h2 class="title is-4">Contacts</h2>

@hasanyrole('Professeur|Administrateur')
<a href="{{ route('employes.create', $entreprise->id) }}" class="button is-primary mb-3">
    Ajouter un contact
</a>
@endhasanyrole

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
            <td>
                @if(auth()->user()->hasAnyRole(['Professeur', 'Administrateur']) || in_array($employe->id, $monMaitreDeStageIds))
                    {{ $employe->telephone ?? '—' }}
                @else
                    <span class="has-text-grey-light" title="Visible uniquement par ton propre maître de stage">masqué</span>
                @endif
            </td>
            <td>Contact</td>
            <td>
                @role('Administrateur')
                    <a href="{{ route('employes.edit', $employe->id) }}" class="button is-small is-warning" title="Modifier">
                        <i class="fas fa-pen"></i>
                    </a>
                @else
                    <a href="{{ route('employes.show', $employe->id) }}" class="button is-small is-info is-light" title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>
                @endrole
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif


<hr>

<h2 class="title is-4">Stages</h2>

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
            <td>
                @if($stage->etudiant)
                    <a href="{{ route('admin.users.edit', $stage->etudiant) }}" class="has-text-link">
                        {{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}
                    </a>
                @else
                    <span class="has-text-grey">—</span>
                @endif
            </td>
            <td>{{ $stage->etudiant?->classe_courante ?? $stage->etudiant?->classe ?? '—' }}</td>
            <td>{{ $stage->date_debut?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $stage->date_fin?->format('d/m/Y') ?? '—' }}</td>
            <td>
    @if($stage->maitreDeStage)
        {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
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

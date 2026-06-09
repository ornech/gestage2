@extends('layouts.app')

@section('content')

<div class="container mt-5">

    <div class="level mb-2">
        <div class="level-left">
            <h1 class="title is-4 mb-0">{{ $entreprise->raison_sociale }}</h1>
        </div>
    </div>

    <p class="is-size-7 has-text-grey mb-1">
        {{ $entreprise->adresse }} — {{ $entreprise->code_postal }} {{ $entreprise->ville }}
    </p>
    <p class="is-size-7 has-text-grey mb-4">
        SIRET&nbsp;{{ $entreprise->siret ?? '—' }}
        · Activité (NAF)&nbsp;{{ $entreprise->code_naf ?? '—' }}
        · Catégorie juridique (code INSEE)&nbsp;{{ $entreprise->type ?? '—' }}
        · Effectif salarié estimé&nbsp;{{ $entreprise->effectif ? '~'.$entreprise->effectif : '—' }}
    </p>

    {{-- Contact (standard) : reste accessible même si le maître de stage
         demande l'anonymisation de ses coordonnées personnelles (RGPD) --}}
    <div class="box mb-4">
        <p class="menu-label mb-2">Contact (standard)</p>

        @hasanyrole('Professeur|Administrateur')
            <form action="{{ route('entreprises.update', $entreprise) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="raison_sociale" value="{{ $entreprise->raison_sociale }}">
                <input type="hidden" name="adresse" value="{{ $entreprise->adresse }}">
                <input type="hidden" name="code_postal" value="{{ $entreprise->code_postal }}">
                <input type="hidden" name="ville" value="{{ $entreprise->ville }}">
                <input type="hidden" name="siret" value="{{ $entreprise->siret }}">
                <div class="columns is-vcentered">
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Téléphone</label>
                            <input class="input is-small" type="text" name="telephone" value="{{ old('telephone', $entreprise->telephone) }}">
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Email</label>
                            <input class="input is-small" type="email" name="email" value="{{ old('email', $entreprise->email) }}">
                        </div>
                    </div>
                    <div class="column is-narrow">
                        <button type="submit" class="button is-link is-small">Enregistrer</button>
                    </div>
                </div>
            </form>
        @else
            <p>
                <i class="fas fa-phone mr-2"></i>{{ $entreprise->telephone ?? '—' }}
                &nbsp;&nbsp;
                <i class="fas fa-envelope mr-2"></i>{{ $entreprise->email ?? '—' }}
            </p>
        @endhasanyrole
    </div>

    {{-- Maître de stage --}}
    <div class="level mb-3">
        <div class="level-left"><h2 class="title is-5 mb-0">Maître de stage</h2></div>
        <div class="level-right">
            @hasanyrole('Professeur|Administrateur')
                <a href="{{ route('employes.create', $entreprise->id) }}" class="button is-primary is-small">
                    Ajouter
                </a>
            @endhasanyrole
        </div>
    </div>

@if($entreprise->employes->isEmpty())
    <p class="has-text-grey">Aucun maître de stage enregistré pour cette entreprise.</p>
@else
<table class="table is-striped is-fullwidth is-narrow">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($entreprise->employes as $employe)
        <tr>
            <td>{{ $employe->prenom }} {{ $employe->nom }}</td>
            <td>{{ $employe->email ?? '—' }}</td>
            <td>
                @if(auth()->user()->hasAnyRole(['Professeur', 'Administrateur']) || in_array($employe->id, $monMaitreDeStageIds))
                    {{ $employe->telephone ?? '—' }}
                @else
                    <span class="has-text-grey-light" title="Visible uniquement par ton propre maître de stage">masqué</span>
                @endif
            </td>
            <td>
                @can('update', $employe)
                    <a href="{{ route('employes.edit', $employe->id) }}" class="button is-small is-warning" title="Modifier">
                        <i class="fas fa-pen"></i>
                    </a>
                @else
                    <a href="{{ route('employes.show', $employe->id) }}" class="button is-small is-info is-light" title="Voir">
                        <i class="fas fa-eye"></i>
                    </a>
                @endcan
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

    <h2 class="title is-5 mt-5 mb-3">Stages</h2>

@if($entreprise->stages->isEmpty())
    <p class="has-text-grey">Aucun stage enregistré pour cette entreprise.</p>
@else
<table class="table is-striped is-fullwidth is-narrow">
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
                    @role('Etudiant')
                        <span class="has-text-grey" title="Données personnelles masquées">—</span>
                    @else
                        {{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}
                    @endrole
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

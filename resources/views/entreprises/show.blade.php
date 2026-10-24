@extends('layouts.app')

@section('content')

@php
    $staff = auth()->user()->hasAnyRole(['Professeur', 'Administrateur']);
    $badgeValidation = fn ($statut) => match ($statut) {
        'valide' => ['is-success', 'Validé'],
        'rejete' => ['is-danger', 'Rejeté'],
        default  => ['is-warning', 'En attente'],
    };
@endphp

<div class="container mt-5">

    <nav class="breadcrumb is-small mb-3" aria-label="Fil d'Ariane">
        <ul>
            <li><a href="{{ route('entreprises.index') }}">Entreprises</a></li>
            <li class="is-active"><a href="#" aria-current="page">{{ $entreprise->raison_sociale }}</a></li>
        </ul>
    </nav>

    {{-- ── En-tête : identité de l'entreprise ── --}}
    <h1 class="title is-3 mb-2">{{ $entreprise->raison_sociale }}</h1>
    <p class="mb-3">
        <i class="fas fa-map-marker-alt has-text-grey mr-1"></i>
        {{ $entreprise->adresse }}, {{ $entreprise->code_postal }} {{ $entreprise->ville }}
    </p>
    <div class="tags mb-5">
        <span class="tag is-light" title="Identifiant de l'établissement">SIRET {{ $entreprise->siret ?? '—' }}</span>
        <span class="tag is-light" title="Code d'activité principale">NAF {{ $entreprise->code_naf ?? '—' }}</span>
        <span class="tag is-light" title="Catégorie juridique (code INSEE)">Forme juridique {{ $entreprise->type ?? '—' }}</span>
        <span class="tag is-light" title="Effectif salarié estimé">Effectif {{ $entreprise->effectif ? '~'.$entreprise->effectif : '—' }}</span>
    </div>

    {{-- ── Stages (ce qu'on vient chercher en premier) ── --}}
    <h2 class="title is-5 mb-3">Stages dans cette entreprise</h2>

    @if($entreprise->stages->isEmpty())
        <p class="has-text-grey mb-5">Aucun stage enregistré pour cette entreprise.</p>
    @else
        <div class="table-container mb-5">
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Année</th>
                        <th>Classe</th>
                        <th>Période</th>
                        <th>Maître de stage</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entreprise->stages as $stage)
                        @php [$couleur, $libelle] = $badgeValidation($stage->statut_validation); @endphp
                        <tr>
                            <td>
                                @if(! $stage->etudiant)
                                    <span class="has-text-grey">Compte supprimé</span>
                                @elseif($staff)
                                    <a href="{{ route('admin.users.edit', $stage->etudiant) }}">{{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}</a>
                                @elseif($stage->etudiant_id === auth()->id())
                                    <a href="{{ route('stages.show', $stage) }}"><strong>Mon stage</strong></a>
                                @else
                                    <span class="has-text-grey" title="Les autres étudiants ne sont pas visibles">Un autre étudiant</span>
                                @endif
                            </td>
                            <td>{{ $stage->annee_scolaire }}</td>
                            <td>{{ $stage->classe ?? '—' }}</td>
                            <td class="is-nowrap">
                                {{ $stage->date_debut?->format('d/m/Y') ?? '—' }} → {{ $stage->date_fin?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td>
                                @if($stage->maitreDeStage)
                                    <a href="{{ route('employes.show', $stage->maitreDeStage) }}">
                                        {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
                                    </a>
                                @else
                                    <span class="has-text-grey">Non défini</span>
                                @endif
                            </td>
                            <td><span class="tag {{ $couleur }} is-light">{{ $libelle }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── Maîtres de stage ── --}}
    <div class="level mb-3">
        <div class="level-left"><h2 class="title is-5 mb-0">Maîtres de stage</h2></div>
        @if($staff)
            <div class="level-right">
                <a href="{{ route('employes.create', $entreprise->id) }}" class="button is-primary is-small">
                    <span class="icon is-small"><i class="fas fa-plus"></i></span>
                    <span>Ajouter un maître de stage</span>
                </a>
            </div>
        @endif
    </div>

    @if($entreprise->employes->isEmpty())
        <p class="has-text-grey mb-5">Aucun maître de stage enregistré pour cette entreprise.</p>
    @else
        <div class="table-container mb-5">
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th style="width:1%;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entreprise->employes as $employe)
                        @php $voitCoordonnees = $staff || in_array($employe->id, $monMaitreDeStageIds); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('employes.show', $employe) }}"><strong>{{ $employe->prenom }} {{ $employe->nom }}</strong></a>
                                @if($employe->fonction)<br><span class="is-size-7 has-text-grey">{{ $employe->fonction }}</span>@endif
                            </td>
                            @foreach([$employe->email, $employe->telephone] as $coordonnee)
                                <td>
                                    @if(! $voitCoordonnees)
                                        <span class="has-text-grey" title="Réservé à son stagiaire et à l'équipe pédagogique">
                                            <i class="fas fa-lock mr-1"></i>Réservé
                                        </span>
                                    @elseif($coordonnee)
                                        {{ $coordonnee }}
                                    @elseif($employe->email_supprime_at)
                                        <span class="has-text-grey" title="Supprimé à sa demande le {{ $employe->email_supprime_at->format('d/m/Y') }}">Supprimé (RGPD)</span>
                                    @else
                                        <span class="has-text-grey">Non renseigné</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="is-nowrap">
                                @can('update', $employe)
                                    <a href="{{ route('employes.edit', $employe) }}" class="button is-small is-light">
                                        <span class="icon is-small"><i class="fas fa-pen"></i></span>
                                        <span>Modifier</span>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── Standard de l'entreprise : reste joignable même si le maître de stage
         demande la suppression de ses coordonnées personnelles (RGPD) ── --}}
    <div class="box">
        <p class="menu-label mb-1">Standard de l'entreprise</p>
        <p class="is-size-7 has-text-grey mb-3">Coordonnées générales, à utiliser si le maître de stage n'est pas joignable.</p>

        <p>
            <i class="fas fa-phone mr-2 has-text-grey"></i>{!! $entreprise->telephone ? e($entreprise->telephone) : '<span class="has-text-grey">Non renseigné</span>' !!}
            <span class="mx-3"></span>
            <i class="fas fa-envelope mr-2 has-text-grey"></i>{!! $entreprise->email ? e($entreprise->email) : '<span class="has-text-grey">Non renseigné</span>' !!}
        </p>

        @if($staff)
            <details class="mt-3">
                <summary class="button is-small is-light" style="list-style:none; cursor:pointer;">
                    <span class="icon is-small"><i class="fas fa-pen"></i></span>
                    <span>Modifier le standard</span>
                </summary>
                <form action="{{ route('entreprises.update', $entreprise) }}" method="POST" class="mt-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="raison_sociale" value="{{ $entreprise->raison_sociale }}">
                    <input type="hidden" name="adresse" value="{{ $entreprise->adresse }}">
                    <input type="hidden" name="code_postal" value="{{ $entreprise->code_postal }}">
                    <input type="hidden" name="ville" value="{{ $entreprise->ville }}">
                    <input type="hidden" name="siret" value="{{ $entreprise->siret }}">
                    <div class="columns is-vcentered">
                        <div class="column">
                            <label class="label is-small" for="telephone">Téléphone</label>
                            <input class="input is-small" id="telephone" type="text" name="telephone" value="{{ old('telephone', $entreprise->telephone) }}">
                        </div>
                        <div class="column">
                            <label class="label is-small" for="email">Email</label>
                            <input class="input is-small" id="email" type="email" name="email" value="{{ old('email', $entreprise->email) }}">
                        </div>
                        <div class="column is-narrow">
                            <button type="submit" class="button is-link is-small">Enregistrer</button>
                        </div>
                    </div>
                </form>
            </details>
        @endif
    </div>

</div>

@endsection

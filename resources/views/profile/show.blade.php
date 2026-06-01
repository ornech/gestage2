@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width:700px;">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">
                    <i class="fas fa-user-circle mr-2 has-text-grey-light"></i>
                    {{ $user->prenom }} {{ $user->nom }}
                </h1>
                <p class="is-size-7 has-text-grey mt-1">{{ $user->email }}</p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('profile.edit') }}" class="button is-light is-small">
                <i class="fas fa-pen mr-1"></i> Modifier
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light py-2 mb-4">{{ session('success') }}</div>
    @endif

    <div class="box mb-3">
        <p class="menu-label mb-3">Informations du compte</p>
        <div class="columns is-multiline">
            <div class="column is-half">
                <p class="is-size-7 has-text-grey mb-1">Nom</p>
                <p class="has-text-weight-semibold">{{ $user->nom }}</p>
            </div>
            <div class="column is-half">
                <p class="is-size-7 has-text-grey mb-1">Prénom</p>
                <p class="has-text-weight-semibold">{{ $user->prenom }}</p>
            </div>
            <div class="column is-half">
                <p class="is-size-7 has-text-grey mb-1">Email</p>
                <p>{{ $user->email }}</p>
            </div>
            @if($user->telephone)
            <div class="column is-half">
                <p class="is-size-7 has-text-grey mb-1">Téléphone</p>
                <p>{{ $user->telephone }}</p>
            </div>
            @endif
        </div>
    </div>

    @role('Etudiant')
    <div class="box mb-3">
        <p class="menu-label mb-3">Scolarité</p>
        <div class="columns is-multiline">
            <div class="column is-one-third">
                <p class="is-size-7 has-text-grey mb-1">Classe</p>
                @if($user->classe_courante)
                    <span class="tag {{ $user->classe_courante === 'SIO1' ? 'is-info' : 'is-primary' }} is-medium">
                        {{ $user->classe_courante }}
                    </span>
                    <span class="is-size-7 has-text-grey ml-2">
                        {{ $user->classe_courante === 'SIO1' ? '1ère année' : '2ème année' }}
                    </span>
                @else
                    <span class="has-text-grey">—</span>
                @endif
            </div>
            <div class="column is-one-third">
                <p class="is-size-7 has-text-grey mb-1">Spécialité</p>
                @if($user->spe)
                    <span class="tag is-link is-light is-medium">{{ $user->spe }}</span>
                @else
                    <span class="has-text-grey is-size-7 is-italic">Non définie</span>
                @endif
            </div>
            <div class="column is-one-third">
                <p class="is-size-7 has-text-grey mb-1">Promotion</p>
                <p class="has-text-weight-semibold">{{ $user->promo ?? '—' }}</p>
            </div>
            @if($user->tuteur)
            <div class="column is-full">
                <p class="is-size-7 has-text-grey mb-1">Tuteur référent</p>
                <p>
                    {{ $user->tuteur->prenom }} {{ $user->tuteur->nom }}
                    @if($user->tuteur->email)
                        &nbsp;·&nbsp;
                        <a href="mailto:{{ $user->tuteur->email }}" class="has-text-grey">{{ $user->tuteur->email }}</a>
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
    @endrole

    <div class="box">
        <p class="menu-label mb-3">Sécurité</p>
        <div class="columns">
            <div class="column">
                <p class="is-size-7 has-text-grey mb-1">CGU acceptées le</p>
                <p>
                    @if($user->cgu_accepted_at)
                        {{ $user->cgu_accepted_at->format('d/m/Y à H:i') }}
                    @else
                        <span class="has-text-warning">Non acceptées</span>
                    @endif
                </p>
            </div>
            <div class="column has-text-right">
                <a href="{{ route('profile.edit') }}" class="button is-light is-small">
                    <i class="fas fa-key mr-1"></i> Changer le mot de passe
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

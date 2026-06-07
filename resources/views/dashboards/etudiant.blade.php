@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- En-tête --}}
    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    Bonjour, {{ $user->prenom }} !
                </h1>
                <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;">
                    @if($user->classe_courante)
                        <span class="tag {{ $user->classe_courante === 'SIO1' ? 'is-info' : 'is-primary' }}">
                            {{ $user->classe_courante }}
                        </span>
                    @endif
                    @if($user->spe)
                        <span class="tag is-link is-light">{{ $user->spe }}</span>
                    @endif
                    @if($user->tuteur)
                        <span class="is-size-7 has-text-grey">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>
                            {{ $user->tuteur->prenom }} {{ $user->tuteur->nom }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('etudiant.stage.nouveau') }}" class="button is-primary is-small">
                <i class="fas fa-briefcase mr-1"></i> Mes stages
            </a>
        </div>
    </div>

    @php
        $stageActif = $stages->first();
        $convBadge  = [
            'hors_app'       => ['is-warning', 'fa-file-alt', 'Convention hors app — remise'],
            'a_faire_signer' => ['is-warning', 'fa-pen', "À faire signer par l'employeur"],
            'en_attente'     => ['is-info',    'fa-clock', 'Déposée — en attente du proviseur'],
            'validee'        => ['is-success', 'fa-check-circle', 'Convention validée ✓'],
        ];
        // Stage de l'année en cours absent — même si un stage d'une année précédente est déjà saisi
        $stageAnneeCouranteManquant = $user->classe_courante
            && $stages->isNotEmpty()
            && !$stages->contains(fn($s) => $s->classe === $user->classe_courante);
    @endphp

    @if($stageAnneeCouranteManquant)
    <div class="notification is-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Stage de {{ $user->classe_courante }} non saisi.</strong>
        Le ou les stages affichés ci-dessous concernent une année précédente.
        Il te reste à renseigner dans l'application ton stage actuel : <strong>entreprise</strong> et <strong>maître de stage</strong>.
        <a href="{{ route('etudiant.stage.nouveau') }}" class="ml-2">→ Saisir mon stage de {{ $user->classe_courante }}</a>
    </div>
    @endif

    @if($stages->isEmpty() && !$convPapier)
    {{-- Aucun stage --}}
    <div class="notification is-warning is-light">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <strong>Aucun stage saisi.</strong>
        Tu n'as pas encore renseigné ton stage dans l'application.
        <a href="{{ route('etudiant.stage.nouveau') }}" class="ml-2">→ Rechercher mon entreprise</a>
    </div>
    @endif

    @if($convPapier && $stages->isEmpty())
    {{-- Convention hors app uniquement --}}
    @php [$badgeColor, $badgeIcon, $badgeLabel] = $convBadge[$convPapier->statut] ?? ['is-light', 'fa-file', '—']; @endphp
    <div class="box mb-4">
        <p class="menu-label mb-2">Convention de stage</p>
        <div class="is-flex is-align-items-center" style="gap:1rem;">
            <span class="tag {{ $badgeColor }} is-medium">
                <i class="fas {{ $badgeIcon }} mr-1"></i> {{ $badgeLabel }}
            </span>
            <span class="is-size-7 has-text-grey">Convention remise en dehors de l'application</span>
        </div>
        @if($convPapier->statut === 'hors_app')
        <div class="notification is-warning is-light py-2 mt-3 mb-0 is-size-7">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Il te reste à renseigner ton <strong>entreprise</strong> et ton <strong>maître de stage</strong> dans l'application.
            <a href="{{ route('etudiant.stage.nouveau') }}" class="ml-1">→ Compléter mon stage</a>
        </div>
        @endif
    </div>
    @endif

    @foreach($stages as $stage)
    @php
        [$badgeColor, $badgeIcon, $badgeLabel] = $convBadge[$stage->statut_convention] ?? ['is-light', 'fa-file', '—'];
        $valBadge = match($stage->statut_validation) {
            'valide' => ['is-success is-light', 'Validé par le professeur ✓'],
            'rejete' => ['is-danger is-light',  'Rejeté — voir la note'],
            default  => ['is-warning is-light', 'En attente de validation'],
        };
    @endphp
    <div class="box mb-4" style="border-left: 4px solid {{ $stage->statut_convention === 'validee' ? '#48c78e' : ($stage->statut_convention === 'en_attente' ? '#3e8ed0' : '#ffe08a') }};">

        <div class="level mb-2">
            <div class="level-left">
                <div>
                    <p class="has-text-weight-semibold">
                        @if($stage->entreprise)
                            <a href="{{ route('stages.show', $stage) }}" class="has-text-dark">
                                {{ $stage->entreprise->raison_sociale }}
                            </a>
                        @else
                            <span class="has-text-grey">Entreprise non renseignée</span>
                        @endif
                    </p>
                    <p class="is-size-7 has-text-grey">
                        @if($stage->date_debut)
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Du {{ $stage->date_debut->format('d/m/Y') }}
                            au {{ $stage->date_fin?->format('d/m/Y') ?? '?' }}
                        @endif
                        @if($stage->maitreDeStage)
                            &nbsp;·&nbsp;
                            <i class="fas fa-user-tie mr-1"></i>
                            {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="level-right">
                <a href="{{ route('stages.show', $stage) }}" class="button is-light is-small">
                    <i class="fas fa-eye mr-1"></i> Détail
                </a>
            </div>
        </div>

        <div class="tags mt-2">
            <span class="tag {{ $badgeColor }}">
                <i class="fas {{ $badgeIcon }} mr-1"></i> {{ $badgeLabel }}
            </span>
            <span class="tag {{ $valBadge[0] }}">{{ $valBadge[1] }}</span>
            @if($stage->journal_entries_count > 0)
                <a href="{{ route('stages.journal.index', $stage) }}" class="tag is-link is-light">
                    <i class="fas fa-book-open mr-1"></i> {{ $stage->journal_entries_count }} réalisation(s)
                </a>
            @else
                <a href="{{ route('stages.journal.index', $stage) }}" class="tag is-light has-text-grey">
                    <i class="fas fa-book-open mr-1"></i> Journal vide
                </a>
            @endif
        </div>

        @if($stage->statut_validation === 'rejete' && $stage->note_rejet)
        <div class="notification is-danger is-light py-2 mt-2" style="font-size:.82rem;">
            <i class="fas fa-comment-alt mr-1"></i>
            <strong>Note du professeur :</strong> {{ $stage->note_rejet }}
        </div>
        @endif
    </div>
    @endforeach

    {{-- Liens rapides --}}
    <div class="columns is-variable is-3 mt-2">
        <div class="column">
            <a href="{{ route('etudiant.stage.nouveau') }}" class="box has-text-centered p-4"
               style="display:block; text-decoration:none; border:2px dashed #3273dc;">
                <span class="icon is-large has-text-link mb-2"><i class="fas fa-plus-circle fa-2x"></i></span>
                <p class="has-text-weight-semibold is-size-7 has-text-link">Ajouter un stage</p>
                <p class="is-size-7 has-text-grey">Rechercher mon entreprise d'accueil</p>
            </a>
        </div>
        <div class="column">
            <a href="{{ route('profile.show') }}" class="box has-text-centered p-4"
               style="display:block; text-decoration:none; border:1px solid #e8e8e8;">
                <span class="icon is-large has-text-grey mb-2"><i class="fas fa-user-circle fa-2x"></i></span>
                <p class="has-text-weight-semibold is-size-7">Mon profil</p>
                <p class="is-size-7 has-text-grey">Mes informations personnelles</p>
            </a>
        </div>
        <div class="column">
            <a href="{{ route('cgu.show') }}" class="box has-text-centered p-4"
               style="display:block; text-decoration:none; border:1px solid #e8e8e8;">
                <span class="icon is-large has-text-grey mb-2"><i class="fas fa-file-contract fa-2x"></i></span>
                <p class="has-text-weight-semibold is-size-7">CGU</p>
                <p class="is-size-7 has-text-grey">
                    @if($user->cgu_accepted_at)
                        Acceptées le {{ $user->cgu_accepted_at->format('d/m/Y') }}
                    @else
                        <span class="has-text-warning">Non acceptées</span>
                    @endif
                </p>
            </a>
        </div>
        @if($stages->isNotEmpty())
        <div class="column">
            <a href="{{ route('stages.journal.index', $stages->first()) }}" class="box has-text-centered p-4"
               style="display:block; text-decoration:none; border:1px solid #e8e8e8;">
                <span class="icon is-large has-text-grey mb-2"><i class="fas fa-book-open fa-2x"></i></span>
                <p class="has-text-weight-semibold is-size-7">Journal de stage</p>
                <p class="is-size-7 has-text-grey">
                    {{ $stages->first()->journal_entries_count }} réalisation(s) saisie(s)
                </p>
            </a>
        </div>
        @endif
    </div>

</div>
@endsection

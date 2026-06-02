@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">Mes stages</h1>
                <p class="is-size-7 has-text-grey mt-1">{{ $stages->count() }} stage(s) enregistré(s)</p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('etudiant.stage.nouveau') }}" class="button is-primary is-small">
                <i class="fas fa-plus mr-1"></i> Saisir un stage
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light py-2 mb-4">{{ session('success') }}</div>
    @endif

    @if($stages->isEmpty())
        <div class="box has-text-centered py-6">
            <span class="icon is-large has-text-grey-light mb-3" style="font-size:3rem;">
                <i class="fas fa-briefcase"></i>
            </span>
            <p class="title is-5 has-text-grey mt-3">Aucun stage enregistré</p>
            <p class="has-text-grey is-size-7 mb-4">
                Commence par rechercher ton entreprise d'accueil via son numéro SIRET.
            </p>
            <a href="{{ route('etudiant.stage.nouveau') }}" class="button is-primary">
                <i class="fas fa-plus mr-2"></i> Saisir mon stage
            </a>
        </div>
    @else
        @foreach($stages as $stage)
        @php
            $badgeConv = match($stage->statut_convention) {
                'a_faire_signer' => ['is-warning',         'fa-pen',          "À faire signer par l'employeur"],
                'en_attente'     => ['is-info',            'fa-clock',        'Déposée — en attente du proviseur'],
                'validee'        => ['is-success',         'fa-check-circle', 'Convention validée ✓'],
                default          => ['is-light',           'fa-file',         '—'],
            };
            $badgeVal = match($stage->statut_validation) {
                'valide'  => ['is-success is-light', 'Validé par le professeur ✓'],
                'rejete'  => ['is-danger is-light',  'Rejeté'],
                default   => ['is-warning is-light', 'En attente de validation'],
            };
            $borderColor = match($stage->statut_convention) {
                'validee'        => '#48c78e',
                'en_attente'     => '#3e8ed0',
                default          => '#ffe08a',
            };
        @endphp

        <div class="box mb-4" style="border-left:4px solid {{ $borderColor }};">

            {{-- En-tête --}}
            <div class="level mb-2">
                <div class="level-left">
                    <div>
                        <p class="has-text-weight-semibold">
                            {{ $stage->entreprise?->raison_sociale ?? 'Entreprise non renseignée' }}
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
                    <div class="tags mb-0" style="gap:.3rem;">
                        <span class="tag {{ $badgeConv[0] }}">
                            <i class="fas {{ $badgeConv[1] }} mr-1"></i>{{ $badgeConv[2] }}
                        </span>
                        <span class="tag {{ $badgeVal[0] }}">{{ $badgeVal[1] }}</span>
                    </div>
                </div>
            </div>

            {{-- Note de rejet --}}
            @if($stage->statut_validation === 'rejete' && $stage->note_rejet)
                <div class="notification is-danger is-light py-2 mb-3" style="font-size:.82rem;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Motif du rejet :</strong> {{ $stage->note_rejet }}
                </div>
            @endif

            {{-- Actions --}}
            <div class="buttons are-small mt-3" style="border-top:1px solid #f0f0f0; padding-top:10px;">
                <a href="{{ route('stages.show', $stage) }}" class="button is-light">
                    <i class="fas fa-eye mr-1"></i> Détail
                </a>
                @can('update', $stage)
                    <a href="{{ route('stages.edit', $stage) }}" class="button is-light">
                        <i class="fas fa-pen has-text-warning-dark mr-1"></i> Modifier
                    </a>
                @endcan
                <a href="{{ route('stages.journal.index', $stage) }}" class="button is-light">
                    <i class="fas fa-book-open has-text-link mr-1"></i> Journal de stage
                </a>
                <a href="{{ route('pdf.convention', $stage) }}" class="button is-light">
                    <i class="fas fa-file-pdf has-text-danger mr-1"></i> Convention PDF
                </a>
            </div>
        </div>
        @endforeach
    @endif

</div>
@endsection

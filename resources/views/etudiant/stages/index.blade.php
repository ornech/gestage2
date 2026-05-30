@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Mes stages</h1>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif


    @if($stages->isEmpty())
        <div class="notification is-light">
            Vous n'avez pas encore de stage enregistré.
            <a href="{{ route('entreprises.index') }}" class="has-text-link ml-2">Rechercher une entreprise →</a>
        </div>
    @else
        @foreach($stages as $stage)
        <div class="box mb-4">
            <div class="level is-mobile mb-2">
                <div class="level-left">
                    <strong>{{ $stage->entreprise?->raison_sociale ?? '—' }}</strong>
                </div>
                <div class="level-right">
                    @php
                        $badgeVal = match($stage->statut_validation) {
                            'valide'  => ['is-success is-light', 'Stage validé'],
                            'rejete'  => ['is-danger is-light',  'Rejeté'],
                            default   => ['is-warning is-light', 'En attente de validation'],
                        };
                        $badgeConv = match($stage->statut_convention) {
                            'a_faire_signer'       => ['is-warning', "Convention à faire signer par l'employeur"],
                            'en_attente' => ['is-info',    'En attente du proviseur'],
                            'validee'      => ['is-success', 'Convention validée ✓'],
                            default                => ['is-light',   '—'],
                        };
                    @endphp
                    <div class="tags">
                        <span class="tag {{ $badgeVal[0] }}">{{ $badgeVal[1] }}</span>
                        <span class="tag {{ $badgeConv[0] }}">{{ $badgeConv[1] }}</span>
                    </div>
                </div>
            </div>

            {{-- Note de rejet --}}
            @if($stage->statut_validation === 'rejete' && $stage->note_rejet)
                <div class="notification is-danger is-light py-2 mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Motif du rejet :</strong> {{ $stage->note_rejet }}
                    <br><small>Corrigez les informations et votre stage repassera en attente de validation.</small>
                </div>
            @endif

            <div class="columns is-mobile is-size-7">
                <div class="column">
                    <p><i class="fas fa-calendar-alt mr-1"></i>
                        Du <strong>{{ $stage->date_debut?->format('d/m/Y') }}</strong>
                        au <strong>{{ $stage->date_fin?->format('d/m/Y') }}</strong>
                    </p>
                    <p><i class="fas fa-user-tie mr-1"></i>
                        Maître de stage : <strong>{{ $stage->maitreDeStage?->prenom }} {{ $stage->maitreDeStage?->nom ?? '—' }}</strong>
                    </p>
                </div>
            </div>

            <div class="buttons are-small mt-2">
                @can('update', $stage)
                    <a href="{{ route('stages.edit', $stage) }}" class="button is-warning">
                        <i class="fas fa-pen mr-1"></i> Modifier
                    </a>
                @endcan
                <a href="{{ route('pdf.convention', $stage) }}" class="button is-link">
                    <i class="fas fa-file-pdf mr-1"></i> Convention PDF
                </a>
            </div>
        </div>
        @endforeach
    @endif

</div>
@endsection

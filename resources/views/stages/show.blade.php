@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">
        Stage
        @if($stage->classe)
            <span class="tag {{ $stage->classe === 'SIO1' ? 'is-info' : 'is-primary' }} is-medium ml-2" style="vertical-align: middle;">
                {{ $stage->classe }}
            </span>
        @endif
    </h1>

    @if(session('success'))
        <div class="notification is-success">{{ session('success') }}</div>
    @endif

    <div class="box">
        <div class="columns">
            <div class="column">
                <p>
                    <strong>Entreprise :</strong>
                    @if($stage->entreprise)
                        <a href="{{ route('entreprises.show', $stage->entreprise) }}" class="has-text-link">
                            {{ $stage->entreprise->raison_sociale }}
                        </a>
                    @else —
                    @endif
                </p>
                <p>
                    <strong>Étudiant :</strong>
                    @if($stage->etudiant)
                        <a href="{{ route('admin.users.edit', $stage->etudiant) }}" class="has-text-link">
                            {{ $stage->etudiant->prenom }} {{ $stage->etudiant->nom }}
                        </a>
                    @else —
                    @endif
                </p>
                <p>
                    <strong>Maître de stage :</strong>
                    @if($stage->maitreDeStage)
                        <a href="{{ route('employes.show', $stage->maitreDeStage) }}" class="has-text-link">
                            {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
                        </a>
                    @else —
                    @endif
                </p>
            </div>
            <div class="column">
                <p><strong>Date de début :</strong> {{ $stage->date_debut?->format('d/m/Y') }}</p>
                <p><strong>Date de fin :</strong> {{ $stage->date_fin?->format('d/m/Y') }}</p>
                <p><strong>Classe :</strong> {{ $stage->classe ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- ── Journal de stage ── --}}
    @if($stage->date_debut && $stage->date_fin)
    @php
        $nbSemaines        = max(1, (int) ceil($stage->date_debut->diffInDays($stage->date_fin) / 7));
        $entriesParSemaine = $stage->journalEntries->groupBy('semaine')->map->count();
    @endphp
    <div class="box mb-4">
        <p class="heading mb-2">Journal de stage</p>
        <div class="buttons are-small mb-0" style="flex-wrap:wrap; gap:4px;">
            @for($s = 1; $s <= $nbSemaines; $s++)
            @php $count = $entriesParSemaine->get($s, 0); @endphp
            <a href="{{ route('stages.journal.index', $stage) }}?semaine={{ $s }}"
               class="button is-small {{ $count > 0 ? 'is-success' : 'is-light has-text-grey' }}"
               title="Semaine {{ $s }} — {{ $count > 0 ? $count.' réalisation(s)' : 'Aucune réalisation' }}">
                S{{ $s }}
            </a>
            @endfor
        </div>
    </div>
    @endif

    <div class="buttons">
        <a href="{{ route('stages.journal.index', $stage) }}" class="button is-info">
            Journal de bord
        </a>
        <a href="{{ route('pdf.convention', $stage) }}" class="button is-link">
            Convention PDF
        </a>
        <a href="{{ route('pdf.attestation', $stage) }}" class="button is-link">
            Attestation PDF
        </a>
        <a href="{{ route('stages.index') }}" class="button is-light">Retour</a>
    </div>

</div>
@endsection

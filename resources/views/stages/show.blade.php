@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Détail du stage</h1>

    @if(session('success'))
        <div class="notification is-success">{{ session('success') }}</div>
    @endif

    <div class="box">
        <div class="columns">
            <div class="column">
                <p><strong>Entreprise :</strong> {{ $stage->entreprise->raison_sociale ?? '—' }}</p>
                <p><strong>Étudiant :</strong> {{ $stage->etudiant->prenom ?? '' }} {{ $stage->etudiant->nom ?? '—' }}</p>
                <p><strong>Maître de stage :</strong> {{ $stage->maitreDeStage->prenom ?? '' }} {{ $stage->maitreDeStage->nom ?? '—' }}</p>
            </div>
            <div class="column">
                <p><strong>Date de début :</strong> {{ $stage->date_debut?->format('d/m/Y') }}</p>
                <p><strong>Date de fin :</strong> {{ $stage->date_fin?->format('d/m/Y') }}</p>
                <p><strong>Classe :</strong> {{ $stage->classe ?? '—' }}</p>
            </div>
        </div>
    </div>

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

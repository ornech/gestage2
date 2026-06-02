@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- ── En-tête ── --}}
    <div class="level mb-4">
        <div class="level-left" style="display:flex; align-items:center; gap:.6rem; flex-wrap:wrap;">
            <h1 class="title is-4 mb-0">Stage</h1>

            @if($stage->classe)
            <div style="display:flex; align-items:center;">
                <span class="tag is-medium {{ $stage->classe === 'SIO1' ? 'is-info' : 'is-primary' }}"
                      style="border-radius:4px 0 0 4px; margin:0;">{{ $stage->classe }}</span>
                <span class="tag is-medium" style="border-radius:0 4px 4px 0; margin:0; background:#e0e0e0; color:#444; border:1px solid #ccc; border-left:none;">
                    {{ $stage->classe === 'SIO1' ? 'Première année' : 'Deuxième année' }}
                </span>
            </div>
            @endif
        </div>
        <div class="level-right" style="gap:.5rem; display:flex;">
            <a href="{{ route('stages.journal.index', $stage) }}" class="button is-info is-small">
                <i class="fas fa-book-open mr-1"></i> Journal de stage
            </a>
            <a href="{{ route('pdf.convention', $stage) }}" class="button is-link is-small" target="_blank" rel="noopener">
                <i class="fas fa-file-pdf mr-1"></i> Convention PDF
            </a>
            <a href="{{ route('stages.index') }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light py-2 mb-4">{{ session('success') }}</div>
    @endif

    {{-- ── Informations du stage ── --}}
    <div class="box mb-4">
        <div class="columns">
            <div class="column">
                <p class="is-size-7 has-text-grey mb-1">Entreprise</p>
                <p class="has-text-weight-semibold">
                    @if($stage->entreprise)
                        <a href="{{ route('entreprises.show', $stage->entreprise) }}" class="has-text-dark lien-info">
                            {{ $stage->entreprise->raison_sociale }}
                        </a>
                    @else —
                    @endif
                </p>
            </div>
            <div class="column">
                <p class="is-size-7 has-text-grey mb-1">Étudiant</p>
                <p class="has-text-weight-semibold">
                    @if($stage->etudiant)
                        <a href="{{ route('admin.users.edit', $stage->etudiant) }}" class="has-text-dark lien-info">
                            {{ $stage->etudiant->prenom }} {{ $stage->etudiant->nom }}
                        </a>
                    @else —
                    @endif
                </p>
            </div>
            <div class="column">
                <p class="is-size-7 has-text-grey mb-1">Maître de stage</p>
                <p class="has-text-weight-semibold">
                    @if($stage->maitreDeStage)
                        <a href="{{ route('employes.show', $stage->maitreDeStage) }}" class="has-text-dark lien-info">
                            {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
                        </a>
                    @else —
                    @endif
                </p>
            </div>
            <div class="column">
                <p class="is-size-7 has-text-grey mb-1">Période</p>
                <p>
                    {{ $stage->date_debut?->format('d/m/Y') }}
                    @if($stage->date_fin) → {{ $stage->date_fin->format('d/m/Y') }} @endif
                </p>
            </div>
        </div>
    </div>

    {{-- ── Journal de stage ── --}}
    @if($stage->date_debut && $stage->date_fin)
    @php
        $nbSemaines        = max(1, (int) ceil($stage->date_debut->diffInDays($stage->date_fin) / 7));
        $entriesParSemaine = $stage->journalEntries->groupBy('semaine')->map->count();
    @endphp
    <div class="box">
        <p class="menu-label mb-3">Journal de stage</p>
        <div style="display:flex; flex-wrap:wrap; gap:6px;">
            @for($s = 1; $s <= $nbSemaines; $s++)
            @php $count = $entriesParSemaine->get($s, 0); @endphp
            <a href="{{ route('stages.journal.index', $stage) }}?semaine={{ $s }}"
               style="text-decoration:none; display:flex; align-items:center; border:1px solid #dbdbdb; border-radius:4px; overflow:hidden;">
                <span class="tag {{ $count > 0 ? 'is-success' : 'is-light has-text-grey' }}"
                      style="border-radius:0; margin:0;">S{{ $s }}</span>
                <span class="tag is-white"
                      style="border-radius:0; margin:0; border-left:1px solid #dbdbdb; min-width:24px; text-align:center;">
                    {{ $count ?: '·' }}
                </span>
            </a>
            @endfor
        </div>
    </div>
    @endif

</div>

<style nonce="{{ $cspNonce ?? '' }}">
.lien-info { border-bottom: 1px dashed #aaa; }
.lien-info:hover { color: #3273dc !important; border-bottom-color: #3273dc; }
</style>
@endsection

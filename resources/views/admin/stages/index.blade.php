@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Stages</h1>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

    {{-- ── Sélecteur d'années ──────────────────────────────────────────── --}}
    <div class="box p-3 mb-4">
        <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
            <span class="has-text-grey is-size-7 mr-2">Année :</span>
            @foreach($annees as $annee)
            @php $isFuture = (int) explode('-', $annee)[0] > (int) explode('-', $anneeActive)[0]; @endphp
                <a href="{{ route('admin.stages.index', array_merge(request()->except('annee','page'), ['annee' => $annee])) }}"
                   class="button is-small {{ $annee === $anneeSelectionnee ? ($isFuture ? 'is-warning' : 'is-link') : 'is-light' }}">
                    {{ $annee }}
                    @if($annee === $anneeActive)
                        <span class="tag is-success ml-1" style="font-size:0.6rem;padding:2px 4px;">●</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- ── Stats ───────────────────────────────────────────────────────── --}}
    <div class="columns is-mobile mb-4">
        <div class="column">
            <div class="box has-text-centered" style="border-bottom:3px solid #3273dc;">
                <p class="heading">Total</p>
                <p class="title is-4">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="column">
            <a href="{{ route('admin.stages.index', ['annee' => $anneeSelectionnee, 'filtre' => 'sio1']) }}"
               class="box has-text-centered" style="border-bottom:3px solid #00d1b2; display:block;">
                <p class="heading">SIO1</p>
                <p class="title is-4">{{ $stats['sio1'] }}</p>
            </a>
        </div>
        <div class="column">
            <a href="{{ route('admin.stages.index', ['annee' => $anneeSelectionnee, 'filtre' => 'sio2']) }}"
               class="box has-text-centered" style="border-bottom:3px solid #48c78e; display:block;">
                <p class="heading">SIO2</p>
                <p class="title is-4">{{ $stats['sio2'] }}</p>
            </a>
        </div>
        <div class="column">
            <a href="{{ route('admin.stages.index', ['annee' => $anneeSelectionnee, 'filtre' => 'sans_maitre']) }}"
               class="box has-text-centered" style="border-bottom:3px solid #f14668; display:block;">
                <p class="heading">Sans maître</p>
                <p class="title is-4">{{ $stats['sans_maitre'] }}</p>
            </a>
        </div>
    </div>

    {{-- ── Filtres + recherche ─────────────────────────────────────────── --}}
    <form method="GET" class="mb-3">
        <input type="hidden" name="annee" value="{{ $anneeSelectionnee }}">
        <div class="columns is-vcentered is-mobile">
            <div class="column">
                <div class="control has-icons-left">
                    <input class="input is-small" type="text" name="search"
                           placeholder="Étudiant ou entreprise…"
                           value="{{ request('search') }}">
                    <span class="icon is-left is-small"><i class="fas fa-search"></i></span>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="buttons has-addons are-small">
                    @foreach(['sio1' => 'SIO1', 'sio2' => 'SIO2', 'sans_maitre' => 'Sans maître', 'tout' => 'Tout afficher'] as $val => $label)
                        <a href="{{ route('admin.stages.index', array_merge(request()->except('filtre','search','page'), ['annee' => $anneeSelectionnee, 'filtre' => $val])) }}"
                           class="button {{ $filtre === $val ? 'is-link' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </form>

    @if(request('search'))
        <div class="notification is-info is-light py-2 mb-3">
            <i class="fas fa-search mr-2"></i>
            Recherche dans <strong>tous les stages</strong> pour « {{ request('search') }} »
            — <a href="{{ route('admin.stages.index', ['annee' => $anneeSelectionnee, 'filtre' => $filtre]) }}">Effacer</a>
        </div>
    @endif

    {{-- ── Tableau ─────────────────────────────────────────────────────── --}}
    @if($stages->isEmpty())
        <div class="notification is-light">Aucun stage pour ce filtre.</div>
    @else
    <table class="table is-striped is-fullwidth is-hoverable is-size-7">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Classe</th>
                <th>Entreprise</th>
                <th>Maître de stage</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stages as $stage)
            @php
                $anneeEtude = $stage->etudiant?->promo
                    ? (3 - ($stage->etudiant->promo - $syInt))
                    : null;
                $classeAnnee = ($anneeEtude >= 1 && $anneeEtude <= 2)
                    ? '<span class="tag is-info is-light">SIO'.$anneeEtude.'</span>'
                    : '<span class="has-text-grey">—</span>';
            @endphp
            <tr>
                <td>
                    @if($stage->etudiant)
                        {{ $stage->etudiant->nom }} {{ $stage->etudiant->prenom }}
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>
                <td>{!! $classeAnnee !!}</td>
                <td>{{ $stage->entreprise?->raison_sociale ?? '—' }}</td>
                <td>
                    <form action="{{ route('admin.stages.assign', $stage->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="select is-small">
                            <select name="maitre_de_stage_id" onchange="this.form.submit()">
                                <option value="">— Choisir —</option>
                                @foreach($tuteurs as $tuteur)
                                    <option value="{{ $tuteur->id }}"
                                        {{ $stage->maitre_de_stage_id == $tuteur->id ? 'selected' : '' }}>
                                        {{ $tuteur->prenom }} {{ $tuteur->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </td>
                <td>{{ $stage->date_debut?->format('d/m/Y') }}</td>
                <td>{{ $stage->date_fin?->format('d/m/Y') }}</td>
                <td>
                    @php
                        $statutBadge = match($stage->statut_validation) {
                            'valide'     => ['is-success', 'Validé'],
                            'rejete'     => ['is-danger',  'Rejeté'],
                            default      => ['is-warning', 'En attente'],
                        };
                    @endphp
                    <span class="tag {{ $statutBadge[0] }} is-light"
                          title="{{ $stage->note_rejet ?? '' }}">
                        {{ $statutBadge[1] }}
                    </span>
                </td>
                <td>
                    <div class="buttons are-small">
                        {{-- Valider --}}
                        @if($stage->statut_validation !== 'valide')
                        <form action="{{ route('admin.stages.valider', $stage) }}" method="POST" style="display:inline">
                            @csrf @method('PATCH')
                            <button class="button is-success" title="Valider">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif

                        {{-- Rejeter --}}
                        @if($stage->statut_validation !== 'rejete')
                        <button class="button is-danger" title="Rejeter"
                                onclick="document.getElementById('reject-modal-{{ $stage->id }}').classList.add('is-active')">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif

                        <a href="{{ route('stages.edit', $stage->id) }}"
                           class="button is-warning" title="Modifier">
                            <i class="fas fa-pen"></i>
                        </a>
                        <form action="{{ route('stages.destroy', $stage->id) }}" method="POST" style="display:inline">
                            @csrf @method('DELETE')
                            <button class="button is-light" onclick="return confirm('Supprimer ?')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Modal rejet --}}
                    <div id="reject-modal-{{ $stage->id }}" class="modal">
                        <div class="modal-background" onclick="this.parentElement.classList.remove('is-active')"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Motif du rejet</p>
                                <button class="delete" onclick="this.closest('.modal').classList.remove('is-active')"></button>
                            </header>
                            <form action="{{ route('admin.stages.rejeter', $stage) }}" method="POST">
                                @csrf @method('PATCH')
                                <section class="modal-card-body">
                                    <textarea class="textarea" name="note_rejet" rows="3"
                                              placeholder="Expliquez pourquoi le stage est rejeté…" required></textarea>
                                </section>
                                <footer class="modal-card-foot">
                                    <button type="submit" class="button is-danger">Rejeter</button>
                                    <button type="button" class="button"
                                            onclick="this.closest('.modal').classList.remove('is-active')">Annuler</button>
                                </footer>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $stages->withQueryString()->links() }}
    @endif

</div>
@endsection

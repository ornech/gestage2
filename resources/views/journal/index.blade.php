@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@creativebulma/bulma-tooltip@1.2.0/dist/bulma-tooltip.min.css">
<style>
    .tab-pane { display: none; }
    .tab-pane.is-visible { display: block; }
    .competence-tags { display: flex; flex-wrap: wrap; gap: 4px; }
    .tabs ul { flex-wrap: wrap; }
</style>
@endpush

@php
    $competences     = \App\Models\JournalEntry::COMPETENCES;
    $competencesDesc = \App\Models\JournalEntry::COMPETENCES_DESCRIPTIONS;
@endphp

@section('content')
<div class="container mt-5">

    {{-- En-tête --}}
    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">
                    <i class="fas fa-book-open mr-2 has-text-grey-light"></i>
                    Journal de bord
                    @if($stage->classe)
                        <span class="tag {{ $stage->classe === 'SIO1' ? 'is-info' : 'is-primary' }} ml-2" style="vertical-align:middle;">{{ $stage->classe }}</span>
                    @endif
                </h1>
                <p class="is-size-7 has-text-grey mt-1">
                    @if($stage->entreprise)
                        <a href="{{ route('entreprises.show', $stage->entreprise) }}" class="has-text-grey">{{ $stage->entreprise->raison_sociale }}</a>
                        &nbsp;&middot;&nbsp;
                    @endif
                    @if($stage->date_debut && $stage->date_fin)
                        du {{ $stage->date_debut->format('d/m/Y') }} au {{ $stage->date_fin->format('d/m/Y') }}
                    @endif
                </p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('stages.show', $stage) }}" class="button is-light is-small mr-2">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <button class="button is-primary is-small" id="btn-add">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Ajouter une réalisation</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="notification is-success is-light">{{ session('success') }}</div>
    @endif

    {{-- Zone principale avec onglets --}}
    <div class="box">
        {{-- Navigation par semaine --}}
        <div class="tabs is-boxed">
            <ul id="tab-nav">
                <li data-tab="all" class="{{ $selectedSemaine === 0 ? 'is-active' : '' }}">
                    <a>Toutes</a>
                </li>
                @for($s = 1; $s <= $nbSemaines; $s++)
                <li data-tab="semaine-{{ $s }}" class="{{ $s === $selectedSemaine ? 'is-active' : '' }}">
                    <a>S{{ $s }}</a>
                </li>
                @endfor
            </ul>
        </div>

        {{-- Contenu par semaine --}}
        @for($s = 1; $s <= $nbSemaines; $s++)
        @php $sEntries = $entries->get($s, collect()); @endphp
        <div id="semaine-{{ $s }}" class="tab-pane {{ ($selectedSemaine === 0 || $s === $selectedSemaine) ? 'is-visible' : '' }}">
            <h4 class="title is-5 mb-3">Semaine {{ $s }}</h4>

            @if($sEntries->isEmpty())
                <div class="notification is-warning is-light">Aucune réalisation pour cette semaine.</div>
            @else
                <div class="columns is-multiline">
                    @foreach($sEntries as $entry)
                    <div class="column is-{{ min($sEntries->count(), 2) === 1 ? '12' : '6' }}">
                        <div class="box p-4" style="height:100%; display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <p class="has-text-weight-semibold mb-2">{{ $entry->titre }}</p>
                                <p class="is-size-7 has-text-grey-dark mb-3" style="white-space: pre-line; line-height:1.6;">{{ $entry->activites }}</p>

                                @if($entry->competences)
                                <div class="mb-3">
                                    <p class="menu-label mb-1">Compétences</p>
                                    <div class="tags">
                                        @foreach($entry->competencesList() as $label)
                                        <span class="tag is-info is-light is-small">{{ $label }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="is-flex mt-3" style="gap:6px; border-top:1px solid #f0f0f0; padding-top:10px;">
                                <button class="button is-small is-light js-edit-btn"
                                    data-id="{{ $entry->id }}"
                                    data-semaine="{{ $entry->semaine }}"
                                    data-titre="{{ e($entry->titre) }}"
                                    data-activites="{{ e($entry->activites) }}"
                                    data-competences="{{ $entry->competences ?? 0 }}">
                                    <span class="icon is-small"><i class="fas fa-pen has-text-warning-dark"></i></span>
                                    <span>Modifier</span>
                                </button>
                                <form action="{{ route('stages.journal.destroy', [$stage, $entry]) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="button is-small is-light"
                                        onclick="return confirm('Supprimer cette réalisation ?')">
                                        <span class="icon is-small"><i class="fas fa-trash has-text-danger"></i></span>
                                        <span>Supprimer</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endfor
    </div>

</div>

{{-- ═══════════════════════════════════════════════
     MODALE — Ajouter une réalisation
════════════════════════════════════════════════ --}}
<div id="modal-add" class="modal">
    <div class="modal-background"></div>
    <div class="modal-card" style="max-width:680px; width:95%; overflow:visible;">
        <header class="modal-card-head">
            <p class="modal-card-title">Ajouter une réalisation</p>
            <button class="delete js-close-add" aria-label="Fermer"></button>
        </header>
        <form action="{{ route('stages.journal.store', $stage) }}" method="POST">
            @csrf
            <section class="modal-card-body" style="overflow:visible;">

                <div class="field">
                    <label class="label">Semaine</label>
                    <div class="control">
                        <div class="select">
                            <select name="semaine" id="add-semaine" required>
                                @for($i = 1; $i <= $nbSemaines; $i++)
                                <option value="{{ $i }}" {{ $i === $selectedSemaine ? 'selected' : '' }}>
                                    Semaine {{ $i }}
                                </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Titre</label>
                    <div class="control">
                        <input class="input" type="text" name="titre" placeholder="Ex : Installation d'un serveur DHCP" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Description des activités</label>
                    <div class="control">
                        <textarea class="textarea" name="activites" rows="4" placeholder="Décrivez les tâches réalisées…" required></textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Compétences mobilisées</label>
                    <div class="control">
                        @foreach($competences as $id => $label)
                        <label class="checkbox" style="display:block; margin-bottom:6px;">
                            <input type="checkbox" name="competences[]" value="{{ $id }}">
                            {{ $label }}
                            <span class="icon is-small has-tooltip-arrow has-tooltip-multiline has-tooltip-right ml-1"
                                  data-tooltip="{{ $competencesDesc[$id] }}">
                                <i class="fas fa-circle-info has-text-info"></i>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </section>
            <footer class="modal-card-foot">
                <button type="submit" class="button is-primary">Ajouter</button>
                <button type="button" class="button js-close-add ml-3">Annuler</button>
            </footer>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     MODALE — Modifier une réalisation
════════════════════════════════════════════════ --}}
<div id="modal-edit" class="modal">
    <div class="modal-background"></div>
    <div class="modal-card" style="max-width:680px; width:95%; overflow:visible;">
        <header class="modal-card-head">
            <p class="modal-card-title">Modifier une réalisation</p>
            <button class="delete js-close-edit" aria-label="Fermer"></button>
        </header>
        <form id="edit-form" action="" method="POST">
            @csrf @method('PUT')
            <section class="modal-card-body" style="overflow:visible;">

                <div class="field">
                    <label class="label">Titre</label>
                    <div class="control">
                        <input class="input" type="text" name="titre" id="edit-titre" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Description des activités</label>
                    <div class="control">
                        <textarea class="textarea" name="activites" id="edit-activites" rows="4" required></textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Compétences mobilisées</label>
                    <div class="control">
                        @foreach($competences as $id => $label)
                        <label class="checkbox" style="display:block; margin-bottom:6px;">
                            <input type="checkbox" name="competences[]" value="{{ $id }}" class="edit-competence-cb">
                            {{ $label }}
                            <span class="icon is-small has-tooltip-arrow has-tooltip-multiline has-tooltip-right ml-1"
                                  data-tooltip="{{ $competencesDesc[$id] }}">
                                <i class="fas fa-circle-info has-text-info"></i>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

            </section>
            <footer class="modal-card-foot">
                <button type="submit" class="button is-warning">Modifier</button>
                <button type="button" class="button js-close-edit ml-3">Annuler</button>
            </footer>
        </form>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', () => {

    // ── Onglets ──────────────────────────────────────────────────────────────
    const tabNav   = document.querySelectorAll('#tab-nav li');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabNav.forEach(tab => {
        tab.addEventListener('click', () => {
            tabNav.forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');

            const target = tab.dataset.tab;

            tabPanes.forEach(pane => {
                if (target === 'all') {
                    pane.classList.add('is-visible');
                } else {
                    pane.id === target
                        ? pane.classList.add('is-visible')
                        : pane.classList.remove('is-visible');
                }
            });
        });
    });

    // ── Modale Ajouter ───────────────────────────────────────────────────────
    const modalAdd   = document.getElementById('modal-add');
    const addSemaine = document.getElementById('add-semaine');

    document.getElementById('btn-add').addEventListener('click', () => {
        // Pré-sélectionne la semaine de l'onglet actif
        const activeTab = document.querySelector('#tab-nav li.is-active');
        if (activeTab && activeTab.dataset.tab !== 'all') {
            const s = activeTab.dataset.tab.replace('semaine-', '');
            addSemaine.value = s;
        }
        modalAdd.classList.add('is-active');
    });

    document.querySelectorAll('.js-close-add').forEach(el =>
        el.addEventListener('click', () => modalAdd.classList.remove('is-active'))
    );

    // ── Modale Modifier ──────────────────────────────────────────────────────
    const modalEdit = document.getElementById('modal-edit');
    const editForm  = document.getElementById('edit-form');
    const baseUrl   = '{{ url("/stages/{$stage->id}/journal") }}';

    document.querySelectorAll('.js-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id          = btn.dataset.id;
            const bitmask     = parseInt(btn.dataset.competences, 10) || 0;

            editForm.action = baseUrl + '/' + id;

            document.getElementById('edit-titre').value     = btn.dataset.titre;
            document.getElementById('edit-activites').value = btn.dataset.activites;

            document.querySelectorAll('.edit-competence-cb').forEach(cb => {
                cb.checked = (bitmask & parseInt(cb.value, 10)) !== 0;
            });

            modalEdit.classList.add('is-active');
        });
    });

    document.querySelectorAll('.js-close-edit').forEach(el =>
        el.addEventListener('click', () => modalEdit.classList.remove('is-active'))
    );

    // Fermeture au clic sur le fond
    [modalAdd, modalEdit].forEach(modal => {
        modal.querySelector('.modal-background').addEventListener('click', () => {
            modal.classList.remove('is-active');
        });
    });
});
</script>
@endpush

@endsection

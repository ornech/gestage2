@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width:760px;">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">Créer un stage pour un étudiant</h1>
                <p class="is-size-7 has-text-grey mt-1">
                    Passe-droit — à utiliser quand un étudiant ne parvient pas à saisir
                    lui-même son stage, son entreprise ou son maître de stage ({{ $annee }}).
                </p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.dashboard') }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form action="{{ route('admin.stages.creer') }}" method="POST" id="form-stage">
        @csrf

        {{-- ── Étape 1 : Étudiant ──────────────────────────────────── --}}
        <div class="box mb-4">
            <p class="menu-label mb-3">
                <span class="tag is-link mr-2">1</span> Étudiant concerné
            </p>

            <div class="field">
                <div class="control has-icons-left">
                    <input class="input" type="text" id="etudiant-recherche" autocomplete="off"
                           placeholder="Rechercher par nom ou prénom...">
                    <span class="icon is-small is-left"><i class="fas fa-search"></i></span>
                </div>
            </div>

            <input type="hidden" name="etudiant_id" id="etudiant-id" value="{{ old('etudiant_id') }}">

            <div id="etudiant-suggestions" class="box py-1" style="display:none; max-height:240px; overflow-y:auto;"></div>

            <div id="etudiant-selectionne" class="notification is-success is-light py-2 mt-2" style="display:none;"></div>
        </div>

        {{-- ── Étape 2 : Entreprise ────────────────────────────────── --}}
        <div class="box mb-4">
            <p class="menu-label mb-3">
                <span class="tag is-link mr-2">2</span> Entreprise d'accueil
            </p>

            {{-- Recherche SIRET --}}
            <div class="field has-addons mb-1">
                <div class="control is-expanded">
                    <input class="input" type="text" id="siret-input"
                           placeholder="N° SIRET — 14 chiffres"
                           maxlength="17" inputmode="numeric"
                           value="{{ old('siret_recherche') }}">
                </div>
                <div class="control">
                    <button type="button" class="button is-link" id="btn-siret">
                        <i class="fas fa-search mr-1"></i> Rechercher
                    </button>
                </div>
            </div>
            <p class="help mb-3">
                <i class="fas fa-external-link-alt mr-1"></i>
                SIRET inconnu ? Recherchez l'entreprise sur
                <a href="https://annuaire-entreprises.data.gouv.fr/" target="_blank" rel="noopener noreferrer">
                    l'Annuaire des Entreprises (data.gouv.fr)
                </a>.
            </p>

            <div id="siret-result" class="mb-3" style="display:none;"></div>

            {{-- Champ caché entreprise_id --}}
            <input type="hidden" name="entreprise_id" id="entreprise-id" value="{{ old('entreprise_id') }}">

            {{-- Bloc entreprise trouvée --}}
            <div id="bloc-entreprise-trouvee" style="display:none;">
                <div class="notification is-success is-light py-2 mb-2" id="entreprise-info"></div>
            </div>

            {{-- Si pas trouvée --}}
            <div id="bloc-entreprise-introuvable" style="display:none;">
                <div class="notification is-warning is-light py-2">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <strong>Entreprise introuvable.</strong>
                    Vérifie le numéro SIRET ou crée la fiche entreprise manuellement.
                </div>
            </div>
        </div>

        {{-- ── Étape 3 : Maître de stage ───────────────────────────── --}}
        <div class="box mb-4" id="bloc-contact" style="display:none;">
            <p class="menu-label mb-3">
                <span class="tag is-link mr-2">3</span> Maître de stage
            </p>

            <div class="field">
                <label class="label is-small">Contact existant dans l'entreprise</label>
                <div class="select is-fullwidth" id="select-contact-wrapper">
                    <select name="maitre_de_stage_id" id="select-contact">
                        <option value="">— Sélectionner —</option>
                    </select>
                </div>
            </div>

            <p class="is-size-7 has-text-grey mt-2 mb-0">
                <i class="fas fa-plus-circle mr-1"></i>
                Le maître de stage n'apparaît pas ?
                <a id="btn-nouveau-contact" href="#">Ajoute-le ici</a>.
            </p>

            {{-- Mini-formulaire d'ajout d'un nouveau maître de stage (AJAX) --}}
            <div id="bloc-nouveau-contact" class="box mt-3" style="display:none;">
                <div class="columns mb-0">
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Nom</label>
                            <input class="input is-small" type="text" id="nc-nom">
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Prénom</label>
                            <input class="input is-small" type="text" id="nc-prenom">
                        </div>
                    </div>
                </div>
                <div class="columns mb-0">
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Email</label>
                            <input class="input is-small" type="email" id="nc-email">
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label is-small">Téléphone</label>
                            <input class="input is-small" type="text" id="nc-telephone">
                        </div>
                    </div>
                </div>
                <p id="nc-erreur" class="help is-danger" style="display:none;"></p>
                <div class="field is-grouped mt-2">
                    <div class="control">
                        <button type="button" id="nc-enregistrer" class="button is-link is-small">Enregistrer</button>
                    </div>
                    <div class="control">
                        <button type="button" id="nc-annuler" class="button is-light is-small">Annuler</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Étape 4 : Dates ─────────────────────────────────────── --}}
        <div class="box mb-4" id="bloc-dates" style="display:none;">
            <p class="menu-label mb-3">
                <span class="tag is-link mr-2">4</span> Dates du stage
            </p>

            @php
                $defaultDateDebut = old('date_debut', '');
                $defaultDuree     = old('duree', 6);
            @endphp

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Date de début</label>
                        <input class="input" type="date" name="date_debut"
                               value="{{ $defaultDateDebut }}" required>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Durée (en semaines)</label>
                        <div class="select is-fullwidth">
                            <select name="duree" required>
                                <option value="">— Choisir —</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $defaultDuree == $i ? 'selected' : '' }}>
                                        {{ $i }} semaine{{ $i > 1 ? 's' : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bouton final --}}
        <div id="bloc-submit" style="display:none;">
            <button type="submit" class="button is-primary is-fullwidth">
                <i class="fas fa-check mr-2"></i> Créer le stage
            </button>
        </div>

    </form>
</div>

@php
    $etudiantsJson = $etudiants->map(fn($e) => [
        'id'     => $e->id,
        'label'  => "{$e->prenom} {$e->nom}",
        'classe' => $e->classe,
    ])->values();
@endphp

<script nonce="{{ $cspNonce ?? '' }}">
const etudiants = {!! $etudiantsJson->toJson() !!};

const etudiantRecherche   = document.getElementById('etudiant-recherche');
const etudiantId          = document.getElementById('etudiant-id');
const etudiantSuggestions = document.getElementById('etudiant-suggestions');
const etudiantSelectionne = document.getElementById('etudiant-selectionne');

function selectionnerEtudiant(e) {
    etudiantId.value = e.id;
    etudiantRecherche.value = e.label;
    etudiantSuggestions.style.display = 'none';
    etudiantSuggestions.innerHTML = '';
    etudiantSelectionne.innerHTML = '<i class="fas fa-user-check mr-2"></i><strong>' + e.label + '</strong>'
        + (e.classe ? ' <span class="tag is-light is-small ml-2">' + e.classe + '</span>' : '');
    etudiantSelectionne.style.display = 'block';
}

etudiantRecherche.addEventListener('input', function () {
    etudiantId.value = '';
    etudiantSelectionne.style.display = 'none';

    const q = this.value.trim().toLowerCase();
    if (q.length < 2) {
        etudiantSuggestions.style.display = 'none';
        etudiantSuggestions.innerHTML = '';
        return;
    }

    const resultats = etudiants.filter(e => e.label.toLowerCase().includes(q)).slice(0, 15);

    if (!resultats.length) {
        etudiantSuggestions.innerHTML = '<p class="is-size-7 has-text-grey px-2 py-1">Aucun étudiant trouvé.</p>';
    } else {
        etudiantSuggestions.innerHTML = '';
        resultats.forEach(e => {
            const item = document.createElement('a');
            item.href = '#';
            item.className = 'panel-block';
            item.style.cssText = 'display:block; padding:.4rem .6rem; border-radius:4px;';
            item.innerHTML = '<strong>' + e.label + '</strong>' + (e.classe ? ' <span class="tag is-light is-small ml-1">' + e.classe + '</span>' : '');
            item.addEventListener('click', function (ev) {
                ev.preventDefault();
                selectionnerEtudiant(e);
            });
            etudiantSuggestions.appendChild(item);
        });
    }
    etudiantSuggestions.style.display = 'block';
});

const siretInput   = document.getElementById('siret-input');
const btnSiret     = document.getElementById('btn-siret');
const siretResult  = document.getElementById('siret-result');
const entrepriseId = document.getElementById('entreprise-id');
const blocTrouvee  = document.getElementById('bloc-entreprise-trouvee');
const entrepriseInfo = document.getElementById('entreprise-info');
const blocIntrouvable = document.getElementById('bloc-entreprise-introuvable');
const blocContact  = document.getElementById('bloc-contact');
const selectContact = document.getElementById('select-contact');
const btnNouveauContact = document.getElementById('btn-nouveau-contact');
const blocNouveauContact = document.getElementById('bloc-nouveau-contact');
const ncNom = document.getElementById('nc-nom');
const ncPrenom = document.getElementById('nc-prenom');
const ncEmail = document.getElementById('nc-email');
const ncTelephone = document.getElementById('nc-telephone');
const ncErreur = document.getElementById('nc-erreur');
const ncEnregistrer = document.getElementById('nc-enregistrer');
const ncAnnuler = document.getElementById('nc-annuler');
const blocDates    = document.getElementById('bloc-dates');
const blocSubmit   = document.getElementById('bloc-submit');

// Formatage SIRET auto
siretInput.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 14);
    this.value = v;
});

btnSiret.addEventListener('click', async function () {
    const siret = siretInput.value.replace(/\s/g, '');
    if (siret.length !== 14) {
        siretResult.style.display = 'block';
        siretResult.innerHTML = '<p class="help is-danger">Le SIRET doit contenir 14 chiffres.</p>';
        return;
    }

    btnSiret.classList.add('is-loading');

    const resp = await fetch('{{ route("admin.stages.recherche-siret") }}?siret=' + siret, {
        headers: { 'Accept': 'application/json',
                   'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await resp.json();

    btnSiret.classList.remove('is-loading');
    blocTrouvee.style.display    = 'none';
    blocIntrouvable.style.display = 'none';
    blocContact.style.display    = 'none';
    blocDates.style.display      = 'none';
    blocSubmit.style.display     = 'none';

    if (data.found) {
        entrepriseId.value = data.id;
        const badge = data.created
            ? '<span class="tag is-warning is-light ml-2">Importée depuis l\'INSEE</span>'
            : '<span class="tag is-success is-light ml-2">Déjà dans la base</span>';
        entrepriseInfo.innerHTML =
            '<i class="fas fa-building mr-2"></i><strong>' + data.nom + '</strong>' + badge +
            (data.adresse ? '<br><span class="is-size-7 has-text-grey">' + data.adresse + '</span>' : '');
        blocTrouvee.style.display = 'block';

        // Contacts
        selectContact.innerHTML = '<option value="">— Sélectionner —</option>';
        data.contacts.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.label;
            selectContact.appendChild(opt);
        });
        blocContact.style.display = 'block';
        blocDates.style.display   = 'block';
        blocSubmit.style.display  = 'block';
    } else {
        entrepriseId.value = '';
        blocIntrouvable.style.display = 'block';
    }
});

// Ajout d'un nouveau maître de stage (AJAX)
btnNouveauContact.addEventListener('click', function (e) {
    e.preventDefault();
    ncErreur.style.display = 'none';
    blocNouveauContact.style.display = blocNouveauContact.style.display === 'none' ? 'block' : 'none';
});

ncAnnuler.addEventListener('click', function () {
    blocNouveauContact.style.display = 'none';
    ncErreur.style.display = 'none';
    [ncNom, ncPrenom, ncEmail, ncTelephone].forEach(i => i.value = '');
});

ncEnregistrer.addEventListener('click', async function () {
    ncErreur.style.display = 'none';

    if (!entrepriseId.value) {
        ncErreur.textContent = 'Sélectionne d\'abord une entreprise.';
        ncErreur.style.display = 'block';
        return;
    }
    if (!ncNom.value.trim() || !ncPrenom.value.trim()) {
        ncErreur.textContent = 'Le nom et le prénom sont obligatoires.';
        ncErreur.style.display = 'block';
        return;
    }

    ncEnregistrer.classList.add('is-loading');

    const resp = await fetch('{{ route("admin.stages.maitre-de-stage.store") }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            entreprise_id: entrepriseId.value,
            nom: ncNom.value.trim(),
            prenom: ncPrenom.value.trim(),
            email: ncEmail.value.trim() || null,
            telephone: ncTelephone.value.trim() || null
        })
    });

    ncEnregistrer.classList.remove('is-loading');

    if (!resp.ok) {
        const data = await resp.json().catch(() => ({}));
        const premiereErreur = data.errors ? Object.values(data.errors)[0][0] : null;
        ncErreur.textContent = premiereErreur || 'Impossible d\'ajouter ce maître de stage.';
        ncErreur.style.display = 'block';
        return;
    }

    const data = await resp.json();
    const opt = document.createElement('option');
    opt.value = data.id;
    opt.textContent = data.label;
    opt.selected = true;
    selectContact.appendChild(opt);

    blocNouveauContact.style.display = 'none';
    [ncNom, ncPrenom, ncEmail, ncTelephone].forEach(i => i.value = '');
});

// Réafficher les blocs si retour avec erreurs (old values)
@if(old('entreprise_id'))
blocContact.style.display = 'block';
blocDates.style.display   = 'block';
blocSubmit.style.display  = 'block';
@endif
@if(old('etudiant_id'))
    @php $oldEtudiant = $etudiants->firstWhere('id', (int) old('etudiant_id')); @endphp
    @if($oldEtudiant)
selectionnerEtudiant({ id: {{ $oldEtudiant->id }}, label: '{{ $oldEtudiant->prenom }} {{ $oldEtudiant->nom }}', classe: '{{ $oldEtudiant->classe }}' });
    @endif
@endif
</script>
@endsection

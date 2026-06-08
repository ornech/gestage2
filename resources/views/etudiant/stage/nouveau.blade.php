@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width:720px;">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">Saisir mon stage</h1>
                <p class="is-size-7 has-text-grey mt-1">
                    {{ $user->prenom }} {{ $user->nom }}
                    @if($user->classe_courante)
                        &nbsp;·&nbsp;
                        <span class="tag {{ $user->classe_courante === 'SIO1' ? 'is-info' : 'is-primary' }} is-small">{{ $user->classe_courante }}</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('etudiant.dashboard') }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    <form action="{{ route('stages.store') }}" method="POST" id="form-stage">
        @csrf

        {{-- ── Étape 1 : Entreprise ────────────────────────────────── --}}
        <div class="box mb-4">
            <p class="menu-label mb-3">
                <span class="tag is-link mr-2">1</span> Entreprise d'accueil
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
                Vous ne connaissez pas le SIRET ? Retrouvez votre entreprise et son numéro SIRET par secteur d'activité sur
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

            {{-- Si pas trouvée : lien vers création manuelle --}}
            <div id="bloc-entreprise-introuvable" style="display:none;">
                <div class="notification is-warning is-light py-2">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <strong>Entreprise introuvable.</strong>
                    Vérifie le numéro SIRET ou contacte ton professeur référent pour qu'il crée la fiche entreprise.
                </div>
            </div>
        </div>

        {{-- ── Étape 2 : Maître de stage ───────────────────────────── --}}
        <div class="box mb-4" id="bloc-contact" style="display:none;">
            <p class="menu-label mb-3">
                <span class="tag is-link mr-2">2</span> Maître de stage
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
                Ton maître de stage n'apparaît pas ?
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

        {{-- ── Étape 3 : Dates ─────────────────────────────────────── --}}
        <div class="box mb-4" id="bloc-dates" style="display:none;">
            <p class="menu-label mb-3">
                <span class="tag is-link mr-2">3</span> Dates du stage
            </p>

            @if($config?->stage_date_debut)
            <div class="notification is-info is-light py-2 mb-3" style="font-size:.82rem;">
                <i class="fas fa-calendar-check mr-1"></i>
                Dates officielles pour {{ $user->classe_courante }} :
                <strong>du {{ $config->stage_date_debut->format('d/m/Y') }}
                au {{ $config->stage_date_fin->format('d/m/Y') }}</strong>
                ({{ $config->duree_en_semaines }} semaines).
                Tu peux les ajuster si nécessaire.
            </div>
        @endif

        @php
            $defaultDateDebut = old('date_debut', $config?->stage_date_debut?->format('Y-m-d') ?? '');
            $defaultDuree     = old('duree', $config?->duree_en_semaines ?? 6);
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
                <i class="fas fa-check mr-2"></i> Enregistrer mon stage
            </button>
        </div>

    </form>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
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

// Formatage SIRET auto (groupes de 3)
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

    const resp = await fetch('{{ route("etudiant.stage.recherche-siret") }}?siret=' + siret, {
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

// Ajout d'un nouveau maître de stage (AJAX, sans quitter le formulaire)
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

    const resp = await fetch('{{ route("etudiant.stage.maitre-de-stage.store") }}', {
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

// Si retour avec erreurs (old values), réafficher les blocs
@if(old('entreprise_id'))
blocContact.style.display = 'block';
blocDates.style.display   = 'block';
blocSubmit.style.display  = 'block';
@endif
</script>
@endsection

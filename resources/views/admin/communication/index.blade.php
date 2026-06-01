@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0"><i class="fas fa-bullhorn mr-2"></i> Communication</h1>
                <p class="is-size-7 has-text-grey">Envoi de messages, suivi RGPD, personnalisation des templates</p>
            </div>
        </div>
    </div>

    @if(session('success_envoi'))
        <div class="notification is-success is-light py-2 mb-4">
            <i class="fas fa-check mr-1"></i> {{ session('success_envoi') }}
        </div>
    @endif
    @if(session('success_template'))
        <div class="notification is-success is-light py-2 mb-4">
            <i class="fas fa-check mr-1"></i> {{ session('success_template') }}
        </div>
    @endif
    @if($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    {{-- Onglets --}}
    <div class="tabs is-boxed mb-0">
        <ul>
            <li id="tab-envoyer" class="is-active" data-tab="envoyer">
                <a><i class="fas fa-paper-plane mr-2"></i> Envoyer une communication</a>
            </li>
            <li id="tab-template" data-tab="template">
                <a><i class="fas fa-envelope-open-text mr-2"></i> Template bienvenue</a>
            </li>
            <li id="tab-rgpd" data-tab="rgpd">
                <a>
                    <i class="fas fa-user-shield mr-2"></i> Demandes RGPD
                    @if($rgpdSuppresses->isNotEmpty())
                        <span class="tag is-warning is-light ml-2">{{ $rgpdSuppresses->count() }}</span>
                    @endif
                </a>
            </li>
        </ul>
    </div>

    {{-- ─── Onglet Envoyer ──────────────────────────────────────────── --}}
    <div id="panel-envoyer" class="box" style="border-radius:0 6px 6px 6px;">
        <form action="{{ route('admin.communication.envoyer') }}" method="POST" id="form-envoyer">
            @csrf

            <div class="columns">
                <div class="column is-three-fifths">
                    <div class="field">
                        <label class="label is-small">Objet du mail</label>
                        <input class="input is-small" type="text" name="sujet"
                               value="{{ old('sujet') }}"
                               placeholder="Ex : Séminaire cybersécurité BTS SIO — invitation" required>
                    </div>

                    <div class="field">
                        <label class="label is-small">Corps du message</label>
                        <textarea class="textarea is-small" name="corps" rows="10" required
                                  placeholder="Rédigez votre message ici. Le nom du destinataire sera ajouté automatiquement en introduction.">{{ old('corps') }}</textarea>
                        <p class="help">Texte brut. Les sauts de ligne sont conservés.</p>
                    </div>
                </div>

                <div class="column">
                    <div class="field">
                        <label class="label is-small">Destinataires</label>
                    </div>

                    <div class="field">
                        <label class="radio is-size-7">
                            <input type="radio" name="mode" value="tous" class="js-mode"
                                   {{ old('mode', 'tous') === 'tous' ? 'checked' : '' }}>
                            <strong>Tous les contacts</strong>
                            <span class="has-text-grey ml-1">({{ $employes->whereNotNull('email')->count() }} avec email)</span>
                        </label>
                    </div>
                    <div class="field">
                        <label class="radio is-size-7">
                            <input type="radio" name="mode" value="jury" class="js-mode"
                                   {{ old('mode') === 'jury' ? 'checked' : '' }}>
                            <strong>Membres de jury</strong>
                            <span class="has-text-grey ml-1">({{ $employes->where('jury', true)->whereNotNull('email')->count() }} contacts)</span>
                        </label>
                    </div>
                    <div class="field">
                        <label class="radio is-size-7">
                            <input type="radio" name="mode" value="manuelle" class="js-mode"
                                   {{ old('mode') === 'manuelle' ? 'checked' : '' }}>
                            <strong>Sélection manuelle</strong>
                        </label>
                    </div>

                    {{-- Liste sélection manuelle --}}
                    <div id="liste-manuelle" style="display:{{ old('mode') === 'manuelle' ? 'block' : 'none' }}; max-height:320px; overflow-y:auto; border:1px solid #dbdbdb; border-radius:4px; padding:8px; margin-top:8px;">
                        <input id="recherche-contact" class="input is-small mb-2" type="text" placeholder="Rechercher un contact…">
                        <label class="is-size-7 has-text-grey mb-2" style="display:block;">
                            <input type="checkbox" id="toggle-all"> Tout cocher / décocher
                        </label>
                        @foreach($employes->whereNotNull('email')->sortBy('nom') as $emp)
                        <label class="checkbox is-size-7 contact-item" style="display:flex; align-items:flex-start; gap:6px; margin-bottom:4px;"
                               data-search="{{ strtolower($emp->nom . ' ' . $emp->prenom . ' ' . ($emp->entreprise->raison_sociale ?? '')) }}">
                            <input type="checkbox" name="destinataires[]" value="{{ $emp->id }}"
                                   {{ is_array(old('destinataires')) && in_array($emp->id, old('destinataires')) ? 'checked' : '' }}>
                            <span>
                                <strong>{{ $emp->nom }} {{ $emp->prenom }}</strong><br>
                                <span class="has-text-grey">{{ $emp->entreprise->raison_sociale ?? '—' }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>

                    <div class="field mt-4">
                        <button type="submit" id="btn-envoyer" class="button is-primary is-small is-fullwidth">
                            <i class="fas fa-paper-plane mr-1"></i> Envoyer
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ─── Onglet Template bienvenue ───────────────────────────────── --}}
    <div id="panel-template" class="box" style="display:none; border-radius:0 6px 6px 6px;">

        <div class="columns">
            {{-- Formulaire --}}
            <div class="column is-two-fifths">
                <p class="menu-label mb-2">Mail de remerciement maître de stage</p>
                <p class="is-size-7 has-text-grey mb-4">
                    Envoyé automatiquement lors de la validation de la convention.
                    Utilisez <code>[PRENOM]</code> et <code>[NOM]</code> pour insérer le nom de l'étudiant.
                </p>
                <form action="{{ route('admin.communication.template') }}" method="POST">
                    @csrf @method('PUT')

                    <div class="field">
                        <label class="label is-small">Préfixe de l'objet du mail</label>
                        <div class="field has-addons">
                            <div class="control is-expanded">
                                <input class="input is-small" type="text"
                                       name="mail_bienvenue_objet_prefix"
                                       value="{{ $templateObjetPrefix }}"
                                       placeholder="Accueil en stage de">
                            </div>
                            <div class="control">
                                <span class="button is-static is-small">[Prénom Nom étudiant]</span>
                            </div>
                        </div>
                        <p class="help">Ex : "Accueil en stage de" → objet = "Accueil en stage de Jean Dupont"</p>
                    </div>

                    <div class="field">
                        <label class="label is-small">Texte de remerciement</label>
                        <textarea class="textarea is-small" name="mail_bienvenue_intro" rows="6"
                                  placeholder="Nous vous remercions chaleureusement d'accueillir [PRENOM] [NOM]…">{{ $templateIntro }}</textarea>
                        <p class="help">Les balises <code>[PRENOM]</code> et <code>[NOM]</code> seront remplacées automatiquement.</p>
                    </div>

                    <div class="field">
                        <label class="label is-small">Message complémentaire <span class="has-text-grey">(facultatif)</span></label>
                        <textarea class="textarea is-small" name="mail_bienvenue_intro_custom" rows="4"
                                  placeholder="Ex : Nous organisons chaque année une journée portes ouvertes…">{{ $templateComplement }}</textarea>
                        <p class="help">Affiché après le remerciement, avant le bloc contact.</p>
                    </div>

                    <div class="field">
                        <button type="submit" class="button is-primary is-small">
                            <i class="fas fa-save mr-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            {{-- Aperçu --}}
            <div class="column">
                <div class="level mb-2">
                    <div class="level-left">
                        <p class="menu-label mb-0">Aperçu du mail</p>
                    </div>
                    <div class="level-right">
                        <a href="{{ route('admin.communication.preview.bienvenue') }}"
                           target="_blank" class="button is-light is-small">
                            <i class="fas fa-external-link-alt mr-1"></i> Plein écran
                        </a>
                    </div>
                </div>
                <p class="is-size-7 has-text-grey mb-2">
                    Données d'exemple — enregistrez d'abord pour actualiser l'aperçu.
                </p>
                <iframe src="{{ route('admin.communication.preview.bienvenue') }}"
                        style="width:100%; height:520px; border:1px solid #dbdbdb; border-radius:4px;"
                        title="Aperçu mail remerciement">
                </iframe>
            </div>
        </div>
    </div>

    {{-- ─── Onglet RGPD ─────────────────────────────────────────────── --}}
    <div id="panel-rgpd" class="box" style="display:none; border-radius:0 6px 6px 6px;">
        <p class="menu-label mb-3">Contacts ayant exercé leur droit à l'effacement</p>

        @if($rgpdSuppresses->isEmpty())
            <p class="has-text-grey is-italic is-size-7">Aucune demande de suppression enregistrée.</p>
        @else
        <div class="table-container">
            <table class="table is-fullwidth is-size-7 is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Entreprise</th>
                        <th>Supprimé le</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rgpdSuppresses as $emp)
                <tr>
                    <td>{{ $emp->nom }} {{ $emp->prenom }}</td>
                    <td>{{ $emp->entreprise->raison_sociale ?? '—' }}</td>
                    <td>{{ $emp->email_supprime_at?->format('d/m/Y à H:i') ?? '—' }}</td>
                    <td>
                        <span class="tag is-warning is-light">
                            <i class="fas fa-ban mr-1"></i> Coordonnées supprimées
                        </span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <p class="help mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            Les nom et prénom sont conservés pour les besoins administratifs des stages.
            Conformément au RGPD, les demandes doivent être traitées dans un délai d'un mois.
        </p>
        @endif
    </div>

</div>

<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function () {

    // ── Onglets ──────────────────────────────────────────────────────
    const tabs   = ['envoyer', 'template', 'rgpd'];
    const tabEls = document.querySelectorAll('[data-tab]');

    function switchTab(tab) {
        tabs.forEach(t => {
            document.getElementById('panel-' + t).style.display = t === tab ? 'block' : 'none';
            document.getElementById('tab-' + t).classList.toggle('is-active', t === tab);
        });
    }

    tabEls.forEach(el => {
        el.addEventListener('click', function () {
            switchTab(this.dataset.tab);
        });
    });

    // ── Mode destinataires ───────────────────────────────────────────
    function switchMode(mode) {
        document.getElementById('liste-manuelle').style.display =
            mode === 'manuelle' ? 'block' : 'none';
    }

    document.querySelectorAll('.js-mode').forEach(radio => {
        radio.addEventListener('change', function () { switchMode(this.value); });
    });

    // ── Recherche contacts ───────────────────────────────────────────
    const rechercheInput = document.getElementById('recherche-contact');
    if (rechercheInput) {
        rechercheInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.contact-item').forEach(el => {
                el.style.display = el.dataset.search.includes(q) ? 'flex' : 'none';
            });
        });
    }

    // ── Tout cocher / décocher ───────────────────────────────────────
    const toggleAll = document.getElementById('toggle-all');
    if (toggleAll) {
        toggleAll.addEventListener('change', function () {
            document.querySelectorAll('#liste-manuelle input[name="destinataires[]"]')
                .forEach(el => el.checked = this.checked);
        });
    }

    // ── Confirmation avant envoi ─────────────────────────────────────
    const btnEnvoyer = document.getElementById('btn-envoyer');
    if (btnEnvoyer) {
        btnEnvoyer.addEventListener('click', function (e) {
            if (!confirm('Confirmer l\'envoi à tous les destinataires sélectionnés ?')) {
                e.preventDefault();
            }
        });
    }

});
</script>
@endsection

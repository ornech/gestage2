@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- En-tête compact --}}
    <div class="level mb-4">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">{{ $user->prenom }} {{ $user->nom }}</h1>
                <p class="is-size-7 has-text-grey">
                    {{ $user->email }}
                    @if($user->classe_courante)
                        · <span class="tag is-info is-light is-small">{{ $user->classe_courante }}</span>
                    @endif
                    @if($user->spe)
                        · <span class="tag is-link is-light is-small">{{ $user->spe }}</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="level-right">
            <div class="buttons has-addons mr-3">
                @if($precedent)
                    <a href="{{ route('admin.users.edit', $precedent) }}" class="button is-light is-small"
                       title="{{ $precedent->prenom }} {{ $precedent->nom }}">
                        <i class="fas fa-chevron-left mr-1"></i> Précédent
                    </a>
                @else
                    <span class="button is-light is-small" disabled><i class="fas fa-chevron-left mr-1"></i> Précédent</span>
                @endif
                @if($suivant)
                    <a href="{{ route('admin.users.edit', $suivant) }}" class="button is-light is-small"
                       title="{{ $suivant->prenom }} {{ $suivant->nom }}">
                        Suivant <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                @else
                    <span class="button is-light is-small" disabled>Suivant <i class="fas fa-chevron-right ml-1"></i></span>
                @endif
            </div>
            <a href="{{ route('admin.users.index', ['classe' => $user->classe_courante ?? $user->classe]) }}"
               class="button is-light is-small">
                ← Retour à la liste
            </a>
        </div>
    </div>

    @include('admin.partials.lien-invitation')

    @if(session('success'))
        <div class="notification is-success is-light py-2 mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @php
        $readOnly = !auth()->user()->hasRole('Administrateur');
        $current = $user->classe_courante ?? $user->classe;
        $stageAnnee = $user->stages->firstWhere('annee_scolaire', $annee);
        $statutLabels = ['actif' => ['is-success', 'Actif'], 'demissionnaire' => ['is-danger', 'Démissionnaire'], 'diplome' => ['is-link', 'Diplômé'], 'archive' => ['is-dark', 'Archivé'], 'echec' => ['is-warning', 'Échec']];
        [$statutClasse, $statutLibelle] = $statutLabels[$user->statut] ?? ['is-light', $user->statut];
        $ouvrirEdition = $errors->any() || old('nom') !== null;
    @endphp

    {{-- Accès rapide : stage et journal de l'année active --}}
    <div class="buttons mb-3">
        @if($stageAnnee)
            <a href="{{ route('stages.show', $stageAnnee) }}" class="button is-info is-small">
                <i class="fas fa-briefcase mr-1"></i> Stage {{ $annee }}
            </a>
            <a href="{{ route('stages.journal.index', $stageAnnee) }}" class="button is-info is-light is-small">
                <i class="fas fa-book mr-1"></i> Journal ({{ $stageAnnee->journal_entries_count }} saisie(s))
            </a>
        @else
            <span class="button is-small" disabled><i class="fas fa-briefcase mr-1"></i> Aucun stage {{ $annee }}</span>
        @endif
    </div>

    {{-- Synthèse de l'étudiant --}}
    <div class="box mb-4">
        <div class="level is-mobile mb-3">
            <div class="level-left"><p class="menu-label mb-0">Identité et scolarité</p></div>
            <div class="level-right">
                <button type="button" class="button is-light is-small" data-toggle-edition>
                    <i class="fas fa-pen mr-1"></i> Modifier
                </button>
            </div>
        </div>

        <div class="columns is-multiline is-mobile mb-0">
            <div class="column is-6-mobile is-2-desktop">
                <p class="heading">Classe</p>
                <p class="has-text-weight-semibold">{{ $current ?? '—' }}</p>
            </div>
            <div class="column is-6-mobile is-2-desktop">
                <p class="heading">Promo</p>
                <p class="has-text-weight-semibold">{{ $user->promo ?? '—' }}</p>
            </div>
            <div class="column is-6-mobile is-3-desktop">
                <p class="heading">Spécialité</p>
                <form action="{{ route('admin.users.spe', $user) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="select is-small">
                        <select name="spe" data-autosubmit aria-label="Spécialité">
                            <option value="">— Non définie —</option>
                            <option value="SLAM" {{ $user->spe === 'SLAM' ? 'selected' : '' }}>SLAM</option>
                            <option value="SISR" {{ $user->spe === 'SISR' ? 'selected' : '' }}>SISR</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="column is-6-mobile is-3-desktop">
                <p class="heading">Tuteur référent</p>
                <p class="has-text-weight-semibold">
                    {{ $user->tuteur ? $user->tuteur->prenom.' '.$user->tuteur->nom : '—' }}
                </p>
            </div>
            <div class="column is-6-mobile is-2-desktop">
                <p class="heading">Statut</p>
                <span class="tag {{ $statutClasse }}">{{ $statutLibelle }}</span>
            </div>
        </div>

        {{-- Zone d'édition : identité (admin) + statut administratif (prof / admin) --}}
        <div id="zone-edition" {!! $ouvrirEdition ? '' : 'hidden' !!}>
        @unless($readOnly)
        <form action="{{ route('admin.users.update', $user) }}" method="POST" id="form-etudiant">
            @csrf @method('PUT')
            <hr style="margin:12px 0;">
            <div class="columns is-multiline is-variable is-2">
                <div class="column is-4">
                    <label class="label is-small" for="f-nom">Nom</label>
                    <input class="input is-small" type="text" name="nom" id="f-nom" value="{{ old('nom', $user->nom) }}" required>
                </div>
                <div class="column is-4">
                    <label class="label is-small" for="f-prenom">Prénom</label>
                    <input class="input is-small" type="text" name="prenom" id="f-prenom" value="{{ old('prenom', $user->prenom) }}" required>
                </div>
                <div class="column is-4">
                    <label class="label is-small" for="f-email">Email</label>
                    <input class="input is-small" type="email" name="email" id="f-email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="column is-4">
                    <label class="label is-small" for="classe-select">Classe</label>
                    <div class="select is-fullwidth is-small">
                        <select name="classe" id="classe-select" data-onchange="updatePromo">
                            <option value="">—</option>
                            @foreach(['SIO1', 'SIO2'] as $c)
                                <option value="{{ $c }}" {{ old('classe', $current) === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="column is-4">
                    <label class="label is-small" for="promo-input">Promo</label>
                    <input class="input is-small" type="number" name="promo" id="promo-input" value="{{ old('promo', $user->promo) }}" min="2020" max="2040">
                </div>
                <div class="column is-4">
                    <label class="label is-small" for="f-tuteur">Tuteur référent</label>
                    <div class="select is-fullwidth is-small">
                        <select name="tuteur_id" id="f-tuteur">
                            <option value="">— Aucun —</option>
                            @foreach($tuteurs as $tuteur)
                                <option value="{{ $tuteur->id }}" {{ old('tuteur_id', $user->tuteur_id) == $tuteur->id ? 'selected' : '' }}>
                                    {{ $tuteur->prenom }} {{ $tuteur->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="buttons is-right mb-0">
                <button type="button" class="button is-light is-small" data-toggle-edition>Annuler</button>
                <button type="submit" class="button is-primary is-small">
                    <i class="fas fa-save mr-1"></i> Enregistrer
                </button>
            </div>
        </form>
        @endunless
            @role('Professeur|Administrateur')
            <hr style="margin:12px 0;">
            <div>
                <p class="menu-label">Statut administratif</p>

                {{-- Statuts : Actif | Démissionnaire | Diplômé (validé par un professeur) | Archivé (résultat inconnu) | Échec --}}
                <div class="buttons are-small mb-2">
                    @foreach(['actif' => ['is-success', 'Actif'], 'demissionnaire' => ['is-danger', 'Démissionnaire'], 'diplome' => ['is-link', 'Diplômé'], 'archive' => ['is-dark', 'Archivé'], 'echec' => ['is-warning', 'Échec']] as $val => [$color, $label])
                        @if($user->statut === $val)
                            <span class="button is-small {{ $color }}">
                                <i class="fas fa-check mr-1"></i> {{ $label }}
                            </span>
                        @else
                            <form action="{{ route('admin.users.statut', $user) }}" method="POST" style="display:inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="statut" value="{{ $val }}">
                                <button type="submit" class="button is-small is-light"
                                        data-confirm="Changer en {{ $label }} ?">
                                    {{ $label }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>

                @if($user->statut === 'demissionnaire')
                    <p class="help is-danger mb-3"><i class="fas fa-ban mr-1"></i> Accès désactivé.</p>
                @endif
                @if(in_array($user->statut, ['archive', 'echec'], true))
                    <p class="help mb-3"><i class="fas fa-ban mr-1"></i> {{ $user->statut === 'echec' ? 'BTS non obtenu' : 'Ancien, résultat non renseigné' }}. Accès désactivé.</p>
                @endif
                @if($user->estDiplome())
                    <p class="help is-link mb-3">
                        <i class="fas fa-graduation-cap mr-1"></i>
                        Diplôme validé{{ $user->diplome_at ? ' le '.$user->diplome_at->format('d/m/Y') : '' }}. Accès désactivé.
                    </p>
                @endif

                {{-- Redoublement : action sur la promo, pas un statut --}}
                <hr style="margin:8px 0;">
                <p class="is-size-7 has-text-grey mb-2">
                    <i class="fas fa-redo mr-1"></i>
                    Redoubler incrémente la promotion d'un an
                    @if($user->promo)
                        ({{ $user->promo }} → {{ $user->promo + 1 }})
                    @endif
                    et conserve le statut <strong>actif</strong>.
                </p>
                <form action="{{ route('admin.users.redoubler', $user) }}" method="POST"
                      data-confirm="Faire redoubler {{ $user->prenom }} {{ $user->nom }} ? Promotion : {{ $user->promo }} → {{ $user->promo ? $user->promo + 1 : '?' }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="button is-small is-warning is-light">
                        <i class="fas fa-redo mr-1"></i> Redoubler
                    </button>
                </form>
            </div>
            @endrole
        </div>
    </div>

    <div class="columns">

        {{-- Colonne droite : Historique des stages --}}
        <div class="column">
            <div class="box">
                <p class="menu-label">Historique des stages</p>

                @php
                    $convBadgeMap = [
                        'hors_app'       => ['is-warning is-light', 'Convention hors app — remise'],
                        'en_attente'     => ['is-info is-light',    'En attente du proviseur'],
                        'validee'        => ['is-success is-light', 'Validée ✓'],
                    ];
                @endphp

                {{-- Convention hors app sans stage numérique --}}
                @php $sansStageCetteAnnee = $user->stages->where('annee_scolaire', $annee)->isEmpty(); @endphp
                @if($user->conventionPapier && $sansStageCetteAnnee)
                <div class="notification is-warning is-light py-2 mb-3">
                    <i class="fas fa-file-alt mr-2"></i>
                    <strong>Convention hors app</strong> — stage non saisi dans l'appli.
                    <span class="tag {{ $convBadgeMap[$user->conventionPapier->statut][0] ?? 'is-light' }} ml-2">
                        {{ $convBadgeMap[$user->conventionPapier->statut][1] ?? $user->conventionPapier->statut }}
                    </span>
                </div>
                @endif

                @if($user->stages->isEmpty() && !$user->conventionPapier)
                    <p class="has-text-grey is-italic is-size-7">Aucun stage enregistré.</p>
                @else

                @foreach($user->stages as $stage)
                @php
                    $periode = $stage->classe;
                    $periodeColor = $periode === 'SIO1' ? 'is-info' : ($periode === 'SIO2' ? 'is-primary' : 'is-light');

                    $valBadge  = match($stage->statut_validation) {
                        'valide'  => ['is-success is-light', 'Validé'],
                        'rejete'  => ['is-danger is-light',  'Rejeté'],
                        default   => ['is-warning is-light', 'En attente'],
                    };
                    $convBadge = $convBadgeMap[$stage->statut_convention] ?? ['is-light', '—'];
                @endphp

                <div class="box p-3 mb-3" style="border-left: 4px solid {{ $periode === 'SIO1' ? '#3273dc' : ($periode === 'SIO2' ? '#00d1b2' : '#dbdbdb') }};">
                    <div class="level is-mobile mb-2">
                        <div class="level-left">
                            <span class="tag {{ $periodeColor }} mr-2">{{ $periode ?? '—' }}</span>
                            <span class="tag is-light mr-2">{{ $stage->annee_scolaire }}</span>
                            @if($stage->entreprise)
                                <a href="{{ route('stages.show', $stage) }}" class="has-text-weight-semibold">
                                    {{ $stage->entreprise->raison_sociale }}
                                    <i class="fas fa-external-link-alt is-size-7 ml-1"></i>
                                </a>
                            @else
                                <span class="has-text-grey">Entreprise non renseignée</span>
                            @endif
                        </div>
                    </div>
                    <div class="is-size-7 has-text-grey mb-2">
                        @if($stage->date_debut)
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Du {{ $stage->date_debut->format('d/m/Y') }} au {{ $stage->date_fin?->format('d/m/Y') ?? '?' }}
                            &nbsp;·&nbsp;
                        @endif
                        @if($stage->maitreDeStage)
                            <i class="fas fa-user-tie mr-1"></i>
                            {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
                        @endif
                    </div>
                    <div class="tags">
                        <span class="tag {{ $valBadge[0] }}">{{ $valBadge[1] }}</span>
                        <span class="tag {{ $convBadge[0] }}">{{ $convBadge[1] }}</span>
                    </div>
                    <div class="buttons are-small mt-2 mb-0">
                        <a href="{{ route('stages.show', $stage) }}" class="button is-small is-info is-light">
                            <i class="fas fa-briefcase mr-1"></i> Voir le stage
                        </a>
                        <a href="{{ route('stages.journal.index', $stage) }}" class="button is-small is-light">
                            <i class="fas fa-book mr-1"></i> Journal ({{ $stage->journal_entries_count }})
                        </a>
                        <a href="{{ route('pdf.convention', $stage) }}" class="button is-small is-light" target="_blank" rel="noopener">
                            <i class="fas fa-file-pdf mr-1"></i> Convention
                        </a>
                    </div>
                </div>
                @endforeach
                @endif

            </div>
        </div>

    </div>

</div>

<script nonce="{{ $cspNonce ?? '' }}">
document.querySelectorAll('[data-toggle-edition]').forEach(function (b) {
    b.addEventListener('click', function () {
        var z = document.getElementById('zone-edition');
        z.hidden = !z.hidden;
        var nom = document.getElementById('f-nom');
        if (!z.hidden && nom) { nom.focus(); }
    });
});
const promos = { 'SIO1': {{ $currentYear + 2 }}, 'SIO2': {{ $currentYear + 1 }} };
function updatePromo() {
    const sel   = document.getElementById('classe-select');
    const input = document.getElementById('promo-input');
    if (sel.value && promos[sel.value]) {
        input.value = promos[sel.value];
    }
}
</script>
@endsection

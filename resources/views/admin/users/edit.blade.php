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
        $statuts = ['actif' => ['is-success', 'Actif'], 'demissionnaire' => ['is-danger', 'Démissionnaire'], 'diplome' => ['is-link', 'Diplômé'], 'archive' => ['is-dark', 'Archivé'], 'echec' => ['is-warning', 'Échec']];
        [$statutClasse, $statutLibelle] = $statuts[$user->statut] ?? ['is-light', $user->statut];
        $ouvrirEdition = $errors->any() || old('nom') !== null;
        $convBadgeMap = [
            'hors_app'   => ['is-warning is-light', 'Hors app — remise'],
            'en_attente' => ['is-info is-light',    'En attente proviseur'],
            'validee'    => ['is-success is-light', 'Validée'],
        ];
    @endphp

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
                <p class="has-text-weight-semibold">{{ $user->tuteur ? $user->tuteur->prenom.' '.$user->tuteur->nom : '—' }}</p>
            </div>
            <div class="column is-6-mobile is-2-desktop">
                <p class="heading">Statut</p>
                <span class="tag {{ $statutClasse }}">{{ $statutLibelle }}</span>
            </div>
        </div>

        {{-- Zone de modification --}}
        <div id="zone-edition" {!! $ouvrirEdition ? '' : 'hidden' !!} class="mt-4 p-4" style="background:#f7f7f9; border-radius:6px;">

            @role('Professeur|Administrateur')
            <div class="columns is-vcentered mb-0">
                <div class="column is-5">
                    <label class="label is-small" for="statut-select">Statut administratif</label>
                    <form action="{{ route('admin.users.statut', $user) }}" method="POST"
                          data-confirm="Changer le statut de {{ $user->prenom }} {{ $user->nom }} ?">
                        @csrf @method('PATCH')
                        <div class="select is-small is-fullwidth">
                            <select name="statut" id="statut-select" data-autosubmit>
                                @foreach($statuts as $val => [$color, $label])
                                    <option value="{{ $val }}" {{ $user->statut === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @if($user->estDiplome())
                        <p class="help is-link"><i class="fas fa-graduation-cap mr-1"></i> Diplôme validé{{ $user->diplome_at ? ' le '.$user->diplome_at->format('d/m/Y') : '' }}. Accès désactivé.</p>
                    @elseif($user->statut === 'demissionnaire')
                        <p class="help is-danger"><i class="fas fa-ban mr-1"></i> Accès désactivé.</p>
                    @elseif(in_array($user->statut, ['archive', 'echec'], true))
                        <p class="help"><i class="fas fa-ban mr-1"></i> {{ $user->statut === 'echec' ? 'BTS non obtenu' : 'Ancien, résultat non renseigné' }}. Accès désactivé.</p>
                    @endif
                </div>
                <div class="column is-7">
                    <label class="label is-small">Redoublement</label>
                    <form action="{{ route('admin.users.redoubler', $user) }}" method="POST"
                          data-confirm="Faire redoubler {{ $user->prenom }} {{ $user->nom }} ? Promotion : {{ $user->promo }} → {{ $user->promo ? $user->promo + 1 : '?' }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="button is-small is-warning is-light" {{ $user->promo ? '' : 'disabled' }}>
                            <i class="fas fa-redo mr-1"></i>
                            Faire redoubler{{ $user->promo ? ' ('.$user->promo.' → '.($user->promo + 1).')' : '' }}
                        </button>
                    </form>
                    <p class="help">Passe la promo à l'année suivante, statut « actif ».</p>
                </div>
            </div>
            @endrole

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
                    <button type="submit" class="button is-primary is-small">
                        <i class="fas fa-save mr-1"></i> Enregistrer
                    </button>
                </div>
            </form>
            @endunless

            <div class="has-text-right mt-2">
                <button type="button" class="button is-light is-small" data-toggle-edition>Fermer</button>
            </div>
        </div>
    </div>

    {{-- Historique des stages : une ligne par stage --}}
    <div class="box">
        <div class="level is-mobile mb-3">
            <div class="level-left"><p class="menu-label mb-0">Stages</p></div>
        </div>

        @if($user->conventionPapier && !$stageAnnee)
        <div class="notification is-warning is-light py-2 mb-3">
            <i class="fas fa-file-alt mr-2"></i>
            <strong>Convention hors app</strong> — stage non saisi dans l'appli.
            <span class="tag {{ $convBadgeMap[$user->conventionPapier->statut][0] ?? 'is-light' }} ml-2">
                {{ $convBadgeMap[$user->conventionPapier->statut][1] ?? $user->conventionPapier->statut }}
            </span>
        </div>
        @endif

        <div class="table-container">
        <table class="table is-fullwidth is-hoverable is-narrow" style="vertical-align:middle;">
            <thead>
                <tr>
                    <th>Année</th>
                    <th>Classe</th>
                    <th>Entreprise</th>
                    <th>Période</th>
                    <th>Maître de stage</th>
                    <th>Validation</th>
                    <th>Convention</th>
                    <th class="has-text-right">Accès</th>
                </tr>
            </thead>
            <tbody>
            @forelse($user->stages as $stage)
                @php
                    $valBadge = match($stage->statut_validation) {
                        'valide' => ['is-success is-light', 'Validé'],
                        'rejete' => ['is-danger is-light', 'Rejeté'],
                        default  => ['is-warning is-light', 'En attente'],
                    };
                    $convBadge = $convBadgeMap[$stage->statut_convention] ?? null;
                @endphp
                <tr>
                    <td class="is-nowrap">{{ $stage->annee_scolaire }}</td>
                    <td>
                        @if($stage->classe)
                            <span class="tag {{ $stage->classe === 'SIO1' ? 'is-info' : 'is-primary' }}">{{ $stage->classe }}</span>
                        @endif
                    </td>
                    <td>
                        @if($stage->entreprise)
                            <a href="{{ route('stages.show', $stage) }}" class="has-text-weight-semibold">{{ $stage->entreprise->raison_sociale }}</a>
                        @else
                            <span class="has-text-grey">—</span>
                        @endif
                    </td>
                    <td class="is-nowrap is-size-7">
                        @if($stage->date_debut)
                            {{ $stage->date_debut->format('d/m/Y') }} → {{ $stage->date_fin?->format('d/m/Y') ?? '?' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $stage->maitreDeStage ? $stage->maitreDeStage->prenom.' '.$stage->maitreDeStage->nom : '—' }}</td>
                    <td><span class="tag {{ $valBadge[0] }}">{{ $valBadge[1] }}</span></td>
                    <td>
                        @if($convBadge)
                            <span class="tag {{ $convBadge[0] }}">{{ $convBadge[1] }}</span>
                        @else
                            <span class="has-text-grey">—</span>
                        @endif
                    </td>
                    <td class="has-text-right is-nowrap">
                        <a href="{{ route('stages.show', $stage) }}" class="button is-small is-info is-light" title="Voir le stage">
                            <i class="fas fa-briefcase"></i>
                        </a>
                        <a href="{{ route('stages.journal.index', $stage) }}" class="button is-small is-light" title="Journal ({{ $stage->journal_entries_count }} saisie(s))">
                            <i class="fas fa-book mr-1"></i> {{ $stage->journal_entries_count }}
                        </a>
                        <a href="{{ route('pdf.convention', $stage) }}" class="button is-small is-light" target="_blank" rel="noopener" title="Convention PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="has-text-centered has-text-grey is-italic">Aucun stage enregistré.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
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

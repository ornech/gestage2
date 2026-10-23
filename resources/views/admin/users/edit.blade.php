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

    @php $readOnly = !auth()->user()->hasRole('Administrateur'); @endphp
            <form action="{{ route('admin.users.update', $user) }}" method="POST" id="form-etudiant">
                @csrf @method('PUT')

                <div class="box mb-4">
                    <p class="menu-label">Identité et scolarité</p>
                    <div class="columns is-multiline is-variable is-2">
                        <div class="column is-4">
                            <label class="label is-small" for="f-nom">Nom</label>
                            <input class="input is-small" type="text" name="nom" id="f-nom"
                                   value="{{ old('nom', $user->nom) }}" required {{ $readOnly ? 'disabled' : '' }}>
                        </div>
                        <div class="column is-4">
                            <label class="label is-small" for="f-prenom">Prénom</label>
                            <input class="input is-small" type="text" name="prenom" id="f-prenom"
                                   value="{{ old('prenom', $user->prenom) }}" required {{ $readOnly ? 'disabled' : '' }}>
                        </div>
                        <div class="column is-4">
                            <label class="label is-small" for="f-email">Email</label>
                            <input class="input is-small" type="email" name="email" id="f-email"
                                   value="{{ old('email', $user->email) }}" required {{ $readOnly ? 'disabled' : '' }}>
                        </div>
                        @php $current = $user->classe_courante ?? $user->classe; @endphp
                        <div class="column is-3">
                            <label class="label is-small" for="classe-select">Classe</label>
                            <div class="select is-fullwidth is-small">
                                <select name="classe" id="classe-select" data-onchange="updatePromo" {{ $readOnly ? 'disabled' : '' }}>
                                    <option value="">—</option>
                                    @foreach(['SIO1', 'SIO2'] as $c)
                                        <option value="{{ $c }}" {{ old('classe', $current) === $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="column is-3">
                            <label class="label is-small" for="promo-input">Promo</label>
                            <input class="input is-small" type="number" name="promo" id="promo-input"
                                   value="{{ old('promo', $user->promo) }}" min="2020" max="2040" {{ $readOnly ? 'disabled' : '' }}>
                        </div>
                        <div class="column is-3">
                            <label class="label is-small" for="f-spe">Spécialité</label>
                            <div class="select is-fullwidth is-small">
                                <select name="spe" id="f-spe" {{ $readOnly ? 'disabled' : '' }}>
                                    <option value="">— Non définie —</option>
                                    <option value="SLAM" {{ old('spe', $user->spe) === 'SLAM' ? 'selected' : '' }}>SLAM</option>
                                    <option value="SISR" {{ old('spe', $user->spe) === 'SISR' ? 'selected' : '' }}>SISR</option>
                                </select>
                            </div>
                        </div>
                        <div class="column is-3">
                            <label class="label is-small" for="f-tuteur">Tuteur référent</label>
                            <div class="select is-fullwidth is-small">
                                <select name="tuteur_id" id="f-tuteur" {{ $readOnly ? 'disabled' : '' }}>
                                    <option value="">— Aucun —</option>
                                    @foreach($tuteurs as $tuteur)
                                        <option value="{{ $tuteur->id }}"
                                            {{ old('tuteur_id', $user->tuteur_id) == $tuteur->id ? 'selected' : '' }}>
                                            {{ $tuteur->prenom }} {{ $tuteur->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @unless($readOnly)
                    <div class="buttons is-right mb-0">
                        <button type="submit" class="button is-primary is-small">
                            <i class="fas fa-save mr-1"></i> Enregistrer
                        </button>
                    </div>
                    @endunless
                </div>
            </form>

    <div class="columns">

        {{-- Colonne gauche : Statut --}}
        <div class="column is-two-fifths">

            {{-- Statut administratif --}}
            @role('Professeur|Administrateur')
            <div class="box">
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
                </div>
                @endforeach
                @endif

            </div>
        </div>

    </div>

</div>

<script nonce="{{ $cspNonce ?? '' }}">
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

@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- En-tête --}}
    <div class="level mb-3">
        <div class="level-left">
            <div>
                <p class="title is-5 mb-0">
                    <span class="icon has-text-danger mr-1"><i class="fas fa-cogs"></i></span>
                    Administration · {{ auth()->user()->prenom }} {{ auth()->user()->nom }}
                </p>
                <p class="is-size-7 has-text-grey">Année scolaire {{ $annee }}</p>
            </div>
        </div>
    </div>

    @include('partials.flash')
    @include('partials.etudiants_sans_classe')


    @if($aValider > 0)
    <a href="{{ route('admin.stages.index', ['filtre' => 'a_faire_signer']) }}" class="notification is-warning is-light py-3 mb-4" style="display:block; text-decoration:none;">
        <i class="fas fa-pen mr-2"></i>
        <strong>{{ $aValider }} convention(s) à faire signer</strong> — en attente de signature employeur
    </a>
    @endif

    {{-- ── Cards SIO1 / SIO2 (même vue que dashboard prof) ──────────── --}}
    <div class="columns">
        @foreach($cartesSio as $classe => $c)
        @php $color = $classe === 'SIO1' ? 'is-info' : 'is-primary'; @endphp
        <div class="column">
            <div class="box" style="border-top: 4px solid {{ $classe === 'SIO1' ? '#3273dc' : '#00d1b2' }};">

                <div class="level mb-2">
                    <div class="level-left">
                        <span class="tag {{ $color }} is-medium mr-2">{{ $classe }}</span>
                        <strong>{{ $c['total'] }} étudiant(s) actifs</strong>
                    </div>
                    @if($c['config']?->profPrincipal)
                        <div class="level-right is-size-7 has-text-grey">
                            Prof : {{ $c['config']->profPrincipal->prenom }} {{ $c['config']->profPrincipal->nom }}
                        </div>
                    @endif
                </div>

                @if($c['config']?->stage_date_debut)
                    <p class="is-size-7 mb-3">
                        <i class="fas fa-calendar-alt mr-1 has-text-{{ $classe === 'SIO1' ? 'info' : 'primary' }}"></i>
                        Stage du <strong>{{ $c['config']->stage_date_debut->format('d/m/Y') }}</strong>
                        au <strong>{{ $c['config']->stage_date_fin->format('d/m/Y') }}</strong>
                        — {{ $c['config']->duree_en_semaines }} semaine(s)
                    </p>
                @else
                    <p class="is-size-7 has-text-grey mb-3">
                        <i class="fas fa-calendar-times mr-1"></i> Dates non définies
                        <a href="{{ route('admin.parametres.index') }}" class="ml-1">→ Paramètres</a>
                    </p>
                @endif

                <hr style="margin:8px 0;">

                <p class="heading mb-1">Spécialités</p>
                <div class="tags mb-3">
                    @if($c['slam'] > 0)
                        <a href="{{ route('admin.users.index', ['classe' => $classe, 'spe' => 'SLAM']) }}"
                           class="tag is-info is-light">{{ $c['slam'] }} SLAM</a>
                    @endif
                    @if($c['sisr'] > 0)
                        <a href="{{ route('admin.users.index', ['classe' => $classe, 'spe' => 'SISR']) }}"
                           class="tag is-link is-light">{{ $c['sisr'] }} SISR</a>
                    @endif
                    @php $sansSpe = $c['total'] - $c['slam'] - $c['sisr']; @endphp
                    @if($sansSpe > 0)
                        <a href="{{ route('admin.users.index', ['classe' => $classe, 'spe' => 'aucune']) }}"
                           class="tag is-light has-text-grey">{{ $sansSpe }} non définie</a>
                    @endif
                </div>

                <p class="heading mb-1">Statuts</p>
                <div class="tags mb-3">
                    @if($c['actifs'] > 0)
                        <a href="{{ route('admin.users.index', ['classe' => $classe]) }}"
                           class="tag is-success is-light">{{ $c['actifs'] }} actif(s)</a>
                    @endif
                    @if($c['demissionnaires'] > 0)
                        <a href="{{ route('admin.users.index', ['classe' => $classe, 'filtre' => 'anciens']) }}"
                           class="tag is-danger is-light">{{ $c['demissionnaires'] }} démissionnaire(s)</a>
                    @endif
                </div>

                <p class="heading mb-1">Conventions</p>
                <div class="tags mb-3">
                    @if($c['sans_stage'] > 0)
                        <a href="{{ route('admin.stages.index', ['classe' => strtolower($classe), 'filtre' => 'sans_stage']) }}"
                           class="tag is-danger">{{ $c['sans_stage'] }} sans stage</a>
                    @endif
                    @if($c['papier_pending'] > 0)
                        <a href="{{ route('admin.stages.index', ['classe' => strtolower($classe), 'filtre' => 'sans_stage']) }}"
                           class="tag is-warning">{{ $c['papier_pending'] }} hors app — à saisir</a>
                    @endif
                    @if($c['a_faire_signer'] > 0)
                        <a href="{{ route('admin.stages.index', ['classe' => strtolower($classe), 'filtre' => 'a_faire_signer']) }}"
                           class="tag is-warning is-light">{{ $c['a_faire_signer'] }} à faire signer</a>
                    @endif
                    @if($c['en_attente'] > 0)
                        <a href="{{ route('admin.stages.index', ['classe' => strtolower($classe), 'filtre' => 'en_attente']) }}"
                           class="tag is-info is-light">{{ $c['en_attente'] }} en attente signature</a>
                    @endif
                    @if($c['remis'] > 0)
                        <a href="{{ route('admin.stages.index', ['classe' => strtolower($classe), 'filtre' => 'validee']) }}"
                           class="tag is-success is-light">{{ $c['remis'] }} remis ✓</a>
                    @endif
                </div>

                <div class="buttons are-small">
                    <a href="{{ route('admin.users.index', ['classe' => $classe]) }}"
                       class="button is-small {{ $color }} is-light">
                        <i class="fas fa-users mr-1"></i> Étudiants {{ $classe }}
                    </a>
                    <a href="{{ route('admin.stages.index', ['classe' => strtolower($classe)]) }}"
                       class="button is-small is-light">
                        <i class="fas fa-briefcase mr-1"></i> Stages {{ $classe }}
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Actions admin, regroupées par thème ─────────────────────── --}}
    <div class="columns is-multiline mb-4">
        <div class="column is-6-tablet is-3-desktop">
            <div class="box p-4" style="height:100%; border-top:3px solid #f97316;">
                <p class="heading mb-2" style="color:#f97316;"><i class="fas fa-calendar-alt mr-1"></i> Année scolaire</p>
                <a href="{{ route('admin.parametres.index') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#f97316;"><i class="fas fa-calendar-alt fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Dates de stage</span>
                        <span class="is-size-7 has-text-grey">Configurer l'année scolaire</span>
                    </span>
                </a>
                <a href="{{ route('imports.pronote.form') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#f97316;"><i class="fas fa-file-import fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Import Pronote</span>
                        <span class="is-size-7 has-text-grey">Import et mise à jour des effectifs</span>
                    </span>
                </a>
            </div>
        </div>
        <div class="column is-6-tablet is-3-desktop">
            <div class="box p-4" style="height:100%; border-top:3px solid #3273dc;">
                <p class="heading mb-2" style="color:#3273dc;"><i class="fas fa-users mr-1"></i> Comptes</p>
                <a href="{{ route('admin.users.create') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#3273dc;"><i class="fas fa-user-plus fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Créer un compte</span>
                        <span class="is-size-7 has-text-grey">Étudiant, prof ou admin</span>
                    </span>
                </a>
                <a href="{{ route('admin.reset-password') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#3273dc;"><i class="fas fa-key fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Réinitialiser un mot de passe</span>
                        <span class="is-size-7 has-text-grey">Générer un mot de passe temporaire</span>
                    </span>
                </a>
                <a href="{{ route('admin.professeurs.index') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#3273dc;"><i class="fas fa-chalkboard-teacher fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Gérer les professeurs</span>
                        <span class="is-size-7 has-text-grey">Droits d'accès et admin</span>
                    </span>
                </a>
            </div>
        </div>
        <div class="column is-6-tablet is-3-desktop">
            <div class="box p-4" style="height:100%; border-top:3px solid #00a38a;">
                <p class="heading mb-2" style="color:#00a38a;"><i class="fas fa-briefcase mr-1"></i> Stages et entreprises</p>
                <a href="{{ route('admin.stages.creer.form') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#00a38a;"><i class="fas fa-briefcase fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Créer un stage</span>
                        <span class="is-size-7 has-text-grey">Passe-droit pour un étudiant bloqué</span>
                    </span>
                </a>
                <a href="{{ route('entreprises.create') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#00a38a;"><i class="fas fa-building fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Ajouter une entreprise</span>
                        <span class="is-size-7 has-text-grey">Saisie manuelle</span>
                    </span>
                </a>
                <a href="{{ route('entreprises.import.form') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#00a38a;"><i class="fas fa-search fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Importer une entreprise</span>
                        <span class="is-size-7 has-text-grey">via son n° de siret</span>
                    </span>
                </a>
            </div>
        </div>
        <div class="column is-6-tablet is-3-desktop">
            <div class="box p-4" style="height:100%; border-top:3px solid #7209b7;">
                <p class="heading mb-2" style="color:#7209b7;"><i class="fas fa-bullhorn mr-1"></i> Communication et exports</p>
                <a href="{{ route('admin.communication.index') }}" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#7209b7;"><i class="fas fa-bullhorn fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Communication</span>
                        <span class="is-size-7 has-text-grey">Envoi, templates, RGPD</span>
                    </span>
                </a>
                <div id="btn-export-csv" style="cursor:pointer;" class="is-flex is-align-items-center py-2 admin-action" style="text-decoration:none; color:inherit;">
                    <span class="icon mr-3" style="color:#7209b7;"><i class="fas fa-file-csv fa-lg"></i></span>
                    <span>
                        <span class="has-text-weight-semibold" style="display:block;">Export maîtres de stage</span>
                        <span class="is-size-7 has-text-grey">Télécharger la liste CSV</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Modale export CSV ──────────────────────────────────────────── --}}
    @php $anneesDisponibles = \App\Models\Stage::anneesDisponibles(); @endphp
    <div id="modal-export-csv" class="modal">
        <div class="modal-background"></div>
        <div class="modal-card" style="max-width:380px;">
            <header class="modal-card-head">
                <p class="modal-card-title"><i class="fas fa-file-csv mr-2"></i>Export maîtres de stage</p>
                <button class="delete js-modal-close" aria-label="close"></button>
            </header>
            <form method="GET" action="{{ route('admin.stages.export-maitres') }}">
            <section class="modal-card-body">
                <div class="field">
                    <label class="label">Année scolaire</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="annee">
                                @foreach($anneesDisponibles as $a)
                                    <option value="{{ $a }}" {{ $a === $annee ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Section</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="classe">
                                <option value="">Toutes les sections</option>
                                <option value="SIO1">SIO1</option>
                                <option value="SIO2">SIO2</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot" style="justify-content:space-between;">
                <button type="submit" class="button is-success">
                    <i class="fas fa-download mr-1"></i> Télécharger CSV
                </button>
                <button type="button" class="button js-modal-close">Annuler</button>
            </footer>
            </form>
        </div>
    </div>
    <script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        var modal  = document.getElementById('modal-export-csv');
        var opener = document.getElementById('btn-export-csv');
        opener.addEventListener('click', function () { modal.classList.add('is-active'); });
        modal.querySelector('.modal-background').addEventListener('click', function () { modal.classList.remove('is-active'); });
        modal.querySelectorAll('.js-modal-close').forEach(function (el) {
            el.addEventListener('click', function () { modal.classList.remove('is-active'); });
        });
    })();
    </script>


</div>
@endsection

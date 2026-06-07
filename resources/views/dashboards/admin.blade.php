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
        <div class="level-right" style="gap:8px; display:flex;">
            <a href="{{ route('admin.stages.index') }}" class="button is-link is-small">
                <i class="fas fa-briefcase mr-1"></i> Tous les stages
            </a>
            <a href="{{ route('admin.users.index') }}" class="button is-info is-small">
                <i class="fas fa-users mr-1"></i> Étudiants
            </a>
            <a href="{{ route('admin.parametres.index') }}" class="button is-light is-small">
                <i class="fas fa-calendar-alt mr-1"></i> Paramètres
            </a>
            <a href="{{ route('imports.pronote.form') }}" class="button is-light is-small">
                <i class="fas fa-file-import mr-1"></i> Import Pronote
            </a>
        </div>
    </div>

    {{-- ── Actions admin ───────────────────────────────────────────── --}}
    <div class="columns is-multiline mb-4">

        <div class="column is-3">
            <a href="{{ route('admin.parametres.index') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#f97316'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large mb-2" style="color:#f97316;"><i class="fas fa-calendar-alt fa-2x"></i></span>
                <p class="has-text-weight-semibold">Dates de stage</p>
                <p class="is-size-7 has-text-grey">Configurer l'année scolaire</p>
            </a>
        </div>

        <div class="column is-3">
            <a href="{{ route('admin.users.create') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#3273dc'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large has-text-info mb-2"><i class="fas fa-user-plus fa-2x"></i></span>
                <p class="has-text-weight-semibold">Créer un compte</p>
                <p class="is-size-7 has-text-grey">Étudiant, prof ou admin</p>
            </a>
        </div>


        <div class="column is-3">
            <a href="{{ route('admin.reset-password') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#f5a623'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large has-text-warning mb-2"><i class="fas fa-key fa-2x"></i></span>
                <p class="has-text-weight-semibold">Réinitialiser un mot de passe</p>
                <p class="is-size-7 has-text-grey">Générer un mot de passe temporaire</p>
            </a>
        </div>

        <div class="column is-3">
            <a href="{{ route('admin.professeurs.index') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#485fc7'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large has-text-dark mb-2"><i class="fas fa-chalkboard-teacher fa-2x"></i></span>
                <p class="has-text-weight-semibold">Gérer les professeurs</p>
                <p class="is-size-7 has-text-grey">Droits d'accès et admin</p>
            </a>
        </div>

        <div class="column is-3">
            <a href="{{ route('entreprises.create') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#00d1b2'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large has-text-primary mb-2"><i class="fas fa-building fa-2x"></i></span>
                <p class="has-text-weight-semibold">Ajouter une entreprise</p>
                <p class="is-size-7 has-text-grey">Saisie manuelle</p>
            </a>
        </div>

        <div class="column is-3">
            <a href="{{ route('entreprises.import.form') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#209cee'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large has-text-link mb-2"><i class="fas fa-search fa-2x"></i></span>
                <p class="has-text-weight-semibold">Importer une entreprise</p>
                <p class="is-size-7 has-text-grey">via son n° de siret</p>
            </a>
        </div>

        <div class="column is-3">
            <a href="{{ route('imports.pronote.form') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#7209b7'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large mb-2" style="color:#7209b7;"><i class="fas fa-file-import fa-2x"></i></span>
                <p class="has-text-weight-semibold">Import Pronote</p>
                <p class="is-size-7 has-text-grey">Import et mise à jour des effectifs</p>
            </a>
        </div>





        <div class="column is-3">
            <a href="{{ route('admin.communication.index') }}" class="box has-text-centered p-4" style="display:block; border:2px dashed #dbdbdb; transition:.15s; text-decoration:none;"
               onmouseover="this.style.borderColor='#3273dc'" onmouseout="this.style.borderColor='#dbdbdb'">
                <span class="icon is-large has-text-info mb-2"><i class="fas fa-bullhorn fa-2x"></i></span>
                <p class="has-text-weight-semibold">Communication</p>
                <p class="is-size-7 has-text-grey">Envoi, templates, RGPD</p>
            </a>
        </div>

        @if($aValider > 0)
        <div class="column is-3">
            <a href="{{ route('admin.stages.index', ['filtre' => 'a_faire_signer']) }}" class="box has-text-centered p-4" style="display:block; border:2px solid #f5a623; transition:.15s; text-decoration:none;">
                <span class="icon is-large has-text-warning mb-2"><i class="fas fa-pen fa-2x"></i></span>
                <p class="has-text-weight-semibold has-text-warning-dark">{{ $aValider }} convention(s) à faire signer</p>
                <p class="is-size-7 has-text-grey">En attente de signature employeur</p>
            </a>
        </div>
        @endif

    </div>

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

</div>
@endsection

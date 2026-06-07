@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- En-tête --}}
    <div class="level mb-3">
        <div class="level-left">
            <div>
                <p class="title is-5 mb-0">Tableau de bord · {{ auth()->user()->prenom }} {{ auth()->user()->nom }}</p>
                <p class="is-size-7 has-text-grey">Année scolaire {{ $annee }}</p>
            </div>
        </div>
        <div class="level-right">
            <a href="{{ route('admin.stages.index') }}" class="button is-link is-small">
                <i class="fas fa-list mr-1"></i> Tous les stages
            </a>
        </div>
    </div>

    {{-- Alertes --}}
    @php
        $alertes = collect([
            $aValider > 0
                ? ['danger', 'fa-clock', "$aValider stage(s) en attente de votre validation",
                   route('admin.stages.index'), 'Valider →']
                : null,
        ])->filter();

        foreach ($cartesSio as $classe => $c) {
            if ($c['sans_stage'] > 0) {
                $alertes->push(['danger', 'fa-user-times',
                    "{$c['sans_stage']} {$classe} sans stage ni convention",
                    route('admin.stages.index', ['classe' => strtolower($classe)]), 'Voir →']);
            }
            if ($c['papier_pending'] > 0) {
                $alertes->push(['warning', 'fa-file-signature',
                    "{$c['papier_pending']} {$classe} — convention hors app reçue, stage à saisir",
                    route('admin.stages.index', ['classe' => strtolower($classe)]), 'Voir →']);
            }
        }
    @endphp

    @if($alertes->isNotEmpty())
        <div class="mb-4">
            @foreach($alertes as $a)
                <div class="notification is-{{ $a[0] }} is-light py-2 px-4 mb-2"
                     style="display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fas {{ $a[1] }} mr-2"></i>{{ $a[2] }}</span>
                    <a href="{{ $a[3] }}" class="button is-{{ $a[0] }} is-small ml-3">{{ $a[4] }}</a>
                </div>
            @endforeach
        </div>
    @else
        <div class="notification is-success is-light py-2 mb-4">
            <i class="fas fa-check-circle mr-2"></i> <strong>Tout est à jour.</strong>
        </div>
    @endif

    {{-- ── 2 cards SIO1 / SIO2 ─────────────────────────────────────── --}}
    <div class="columns">
        @foreach($cartesSio as $classe => $c)
        @php $color = $classe === 'SIO1' ? 'is-info' : 'is-primary'; @endphp
        <div class="column">
            <div class="box" style="border-top: 4px solid {{ $classe === 'SIO1' ? '#3273dc' : '#00d1b2' }};">

                {{-- En-tête de la card --}}
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

                {{-- Dates du stage --}}
                @if($c['config']?->stage_date_debut)
                    <p class="is-size-7 mb-3">
                        <i class="fas fa-calendar-alt mr-1 has-text-{{ $classe === 'SIO1' ? 'info' : 'primary' }}"></i>
                        Stage du <strong>{{ $c['config']->stage_date_debut->format('d/m/Y') }}</strong>
                        au <strong>{{ $c['config']->stage_date_fin->format('d/m/Y') }}</strong>
                        — {{ $c['config']->duree_en_semaines }} semaine(s)
                    </p>
                @else
                    <p class="is-size-7 has-text-grey mb-3">
                        <i class="fas fa-calendar-times mr-1"></i>
                        Dates de stage non définies
                        <a href="{{ route('admin.parametres.index') }}" class="ml-1">→ Paramètres</a>
                    </p>
                @endif

                <hr style="margin:8px 0;">

                {{-- Spécialités --}}
                <p class="heading mb-1">Spécialités</p>
                <div class="tags mb-3">
                    @if($c['slam'] > 0)
                        <span class="tag is-info is-light">{{ $c['slam'] }} SLAM</span>
                    @endif
                    @if($c['sisr'] > 0)
                        <span class="tag is-link is-light">{{ $c['sisr'] }} SISR</span>
                    @endif
                    @php $sansSpe = $c['total'] - $c['slam'] - $c['sisr']; @endphp
                    @if($sansSpe > 0)
                        <span class="tag is-light has-text-grey">{{ $sansSpe }} non définie</span>
                    @endif
                </div>

                {{-- Statuts administratifs --}}
                <p class="heading mb-1">Statuts</p>
                <div class="tags mb-3">
                    @if($c['actifs'] > 0)
                        <span class="tag is-success is-light">{{ $c['actifs'] }} actif(s)</span>
                    @endif
                    @if($c['demissionnaires'] > 0)
                        <span class="tag is-danger is-light">{{ $c['demissionnaires'] }} démissionnaire(s)</span>
                    @endif
                </div>

                {{-- Conventions --}}
                <p class="heading mb-1">Conventions</p>
                <div class="tags">
                    @if($c['sans_stage'] > 0)
                        <a href="{{ route('admin.stages.index', ['classe' => strtolower($classe), 'filtre' => 'sans_stage']) }}"
                           class="tag is-danger">{{ $c['sans_stage'] }} sans stage</a>
                    @endif
                    @if($c['papier_pending'] > 0)
                        <span class="tag is-warning">{{ $c['papier_pending'] }} hors app — à saisir</span>
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
                    @if($c['sans_stage'] === 0 && $c['papier_pending'] === 0 && $c['a_faire_signer'] === 0 && $c['en_attente'] === 0 && $c['remis'] > 0)
                        <span class="tag is-success">✓ Toutes les conventions sont remises</span>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection

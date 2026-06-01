@extends('layouts.app')

@push('styles')
<style nonce="{{ $cspNonce ?? '' }}">
.stages-table td, .stages-table th { vertical-align: middle; }
</style>
@endpush

@section('content')
<div class="container mt-5">

    <h1 class="title mb-4">
        Stages
        @if($classe !== 'tous')
            <span class="tag {{ $classe === 'sio1' ? 'is-info' : 'is-primary' }} is-large ml-2" style="vertical-align:middle;">{{ strtoupper($classe) }}</span><span class="tag is-large" style="vertical-align:middle; background:#e0e0e0; color:#555;">{{ $classe === 'sio1' ? 'Première année' : 'Deuxième année' }}</span>
        @endif
    </h1>

    @if(session('success'))
        <div class="notification is-success is-light py-2">{{ session('success') }}</div>
    @endif

    {{-- ── Filtres convention (gauche) + Année (droite) ─────────────── --}}
    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;" class="mb-3">

        {{-- Convention : gauche --}}
        <div class="buttons are-small mb-0" style="gap:3px;">
            @foreach([
                'tous'           => ['Tous',       ''],
                'sans_stage'     => ['Sans stage', 'is-light'],
                'a_faire_signer' => ['À signer',   'is-warning is-light'],
                'en_attente'     => ['En attente', 'is-info is-light'],
                'validee'        => ['Validée ✓',  'is-success is-light'],
            ] as $val => [$label, $color])
                <a href="{{ route('admin.stages.index', ['annee' => $anneeSelectionnee, 'classe' => $classe, 'filtre' => $val]) }}"
                   class="button is-small {{ $filtre === $val ? 'is-link' : $color }}">{{ $label }}</a>
            @endforeach
        </div>

        {{-- Année : droite (dropdown, défaut = année active) --}}
        <form method="GET">
            <input type="hidden" name="classe" value="{{ $classe }}">
            <input type="hidden" name="filtre" value="{{ $filtre }}">
            <div class="select is-small">
                <select name="annee" onchange="this.form.submit()">
                    @foreach($annees as $annee)
                        <option value="{{ $annee }}" {{ $annee === $anneeSelectionnee ? 'selected' : '' }}>
                            {{ $annee }}{{ $annee === $anneeActive ? ' ●' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

    </div>

    @if($etudiants->isEmpty())
        <div class="notification is-light">Aucun étudiant actif pour ce filtre.</div>
    @else

    <p class="is-size-7 has-text-grey mb-2">{{ $etudiants->count() }} étudiant(s) — {{ $classeStr }}</p>

    <table class="table is-striped is-fullwidth is-hoverable is-size-7 stages-table">
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Classe</th>
                <th>Entreprise</th>
                <th>Maître de stage</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Statut</th>
                <th>Action</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($etudiants as $etudiant)
            @php
                $stage = $etudiant->stages->first();
                $anneeEtude = $etudiant->promo ? (3 - ($etudiant->promo - $syInt)) : null;
                $classeTag  = ($anneeEtude >= 1 && $anneeEtude <= 2) ? 'SIO'.$anneeEtude : null;
            @endphp
            @if($stage)
            {{-- Étudiant AVEC stage --}}
            @php
                // Badge statut courant de la convention
                $statutConvBadge = match($stage->statut_convention) {
                    'a_faire_signer' => ['is-warning is-light', "À faire signer par l'employeur"],
                    'en_attente'     => ['is-info is-light',    'En attente'],
                    'validee'        => ['is-success is-light', 'Validée ✓'],
                    default          => ['is-light',            '—'],
                };

                // Libellé du bouton "revenir en arrière"
                $revertLabel = match($stage->statut_convention) {
                    'en_attente' => '↩ Annuler le dépôt',
                    'validee'      => '↩ Remettre en attente',
                    default                => null,
                };

                $rowBg = match($stage->statut_convention) {
                    'a_faire_signer'       => '#fff8e1',  // jaune pâle
                    'en_attente' => '#e3f2fd',  // bleu pâle
                    'validee'      => '#e8f5e9',  // vert pâle
                    default                => '',
                };
                $convSeq = [
                    'a_faire_signer'       => ['next' => 'en_attente', 'label' => 'Déposée direction',           'class' => 'is-warning'],
                    'en_attente' => ['next' => 'validee',      'label' => 'Rendue à l\'étudiant',            'class' => 'is-primary'],
                    'validee'      => ['next' => null,                   'label' => 'Validée ✓',            'class' => 'is-success'],
                ];
                $conv = $convSeq[$stage->statut_convention] ?? $convSeq['a_faire_signer'];
                $valBadge = match($stage->statut_validation) {
                    'valide'  => ['is-success is-light', 'Validé'],
                    'rejete'  => ['is-danger is-light',  'Rejeté'],
                    default   => ['is-warning is-light', 'En attente'],
                };
            @endphp
            <tr style="{{ $rowBg ? 'background-color:'.$rowBg.';' : '' }}">
                <td><strong>{{ $etudiant->nom }}</strong> {{ $etudiant->prenom }}</td>
                <td>
                    @if($classeTag)
                        <span class="tag {{ $classeTag === 'SIO1' ? 'is-info' : 'is-primary' }}">{{ $classeTag }}</span>
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>
                <td title="{{ $stage->entreprise?->raison_sociale }}">
                    {{ Str::limit($stage->entreprise?->raison_sociale ?? '—', 30) }}
                </td>
                <td>
                    @if($stage->maitreDeStage)
                        {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>
                <td>{{ $stage->date_debut?->format('d/m/Y') }}</td>
                <td>{{ $stage->date_fin?->format('d/m/Y') }}</td>
                <td>
                    <span class="tag {{ $statutConvBadge[0] }}">{{ $statutConvBadge[1] }}</span>
                </td>
                <td>
                    <div style="display:flex; gap:4px; align-items:center;">
                        {{-- Bouton action principale (avancer) --}}
                        @if($conv['next'])
                            <form action="{{ route('admin.stages.convention', [$stage, $conv['next']]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Confirmer : {{ addslashes($conv['label']) }} ?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="button is-small {{ $conv['class'] }}" style="min-width:145px;">
                                    {{ $conv['label'] }}
                                </button>
                            </form>
                        @else
                            <span class="button is-small {{ $conv['class'] }} is-static" style="min-width:145px;">
                                {{ $conv['label'] }}
                            </span>
                        @endif

                        {{-- Bouton revenir en arrière --}}
                        @if($revertLabel)
                            <form action="{{ route('admin.stages.revert', $stage) }}"
                                  method="POST"
                                  onsubmit="return confirm('{{ addslashes($revertLabel) }} — Confirmer ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="button is-small is-light has-text-grey"
                                        title="{{ $revertLabel }}">
                                    ↩
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
                <td>
                    @php
                        $hasJournal      = $stage->journal_entries_count > 0;
                        $convValidee     = $stage->statut_convention === 'validee';
                        [$jColor, $jTitle] = match(true) {
                            $hasJournal && !$convValidee => [
                                '#f97316',
                                "⚠ {$stage->journal_entries_count} réalisation(s) saisie(s) — convention non encore validée informatiquement",
                            ],
                            $hasJournal => [
                                '#3273dc',
                                "{$stage->journal_entries_count} réalisation(s) saisie(s)",
                            ],
                            default => [
                                '#dbdbdb',
                                'Journal vide',
                            ],
                        };
                    @endphp
                    <a href="{{ route('stages.journal.index', $stage) }}"
                       class="button is-small"
                       style="background:transparent; border:none; box-shadow:none;"
                       title="{{ $jTitle }}">
                        <i class="fas fa-book-open" style="color:{{ $jColor }}"></i>
                    </a>
                    <a href="{{ route('stages.show', $stage) }}" class="button is-small is-light" title="Voir le détail">
                        <i class="fas fa-eye"></i>
                    </a>
                    @role('Administrateur')
                    <a href="{{ route('stages.edit', $stage) }}" class="button is-small is-light" title="Modifier">
                        <i class="fas fa-pen"></i>
                    </a>
                    @endrole
                </td>
            </tr>

            @else
            {{-- Étudiant SANS stage --}}
            @php
                $conv = $etudiant->conventionPapier;
                $rowBgPapier = $conv ? match($conv->statut) {
                    'a_faire_signer'       => '#fff8e1',
                    'en_attente' => '#e3f2fd',
                    'validee'      => '#e8f5e9',
                    default                => '',
                } : '';
                $convPapierSeq = [
                    'a_faire_signer'       => ['label' => 'Déposée direction',          'class' => 'is-warning'],
                    'en_attente' => ['label' => 'Signée et remise à l\'étudiant', 'class' => 'is-primary'],
                    'validee'      => ['label' => 'Validée ✓',                       'class' => 'is-success'],
                ];
            @endphp
            <tr style="{{ $rowBgPapier ? 'background-color:'.$rowBgPapier.';' : (!$conv ? 'background-color:#fff0f0;' : '') }}">
                <td><strong>{{ $etudiant->nom }}</strong> {{ $etudiant->prenom }}</td>
                <td>
                    @if($classeTag)
                        <span class="tag {{ $classeTag === 'SIO1' ? 'is-info' : 'is-primary' }}">{{ $classeTag }}</span>
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>

                @if($conv)
                {{-- Convention papier en cours --}}
                <td colspan="4" class="is-size-7 has-text-grey is-italic">
                    Convention papier
                </td>
                <td>
                    {{-- Statut courant convention papier --}}
                    @php
                        $badgePapier = match($conv->statut) {
                            'a_faire_signer' => ['is-warning is-light', "À faire signer par l'employeur"],
                            'en_attente'     => ['is-info is-light',    'En attente'],
                            'validee'        => ['is-success is-light', 'Validée ✓'],
                            default          => ['is-light',            '—'],
                        };
                    @endphp
                    <span class="tag {{ $badgePapier[0] }} is-small">{{ $badgePapier[1] }}</span>
                </td>
                <td>
                    <div style="display:flex; flex-wrap:wrap; gap:4px; align-items:center;">
                        @php $seq = $convPapierSeq[$conv->statut] ?? null; @endphp
                        @if($seq && $conv->statutSuivant())
                            <form action="{{ route('admin.conventions-papier.avancer', $conv) }}"
                                  method="POST"
                                  onsubmit="return confirm('Confirmer : {{ addslashes($seq['label']) }} ?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="button is-small {{ $seq['class'] }}" style="min-width:155px;">
                                    {{ $seq['label'] }}
                                </button>
                            </form>
                        @elseif($seq)
                            <span class="button is-small {{ $seq['class'] }} is-static" style="min-width:155px;">
                                {{ $seq['label'] }}
                            </span>
                        @endif
                        {{-- Revenir --}}
                        <form action="{{ route('admin.conventions-papier.revert', $conv) }}"
                              method="POST"
                              onsubmit="return confirm('{{ $conv->statut === 'a_faire_signer' ? 'Supprimer cette convention papier ?' : '↩ Revenir à l\'étape précédente ?' }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="button is-small is-light has-text-grey"
                                    title="{{ $conv->statut === 'a_faire_signer' ? 'Supprimer' : 'Revenir' }}">
                                {{ $conv->statut === 'a_faire_signer' ? '✕' : '↩' }}
                            </button>
                        </form>
                    </div>
                </td>
                @else
                {{-- Aucune convention --}}
                <td colspan="4" class="has-text-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Aucun stage saisi
                </td>
                <td></td>
                <td>
                    <form action="{{ route('admin.stages.hors-appli', $etudiant) }}"
                          method="POST"
                          onsubmit="return confirm('Confirmer que {{ $etudiant->prenom }} {{ $etudiant->nom }} a remis une convention papier ?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="button is-small is-warning is-light" style="min-width:155px;">
                            ⚠ Convention papier remise
                        </button>
                    </form>
                </td>
                @endif
                <td></td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    @endif

</div>
@endsection

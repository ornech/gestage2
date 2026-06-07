@extends('layouts.app')

@push('styles')
<style nonce="{{ $cspNonce ?? '' }}">
.stages-table td, .stages-table th { vertical-align: middle; }
.stages-table a.lien-cellule {
    color: #363636;
    text-decoration: none;
    border-bottom: 1px dashed #aaa;
}
.stages-table a.lien-cellule:hover {
    color: #3273dc;
    border-bottom-color: #3273dc;
    border-bottom-style: solid;
}

</style>
@endpush

@section('content')
<div class="container mt-5">

    {{-- ── En-tête ── --}}
    <div style="display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; margin-bottom:1rem;">
        <h1 class="title is-4 mb-0">Stages</h1>

        @if($classe !== 'tous')
        <div style="display:flex; align-items:center;">
            <span class="tag is-medium {{ $classe === 'sio1' ? 'is-info' : 'is-primary' }}"
                  style="border-radius:4px 0 0 4px; margin:0;">{{ strtoupper($classe) }}</span>
            <span class="tag is-medium" style="border-radius:0 4px 4px 0; margin:0; background:#e0e0e0; color:#444; border:1px solid #ccc; border-left:none;">
                {{ $classe === 'sio1' ? 'Première année' : 'Deuxième année' }}
            </span>
        </div>
        @endif
    </div>

    @if(session('success'))
        <div class="notification is-success is-light py-2">{{ session('success') }}</div>
    @endif

    {{-- ── Filtres avec compteurs (gauche) + Année (droite) ───────────── --}}
    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;" class="mb-3">

        {{-- Filtres double tag ── --}}
        <div style="display:flex; gap:5px; flex-wrap:wrap;">
            @foreach([
                'tous'           => ['Tous',             'is-dark',    'is-dark'],
                'sans_stage'     => ['Sans stage',       'is-danger',  'is-danger'],
                'hors_app'       => ['Hors app',         'is-warning', 'is-warning'],
                'a_faire_signer' => ['À faire signer',   'is-warning', 'is-warning'],
                'en_attente'     => ['Déposée direction','is-info',    'is-info'],
                'validee'        => ['Remise étudiant ✓','is-success', 'is-success'],
            ] as $val => [$label, $color, $actif])
            @php $isActive = $filtre === $val; @endphp
            <a href="{{ route('admin.stages.index', ['annee' => $anneeSelectionnee, 'classe' => $classe, 'filtre' => $val]) }}"
               style="text-decoration:none; display:flex; align-items:center; border:1px solid #dbdbdb; border-radius:4px; overflow:hidden;">
                <span class="tag {{ $isActive ? $color : $color.' is-light' }}"
                      style="border-radius:0; margin:0; font-size:.75rem;">{{ $label }}</span>
                <span class="tag is-white"
                      style="border-radius:0; margin:0; border-left:1px solid #dbdbdb; font-size:.75rem; min-width:24px; text-align:center;">
                    {{ $compteurs[$val] ?? 0 }}
                </span>
            </a>
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

    <div class="table-scroll"><table class="table is-striped is-fullwidth is-hoverable is-size-7 stages-table">
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
                <td>
                    <a href="{{ route('admin.users.edit', $etudiant) }}" class="lien-cellule">
                        <strong>{{ $etudiant->nom }}</strong> {{ $etudiant->prenom }}
                    </a>
                </td>
                <td>
                    @if($classeTag)
                        <span class="tag {{ $classeTag === 'SIO1' ? 'is-info' : 'is-primary' }}">{{ $classeTag }}</span>
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>
                <td title="{{ $stage->entreprise?->raison_sociale }}">
                    @if($stage->entreprise)
                        <a href="{{ route('entreprises.show', $stage->entreprise) }}" class="lien-cellule">
                            {{ Str::limit($stage->entreprise->raison_sociale, 30) }}
                        </a>
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>
                <td>
                    @if($stage->maitreDeStage)
                        <a href="{{ route('employes.show', $stage->maitreDeStage) }}" class="lien-cellule">
                            {{ $stage->maitreDeStage->prenom }} {{ $stage->maitreDeStage->nom }}
                        </a>
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
                    'hors_app'   => '#fdf2e9',
                    'en_attente' => '#e3f2fd',
                    'validee'    => '#e8f5e9',
                    default      => '',
                } : '';
                $convPapierSeq = [
                    'hors_app'   => ['label' => 'Déposée direction',              'class' => 'is-warning'],
                    'en_attente' => ['label' => 'Signée et remise à l\'étudiant', 'class' => 'is-primary'],
                    'validee'    => ['label' => 'Validée ✓',                      'class' => 'is-success'],
                ];
            @endphp
            <tr style="{{ $rowBgPapier ? 'background-color:'.$rowBgPapier.';' : (!$conv ? 'background-color:#fdecea;' : '') }}">
                <td>
                    <a href="{{ route('admin.users.edit', $etudiant) }}" class="lien-cellule">
                        <strong>{{ $etudiant->nom }}</strong> {{ $etudiant->prenom }}
                    </a>
                </td>
                <td>
                    @if($classeTag)
                        <span class="tag {{ $classeTag === 'SIO1' ? 'is-info' : 'is-primary' }}">{{ $classeTag }}</span>
                    @else
                        <span class="has-text-grey">—</span>
                    @endif
                </td>

                @if($conv)
                {{-- Convention hors app en cours --}}
                <td colspan="4" class="is-size-7 has-text-grey is-italic">
                    Convention hors app
                </td>
                <td>
                    {{-- Statut courant convention hors app --}}
                    @php
                        $badgePapier = match($conv->statut) {
                            'hors_app'   => ['is-warning is-light', 'Convention hors app — remise'],
                            'en_attente' => ['is-info is-light',    'En attente'],
                            'validee'    => ['is-success is-light', 'Validée ✓'],
                            default      => ['is-light',            '—'],
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
                              onsubmit="return confirm('{{ $conv->statut === 'hors_app' ? 'Supprimer cette convention hors app ?' : '↩ Revenir à l\'étape précédente ?' }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="button is-small is-light has-text-grey"
                                    title="{{ $conv->statut === 'hors_app' ? 'Supprimer' : 'Revenir' }}">
                                {{ $conv->statut === 'hors_app' ? '✕' : '↩' }}
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
                          onsubmit="return confirm('Confirmer que {{ $etudiant->prenom }} {{ $etudiant->nom }} a remis une convention hors app ?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="button is-small is-warning is-light" style="min-width:155px;">
                            ⚠ Convention hors app remise
                        </button>
                    </form>
                </td>
                @endif
                <td></td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table></div>
    @endif

</div>
@endsection

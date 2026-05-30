@extends('layouts.app')

@section('content')
<div class="container mt-6">

    <h1 class="title has-text-centered mb-5">Créer un stage</h1>

    @if($errors->any())
        <div class="notification is-danger">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @php
        $dateDebut  = $config?->stage_date_debut?->format('Y-m-d');
        $duree      = $config?->duree_en_semaines ?? 6;
        $autoSelect = $employes->count() === 1 ? $employes->first()->id : null;
    @endphp

    @if($config && $dateDebut)
        <div class="notification is-info is-light mb-4">
            <i class="fas fa-info-circle mr-2"></i>
            Dates pré-remplies depuis la configuration de la classe <strong>{{ $classe }}</strong>
            ({{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} — {{ $duree }} semaines).
        </div>
    @elseif(!$classe)
        <div class="notification is-warning is-light mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Votre classe n'est pas encore définie. Les dates ne peuvent pas être pré-remplies.
        </div>
    @endif

    <div class="box">
        <form action="{{ route('stages.store') }}" method="POST">
            @csrf

            <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">
            <input type="hidden" name="classe"        value="{{ $classe }}">
            <input type="hidden" name="etudiant_id"   value="{{ auth()->id() }}">

            {{-- Entreprise (lecture seule) --}}
            <div class="field">
                <label class="label">Entreprise</label>
                <input class="input" type="text" value="{{ $entreprise->raison_sociale }}" readonly
                       readonly>
            </div>

            {{-- Classe (lecture seule) --}}
            <div class="field">
                <label class="label">Classe</label>
                <input class="input" type="text" value="{{ $classe ?? '—' }}" readonly
                       readonly>
            </div>

            {{-- Étudiant (lecture seule) --}}
            <div class="field">
                <label class="label">Étudiant</label>
                <input class="input" type="text"
                       value="{{ auth()->user()->prenom }} {{ auth()->user()->nom }}" readonly
                       readonly>
            </div>

            {{-- Maître de stage --}}
            <div class="field">
                <label class="label">Maître de stage</label>
                <div class="select is-fullwidth">
                    <select name="maitre_de_stage_id" required>
                        @if(!$autoSelect)
                            <option value="">Sélectionner</option>
                        @endif
                        @foreach($employes as $employe)
                            <option value="{{ $employe->id }}"
                                {{ $employe->id === $autoSelect ? 'selected' : '' }}>
                                {{ $employe->prenom }} {{ $employe->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($autoSelect)
                    <p class="help has-text-grey">
                        <i class="fas fa-check-circle mr-1 has-text-success"></i>
                        Seul contact disponible — sélectionné automatiquement.
                    </p>
                @endif
            </div>

            {{-- Date de début --}}
            <div class="field">
                <label class="label">
                    Date de début
                    @if($dateDebut)
                        <span class="tag is-info is-light ml-2">pré-remplie</span>
                    @endif
                </label>
                <input class="input" type="date" name="date_debut"
                       value="{{ old('date_debut', $dateDebut) }}" required
                       id="date-debut" oninput="majDateFin()">
            </div>

            {{-- Durée --}}
            <div class="field">
                <label class="label">
                    Durée du stage
                    @if($config?->duree_en_semaines)
                        <span class="tag is-info is-light ml-2">pré-remplie</span>
                    @endif
                </label>
                <div class="select is-fullwidth">
                    <select name="duree" id="duree" required onchange="majDateFin()">
                        @for($i = 1; $i <= 16; $i++)
                            <option value="{{ $i }}" {{ $duree == $i ? 'selected' : '' }}>
                                {{ $i }} semaine{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- Date de fin calculée --}}
            <div class="field">
                <label class="label has-text-grey">
                    Date de fin <span class="tag is-light ml-1">calculée</span>
                </label>
                <input class="input" type="text" id="date-fin" readonly
                       readonly>
            </div>

            <div class="field is-grouped mt-5">
                <div class="control">
                    <button class="button is-link">Ajouter</button>
                </div>
                <div class="control">
                    <a href="{{ route('entreprises.show', $entreprise->id) }}" class="button is-light">Annuler</a>
                </div>
            </div>

        </form>
    </div>

</div>

<script nonce="{{ $cspNonce ?? '' }}">
function majDateFin() {
    const debut  = document.getElementById('date-debut').value;
    const duree  = parseInt(document.getElementById('duree').value);
    const output = document.getElementById('date-fin');

    if (!debut || !duree) { output.value = ''; return; }

    const d = new Date(debut);
    d.setDate(d.getDate() + duree * 7);
    output.value = d.toLocaleDateString('fr-FR');
}

document.addEventListener('DOMContentLoaded', majDateFin);
</script>
@endsection

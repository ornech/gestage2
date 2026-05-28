@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:700px;">

    <h1 class="title mb-5">Modifier le stage</h1>

    @if($errors->any())
        <div class="notification is-danger is-light">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('stages.update', $stage) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Entreprise (lecture seule) --}}
            <div class="field">
                <label class="label">Entreprise</label>
                <input class="input" type="text"
                       value="{{ $stage->entreprise?->raison_sociale ?? '—' }}" readonly>
                <input type="hidden" name="entreprise_id" value="{{ $stage->entreprise_id }}">
            </div>

            {{-- Étudiant (lecture seule) --}}
            <div class="field">
                <label class="label">Étudiant</label>
                <input class="input" type="text"
                       value="{{ $stage->etudiant?->prenom }} {{ $stage->etudiant?->nom }}" readonly>
                <input type="hidden" name="etudiant_id" value="{{ $stage->etudiant_id }}">
            </div>

            {{-- Maître de stage --}}
            <div class="field">
                <label class="label">Maître de stage</label>
                <div class="select is-fullwidth">
                    <select name="maitre_de_stage_id" required>
                        @if($employes->isEmpty())
                            <option value="">Aucun contact pour cette entreprise</option>
                        @else
                            @foreach($employes as $employe)
                                <option value="{{ $employe->id }}"
                                    {{ old('maitre_de_stage_id', $stage->maitre_de_stage_id) == $employe->id ? 'selected' : '' }}>
                                    {{ $employe->prenom }} {{ $employe->nom }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- Date de début --}}
            <div class="field">
                <label class="label">Date de début</label>
                <input class="input" type="date" name="date_debut"
                       value="{{ old('date_debut', $stage->date_debut?->format('Y-m-d')) }}"
                       id="date-debut-edit" oninput="majDateFinEdit()" required>
            </div>

            {{-- Durée --}}
            <div class="field">
                <label class="label">Durée du stage</label>
                <div class="select is-fullwidth">
                    <select name="duree" id="duree-edit" onchange="majDateFinEdit()" required>
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
                <input class="input" type="text" id="date-fin-edit" readonly
                       value="{{ $stage->date_fin?->format('d/m/Y') }}">
            </div>

            <div class="field is-grouped mt-5">
                <div class="control">
                    <button class="button is-success">Mettre à jour</button>
                </div>
                <div class="control">
                    <a href="{{ route('stages.index') }}" class="button is-light">Annuler</a>
                </div>
            </div>

        </form>
    </div>

</div>

<script nonce="{{ $cspNonce ?? '' }}">
function majDateFinEdit() {
    const debut  = document.getElementById('date-debut-edit').value;
    const duree  = parseInt(document.getElementById('duree-edit').value);
    const output = document.getElementById('date-fin-edit');
    if (!debut || !duree) { output.value = ''; return; }
    const d = new Date(debut);
    d.setDate(d.getDate() + duree * 7);
    output.value = d.toLocaleDateString('fr-FR');
}
document.addEventListener('DOMContentLoaded', majDateFinEdit);
</script>
@endsection

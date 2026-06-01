@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <div class="level mb-3">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-0">Annuaire des entreprises</h1>
                <p class="is-size-7 has-text-grey mt-1">
                    Filtrage en temps réel &nbsp;·&nbsp; {{ $nbEntreprises }} entreprises
                </p>
            </div>
        </div>
        <div class="level-right" style="gap:.5rem; display:flex;">
            <div class="tags has-addons mb-0">
                <span class="tag is-dark">Stages</span>
                <span class="tag is-success"><b>{{ $nbStages }}</b></span>
            </div>
            <div class="tags has-addons mb-0">
                <span class="tag is-dark">Contacts</span>
                <span class="tag is-warning"><b>{{ $nbContacts }}</b></span>
            </div>
        </div>
    </div>

    {{-- Filtres en ligne --}}
    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;" class="mb-3">
        <div class="control">
            <input class="input is-small" type="text" id="f-nom"   placeholder="Nom entreprise…">
        </div>
        <div class="control">
            <input class="input is-small" type="text" id="f-ville" placeholder="Ville…">
        </div>
        <div class="control">
            <input class="input is-small" type="text" id="f-cp"    placeholder="Code postal…">
        </div>
        <div class="control">
            <input class="input is-small" type="text" id="f-naf"   placeholder="Code NAF…">
        </div>
        <div class="control">
            <button class="button is-small is-light" id="btn-reset">✕ Réinitialiser</button>
        </div>
        <span id="compteur" class="is-size-7 has-text-grey" style="align-self:center;"></span>
    </div>

    {{-- Tableau --}}
    <table class="table is-striped is-hoverable is-fullwidth is-size-7" id="tbl-entreprises">
        <thead>
            <tr>
                <th>Nom entreprise</th>
                <th>Adresse</th>
                <th>Ville</th>
                <th>CP</th>
                <th>NAF</th>
                <th>Stages</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entreprises as $e)
            <tr
                data-nom="{{ strtolower($e->raison_sociale) }}"
                data-ville="{{ strtolower($e->ville ?? '') }}"
                data-cp="{{ $e->code_postal ?? '' }}"
                data-naf="{{ strtolower($e->code_naf ?? '') }}"
            >
                <td>
                    <a href="{{ route('entreprises.show', $e) }}" class="has-text-link">
                        {{ $e->raison_sociale }}
                    </a>
                </td>
                <td>{{ $e->adresse ?? '—' }}</td>
                <td>{{ $e->ville ?? '—' }}</td>
                <td>{{ $e->code_postal ?? '—' }}</td>
                <td>{{ $e->code_naf ?? '—' }}</td>
                <td>{{ $e->stages_count > 0 ? $e->stages_count : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function () {
    const ids    = ['f-nom', 'f-ville', 'f-cp', 'f-naf'];
    const rows   = Array.from(document.querySelectorAll('#tbl-entreprises tbody tr'));
    const counter = document.getElementById('compteur');

    function filtrer() {
        const nom   = document.getElementById('f-nom').value.toLowerCase().trim();
        const ville = document.getElementById('f-ville').value.toLowerCase().trim();
        const cp    = document.getElementById('f-cp').value.trim();
        const naf   = document.getElementById('f-naf').value.toLowerCase().trim();

        let visible = 0;
        rows.forEach(function(row) {
            const match =
                row.dataset.nom.includes(nom)     &&
                row.dataset.ville.includes(ville)  &&
                row.dataset.cp.startsWith(cp)      &&
                row.dataset.naf.includes(naf);

            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        counter.textContent = visible < rows.length ? visible + ' résultat(s)' : '';
    }

    ids.forEach(function(id) {
        document.getElementById(id).addEventListener('input', filtrer);
    });

    document.getElementById('btn-reset').addEventListener('click', function() {
        ids.forEach(function(id) { document.getElementById(id).value = ''; });
        filtrer();
    });
});
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mt-6">

    <h1 class="title has-text-centered mb-5">Créer un stage</h1>

    @if ($errors->any())
        <div class="notification is-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('stages.store') }}" method="POST">
            @csrf

            <div class="field">
                <label class="label">Titre</label>
                <div class="control">
                    <input class="input" type="text" name="titre" value="{{ old('titre') }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Description</label>
                <div class="control">
                    <textarea class="textarea" name="description">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="field">
                <label class="label">Date de début</label>
                <div class="control">
                    <input class="input" type="date" name="date_debut" value="{{ old('date_debut') }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Date de fin</label>
                <div class="control">
                    <input class="input" type="date" name="date_fin" value="{{ old('date_fin') }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Entreprise</label>
                <div class="control">
                    <div class="select is-fullwidth">
                      <select name="entreprise_id" id="entrepriseSelect" required>

                            @foreach($entreprises as $entreprise)
                                <option value="{{ $entreprise->id }}" {{ old('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                    {{ $entreprise->raison_sociale }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label">Maître de stage</label>
                <div class="control">
                    <div class="select is-fullwidth">
                       <select name="maitre_de_stage_id" id="maitreSelect" required>
                            <option value="">Sélectionnez une entreprise d'abord</option>
                    </select>

                    </div>
                </div>
            </div>

           

            <div class="field is-grouped mt-5">
                <div class="control">
                    <button class="button is-link">Enregistrer</button>
                </div>
                <div class="control">
                    <a href="{{ route('stages.index') }}" class="button is-light">Annuler</a>
                </div>
            </div>

        </form>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateDebutInput = document.querySelector('input[name="date_debut"]');
    const dateFinInput = document.querySelector('input[name="date_fin"]');

    dateDebutInput.addEventListener('change', function () {
        const debut = new Date(this.value);

        if (!isNaN(debut.getTime())) {
            // Calcul de la date maximale = début + 42 jours
            const finMax = new Date(debut);
            finMax.setDate(finMax.getDate() + 42);

            const yyyy = finMax.getFullYear();
            const mm = String(finMax.getMonth() + 1).padStart(2, '0');
            const dd = String(finMax.getDate()).padStart(2, '0');
            const maxDate = `${yyyy}-${mm}-${dd}`;

            // Appliquer la limite max
            dateFinInput.setAttribute('max', maxDate);

            // Si la date fin dépasse la limite → correction automatique
            if (dateFinInput.value > maxDate) {
                dateFinInput.value = maxDate;
            }
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const entrepriseSelect = document.getElementById('entrepriseSelect');
    const maitreSelect = document.getElementById('maitreSelect');

    entrepriseSelect.addEventListener('change', function () {
        const entrepriseId = this.value;

        // Message temporaire
        maitreSelect.innerHTML = '<option>Chargement...</option>';

        fetch(`/entreprises/${entrepriseId}/employes`)
            .then(response => response.json())
            .then(data => {
                maitreSelect.innerHTML = '';

                if (data.length === 0) {
                    maitreSelect.innerHTML = '<option value="">Aucun employé trouvé</option>';
                    return;
                }

                data.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.id;
                    option.textContent = `${emp.nom} ${emp.prenom}`;
                    maitreSelect.appendChild(option);
                });

                // Auto‑sélection si un seul employé
                if (data.length === 1) {
                    maitreSelect.value = data[0].id;
                }
            });
    });

});
</script>

</div>
@endsection

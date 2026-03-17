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
                        <select name="entreprise_id" required>
                            @foreach($entreprises as $entreprise)
                                <option value="{{ $entreprise->id }}" {{ old('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                    {{ $entreprise->nom }}
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
                        <select name="maitre_de_stage_id" required>
                            @foreach($employes as $employe)
                                <option value="{{ $employe->id }}" {{ old('maitre_de_stage_id') == $employe->id ? 'selected' : '' }}>
                                    {{ $employe->nom }} {{ $employe->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label">Étudiant</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="etudiant_id">
                            <option value="">Aucun</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('etudiant_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
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

</div>
@endsection

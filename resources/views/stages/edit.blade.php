@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title mb-5">Modifier le stage</h1>

    <form action="{{ route('stages.update', $stage) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Entreprise --}}
        <div class="field">
            <label class="label">Entreprise</label>
            <div class="control">
                <div class="select is-fullwidth is-primary">
                    <select name="entreprise_id" required>
                        @foreach($entreprises as $entreprise)
                            <option value="{{ $entreprise->id }}"
                                {{ old('entreprise_id', $stage->entreprise_id) == $entreprise->id ? 'selected' : '' }}>
                                {{ $entreprise->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Maître de stage --}}
        <div class="field">
            <label class="label">Maître de stage</label>
            <div class="control">
                <div class="select is-fullwidth is-primary">
                    <select name="maitre_de_stage_id" required>
                        @foreach($tuteurs as $employe)
                            <option value="{{ $employe->id }}"
                                {{ old('maitre_de_stage_id', $stage->maitre_de_stage_id) == $employe->id ? 'selected' : '' }}>
                                {{ $employe->nom }} {{ $employe->prenom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Date de début --}}
        <div class="field">
            <label class="label">Date de début</label>
            <div class="control">
                <input type="date" name="date_debut" class="input"
                       value="{{ old('date_debut', $stage->date_debut) }}" required>
            </div>
        </div>

        {{-- Durée du stage --}}
        <div class="field">
            <label class="label">Durée du stage</label>
            <div class="control">
                <div class="select is-fullwidth is-primary">
                    <select name="duree" required>
                        @foreach([1,2,3,4,6] as $semaines)
                            <option value="{{ $semaines }}"
                                {{ old('duree', $stage->duree) == $semaines ? 'selected' : '' }}>
                                {{ $semaines }} semaines
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Étudiant --}}
        <div class="field">
            <label class="label">Étudiant</label>
            <div class="control">
                <div class="select is-fullwidth is-primary">
                    <select name="etudiant_id">
                        <option value="">Aucun</option>
                        @foreach($etudiants as $user)
                            <option value="{{ $user->id }}"
                                {{ old('etudiant_id', $stage->etudiant_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Boutons --}}
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
@endsection

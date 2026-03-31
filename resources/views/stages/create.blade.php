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

            {{-- L'entreprise est déjà connue --}}
            <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">

            {{-- Classe automatique --}}
            <input type="hidden" name="classe" value="{{ auth()->user()->classe }}">

            {{-- Étudiant automatique --}}
            <input type="hidden" name="etudiant_id" value="{{ auth()->id() }}">

            {{-- Maître de stage --}}
            <div class="field">
                <label class="label">Maître de stage</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="employe_id" required>
                            <option value="">Sélectionner</option>
                            @foreach($employes as $employe)
                                <option value="{{ $employe->id }}">
                                    {{ $employe->prenom }} {{ $employe->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Date début --}}
            <div class="field">
                <label class="label">Date de début</label>
                <div class="control">
                    <input class="input" type="date" name="date_debut" required>
                </div>
            </div>

            {{-- Durée en semaines --}}
            <div class="field">
                <label class="label">Durée (en semaines)</label>
                <div class="control">
                    <input class="input" type="number" name="duree" min="1" required>
                </div>
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
@endsection


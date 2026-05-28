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

            {{-- Valeurs envoyées au serveur --}}
            <input type="hidden" name="entreprise_id" value="{{ $entreprise->id }}">
            <input type="hidden" name="classe" value="{{ auth()->user()->classe }}">
            <input type="hidden" name="etudiant_id" value="{{ auth()->id() }}">

            {{-- Entreprise --}}
            <div class="field">
                <label class="label">Entreprise</label>
                <div class="control">
                    <input class="input is-info-light" type="text" 
                           value="{{ $entreprise->raison_sociale }}" readonly>
                </div>
            </div>

            {{-- Classe --}}
            <div class="field">
                <label class="label">Classe</label>
                <div class="control">
                    <input class="input is-info-light" type="text" 
                           value="{{ auth()->user()->classe }}" readonly>
                </div>
            </div>

            {{-- Étudiant --}}
            <div class="field">
                <label class="label">Étudiant</label>
                <div class="control">
                    <input class="input is-info-light" type="text" 
                           value="{{ auth()->user()->prenom }} {{ auth()->user()->nom }}" 
                           readonly>
                </div>
            </div>

            {{-- Maître de stage --}}
            <div class="field">
                <label class="label">Maître de stage</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="maitre_de_stage_id" required>
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

            {{-- Durée en semaines (1 à 6) --}}
            <div class="field">
                <label class="label">Durée du stage</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="duree" required>
                            <option value="">Sélectionner</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">{{ $i }} semaine{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>
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

@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Nouvelle entrée — Journal de bord</h1>

    @if($errors->any())
        <div class="notification is-danger">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('stages.journal.store', $stage) }}" method="POST">
            @csrf

            <div class="columns">
                <div class="column is-narrow">
                    <div class="field">
                        <label class="label">Semaine n°</label>
                        <input class="input" type="number" name="semaine" min="1" max="52"
                               value="{{ old('semaine') }}" required>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">Date de début de semaine</label>
                        <input class="input" type="date" name="date_debut_semaine"
                               value="{{ old('date_debut_semaine') }}" required>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label">Activités réalisées</label>
                <textarea class="textarea" name="activites" rows="4" required>{{ old('activites') }}</textarea>
            </div>

            <div class="field">
                <label class="label">Compétences mobilisées</label>
                <textarea class="textarea" name="competences" rows="3">{{ old('competences') }}</textarea>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary">Enregistrer</button>
                </div>
                <div class="control">
                    <a href="{{ route('stages.journal.index', $stage) }}" class="button is-light">Annuler</a>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

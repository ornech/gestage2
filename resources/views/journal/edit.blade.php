@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Modifier l'entrée — Semaine {{ $entry->semaine }}</h1>

    @if($errors->any())
        <div class="notification is-danger">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('stages.journal.update', [$stage, $entry]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label class="label">Activités réalisées</label>
                <textarea class="textarea" name="activites" rows="4" required>{{ old('activites', $entry->activites) }}</textarea>
            </div>

            <div class="field">
                <label class="label">Compétences mobilisées</label>
                <textarea class="textarea" name="competences" rows="3">{{ old('competences', $entry->competences) }}</textarea>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary">Mettre à jour</button>
                </div>
                <div class="control">
                    <a href="{{ route('stages.journal.index', $stage) }}" class="button is-light">Annuler</a>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

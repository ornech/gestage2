@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Créer un stage</h1>

    <form action="{{ route('stages.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Date de début</label>
            <input type="date" name="date_debut" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Date de fin</label>
            <input type="date" name="date_fin" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Maître de stage</label>
            <select name="employe_id" class="form-control" required>
                @foreach($employes as $employe)
                    <option value="{{ $employe->id }}">
                        {{ $employe->nom }} {{ $employe->prenom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Étudiant</label>
            <select name="user_id" class="form-control">
                <option value="">Aucun</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-success">Enregistrer</button>
        <a href="{{ route('stages.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection

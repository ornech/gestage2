@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Modifier le stage</h1>

    <form action="{{ route('stages.update', $stage) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" value="{{ $stage->titre }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ $stage->description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Date de début</label>
            <input type="date" name="date_debut" class="form-control" value="{{ $stage->date_debut }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Date de fin</label>
            <input type="date" name="date_fin" class="form-control" value="{{ $stage->date_fin }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Maître de stage</label>
            <select name="employe_id" class="form-control" required>
                @foreach($employes as $employe)
                    <option value="{{ $employe->id }}" 
                        @if($employe->id == $stage->employe_id) selected @endif>
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
                    <option value="{{ $user->id }}" 
                        @if($user->id == $stage->user_id) selected @endif>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-success">Mettre à jour</button>
        <a href="{{ route('stages.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection

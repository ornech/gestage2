@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Créer un stage</h1>

    <form action="{{ route('stages.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" value="{{ old('titre') }}" required>

        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Date de début</label>
            <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Date de fin</label>
            <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}" required>
        </div>
        <div class="mb-3">
             <label class="form-label">Entreprise</label>
            <select name="entreprise_id" class="form-control" required>
        @foreach($entreprises as $entreprise)
            <option value="{{ $entreprise->id }}" {{ old('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                {{ $entreprise->nom }}
            </option>
        @endforeach
        </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Maître de stage</label>
            <select name="maitre_de_stage_id" class="form-control" required>
                @foreach($employes as $employe)
                    <option value="{{ $employe->id }}" {{ old('maitre_de_stage_id') == $employe->id ? 'selected' : '' }}>
                        {{ $employe->nom }} {{ $employe->prenom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Étudiant</label>
           <select name="etudiant_id" class="form-control">
                <option value="">Aucun</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('etudiant_id') == $user->id ? 'selected' : '' }}>

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

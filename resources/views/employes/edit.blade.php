@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Titre de la page --}}
    <h1 class="mb-4">Modifier un employé</h1>

    {{-- Affichage des erreurs de validation si le formulaire contient des erreurs --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                {{-- Boucle sur chaque erreur et affichage --}}
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire de modification d'un employé existant --}}
    {{-- La méthode PUT est utilisée pour mettre à jour une ressource --}}
    <form action="{{ route('employes.update', $employe) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Champ Nom, pré-rempli avec la valeur actuelle --}}
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom', $employe->nom) }}">
        </div>

        {{-- Champ Prénom --}}
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" id="prenom" class="form-control" value="{{ old('prenom', $employe->prenom) }}">
        </div>

        {{-- Champ Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $employe->email) }}">
        </div>

        {{-- Champ Téléphone --}}
        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text" name="telephone" id="telephone" class="form-control" value="{{ old('telephone', $employe->telephone) }}">
        </div>

        {{-- Champ Entreprise --}}
        <div class="mb-3">
            <label for="entreprise_id" class="form-label">Entreprise</label>
            <select name="entreprise_id" id="entreprise_id" class="form-control">
                @foreach($entreprises as $entreprise)
                    <option value="{{ $entreprise->id }}"
                        {{ old('entreprise_id', $employe->entreprise_id) == $entreprise->id ? 'selected' : '' }}>
                        {{ $entreprise->raison_sociale }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Bouton pour enregistrer les modifications --}}
        <button type="submit" class="btn btn-success">Mettre à jour</button>

        {{-- Bouton pour revenir à la liste des employés --}}
        <a href="{{ route('employes.index') }}" class="btn btn-secondary ms-2">Annuler</a>

    </form>

</div>
@endsection

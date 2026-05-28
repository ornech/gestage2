@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Titre de la page --}}
    <h1 class="mb-4">Ajouter un employé</h1>

    {{-- Affichage des erreurs de validation si le formulaire est incorrect --}}
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

    {{-- Formulaire d'ajout d'un employé --}}
    <form action="{{ route('employes.store', $entreprise_id) }}" method="POST">
        @csrf  {{-- Jeton de sécurité obligatoire dans les formulaires Laravel --}}
            <input type="hidden" name="entreprise_id" value="{{ $entreprise_id }}">

        {{-- Champ Nom --}}
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom') }}">
        </div>

        {{-- Champ Prénom --}}
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" id="prenom" class="form-control" value="{{ old('prenom') }}">
        </div>

        {{-- Champ Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
        </div>

        {{-- Champ Téléphone --}}
        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text" name="telephone" id="telephone" class="form-control" value="{{ old('telephone') }}">
        </div>

     

        {{-- Bouton de validation du formulaire --}}
        <button type="submit" class="btn btn-success">Enregistrer</button>

        {{-- Bouton pour revenir à la liste des employés --}}
        <a href="{{ route('employes.index') }}" class="btn btn-secondary ms-2">Annuler</a>

    </form>

</div>
@endsection

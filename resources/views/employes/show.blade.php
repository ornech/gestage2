@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Détails de l'employé</h1>

    <p><strong>Nom :</strong> {{ $employe->nom }}</p>
    <p><strong>Prénom :</strong> {{ $employe->prenom }}</p>
    <p><strong>Email :</strong> {{ $employe->email }}</p>
    <p><strong>Téléphone :</strong> {{ $employe->telephone }}</p>

    <a href="{{ route('employes.index') }}" class="btn btn-secondary">Retour</a>
</div>
@endsection

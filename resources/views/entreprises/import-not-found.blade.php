@extends('layouts.app')

@section('content')
<div class="container mt-6">

    <h1 class="title">Entreprise introuvable</h1>

    <p>Aucune entreprise n’a été trouvée pour le SIRET : <strong>{{ $siret }}</strong></p>

    <p>Vous pouvez :</p>

    <a href="{{ route('entreprises.create') }}" class="button is-success">
        Créer l’entreprise manuellement
    </a>

    <a href="{{ route('entreprises.import.form') }}" class="button is-light">
        Réessayer avec un autre SIRET
    </a>

</div>
@endsection

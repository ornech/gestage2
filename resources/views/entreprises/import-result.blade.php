@extends('layouts.app')

@section('content')
<div class="container mt-6">

    <h1 class="title">Résultat de l'import</h1>

    <div class="box">
        <p><strong>Raison sociale :</strong> {{ $data['nom'] }}</p>
        <p><strong>Adresse :</strong> {{ $data['adresse'] }}</p>
        <p><strong>Code postal :</strong> {{ $data['cp'] }}</p>
        <p><strong>Ville :</strong> {{ $data['ville'] }}</p>
        <p><strong>SIRET :</strong> {{ $data['siret'] }}</p>
    </div>

    @if ($entreprise)
        <form action="{{ route('entreprises.update', $entreprise) }}" method="POST">
            @csrf
            @method('PUT')
            <button class="button is-warning">Mettre à jour l’entreprise existante</button>
            <input type="hidden" name="raison_sociale" value="{{ $data['nom'] }}">
            <input type="hidden" name="adresse" value="{{ $data['adresse'] }}">
            <input type="hidden" name="code_postal" value="{{ $data['cp'] }}">
            <input type="hidden" name="ville" value="{{ $data['ville'] }}">
            <input type="hidden" name="siret" value="{{ $data['siret'] }}">

        </form>
    @else
        <form action="{{ route('entreprises.store') }}" method="POST">
            @csrf
            <input type="hidden" name="raison_sociale" value="{{ $data['nom'] }}">
            <input type="hidden" name="adresse" value="{{ $data['adresse'] }}">
            <input type="hidden" name="code_postal" value="{{ $data['cp'] }}">
            <input type="hidden" name="ville" value="{{ $data['ville'] }}">
            <input type="hidden" name="siret" value="{{ $data['siret'] }}">
            <button class="button is-success">Créer cette entreprise</button>
        </form>
    @endif

</div>
@endsection

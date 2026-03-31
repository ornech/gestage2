@extends('layouts.app')

@section('content')
<div class="container mt-6">
    <h1 class="title">Créer une entreprise</h1>

    <form action="{{ route('entreprises.store') }}" method="POST">
        @csrf

        <div class="field">
            <label class="label">Raison sociale</label>
            <input class="input" type="text" name="raison_sociale" required>
        </div>

        <div class="field">
            <label class="label">Adresse</label>
            <input class="input" type="text" name="adresse" required>
        </div>

        <div class="field">
            <label class="label">Code postal</label>
            <input class="input" type="text" name="code_postal" required>
        </div>

        <div class="field">
            <label class="label">Ville</label>
            <input class="input" type="text" name="ville" required>
        </div>

        <div class="field">
            <label class="label">SIRET</label>
            <input class="input" type="text" name="siret" required>
        </div>

        <button class="button is-success">Créer l’entreprise</button>
    </form>
</div>
@endsection

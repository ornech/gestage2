@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Import promotion — Pronote</h1>

    @if($errors->any())
        <div class="notification is-danger is-light">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="box">
        <p class="mb-4">
            Exportez la liste des élèves depuis
            <strong>Pronote → Exports → Liste des élèves (CSV ; séparateur point-virgule)</strong>
            puis importez-la ici.
        </p>

        <form action="{{ route('imports.pronote.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="columns">
                <div class="column is-one-quarter">
                    <div class="field">
                        <label class="label">Classe importée</label>
                        <div class="select is-fullwidth">
                            <select name="classe_forcee" required>
                                <option value="">— Choisir —</option>
                                <option value="SIO1">BTS SIO — 1ère année (SIO1)</option>
                                <option value="SIO2">BTS SIO — 2ème année (SIO2)</option>
                            </select>
                        </div>
                        <p class="help">
                            Pronote exporte la même date d'entrée pour SIO1 et SIO2 —
                            la classe doit être précisée manuellement.
                        </p>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">Fichier CSV Pronote</label>
                        <div class="control">
                            <input class="input" type="file" name="fichier" accept=".csv,.txt" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="field mt-2">
                <button type="submit" class="button is-primary">
                    <i class="fas fa-eye mr-2"></i> Prévisualiser
                </button>
                <a href="{{ route('admin.users.index') }}" class="button is-light ml-2">Annuler</a>
            </div>
        </form>
    </div>

</div>
@endsection

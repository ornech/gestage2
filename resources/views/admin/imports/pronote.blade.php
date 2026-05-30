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
            <div class="field">
                <label class="label">Fichier CSV Pronote</label>
                <div class="control">
                    <input class="input" type="file" name="fichier" accept=".csv,.txt" required>
                </div>
            </div>
            <div class="field mt-4">
                <button type="submit" class="button is-primary">
                    <i class="fas fa-eye mr-2"></i> Prévisualiser
                </button>
                <a href="{{ route('admin.users.index') }}" class="button is-light ml-2">Annuler</a>
            </div>
        </form>
    </div>

</div>
@endsection

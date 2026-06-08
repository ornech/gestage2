@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:640px;">

    <div class="level mb-4">
        <div class="level-left">
            <h1 class="title is-4 mb-0">Modifier le maître de stage</h1>
        </div>
        <div class="level-right">
            <a href="{{ route('employes.show', $employe) }}" class="button is-light is-small">← Retour</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="notification is-danger is-light mb-4">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="box">
        <form action="{{ route('employes.update', $employe) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Nom</label>
                        <input class="input is-small" type="text" name="nom" value="{{ old('nom', $employe->nom) }}" required>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label is-small">Prénom</label>
                        <input class="input is-small" type="text" name="prenom" value="{{ old('prenom', $employe->prenom) }}" required>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label is-small">Email</label>
                <input class="input is-small" type="email" name="email" value="{{ old('email', $employe->email) }}">
            </div>

            <div class="field">
                <label class="label is-small">Téléphone</label>
                <input class="input is-small" type="text" name="telephone" value="{{ old('telephone', $employe->telephone) }}">
            </div>

            {{-- Entreprise : information non modifiable depuis ce formulaire --}}
            <div class="field">
                <label class="label is-small">Entreprise</label>
                <input class="input is-small" type="text" value="{{ $employe->entreprise?->raison_sociale ?? '—' }}" readonly disabled>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary is-small">Mettre à jour</button>
                </div>
                <div class="control">
                    <a href="{{ route('employes.show', $employe) }}" class="button is-light is-small">Annuler</a>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

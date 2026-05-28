@extends('layouts.app')

@section('content')
<div class="container">
    <div class="box mt-5">
        <h1 class="title">Modifier mon profil</h1>

        @if($errors->any())
            <div class="notification is-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label class="label">Nom</label>
                <div class="control">
                    <input class="input" type="text" name="nom"
                           value="{{ old('nom', $user->nom) }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Prénom</label>
                <div class="control">
                    <input class="input" type="text" name="prenom"
                           value="{{ old('prenom', $user->prenom) }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Email</label>
                <div class="control">
                    <input class="input" type="email" name="email"
                           value="{{ old('email', $user->email) }}" required>
                </div>
            </div>

            <div class="field">
                <label class="label">Téléphone</label>
                <div class="control">
                    <input class="input" type="text" name="telephone"
                           value="{{ old('telephone', $user->telephone) }}">
                </div>
            </div>

            <div class="field is-grouped mt-4">
                <div class="control">
                    <button type="submit" class="button is-primary">Enregistrer</button>
                </div>
                <div class="control">
                    <a href="{{ route('profile.show') }}" class="button is-light">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container" style="max-width:480px; margin-top:4rem;">
    <div class="box">
        <h1 class="title is-4">
            <i class="fas fa-lock mr-2"></i> Choisissez votre mot de passe
        </h1>
        <p class="mb-4 has-text-grey">
            C'est votre première connexion. Vous devez définir un mot de passe personnel
            avant de continuer.
        </p>

        @if($errors->any())
            <div class="notification is-danger is-light">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form action="{{ route('password.first-change.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label class="label">Nouveau mot de passe</label>
                <div class="control has-icons-left">
                    <input class="input" type="password" name="password"
                           placeholder="8 caractères minimum" required autofocus>
                    <span class="icon is-left"><i class="fas fa-key"></i></span>
                </div>
            </div>

            <div class="field">
                <label class="label">Confirmer le mot de passe</label>
                <div class="control has-icons-left">
                    <input class="input" type="password" name="password_confirmation"
                           placeholder="Répéter le mot de passe" required>
                    <span class="icon is-left"><i class="fas fa-key"></i></span>
                </div>
            </div>

            <div class="field mt-5">
                <button type="submit" class="button is-primary is-fullwidth">
                    <i class="fas fa-check mr-2"></i> Enregistrer et continuer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

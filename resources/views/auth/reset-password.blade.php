@extends('layouts.app')

@section('content')
<div class="container" style="max-width:480px; margin-top:4rem;">
    <div class="box">
        <h1 class="title is-4">
            <i class="fas fa-lock mr-2"></i> Choisissez votre mot de passe
        </h1>

        @if($errors->any())
            <div class="notification is-danger is-light">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                <p class="mt-2"><a href="{{ route('password.request') }}">Demander un nouveau lien</a></p>
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ old('email', $email) }}">

            <p class="mb-4 has-text-grey">Compte : <strong>{{ old('email', $email) }}</strong></p>

            <div class="field">
                <label class="label">Nouveau mot de passe</label>
                <div class="control has-icons-left">
                    <input class="input" type="password" name="password"
                           placeholder="8 caractères minimum" required autofocus autocomplete="new-password">
                    <span class="icon is-left"><i class="fas fa-key"></i></span>
                </div>
            </div>

            <div class="field">
                <label class="label">Confirmer le mot de passe</label>
                <div class="control has-icons-left">
                    <input class="input" type="password" name="password_confirmation"
                           placeholder="Répéter le mot de passe" required autocomplete="new-password">
                    <span class="icon is-left"><i class="fas fa-key"></i></span>
                </div>
            </div>

            <div class="field mt-5">
                <button type="submit" class="button is-primary is-fullwidth">
                    <i class="fas fa-check mr-2"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

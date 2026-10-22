@extends('layouts.app')

@section('content')
<div class="container" style="max-width:480px; margin-top:4rem;">
    <div class="box">
        <h1 class="title is-4">
            <i class="fas fa-key mr-2"></i> Mot de passe oublié
        </h1>

        @if(session('status'))
            <div class="notification is-success is-light">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="notification is-danger is-light">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <p class="mb-4 has-text-grey">
            Saisissez l'adresse email de votre compte : vous recevrez un lien pour choisir un nouveau mot de passe.
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="field">
                <label class="label">Email</label>
                <div class="control has-icons-left">
                    <input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    <span class="icon is-left"><i class="fas fa-envelope"></i></span>
                </div>
            </div>
            <div class="field mt-5">
                <button type="submit" class="button is-primary is-fullwidth">Envoyer le lien</button>
            </div>
        </form>

        <p class="mt-4 has-text-centered"><a href="{{ route('login') }}">Retour à la connexion</a></p>
    </div>
</div>
@endsection

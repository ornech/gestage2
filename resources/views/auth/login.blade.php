@extends('layouts.app')

@section('content')
<section class="hero is-fullheight-with-navbar">
    <div class="hero-body">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-5-tablet is-4-desktop is-3-widescreen">
                    
                    <div class="box shadow-lg">
                        <h1 class="title has-text-centered has-text-grey-dark">Connexion</h1>
                        <hr>

                        <form method="POST" action="/login">
                            @csrf

                            {{-- Champ Email --}}
                            <div class="field">
                                <label class="label">Email</label>
                                <div class="control has-icons-left">
                                    <input 
                                        class="input @error('email') is-danger @enderror" 
                                        type="email" 
                                        name="email" 
                                        placeholder="nom@exemple.com" 
                                        value="{{ old('email') }}" 
                                        required
                                    >
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                                @error('email')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Champ Mot de passe --}}
                            <div class="field">
                                <label class="label">Mot de passe</label>
                                <div class="control has-icons-left">
                                    <input 
                                        class="input @error('password') is-danger @enderror" 
                                        type="password" 
                                        name="password" 
                                        required
                                    >
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <p class="help is-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Bouton de validation --}}
                            <div class="field mt-5">
                                <button type="submit" class="button is-primary is-fullwidth is-medium">
                                    <strong>Se connecter</strong>
                                </button>
                            </div>
                        </form>
                    </div>

                    <p class="has-text-centered mt-4">
                        <a href="/password/reset" class="has-text-grey">Mot de passe oublié ?</a>
                    </p>
                    <p class="mt-2">
                        Pas de compte ?
                        <a href="{{ route('register') }}">Inscrivez‑vous</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
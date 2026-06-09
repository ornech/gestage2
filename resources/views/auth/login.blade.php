@extends('layouts.app')

@section('content')
<section class="hero is-fullheight-with-navbar">
    <div class="hero-body">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-8-mobile is-6-tablet is-5-desktop is-4-widescreen">

                    <div class="box shadow-lg">
                        <div class="has-text-centered mb-4">
                            <p class="is-size-2 has-text-weight-bold mb-0"
                               style="background:linear-gradient(90deg, #485fc7, #00d1b2); -webkit-background-clip:text; background-clip:text; color:transparent; letter-spacing:.15rem;">
                                BTS SIO
                            </p>
                            <span style="display:inline-block; width:56px; height:3px; border-radius:2px; background:linear-gradient(90deg, #485fc7, #00d1b2); margin:.4rem 0 .6rem;"></span>
                            <p class="is-size-6 has-text-grey" style="letter-spacing:.25rem; text-transform:uppercase;">
                                Gestion stage
                            </p>
                        </div>
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


                </div>
            </div>
        </div>
    </div>
</section>
@endsection